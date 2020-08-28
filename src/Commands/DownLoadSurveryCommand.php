<?php


namespace clk528\NyuJiaoWei\Commands;


use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DownLoadSurveryCommand extends Command
{
    protected $signature = 'jiaowei:downlaod-suvery';

    protected $description = '导入安全培训人员名单';

    private $client;

    private $redisKey = 'progressId';

    private $token;

    private $surverId;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'base_uri' => 'https://ca1.qualtrics.com'
        ]);

        $this->token = config('jiaowei.token');
        $this->surverId = config('jiaowei.surver_id');
    }

    function handle()
    {
        if (empty($this->token) && empty($this->surverId)) {
            $this->error("错误：请配置Token和surverId");
            return;
        }
        $progressId = \Cache::tags('SURVERYS')->get($this->redisKey);

        if (empty($progressId)) {
            $start = $this->startExport($this->surverId);
            \Cache::tags('SURVERYS')->set('progressId', $start['result']['progressId']);
            $this->info('开始发起导入，请稍后再来查询进度。。。。。。');
            return;
        }


        $progress = $this->getProgress($this->surverId, $progressId);

        if ($progress['result']['percentComplete'] != 100) {
            $this->warn("$progressId 还在导出中，请稍后再试。。。。。。");
            return;
        }

        $fileId = $progress['result']['fileId'];

        $this->info("fileId:{$fileId}开始下载");

        $this->downloadFile($this->surverId, $fileId);
    }

    public function startExport($surveyId)
    {

        $result = $this->client->post("/API/v3/surveys/$surveyId/export-responses", [
            'headers' => [
                'X-API-TOKEN' => $this->token
            ],
            'json' => [
                'format' => 'json',
                'compress' => false,
            ]
        ]);

        return json_decode($result->getBody()->getContents(), true);
    }

    public function getProgress($surveyId, $exportProgressId)
    {
        $result = $this->client->get("/API/v3/surveys/{$surveyId}/export-responses/{$exportProgressId}", [
            'headers' => [
                'X-API-TOKEN' => $this->token
            ]
        ]);

        return json_decode($result->getBody()->getContents(), true);
    }

    public function downloadFile($surveyId, $fileId)
    {
        $file = storage_path('logs/' . time() . '.json');

        $result = $this->client->get("/API/v3/surveys/{$surveyId}/export-responses/{$fileId}/file", [
            'headers' => [
                'X-API-TOKEN' => $this->token
            ],
            'save_to' => $file
        ]);
        $this->info("下载完成；文件路径：{$file}");
        \Cache::tags('SURVERYS')->forget($this->redisKey);
    }
}
