<?php

namespace clk528\NyuJiaoWei\Traits;

use App\Services\AccessService;
use clk528\NyuJiaoWei\Models\NyuStudent;
use clk528\NyuJiaoWei\Models\NyuSurvey;

trait JiaoweiTrait
{
    /**
     * @var AccessService
     */
    private $accessService;

    private $app;

    private $client;

    /**
     * 禁用一个netId对全部权限
     * @param $netid
     */
    private function decrAccess($netid)
    {
        $this->accessService->decrAllAccessTimeByNetId($netid, '2020-09-01 12:00:00', '系统禁用:未进行入学申报或未通过安全培训');
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
        $response = $this->client->post('/api/realname/realnameUser/queryByNetIds', [
            'json' => [
                'netIds' => [
                    $netId
                ]
            ]
        ]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->info("第{$this->index}个人，NetId:{$netId}申报结果:||" . json_encode($result, JSON_UNESCAPED_UNICODE));
        $data = $result['result'] ?? [];
        if ($data) {
            $user = $data[0];
            return !$user['health'] && !$user['tourCode'] && !$user['healthCode'];
        } else {
            return false;
        }
    }


    /**
     * 发送微信消息
     * @param string $netId
     * @param string $message
     */
    private function sendWeChatMessage(string $netId, string $message)
    {
        $this->app->messenger->toUser($netId)->send($message);
    }
}