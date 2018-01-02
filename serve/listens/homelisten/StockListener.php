<?php
namespace Shop\Home\Listens;
use Shop\Home\Datas\BaiyangGoodsStockChangeLogData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangOrderGoodsReturnData;

class StockListener extends BaseListen {

    /**
     * @desc 同步库存和销量
     * @param string $param
     * @author 柯琼远
     * @return bool
     */
    public function syncStockAndSaleNumber($event, $class, $param) {
        $order_sn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $goodsList = BaiyangGoodsStockChangeLogData::getInstance()->getNotSyncStock($order_sn);
        if (!empty($goodsList)) {
            // 获取平台
            $channel_subid = $goodsList[0]['channel'];
            if (empty($channel_subid)) {
                $channel_subid = $order_sn[0] == "G" ? substr($order_sn, 1, 2) : substr($order_sn, 0, 2);
            }
            $platform = \Shop\Models\OrderEnum::PLATFORM_PC;
            $fieldName = 'virtual_stock_pc';
            switch ($channel_subid) {
                case \Shop\Models\OrderEnum::WECHAT:
                    // $platform = \Shop\Models\OrderEnum::PLATFORM_WECHAT;
                    // $fieldName = 'virtual_stock_wechat';
                    // 微商城和wap共同一个库存
                    $platform = \Shop\Models\OrderEnum::PLATFORM_WAP;
                    $fieldName = 'virtual_stock_wap';
                    break;
                case \Shop\Models\OrderEnum::ANDROID:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_APP;
                    $fieldName = 'virtual_stock_app';
                    break;
                case \Shop\Models\OrderEnum::IOS:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_APP;
                    $fieldName = 'virtual_stock_app';
                    break;
                case \Shop\Models\OrderEnum::WAP:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_WAP;
                    $fieldName = 'virtual_stock_wap';
                    break;
                case \Shop\Models\OrderEnum::PC:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_PC;
                    $fieldName = 'virtual_stock_pc';
                    break;
            }
            // 同步库存和销量到商品表
            $skuDataInstance = BaiyangSkuData::getInstance();
            $redis = $this->cache;
            foreach ($goodsList as $key => $value) {
                $goodsInfo = $skuDataInstance->getSkuInfo($value['goods_id'], $platform);//sales_number,sku_stock
                if ($value['stock_type'] == 1) {
                    $skuStock = $goodsInfo['v_stock'] + $value['change_num'];
                } elseif ($value['stock_type'] == 2) {
                    $skuStock = $goodsInfo['virtual_stock_default'] + $value['change_num'];
                } else {
                    $skuStock = $goodsInfo[$fieldName] + $value['change_num'];
                }
                if (!$skuDataInstance->editStockAndSaleNumber([
                    'sku_id'       => $value['goods_id'],
                    'stock_type'   => $value['stock_type'],
                    'sales_number' => $goodsInfo['sales_number'] + (-$value['change_num']),
                    'sku_stock'    => $skuStock > 0 ? $skuStock : 0,
                    'platform'     => $platform,
                ])) return false;
                $redis->selectDb(6);
                $redis->rPush(\Shop\Models\CacheKey::ES_STOCK_KEY, [
                    'goodsId' => $value['goods_id'],
                    'platform' => $platform
                ]);
            }
            // 把锁定的库存的同步状态更新为1
            if (!BaseData::getInstance()->updateData([
                'table'  => "\\Shop\\Models\\BaiyangGoodsStockChangeLog",
                'column' => "sync = 1,change_reason = 4,sync_time = '" . date('Y-m-d H:i:s') . "'",
                'where'  => "where order_id = '{$order_sn}'",
            ])) return false;
        }
        return true;
    }

    /**
     * @desc 退款恢复库存和销量
     * @param string $param
     * @author 柯琼远
     * @return bool
     * @author Chensonglu edit 20170525
     */
    public function recoverStockAndSaleNumber($event, $class, $param) {
//        $order_sn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
//        $goodsList = BaiyangGoodsStockChangeLogData::getInstance()->getNotSyncStock($order_sn, 1);
        $serviceSn = isset($param['serviceSn']) ? $param['serviceSn'] : '';
        $isRecover = isset($param['isRecover']) ? $param['isRecover'] : true;
        $goodsList = BaiyangOrderGoodsReturnData::getInstance()->getReturnGoods($serviceSn);
        if (!empty($goodsList)) {
            // 获取平台
            $channel_subid = $goodsList[0]['channel'];
            $order_sn = $goodsList[0]['order_sn'];
            if (empty($channel_subid)) {
//                $channel_subid = $order_sn[0] == "G" ? substr($order_sn, 1, 2) : substr($order_sn, 0, 2);
                $channel_subid = $order_sn == "G" ? substr($order_sn, 1, 2) : substr($order_sn, 0, 1);
            }
            $platform = \Shop\Models\OrderEnum::PLATFORM_PC;
            $fieldName = 'virtual_stock_pc';
            switch ($channel_subid) {
                case \Shop\Models\OrderEnum::WECHAT:
                    // $platform = \Shop\Models\OrderEnum::PLATFORM_WECHAT;
                    // $fieldName = 'virtual_stock_wechat';
                    // 微商城和wap共同一个库存
                    $platform = \Shop\Models\OrderEnum::PLATFORM_WAP;
                    $fieldName = 'virtual_stock_wap';
                    break;
                case \Shop\Models\OrderEnum::ANDROID:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_APP;
                    $fieldName = 'virtual_stock_app';
                    break;
                case \Shop\Models\OrderEnum::IOS:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_APP;
                    $fieldName = 'virtual_stock_app';
                    break;
                case \Shop\Models\OrderEnum::WAP:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_WAP;
                    $fieldName = 'virtual_stock_wap';
                    break;
                case \Shop\Models\OrderEnum::PC:
                    $platform = \Shop\Models\OrderEnum::PLATFORM_PC;
                    $fieldName = 'virtual_stock_pc';
                    break;
            }
            // 恢复库存和销量到商品表
            $skuDataInstance = BaiyangSkuData::getInstance();
            $redis = $this->cache;
            foreach ($goodsList as $key => $value) {
                $goodsInfo = $skuDataInstance->getSkuInfo($value['goods_id'], $platform);//sales_number,sku_stock
                if ($value['stock_type'] == 1) {
                    $skuStock = $isRecover ? $goodsInfo['v_stock'] + $value['change_num'] : $goodsInfo['v_stock'] - $value['change_num'];
                } elseif ($value['stock_type'] == 2) {
                    $skuStock = $isRecover ? $goodsInfo['virtual_stock_default'] + $value['change_num'] : $goodsInfo['virtual_stock_default'] - $value['change_num'];
                } else {
                    $skuStock = $isRecover ? $goodsInfo[$fieldName] + $value['change_num'] : $goodsInfo[$fieldName] - $value['change_num'];
                }
                $salesNum = $isRecover ? $goodsInfo['sales_number'] - $value['change_num'] : $goodsInfo['sales_number'] + $value['change_num'];
                if (!$skuDataInstance->editStockAndSaleNumber([
                    'sku_id'       => $value['goods_id'],
                    'stock_type'   => $value['stock_type'],
                    'sales_number' => $salesNum > 0 ?  $salesNum : 0,
                    'sku_stock'    => $skuStock > 0 ? $skuStock : 0,
                    'platform'     => $platform,
                ])) {
                    return false;
                }
                $redis->selectDb(6);
                $redis->rPush(\Shop\Models\CacheKey::ES_STOCK_KEY, [
                    'goodsId' => $value['goods_id'],
                    'platform' => $platform
                ]);
            }
        }
        return true;
    }
}