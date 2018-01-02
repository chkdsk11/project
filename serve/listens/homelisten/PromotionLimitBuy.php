<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/12 0012
 * Time: 上午 11:16
 */
namespace Shop\Home\Listens;
use Shop\Home\Datas\BaiyangPromotionData;
use Shop\Home\Datas\BaiyangLimitedLogData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangGoodsTreatmentData;
use Shop\Models\HttpStatus;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Home\Datas\BaiyangGoodsShoppingCart;

class PromotionLimitBuy extends BaseListen
{
    /**
     * @desc 购物车结算、提交订单时，判断限购商品数
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *      - array [二维数组] limitBuyList 进行中的限购活动
     *      - array [二维数组] goodsList  商品列表，商品属性字段：goods_id、goods_name、brand_id、category_id、goods_number
     *       -string  platform 平台【pc、app、wap】
     *       -int     user_id  用户id (临时用户或真实用户id)
     *       -int     is_temp  是否为临时用户 (1为临时用户、0为真实用户)
     * @return array 结果信息
     *      - bool   error     是否已超过限购数(1或0，1为已超过限购数)
     *      - int    code      状态码
     *      - array [二维数组] data      达到限购数的提示信息|[]
     *          - string limit_scope  限购范围(single:单品 more:多品 brand:品牌 category:品类 all:全场)
     *          - int    limit_number 限购数
     *          - int    limit_unit   限购单位(1:件 2:种 3:次)
     *          - string goods_name   商品名称
     * @author 吴俊华
     */
    public function limitBuy($event,$class,$param)
    {
        $limitBuy = $param['limitBuyList']; //进行中的限购活动
        if(empty($limitBuy)){
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
        }
        $limitInfo = []; //限购信息
        $goodsList = $param['goodsList'];
        // 限购活动匹配购物车的商品
        $this->limitBuyMatchJoinGoods($limitBuy, $goodsList, $param);
        foreach($goodsList as $goodsValue){
            $goodsValue['platform'] = $param['platform'];
            if(isset($param['user_id'])){
                $goodsValue['user_id'] = $param['user_id'];
            }
            if(isset($param['is_temp'])){
                $goodsValue['is_temp'] = $param['is_temp'];
            }
            $result = $this->verifyLimitBuyNumber($limitBuy,$goodsList,$goodsValue);
            if($result['error'] == 1){
                return ['error' => 1,'code' => HttpStatus::OVER_GOOD_LIMIT_BUY_NUM,'data' => [$result['data']['tips']]];
            }
//            if($result['error'] == 1){
//                $limitInfo[] = [
//                    'limit_scope' => $result['data']['limit_scope'],
//                    'limit_number' => $result['data']['limit_number'],
//                    'limit_unit' => $result['data']['limit_unit'],
//                    'goods_name' => $goodsValue['goods_name'],
//                ];
//                continue;
//            }
        }
//        if(!empty($limitInfo)){
//            return ['error' => 1,'code' => HttpStatus::OVER_GOOD_LIMIT_BUY_NUM,'data' => $limitInfo];
//        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
    }

    /**
     * @desc 验证商品是否达到限购数
     * @param array $limitBuyList 进行中的所有限购活动
     * @param array $goodsList 商品列表，商品属性字段：goods_id、goods_name、brand_id、category_id、goods_number
     * @param array $currentGood 当前商品信息，上面的基础上补：platform、user_id、is_temp
     * @return array 结果信息
     *      - int    error  是否错误(0或1)
     *      - int    code   状态码
     *      - array  data   达到限购数的提示信息|[]
     * @return array [] 结果信息
     * @author 吴俊华
     */
    private function verifyLimitBuyNumber($limitBuyList,$goodsList,$currentGood)
    {
        $BaiyangLimitedLogData = BaiyangLimitedLogData::getInstance();
        foreach($limitBuyList as $limitBuy){
            $shoppingCartNums = 0; //购物车数
            $boughtNums = 0; //已购数
            switch ($limitBuy['promotion_scope']) {
                case BaiyangPromotionEnum::SINGLE_RANGE:
                    //验证单品已买数+购物车数
                    if($this->func->isRelatedGoods($limitBuy,$currentGood)){
                        if(!empty($limitBuy['rule_value'])){
                            foreach(json_decode($limitBuy['rule_value'],true) as $ruleValue){
                                if($ruleValue['id'] == $currentGood['goods_id']){
                                    if($currentGood['is_temp'] == 0) {
                                        //真实用户查已购数
                                        $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood, $limitBuy, $ruleValue['id'], true);
                                    }
                                    $shoppingCartNums += (int)$currentGood['goods_number'];
                                    $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy);
                                    $limitBuyGoodsInfo['goodsInfo'][0] = [
                                        'goods_id' => $currentGood['goods_id'],
                                        'brand_id' => $currentGood['brand_id'],
                                        'category_id' => $currentGood['category_id'],
                                    ];
                                    $limitBuyGoodsInfo['type'] = 'single';
                                    $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,true,(int)$ruleValue['promotion_num'],$limitBuyGoodsInfo);
                                    if($limitBuyMsg !== true) {
                                        return $limitBuyMsg;
                                    }
                                }
                            }
                        }
                    }
                    break;

