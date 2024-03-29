<?php

namespace clk528\NyuJiaoWei\Commands;

use App\Console\Commands\BaseCommand;
use App\Services\AccessService;
use clk528\NyuJiaoWei\Models\NyuStudent;
use clk528\NyuJiaoWei\Models\NyuSurvey;
use clk528\NyuJiaoWei\Models\RealNameUser;
use clk528\NyuJiaoWei\Traits\JiaoweiTrait;
use GuzzleHttp\Client;

class DisableAccessCommand extends BaseCommand
{
    use JiaoweiTrait;

    protected $name = "jiaowei:disable-access";

    protected $description = "检查安全培训和入学检查有没有做的人";

    protected $taotal = 0;

    protected $index = 1;

    protected $alertLimit = 2;

    public function __construct(AccessService $accessService)
    {
        parent::__construct();
        $this->accessService = $accessService;
        $this->app = \EasyWeChat::work(config('jiaowei.wechat'));
        $this->fileName = date('Y-m-d-H-i') . '-disable.log';

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
            if ($this->surveryIsSuccess($student->netId) && $this->realNameIsSuccess($student->netId)) { // 都完成了的

                if ($student->status == 'alert') {
                    $student->fill([
                        'status' => 'enabled',//设置正常状态
                        'alert_total' => 0
                    ])->save();
                    $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}已经完成了健康申报和安全培训,恢复它正常状态");
                } else {
                    $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}已经完成了健康申报和安全培训;不需要封禁权限");
                }
            } else {
//                $this->fireInthHole($student);
                $this->kill($student);
            }
            $this->index += 1;
        }
    }

    /**
     * 没完成培训的人，看看是要进行微信提醒还是进行权限禁用
     * @param NyuStudent $student
     */
    private function fireInthHole(NyuStudent $student)
    {
        if ($student->status == "disabled") {//禁用状态
            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成健康申报和安全培训，系统已将其禁用");
            return;
        }

        if ($student->status == "enabled") {//启用状态
            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成健康申报和安全培训，系统对其第1次提醒");
            $student->fill([
                'status' => 'alert',//设置为警告状态
                'alert_total' => 1
            ])->save();
            $this->sendWeChatMessage($student->netId, "Please submit your health declaration form on campus by scanning the dynamic QR code at the reception desk of Public Safety, to restore your campus access.");
            return;
        }

        if ($student->status == "alert") { // 警告状态

            if ($student->alert_total == $this->alertLimit) { // 已经提醒三次了
                //            $this->decrAccess($student->netId);
                $this->disableBadge($student->netId);
                $student->fill([
                    'status' => 'disabled',
                ])->save();
                $this->sendWeChatMessage($student->netId, "Please submit your health declaration form on campus by scanning the dynamic QR code at the reception desk of Public Safety, to restore your campus access.");
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成健康申报和安全培训，并且已经提醒了{$student->alert_total}次,现在对其权限予以封禁");
                return;
            }

            $alertTotal = $student->alert_total + 1;

            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成健康申报和安全培训，进行第{$alertTotal}次提醒");

            $student->fill([
                'status' => 'alert',//设置为警告状态
                'alert_total' => $alertTotal
            ])->save();

            $this->sendWeChatMessage($student->netId, "Please submit your health declaration form on campus by scanning the dynamic QR code at the reception desk of Public Safety, to restore your campus access.");
            return;
        }
    }

    /**
     * @param NyuStudent $student
     */
    private function kill(NyuStudent $student)
    {
        if ($student->status == "disabled") {//禁用状态
            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成健康申报和安全培训，系统已将其禁用");
            return;
        }
        try {
            \DB::beginTransaction();
            $this->decrAccess($student->netId);
//            $this->disableBadge($student->netId);

            $student->fill([
                'status' => 'disabled',
            ])->save();

            $this->warn("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成健康申报和安全培训，对其权限予以封禁");

            $this->sendWeChatMessage($student->netId, "Please submit your health declaration form on campus by scanning the dynamic QR code at the reception desk of Public Safety, to restore your campus access.");
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error("第{$this->index}个人;NetId:{$student->netId}封禁权限出现了异常情况：{$e->getMessage()}");
            $this->error("第{$this->index}个人;NetId:{$student->netId}：{$e->getTraceAsString()}");
        }
    }

    /**
     * 获取所有未被禁用的学生名单
     * @param int $page
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function getNyuStudents($page = 1, $pageSize = 200)
    {
        return NyuStudent::query()->where('status', '<>', 'disabled')->paginate($pageSize, ['*'], 'page', $page);
    }
}
