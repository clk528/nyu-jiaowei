<?php

namespace clk528\NyuJiaoWei\Commands;

use App\Services\AccessService;
use clk528\NyuJiaoWei\Models\NyuSurvey;
use clk528\NyuJiaoWei\Models\RealNameUser;
use Illuminate\Console\Command;

class SyncAccessCommand extends Command
{
    protected $name = "jiaowei:sync-command";

    protected $description = "检查安全培训和入学检查有没有做的人";

    protected $taotal = 0;

    protected $index = 1;

    private AccessService $accessService;

    public function __construct(AccessService $accessService)
    {
        parent::__construct();
        $this->accessService = $accessService;
    }

    function handle()
    {
        $pageSize = 200;
        $page = 1;

        $realNameusers = $this->getRealNameUsers($page, $pageSize);

        $this->total = $realNameusers->total();

        $this->info("共{$realNameusers->total()}条数据，共{$realNameusers->lastPage()}页");

        if ($realNameusers->total() <= 0) {
            $this->question(">>>>>>>>>>退出执行<<<<<<<<<<");
            return;
        }

        $this->runExecute($realNameusers->items());

        for ($pageNo = 2; $pageNo <= $realNameusers->lastPage(); $pageNo++) {
            $data = $this->getRealNameUsers($pageNo, $pageSize);
            $this->runExecute($data->items());
        }
    }


    private function runExecute(array $data)
    {
        foreach ($data as $datum) {
            $this->info("第{$this->index}个人;NetId:{$datum->netid}");

            if (NyuSurvey::query()->where('netId', $datum->netid)->count() <= 0) {
                $this->info("第{$this->index}个人;NetId:{$datum->netid}未进行入学申报或未通过安全培训,将被禁用");
                $this->accessService->decrAllAccessTimeByNetId($datum->netid, '系统禁用:未进行入学申报或未通过安全培训');
            }

            $this->index += 1;
        }
    }

    private function getRealNameUsers($page = 1, $pageSize = 200)
    {
        $query = RealNameUser::query()
            ->where('health_code', 1)
            ->where('tour_code', 1)
            ->where('health', 1);

        return $query->paginate($pageSize, ['netid'], 'page', $page);
    }
}
