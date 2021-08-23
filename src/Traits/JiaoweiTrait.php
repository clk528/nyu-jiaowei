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
        $this->accessService->decrAllAccessTimeByNetId($netid, '2020-09-01 12:00:00', '系统禁用:未完成入学申报');
    }

    /**
     * 启用一个netId对全部权限
     * @param $netid
     */
    private function incrAccess($netid)
    {
        $this->accessService->incrAllAccessTimeByNetId($netid, '系统启用:入学申报完成');
    }

    /**
     * 启用一张卡
     * @param $netId
     */
    private function enableBadge($netId)
    {
        $list = [
            $netId
        ];
        $this->accessService->editBadge($list, 'enable', '系统禁用:入学申报完成');
    }

    /**
     * 禁用一张卡
     * @param $netId
     */
    private function disableBadge($netId)
    {
        $list = [
            $netId
        ];
        $this->accessService->editBadge($list, 'disable', '系统禁用:未完成入学申报');
    }

    /**
     * 判断一个netId 是否完成安全培训
     * @param $netid
     * @return bool
     */
    private function surveryIsSuccess($netid)
    {
        return true;
//        return NyuSurvey::query()->where('netId', $netid)->count() > 0;
    }

    /**
     * 判断一个netId是否完成入学申报
     * @param $netId
     * @return bool
     */
    private function realNameIsSuccess($netId, $isEnableMode = false)
    {
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

        // 1: 没有做过健康申报
        if (empty($data)) {
            return false;
        }

        $user = $data[0];

        if (empty($user)) {
            return false;
        }

        if ($isEnableMode) { // 如果是解封模式
            if (!$this->endTime2021($user['endTime'] ?? null, $netId)) {// 返回为false代表未完成入学申报的流程
                return false; // 未完成入学申报
            }
        }

        // 2: endTime 小于2021-02-01
        if (!$isEnableMode) { //如果是禁用模式
            if ($this->endTime2022($user['endTime'] ?? null, $netId)) {// 返回为true符合封禁条件
                $this->info("第{$this->index}个人，NetId:{$netId} endTime为：" . ($user['endTime'] ?? null) . "，小于2021-02-01 需要封禁");
                return false; // 未完成入学申报
            }
        }

        // 3：三个码全部不为false
        return !$user['health'] && !$user['tourCode'] && !$user['healthCode'];
    }

    /**
     * 判断是否符合解封条件
     * @param $endTime
     * @param $netId
     * @return bool
     */
    private function endTime2021($endTime, $netId)
    {
        if (empty($endTime)) { // 没有做过申报
            return false;
        }

        $now = strtotime("2021-08-20 00:00:00");
        $reportTime = strtotime($endTime);

        if ($reportTime >= $now) { // 报告时间大于 2021-08-20
            $this->info("第{$this->index}个人，NetId:{$netId}报告日期为：{$endTime}，符合解封条件");
            return true;
        } else {
            $this->info("第{$this->index}个人，NetId:{$netId}报告日期为：{$endTime}，不符合解封条件");
            return false;
        }
    }

    /**
     * 判断是否需要封禁
     * @param $endTime
     * @param $netId
     * @return bool
     */
    private function endTime2022($endTime, $netId)
    {
        if (empty($endTime)) {
            return true;
        }

        $now = strtotime("2021-02-01 00:00:00");
        $reportTime = strtotime($endTime);

        if ($reportTime >= $now) { // 报告时间大于 2021-02-01
            $this->info("第{$this->index}个人，NetId:{$netId}报告日期为：{$endTime}，报告时间大于2021-02-01 不需要封禁");
            return false;
        } else {
            $this->info("第{$this->index}个人，NetId:{$netId}报告日期为：{$endTime}，报告时间小于2021-02-01 需要封禁");
            return true;
        }
    }

    private function workFlowExpireTime($expireTime, $netId)
    {
        if (empty($expireTime)) {
            return false;
        }

        $currentDate = time();

        $expireDate = strtotime($expireTime);

        if ($currentDate < $expireDate) {
            $this->info("第{$this->index}个人，NetId:{$netId}解封日期为：{$expireTime}，不能解封");
            return true;
        }
        return false;
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
