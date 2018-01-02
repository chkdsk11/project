<?php
namespace Shop\Home\Datas;
use Shop\Home\Datas\BaseData;

class BaiyangCpsData extends BaseData
{
    protected static $instance = null;

    /**
     * @desc 推送CPS（放到缓存异步推送）
     * @param array $param
     * @return array [] 结果信息
     * @author  柯琼远
     */
    public function pushCps($param) {
        if (!empty($param['inviteCode'])) {
            $redis = $this->cache;
            $redis->selectDb(6);
            $redis->rPush(\Shop\Models\CacheKey::CPS_ORDER_KEY, [
                'orderId' => $param['orderSn'],
                'inviteCode' => $param['inviteCode']
            ]);
        }
        return true;
    }

    /**
     * 根据用户ID获取渠道号
     * @param $userId
     * @return bool|string
     * @author  CSL
     * @date 2017.9.13
     */
    public function getUserChannelId($userId)
    {
        //用户绑定渠道ID
        $bindChannelId = false;/*$this->getData([
            'column' => 'cu.channel_id',
            'table' => '\Shop\Models\BaiyangUser as u ',
            'join' => 'INNER JOIN \Shop\Models\BaiyangCpsUserChannel as cuh ON u.id = cuh.user_id and u.invite_code = cuh.invite_code '
                . 'INNER JOIN \Shop\Models\BaiyangCpsUser as cu ON cuh.invite_code = cu.invite_code',
            'where' => 'where cuh.user_id = :user_id:',
            'bind' => [
                'user_id' => $userId,
            ],
        ],1);*/
        //用户推广员所在渠道ID
        $userChannelId = $this->getData([
            'column' => 'cu.channel_id',
            'table' => '\Shop\Models\BaiyangUser as u ',
            'join' => 'INNER JOIN \Shop\Models\BaiyangCpsUser as cu ON (u.phone = cu.user_id OR u.user_id = cu.user_id) ',
            'where' => 'where u.id = :user_id:',
            'bind' => [
                'user_id' => $userId,
            ],
        ], 1);
        if (isset($bindChannelId['channel_id']) && isset($userChannelId['channel_id'])) {
            return $bindChannelId['channel_id'] . ',' . $userChannelId['channel_id'];
        } elseif (isset($bindChannelId['channel_id']) && !isset($userChannelId['channel_id'])) {
            return $bindChannelId['channel_id'];
        } elseif (!isset($bindChannelId['channel_id']) && isset($userChannelId['channel_id'])) {
            return $userChannelId['channel_id'];
        } else {
            return false;
        }
    }
}