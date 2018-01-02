<?php
/**
 * @author 吴俊华
 */
namespace Shop\Home\Datas;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\OrderEnum;

class BaiyangLimitedLogData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取商品参加了限购活动的记录
     * @param array $param
     *       -int user_id 用户id
     * @param array $limitBuy 单个限购活动信息
     * @param int|string $salesId 单品或品牌或分类id，默认为0，兼容全场
     * @param bool $unit 单品、单品牌的限购单位，只能是件
     * @return array|bool [] 用户订单信息|false
     * @author 吴俊华
     */
    public function getLimitedLog($param,$limitBuy,$salesId = 0,$unit = false)
    {
        //单品、单品牌时，单位只有件数
        if($unit){
            $limitBuy['limit_unit'] = BaiyangPromotionEnum::UNIT_ITEM;
        }
        //订单在哪个平台的绑定条件
        $platformCondition = $this->returnPlatformCondition($limitBuy);
        //用户历史购买记录
        $userOrderTotalSnArr = $this->getData([
            'column' => 'total_sn',
            'table' => '\Shop\Models\BaiyangOrder',
            'where' => "WHERE channel_subid in({$platformCondition}) AND user_id = {$param['user_id']} "
                . "AND (add_time BETWEEN {$limitBuy['promotion_start_time']} AND {$limitBuy['promotion_end_time']}) "
                . "AND status <> 'canceled' GROUP BY total_sn ",
        ]);
        //默认限购数
        $result = 0;
        if ($userOrderTotalSnArr) {
            $userOrderTotalSnStr = '';
            foreach ($userOrderTotalSnArr as $item) {
                $userOrderTotalSnStr .= "'".$item['total_sn']."',";
            }
            $userOrderTotalSnStr = trim($userOrderTotalSnStr, ',');
            //基础条件
            $condition = [
                'table' => '\Shop\Models\BaiyangLimitedLog',
                'bind' => [
                    'user_id' => $param['user_id'],
                    'activity_id' => $limitBuy['promotion_id']
                ],
                'where' => "where user_id = :user_id: and activity_id = :activity_id: and is_delete = 0 and order_sn in ({$userOrderTotalSnStr})"
            ];

            //单品或品牌或分类的字段
            $column = '';
            //单位为件数时的分组字段,默认为商品id
            $groupColumn = 'goods_id';
            //根据使用范围来定义字段
            switch ($limitBuy['promotion_scope']) {
                case BaiyangPromotionEnum::ALL_RANGE:
                    $column = !empty($salesId) ? 'goods_id': '';
                    break;
                case BaiyangPromotionEnum::CATEGORY_RANGE:
                    $column = !empty($salesId) ? 'category_id': '';
                    break;
                case BaiyangPromotionEnum::BRAND_RANGE:
                    $column = !empty($salesId) ? 'brand_id': '';
                    $groupColumn = 'brand_id';
                    break;
                case BaiyangPromotionEnum::SINGLE_RANGE:
                    $column = !empty($salesId) ? 'goods_id': '';
                    break;
                case BaiyangPromotionEnum::MORE_RANGE:
                    $column = !empty($salesId) ? 'goods_id': '';
                    break;
            }

            if(!empty($column) && !empty($salesId)){
                $condition['where'] .= " and {$column} IN({$salesId})";
            }

            //判断限购单位
            switch ($limitBuy['limit_unit']) {
                case BaiyangPromotionEnum::UNIT_ITEM:
                    $condition['column'] = 'sum(goods_number) as goods_number';
                    $resultArr = $this->getData($condition,true);
                    $result = !empty($resultArr) ? (int)$resultArr['goods_number'] : 0;
                    break;
                case BaiyangPromotionEnum::UNIT_KIND:
                    $cateGoodsId = [];
                    $condition['column'] = 'goods_id';
                    $condition['where'] .= ' group by '.$groupColumn;
                    $resultArr = $this->getData($condition);
                    if(!empty($limitBuy['goodsList'])){
                        $cateGoodsId = array_column($limitBuy['goodsList'],'goods_id');
                    }
                    // 品类的种作特殊处理
                    if(!empty($resultArr) && $limitBuy['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE && !empty($cateGoodsId)){
                        foreach ($resultArr as $key => $value){
                            foreach ($cateGoodsId as $ke => $val){
                                if($value['goods_id'] == $val){
                                    unset($resultArr[$key]);
                                    break;
                                }
                            }
                        }
                        $resultArr = array_values($resultArr);
                    }
                    $result = !empty($resultArr) ? count($resultArr) : 0;
                    break;
                case BaiyangPromotionEnum::UNIT_TIME:
                    $condition['column'] = 'goods_id';
                    $condition['where'] .= ' group by order_sn';
                    $resultArr = $this->getData($condition);
                    $result = !empty($resultArr) ? count($resultArr) : 0;
                    break;
            }
        }
        return ($result < 0) ? 0 : $result;
    }


    /**
     * @desc 返回限购活动作用平台的条件
     * @param array $limitBuy 单个限购活动信息
     *       -int promotion_platform_pc  pc平台
     *       -int promotion_platform_app app平台
     *       -int promotion_platform_wap wap平台
     * @return string [] 限购活动作用平台的条件
     * @author 吴俊华
     */
    private function returnPlatformCondition($limitBuy)
    {
        //默认条件
        $defaultCondiiton = OrderEnum::ANDROID.','.OrderEnum::IOS;
        //平台拼接条件
        $platformCondition = '';
        if($limitBuy['promotion_platform_pc'] == 1){
            $platformCondition .= OrderEnum::PC.',';
        }
        if($limitBuy['promotion_platform_app'] == 1){
            $platformCondition .= OrderEnum::ANDROID.','.OrderEnum::IOS.',';
        }
        if($limitBuy['promotion_platform_wap'] == 1){
            $platformCondition .= OrderEnum::WAP.','.OrderEnum::WECHAT.',';
        }
        if($limitBuy['promotion_platform_wechat'] == 1){
            $platformCondition .= OrderEnum::WAP.','.OrderEnum::WECHAT.',';
        }
        //默认返回app的条件
        return empty($platformCondition) ? $defaultCondiiton : rtrim($platformCondition,',');
    }


}