<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/26 1504
 */
namespace Shop\Home\Datas;

class BaiyangO2OGoodsStockChangeLogData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 根据商品id获取商品库存变化信息  【未同步】
     * @param array $param
     *       -int|string goods_id 商品id(多个以逗号隔开)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getGoodsStockChange($param)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsStockChangeLog',
            'column' => 'goods_id,sum(change_num) as change_num,stock_type,channel',
            'bind' => [],
            'where' => "where sync = 0 and goods_id in(".$param['goods_id'].") group by goods_id,stock_type,channel"
        ];
        return $this->getData($condition);
    }

    /**
     * @desc 获取未同步的商品库存
     * @param array $order_sn
     * @param array $sync
     * @return array [] 结果信息
     * @author 柯琼远
     */
    public function getNotSyncStock($order_sn, $sync = 0) {
        $sync = in_array($sync, [0,1]) ? (int)$sync : 0;
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsStockChangeLog',
            'column' => 'goods_id,change_num,stock_type,channel',
            'bind' => ["order_sn" => $order_sn],
            'where' => "where sync = {$sync} and order_id = :order_sn:"
        ];
        return $this->getData($condition);
    }

    /**
     * @desc 添加到锁定库存
     * @param $param
     * @return bool
     * @author 柯琼远
     */
    public function insertGoodsStockChange($param) {
        $redis = $this->cache;
        $redis->selectDb(6);
        // 锁定库存
        foreach ($param['goodsList'] as $value) {
            $redis->rPush(\Shop\Models\CacheKey::ES_STOCK_KEY, [
                'goodsId' => $value['goods_id'],
                'platform' => $this->config->platform
            ]);
            if (!$this->addData([
                'table' => '\Shop\Models\BaiyangGoodsStockChangeLog',
                'bind'  => array(
                    'order_id'          => $param['orderSn'],
                    'goods_id'          => $value['goods_id'],
                    'change_num'        => -$value['goods_number'],
                    'stock_type'        => $value['stock_type'],
                    'change_reason'     => 1,
                    'sync'              => 0,
                    'change_time'       => date('Y-m-d H:i:s'),
                    'channel'           => $this->config->channel_subid,
                )
            ])) return false;
        }
        foreach ($param['giftList'] as $value) {
            $redis->rPush(\Shop\Models\CacheKey::ES_STOCK_KEY, [
                'goodsId' => $value['goods_id'],
                'platform' => $this->config->platform
            ]);
            if (!$this->addData([
                'table' => '\Shop\Models\BaiyangGoodsStockChangeLog',
                'bind'  => array(
                    'order_id'          => $param['orderSn'],
                    'goods_id'          => $value['goods_id'],
                    'change_num'        => -$value['goods_number'],
                    'stock_type'        => $value['stock_type'],
                    'change_reason'     => 1,
                    'sync'              => 0,
                    'change_time'       => date('Y-m-d H:i:s'),
                    'channel'           => $this->config->channel_subid,
                )
            ])) return false;
        }
        return true;
    }
}