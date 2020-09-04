<?php


namespace clk528\NyuJiaoWei\Commands;


use App\Console\Commands\BaseCommand;
use App\Services\AccessService;
use clk528\NyuJiaoWei\Models\NyuStudent;
use clk528\NyuJiaoWei\Models\NyuSurvey;
use clk528\NyuJiaoWei\Traits\JiaoweiTrait;
use GuzzleHttp\Client;

class EnabledAccessCommand extends BaseCommand
{
    use JiaoweiTrait;

    protected $name = "jiaowei:enable-access";

    protected $description = "检查封禁了权限的人，检查完成情况并对其进行解封";

    protected $taotal = 0;

    protected $index = 1;

    public function __construct(AccessService $accessService)
    {
        parent::__construct();
        $this->accessService = $accessService;
        $this->app = \EasyWeChat::work(config('jiaowei.wechat'));
        $this->fileName = date('Y-m-d-H-i') . '-enable.log';

        $this->logPath = storage_path('logs/checkHealth');

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath);
        }

        $this->client = new Client([
            'base_uri' => 'https://review.shanghai.nyu.edu'
        ]);
    }

    function handle()
    {
        $pageSize = 200;
        $page = 1;

        $students = $this->getNyuStudents($page, $pageSize);

        $this->total = $students->total();

        $this->info("共{$students->total()}条数据，共{$students->lastPage()}页");

        if ($students->total() <= 0) {
            $this->question(">>>>>>>>>>退出执行<<<<<<<<<<");
            return;
        }

        $this->runExecute($students->items());

        for ($pageNo = 2; $pageNo <= $students->lastPage(); $pageNo++) {
            $data = $this->getNyuStudents($pageNo, $pageSize);
            $this->runExecute($data->items());
        }
    }

    /**
     * 执行开始
     * @param array $data
     */
    private function runExecute(array $data)
    {
        foreach ($data as $student) {
            if ($this->surveryIsSuccess($student->netId) && $this->realNameIsSuccess($student->netId)) { // 都完成了
                $this->boom($student);
            } else {
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}没有完成健康申报和安全培训，不符合解封条件");
            }
            $this->index += 1;
        }
    }

    /**
     * 对完成对人进行一系列操作
     * @param NyuStudent $student
     */
    private function boom(NyuStudent $student)
    {
        switch ($student->status) {
            case 'enabled':
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}状态正常跳过");
                break;
            case 'disabled':
                $student->fill([
                    'status' => 'enabled',//恢复状态
                ])->save();
                $this->incrAccess($student->netId);
                $this->sendWeChatMessage($student->netId, "Thanks for your time on the health declaration and safety training! Your access privileges are restored now.");
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}完成了健康申报和安全培训予以解封");
                break;
            case 'alert':
                $student->fill([
                    'status' => 'enabled',//恢复状态
                ])->save();
                $this->incrAccess($student->netId);
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}完成了健康申报和安全培训予以解封");
                break;
            default:
                break;
        }
    }

    /**
     * 获取所有被禁用的学生名单
     * @param int $page
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function getNyuStudents($page = 1, $pageSize = 200)
    {
        return NyuStudent::query()->where('status', 'disabled')->paginate($pageSize, ['*'], 'page', $page);
    }
}