                case BaiyangPromotionEnum::MORE_RANGE:
                    if($this->func->isRelatedGoods($limitBuy,$currentGood)){
                        if(!empty($limitBuy['rule_value'])){
                            //验证多单品
                            if($currentGood['is_temp'] == 0) {
                                //真实用户查已购数
                                $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood, $limitBuy, $limitBuy['condition']);
                            }
                            $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy);
                            if($limitBuy['limit_unit'] != BaiyangPromotionEnum::UNIT_TIME){
                                $shoppingCartNums = $limitBuyGoodsInfo['number'];
                            }
                            $limitBuyGoodsInfo['type'] = 'total';
                            $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,false,0,$limitBuyGoodsInfo);
                            if($limitBuyMsg !== true) {
                                return $limitBuyMsg;
                            }

                            //重置已购数和购物车数
                            $shoppingCartNums = $boughtNums = 0;
                            $limitBuyGoodsInfo = [];
                            //验证多单品的单品
                            foreach(json_decode($limitBuy['rule_value'],true) as $ruleValue){
                                if($ruleValue['id'] == $currentGood['goods_id']){
                                    if($currentGood['is_temp'] == 0) {
                                        //真实用户查已购数
                                        $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood, $limitBuy, $ruleValue['id'], true);
                                    }
                                    $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy,(int)$ruleValue['id']);
                                    $shoppingCartNums = $limitBuyGoodsInfo['number'];
                                    $limitBuyGoodsInfo['goodsInfo'][0] = [
                                        'goods_id' => $currentGood['goods_id'],
                                        'brand_id' => $currentGood['brand_id'],
                                        'category_id' => $currentGood['category_id'],
                                    ];
                                    $limitBuyGoodsInfo['type'] = 'single';
                                    $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,true,(int)$ruleValue['promotion_num'],$limitBuyGoodsInfo);
                                    if($limitBuyMsg !== true) {
                                        return $limitBuyMsg;
                                    }
                                }
                            }

                        }
                    }
                    break;

                case BaiyangPromotionEnum::BRAND_RANGE:
                    //验证品牌已买数+购物车数
                    if($this->func->isRelatedGoods($limitBuy,$currentGood)){
                        if(!empty($limitBuy['rule_value'])){
                            //验证多品牌
                            if($currentGood['is_temp'] == 0) {
                                //真实用户查已购数
                                $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood, $limitBuy, $limitBuy['condition']);
                            }
                            $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy);
                            if($limitBuy['limit_unit'] != BaiyangPromotionEnum::UNIT_TIME){
                                $shoppingCartNums = $limitBuyGoodsInfo['number'];
                            }
                            $limitBuyGoodsInfo['type'] = 'total';
                            $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,false,0,$limitBuyGoodsInfo);
                            if($limitBuyMsg !== true) {
                                return $limitBuyMsg;
                            }

                            //重置已购数和购物车数
                            $shoppingCartNums = $boughtNums = 0;
                            $limitBuyGoodsInfo = [];
                            //验证单品牌
                            foreach(json_decode($limitBuy['rule_value'],true) as $ruleValue){
                                if($ruleValue['id'] == $currentGood['brand_id']){
                                    if($currentGood['is_temp'] == 0) {
                                        //真实用户查已购数
                                        $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood, $limitBuy, $ruleValue['id'], true);
                                    }
                                    $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy,(int)$ruleValue['id']);
                                    $shoppingCartNums = $limitBuyGoodsInfo['number'];
                                    $limitBuyGoodsInfo['goodsInfo'][0] = [
                                        'goods_id' => $currentGood['goods_id'],
                                        'brand_id' => $currentGood['brand_id'],
                                        'category_id' => $currentGood['category_id'],
                                    ];
                                    $limitBuyGoodsInfo['type'] = 'single';
                                    $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,true,(int)$ruleValue['promotion_num'],$limitBuyGoodsInfo);
                                    if($limitBuyMsg !== true) {
                                        return $limitBuyMsg;
                                    }
                                }
                            }

                        }
                    }
                    break;

                case BaiyangPromotionEnum::CATEGORY_RANGE:
                    if($this->func->isRelatedGoods($limitBuy,$currentGood)){
                        //验证分类已买数+购物车数
                        if($currentGood['is_temp'] == 0) {
                            //真实用户查已购数
                            $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood,$limitBuy,$limitBuy['condition']);
                        }
                        $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy);
                        if($limitBuy['limit_unit'] != BaiyangPromotionEnum::UNIT_TIME){
                            $shoppingCartNums = $limitBuyGoodsInfo['number'];
                        }
                        $limitBuyGoodsInfo['type'] = 'total';
                        $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,false,0,$limitBuyGoodsInfo);
                        if($limitBuyMsg !== true) {
                            return $limitBuyMsg;
                        }
                    }
                    break;

                case BaiyangPromotionEnum::ALL_RANGE:
                    if($this->func->isRelatedGoods($limitBuy,$currentGood)){
                        //验证全场已买数+购物车数
                        if($currentGood['is_temp'] == 0){
                            //真实用户查已购数
                            $boughtNums = $BaiyangLimitedLogData->getLimitedLog($currentGood,$limitBuy);
                        }
                        $limitBuyGoodsInfo = $this->getShoppingCartNum($currentGood,$limitBuy['goodsList'],$limitBuy);
                        if($limitBuy['limit_unit'] != BaiyangPromotionEnum::UNIT_TIME){
                            $shoppingCartNums = $limitBuyGoodsInfo['number'];
                        }
                        $limitBuyGoodsInfo['type'] = 'total';
                        $limitBuyMsg = $this->returnLimitBuyMsg($limitBuy,$boughtNums,$shoppingCartNums,false,0,$limitBuyGoodsInfo);
                        if($limitBuyMsg !== true) {
                            return $limitBuyMsg;
                        }
                    }
                    break;

                default:
                    return ['error' => 1,'code' => HttpStatus::SYSTEM_ERROR,'data' => []];
                    break;
            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
    }

    /**
     * @desc 加入购物车、修改购物车商品数量时，判断限购商品数
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array
     *       -int     goods_id 当前操作的商品id
     *       -int     goods_number 当前操作的商品数量
     *       -string  platform 平台【pc、app、wap】
     *       -int     user_id 用户id (临时用户或真实用户id)
     *       -int     is_temp 是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 结果信息
     *       -int     error  是否错误(0或1)
     *       -int     code   状态码
     *       -array [一维数组]  data   达到限购数的提示信息|[]
     *          - string limit_scope  限购范围(single:单品 more:多品 brand:品牌 category:品类 all:全场)
     *          - int    limit_number 限购数
     *          - int    limit_unit   限购单位(1:件 2:种 3:次)
     *          - string goods_name   商品名称
     * @author 吴俊华
     */
    public function countLimitNumToCart($event,$class,$param)
    {
        //进行中的限购活动
        $limitBuy = $this->getProcessingPromotions($event,$class,['platform' => $param['platform'], 'user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'promotion_type' => BaiyangPromotionEnum::LIMIT_BUY]);
        if(empty($limitBuy)){
            return ['error' => 0,'code' => HttpStatus::NOT_PROCESSING_LIMIT_BUY,'data' => []];
        }
        //加入购物车或+商品数量操作的目标商品(品牌、分类等信息)
        $goalGoodInfo = $this->getGoodsDetail($param);
        if(empty($goalGoodInfo)){
            return ['error' => 1,'code' => HttpStatus::NOT_GOOD_INFO,'data' => []];
        }

        //目标商品信息合并品牌、分类
        if(isset($goalGoodInfo[0]['category_id']) && isset($goalGoodInfo[0]['category_id'])){
            $param['category_id'] = $goalGoodInfo[0]['category_id'];
            $param['brand_id'] = $goalGoodInfo[0]['brand_id'];
        }
        //目标商品匹配限购活动
        $goalGoodPromotion = $this->goodMatchPromotion($goalGoodInfo,$limitBuy);
        if(empty($goalGoodPromotion)){
            return ['error' => 0,'code' => HttpStatus::NOT_GOOD_LIMIT_BUY,'data' => []];
        }
        //获取当前用户的购物车信息
        $shoppingCartItems = BaiyangGoodsShoppingCart::getInstance()->getCartGoodsInfo(['user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'group_id' => 0,'selected' => 1,'is_global' => 0]);
        //最终购物车需要验证的信息
        $shoppingCartGoods = [];
        //购物车不为空时
        if(!empty($shoppingCartItems)) {
            //购物车的商品信息
            $shoppingCartGoodsInfo = $this->getGoodsDetail(['goods_id' => implode(',',array_column($shoppingCartItems,'goods_id')),'platform' => $param['platform']]);
            if (empty($shoppingCartGoodsInfo)){
                return ['error' => 1,'code' => HttpStatus::NOT_CART_GOODS,'data' => []];
            }
            //合并购物车和商品信息
            foreach ($shoppingCartGoodsInfo as $value) {
                foreach ($shoppingCartItems as $val) {
                    if ($value['id'] == $val['goods_id'] && $val['group_id'] == 0) {
                        $shoppingCartGoods[] = [
                            'goods_id' => $val['goods_id'],
                            'category_id' => $value['category_id'],
                            'brand_id' => $value['brand_id'],
                            'group_id' => $val['group_id'],
                            'goods_number' => $val['goods_number'],
                            'is_global' => $val['is_global'],
                        ];
                    }
                }
            }

            $verify = false;
            //如果当前商品已存在购物车里,则商品数量要相加
            foreach ($shoppingCartGoods as $key => $value) {
                if ($shoppingCartGoods[$key]['goods_id'] == $param['goods_id']) {
                    $shoppingCartGoods[$key]['goods_number'] += $param['goods_number'];
                    $param['goods_number'] = $shoppingCartGoods[$key]['goods_number'];
                    $verify = true;
                }
            }
            //目标商品不存在于购物车时,合并到购物车里面去
            if ($verify === false) {
                $newParam = [
                    count($shoppingCartGoods) => [
                        'goods_id' => $param['goods_id'],
                        'category_id' => $param['category_id'],
                        'brand_id' => $param['brand_id'],
                        'goods_number' => $param['goods_number'],
                        'group_id' => 0
                    ]
                ];
                $shoppingCartGoods = array_merge($shoppingCartGoods, $newParam);
            }

        }else{
            //购物车为空时，把目标商品放进购物车的数组里
            $goalGoodInfo[0]['group_id'] = 0;
            $goalGoodInfo[0]['goods_id'] = $goalGoodInfo[0]['id'];
            unset($goalGoodInfo[0]['id']);
            $shoppingCartGoods = $goalGoodInfo;
        }

        //排除疗程商品
//        foreach($shoppingCartGoods as $key => $value){
//            $getGoodsTreatment = BaiyangGoodsTreatmentData::getInstance()->getGoodsTreatment
//            ([
//                'platform' => $param['platform'],
//                'goods_id' =>  $value['goods_id'],
//                'goods_number' => $value['goods_number'],
//            ]);
//            if(!empty($getGoodsTreatment)){
//                unset($shoppingCartGoods[$key]);
//            }
//        }
        sort($shoppingCartGoods);
        // 限购活动匹配购物车的商品
        $this->limitBuyMatchJoinGoods($limitBuy, $shoppingCartGoods, $param);
        
        //验证不同范围的已买数+购物车数是否大于限购数
        if(!empty($param['user_id'])){
            $result = $this->verifyLimitBuyNumber($limitBuy,$shoppingCartGoods,$param);
            if($result['error'] == 1){
                return ['error' => 1,'code' => HttpStatus::OVER_GOOD_LIMIT_BUY_NUM,'data' => [$result['data']['tips']]];
            }
//            if($result['error'] == 1){
//                $result['data']['goods_name'] = $goalGoodInfo[0]['name'];
//                return $result;
//            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
    }

    /**
     * @desc 计算不同限购单位时,购物车里的件数、种数 [单品使用范围不需要这步判断]
     * @param array $param 目标商品信息
     * @param array $shoppingCart 购物车信息
     * @param array $limitBuy 限购活动信息
     * @param int $single 单品牌id或单品id
     * @return array [] (number、sales_id) 各个端商品的已购件数/种数和触发的商品/品牌id
     * @author 吴俊华
     */
    private function getShoppingCartNum($param,$shoppingCart,$limitBuy,$single = 0)
    {
        //单品、单品牌时，单位只有件数 (针对多单品的单品和多品牌的单品牌)
        if(($limitBuy['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE || $limitBuy['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE) && $single != 0){
            $limitBuy['limit_unit'] = BaiyangPromotionEnum::UNIT_ITEM;
        }
        //单品、单品牌时,条件改为当前的id(针对多单品的单品和多品牌的单品牌)
        if(($limitBuy['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE || $limitBuy['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE) && $single != 0){
            $limitBuy['condition'] = $single;
        }

        $salesId = 0; //商品或品牌或分类id
        $newShoppingCart = []; //排除使用范围以外的购物车商品信息
        $goodsSalesIds = []; //参加限购活动的商品/品牌/品类id
        //根据不同使用范围,排除使用范围以外的商品
        switch ($limitBuy['promotion_scope']) {
            case BaiyangPromotionEnum::ALL_RANGE:
                $newShoppingCart = $shoppingCart;
                $goodsSalesIds = $this->getGoodsSalesId($newShoppingCart);
                $salesId = $this->getSalesId($limitBuy,$newShoppingCart);
                break;
            case BaiyangPromotionEnum::CATEGORY_RANGE:
                foreach($shoppingCart as $key => $value){
                    if($value['category_id'] == $limitBuy['condition']){
                        if($limitBuy['limit_unit'] == BaiyangPromotionEnum::UNIT_KIND){
                            $newShoppingCart[$value['goods_id']] = $value;
                        }else{
                            $newShoppingCart[] = $value;
                        }
                    }
                }
                $goodsSalesIds = $this->getGoodsSalesId($newShoppingCart);
                $salesId = $this->getSalesId($limitBuy,$newShoppingCart);
                break;
            case BaiyangPromotionEnum::BRAND_RANGE:
                $conditionArr = explode(',',$limitBuy['condition']);
                foreach($shoppingCart as $key => $value){
                    if(in_array($value['brand_id'],$conditionArr)){
                        if($limitBuy['limit_unit'] == BaiyangPromotionEnum::UNIT_KIND){
                            //限购单位为种时,相同的只能存在一个品牌
                            $newShoppingCart[$value['brand_id']] = $value;
                        }else{
                            $newShoppingCart[] = $value;
                        }
                    }
                }
                $goodsSalesIds = $this->getGoodsSalesId($newShoppingCart);
                $salesId = $this->getSalesId($limitBuy,$newShoppingCart);
                break;
            case BaiyangPromotionEnum::MORE_RANGE:
                $conditionArr = explode(',',$limitBuy['condition']);
                foreach($shoppingCart as $key => $value){
                    if(in_array($value['goods_id'],$conditionArr)){
                        if($limitBuy['limit_unit'] == BaiyangPromotionEnum::UNIT_KIND){
                            //限购单位为种时,相同的只能存在一个商品
                            $newShoppingCart[$value['goods_id']] = $value;
                        }else{
                            $newShoppingCart[] = $value;
                        }
                    }
                }
                $goodsSalesIds = $this->getGoodsSalesId($newShoppingCart);
                $salesId = $this->getSalesId($limitBuy,$newShoppingCart);
                break;
            case BaiyangPromotionEnum::SINGLE_RANGE:
                $goodsSalesIds = $this->getGoodsSalesId($shoppingCart);
        }

        //购物车里默认的件数、种数
        $number = 0;
        //根据不同限购单位来计算购物车的件数、种数
        switch ($limitBuy['limit_unit']) {
            case BaiyangPromotionEnum::UNIT_ITEM:
                //购物车和目标商品的件量相加
                $number = array_sum(array_column($newShoppingCart,'goods_number'));
                break;

            case BaiyangPromotionEnum::UNIT_KIND:
                //购物车和目标商品的种量相加
                $number = count($newShoppingCart);
                if($salesId == 0){
                    $itemNums = 0;
                }else{
                    $itemNums = BaiyangLimitedLogData::getInstance()->getLimitedLog($param,$limitBuy,$salesId);
                }
                //已购商品种数排除购物车和目标商品的相同种数
                $number -= $itemNums;
                break;

            case BaiyangPromotionEnum::UNIT_TIME:
                $number = 0;
                break;
        }
        $number = ($number < 0) ? 0 : $number;
        return ['number' => $number, 'goodsInfo' => $goodsSalesIds];
    }

    /**
     * @desc 验证限购是否满足条件:根据不同使用范围、不同限购单位来返回不同的提示信息
     * @param array $limitBuy 限购信息
     * @param int $boughtNums 已购数
     * @param int $shoppingCartNums 购物车数
     * @param bool $unit 限购单位开关(默认为false,当为true时,限购单位只能是件。兼容单品、单品牌)
     * @param int $limitNums 单品、单品牌的限购件数
     * @param array $limitBuyGoodsInfo 参加限购的商品id/品牌id/分类id
     * @return array|bool []|true 限购提示信息|true
     * @author 吴俊华
     */
    private function returnLimitBuyMsg($limitBuy,$boughtNums = 0,$shoppingCartNums = 0,$unit = false,$limitNums = 0,$limitBuyGoodsInfo = [])
    {
        //单品或单品牌时，限购单位只能件
        if($unit == true){
            $limitBuy['limit_unit'] = BaiyangPromotionEnum::UNIT_ITEM;
            $limitBuy['limit_number'] = $limitNums;
        }
        //限购三种判断，返回限购信息
        //1.已购次数与限购门槛次数相等
        //2.已购件数与限购门槛件数相等
        //3.已购数与购物车数大于限购门槛数
        if($limitBuy['limit_number'] < ($boughtNums + $shoppingCartNums) || ($limitBuy['limit_unit'] != BaiyangPromotionEnum::UNIT_KIND && $limitBuy['limit_number'] == $boughtNums)){
            $tips = $this->generateLimitBuyTips($limitBuy, $limitBuyGoodsInfo);
            return [
                'error' => 1,
                'code' => HttpStatus::OVER_GOOD_LIMIT_BUY_NUM,
                'data' => [
                    'tips' => $tips
//                    'limit_scope' => $limitBuy['promotion_scope'],
//                    'limit_number' => $limitBuy['limit_number'],
//                    'limit_unit' => $limitBuy['limit_unit']
                ]
            ];
        }
        return true;
    }

    /**
     * @desc 获取限购单位为种时的商品id或品牌id
     * @param array $limitBuy 限购活动信息
     * @param array $newShoppingCart 购物车信息
     * @return string|int $salesId 单个或多个商品id、品牌id
     * @author 吴俊华
     */
    private function getSalesId($limitBuy, $newShoppingCart)
    {
        //默认商品id
        $columnKey = 'goods_id';
        //品牌时,改为品牌id
        if($limitBuy['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
            $columnKey = 'brand_id';
        }
        $salesId = 0;
        //限购单位为种时,返回数组单一列的值[商品id或品牌id的值]
        if($limitBuy['limit_unit'] == BaiyangPromotionEnum::UNIT_KIND && !empty($newShoppingCart)){
            $salesId = implode(',',array_column($newShoppingCart,$columnKey));
        }
        return ($salesId == '') ? 0 : $salesId;
    }

    /**
     * @desc 获取参加限购活动的商品id/品牌id/分类id
     * @param array $newShoppingCart 购物车信息
     * @return array $goodsSalesId 参加限购活动的商品id/品牌id/分类id
     * @author 吴俊华
     */
    private function getGoodsSalesId($newShoppingCart)
    {
        if(empty($newShoppingCart)){
            return [];
        }
        $goodsSalesId = [];
        foreach ($newShoppingCart as $key => $value){
            $goodsSalesId[$key] = [
                'goods_id' => $value['goods_id'],
                'brand_id' => $value['brand_id'],
                'category_id' => $value['category_id'],
            ];
        }
        return $goodsSalesId;
    }

    /**
     * @desc 生成限购活动的提示语
     * @param array $limitBuy 当前限购活动
     * @param array $limitBuyGoodsInfo 参加该限购活动的商品id/品牌id/分类id
     * @return string $tips 提示语
     * @author 吴俊华
     */
    private function generateLimitBuyTips($limitBuy, $limitBuyGoodsInfo)
    {
        $tips = '所有限购活动商品限购'.$limitBuy['limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$limitBuy['limit_unit']];
        if(isset($limitBuyGoodsInfo['goodsInfo'][0])){
            $currentGoods = $limitBuyGoodsInfo['goodsInfo'][0];
            // 单品或多单品的单品提示语
            if($limitBuy['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || ($limitBuy['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE && $limitBuyGoodsInfo['type'] == 'single')){
                $goodsInfo = $this->getGoodsDetail(['goods_id' => $currentGoods['goods_id'],'platform' => $this->config->platform]);
                if(empty($goodsInfo)){
                    return $tips;
                }
                $tips = '商品：'.$goodsInfo[0]['name'].'限购'.$limitBuy['limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$limitBuy['limit_unit']];
            }
            // 单品牌提示语
            if($limitBuy['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE && $limitBuyGoodsInfo['type'] == 'single'){
                $brandInfo = BaiyangSkuData::getInstance()->getSkuBrand($currentGoods['brand_id']);
                $tips = '品牌：'.$brandInfo['brand_name'].'限购'.$limitBuy['limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$limitBuy['limit_unit']];
            }
        }
        return $tips;
    }

    /**
     * @desc 限购活动匹配参加的商品信息
     * @param array $limitBuyList 进行中的限购活动
     * @param array $shoppingCartGoodsInfo 购物车的商品信息
     * @param array $param 必填参数
     * @author 吴俊华
     */
    private function limitBuyMatchJoinGoods(&$limitBuyList, $shoppingCartGoodsInfo, $param)
    {
        $platform = $param['platform'];
        $userId = $param['user_id'];
        $isTemp = $param['is_temp'];
        $limitTime = $this->getProcessingPromotions('','',['platform' => $platform, 'user_id' => $userId,'is_temp' => $isTemp,'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        foreach ($shoppingCartGoodsInfo as $key => $value){
            $shoppingCartGoodsInfo[$key]['treatment'] = 0; // 是否疗程价
            $goodsInfo = $this->getGoodsDetail(['goods_id' => $value['goods_id'], 'platform' => $platform]);
            $skuPrice = $goodsInfo[0]['sku_price'];
            $condition = [
                'goodsInfo' => [
                    'goods_id' => $value['goods_id'],
                    'sku_price' => $skuPrice,
                    'goods_number' => $value['goods_number'],
                ],
                'platform' => $platform,
                'user_id' => $userId,
                'is_temp' => $isTemp,
                'limitTime' => $limitTime,
            ];
            $goodsDiscountPrice = $this->getGoodsDiscountPrice('','',$condition);
            if(isset($goodsDiscountPrice['discountPromotion']) && $goodsDiscountPrice['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::TREATMENT){
                $shoppingCartGoodsInfo[$key]['treatment'] = 1;
            }
        }
        foreach($limitBuyList as $promotionKey => $promotionValue){
            $limitBuyList[$promotionKey]['goodsList'] = [];
            foreach($shoppingCartGoodsInfo as $key => $value) {
                if ($this->func->isRelatedGoods($promotionValue, $value) && $value['treatment'] != 1) {
                    $limitBuyList[$promotionKey]['goodsList'][] = $value;
                }
            }
        }
    }

}