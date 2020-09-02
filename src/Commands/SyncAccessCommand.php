<?php

namespace clk528\NyuJiaoWei\Commands;

use App\Console\Commands\BaseCommand;
use App\Services\AccessService;
use clk528\NyuJiaoWei\Models\NyuStudent;
use clk528\NyuJiaoWei\Models\NyuSurvey;
use clk528\NyuJiaoWei\Models\RealNameUser;

class SyncAccessCommand extends BaseCommand
{
    protected $name = "jiaowei:sync-command";

    protected $description = "检查安全培训和入学检查有没有做的人";

    protected $taotal = 0;

    protected $index = 1;

    private $accessService;

    private $app;

    public function __construct(AccessService $accessService)
    {
        parent::__construct();
        $this->accessService = $accessService;
        $this->app = \EasyWeChat::work();
        $this->fileName = date('Y-m-d-H-i') . '.log';

        $this->logPath = storage_path('logs/checkHealth');

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath);
        }
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
            if ($this->surveryIsSuccess($student->netId) && $this->realNameIsSuccess($student->netId)) {
                $this->boom($student);
            } else {
                $this->fireInthHole($student);
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
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}完成了入学申报和安全培训予以解封");
                break;
            case 'alert':
                $student->fill([
                    'status' => 'enabled',//恢复状态
                ])->save();
                $this->incrAccess($student->netId);
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}完成了入学申报和安全培训予以解封");
                break;
            default:
                break;
        }
    }

    /**
     * 没完成培训的人，看看是要进行微信提醒还是进行权限禁用
     * @param NyuStudent $student
     */
    private function fireInthHole(NyuStudent $student)
    {
        if ($student->status == "disabled") {//禁用状态
            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成入学申报和安全培训，系统已将其禁用");
            return;
        }

        if ($student->status == "enabled") {//启用状态
            $student->fill([
                'status' => 'alert',//设置为警告状态
                'alert_total' => 1
            ])->save();
            $this->sendWeChatMessage($student->netId, "亲爱的{$student->netId}您好，你的入学申报和安全还未通过！请尽快去完成哦～");
            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成入学申报和安全培训，系统对其第1次提醒");
            return;
        }

        if ($student->status == "alert") { // 警告状态
            if ($student->alert_total == 3) { // 已经提醒三次了
                $student->fill([
                    'status' => 'disabled',
                ])->save();
                $this->decrAccess($student->netId);
                $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成入学申报和安全培训，并且已经提醒了{$student->alert_total}次,现在对其权限予以封禁");
                return;
            }

            $alertTotal = $student->alert_total + 1;

            $student->fill([
                'status' => 'alert',//设置为警告状态
                'alert_total' => $alertTotal
            ])->save();

            $this->sendWeChatMessage($student->netId, "亲爱的{$student->netId}您好，你的入学申报和安全还未通过！请尽快去完成哦～");

            $this->info("第{$this->index}个人的状态:{$student->status};NetId:{$student->netId}还未完成入学申报和安全培训，进行第{$alertTotal}次提醒");

            return;
        }
    }

    /**
     * 发送微信消息
     * @param string $netId
     * @param string $message
     */
    private function sendWeChatMessage(string $netId, string $message)
    {
//        $this->app->messenger->toUser($netId)->send($message);
    }

    /**
     * 禁用一个netId对全部权限
     * @param $netid
     */
    private function decrAccess($netid)
    {
        $this->accessService->decrAllAccessTimeByNetId($netid, '系统禁用:未进行入学申报或未通过安全培训');
    }

    /**
     * 启用一个netId对全部权限
     * @param $netid
     */
    private function incrAccess($netid)
    {
        $this->accessService->incrAllAccessTimeByNetId($netid, '系统启用:入学申报完成以及通过安全培训');
    }

    /**
     * 判断一个netId 是否完成安全培训
     * @param $netid
     * @return bool
     */
    private function surveryIsSuccess($netid)
    {
//        return true;
        return NyuSurvey::query()->where('netId', $netid)->count() > 0;
    }

    /**
     * 判断一个netId是否完成入学申报
     * @param $netId
     * @return bool
     */
    private function realNameIsSuccess($netId)
    {
//        return true;
        return RealNameUser::query()->where([
                'health_code' => 1,// => 1
                'tour_code' => 1,// => 1
                'health' => 1,// => 1
                'netid' => $netId,
            ])->count() > 0;
    }

    /**
     * 获取所有对学生名单
     * @param int $page
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function getNyuStudents($page = 1, $pageSize = 200)
    {
        return NyuStudent::query()->paginate($pageSize, ['*'], 'page', $page);
    }
}
