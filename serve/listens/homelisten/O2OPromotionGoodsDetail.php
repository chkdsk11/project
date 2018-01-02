<?php

/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/17
 * Time: 上午 11:16
 */

namespace Shop\Home\Listens;

use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangGoodsTreatmentData;
use Shop\Home\Datas\BaiyangUserGoodsPriceTagData;
use Shop\Home\Services\ShopService;
use Shop\Models\HttpStatus;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\CacheKey;
use Shop\Home\Datas\BaiyangPromotionData;
use Shop\Models\OrderEnum;
use Shop\Home\Listens\O2OPromotionShopping;

class O2OPromotionGoodsDetail extends BaseListen {

    /**
     * @desc 根据商品id获取促销活动的信息【商品详情】（已用）
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -int     goods_id  商品id
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getPromotionInfoByGoodsId($event, $class, $param) {
        $promotions = []; //促销活动相关信息
        $promotionArr = []; //促销活动归类
        $isJoinPromotion = false; //是否参加促销活动 (会员价/疗程/限时优惠/限购，用于标识售罄)
        $goodsInfo = $this->getGoodsDetail($param);
        if (empty($goodsInfo)) {
            return ['error' => 1, 'code' => HttpStatus::NOT_GOOD_INFO, 'data' => []];
        }
        $currentGoodsInfo = $goodsInfo[0]; //当前商品信息
        //初始化
        $goodStatus = 0;
        $stock = $this->func->getCanSaleStock(['goods_id' => $currentGoodsInfo['id'], 'platform' => $param['platform']]);
        //获取可售库存和商品状态 (0:正常 1:下架 2:缺货 3:售罄)
        if ($stock < 1) {
            $goodStatus = 2;
        }
        if ($currentGoodsInfo['sale'] == 0) {
            $goodStatus = 1;
        }
        $promotionArr['discountInfo']['discount_type'] = 0;
        $promotionArr['discountInfo']['goods_price'] = $currentGoodsInfo['sku_price'];
        $promotionArr['discountInfo']['market_price'] = $currentGoodsInfo['sku_market_price'];
        $promotionArr['discountInfo']['product_type'] = $currentGoodsInfo['product_type'];
        $promotionArr['discountInfo']['end_time'] = 0;
        $promotionArr['discountInfo']['tag_name'] = '';
        $promotionArr['discountInfo']['stock'] = $stock;
        $promotionArr['discountInfo']['goods_status'] = $goodStatus;
        // 处方药商品——是否显示"提交需求"按钮(普通商品都有"加入购物车"按钮)
        $promotionArr['discountInfo']['display_add_cart'] = ($currentGoodsInfo['drug_type'] == 1) ? $this->func->getDisplayAddCart($param['platform']) : 1;
        // 赠品、附属赠品不能加入购物车
        if($currentGoodsInfo['product_type'] > 0){
            $promotionArr['discountInfo']['display_add_cart'] = 0;
        }
        
        //进行中的促销活动
        $param['promotion_type'] = '35';
        $promotionInfo = $this->getProcessingPromotions($event, $class, $param);
        if (empty($promotionInfo)) {
            $this->editPromotionKey($promotionArr,true);
            return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
        }
        //商品匹配促销活动
        $goodMatchPromotion = $this->goodMatchPromotion($goodsInfo, $promotionInfo);
        if (!empty($goodMatchPromotion)) {
            foreach ($goodMatchPromotion as $key => $value) {
                if ($value['promotionInfo']['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE) {
                    $promotions[] = $value['promotionInfo'];
                }
            }
        }
        //整合促销活动
        if (!empty($promotions)) {
            $promotions = $this->sortPromotionByRange($promotions);
            foreach ($promotions as $promotionTypeKey => $value) {
                $promotionTypeKey = $this->getPromotionKey($value['promotion_type']);
                if (empty($promotionTypeKey)) {
                    return ['error' => 0, 'code' => HttpStatus::SYSTEM_ERROR, 'data' => $promotionArr];
                }
                $promotionArr[$promotionTypeKey][] = [
                    'promotion_id' => $value['promotion_id'],
                    'promotion_title' => $value['promotion_title'],
                    'promotion_copywriter' => $value['promotion_copywriter'],
                    'promotion_type' => $value['promotion_type'],
                    'promotion_scope' => $value['promotion_scope'],
                    'promotion_end_time' => $value['promotion_end_time'],
                    'condition' => $value['condition'],
                    'rule_value' => $value['rule_value'],
                    'offer_type' => $value['offer_type'],
                    'limit_unit' => $value['limit_unit'],
                    'limit_number' => $value['limit_number'],
                ];
            }
        }

        //生成限购促销文案
//        if(isset($promotionArr['limitBuy'])){
//            $limitBuyArr = $this->handleLimitBuyPromotion($promotionArr['limitBuy'],$currentGoodsInfo);
//            unset($promotionArr['limitBuy']);
//        }
        //生成限时促销文案
        $limitTimeArr = [];
        if (isset($promotionArr['limitTime'])) {
            $limitTimeArr = $promotionArr['limitTime'];
            unset($promotionArr['limitTime']);
        }
        //生成满减、满折、满赠、包邮、加价购的促销文案
        $discountInfo = $promotionArr['discountInfo'];
//        if(!empty($promotionArr)){
//            unset($promotionArr['discountInfo']);
//            $promotionArr = $this->handlePromotions($promotionArr);
//            if(isset($limitBuyArr)){
//                $promotionArr['limitBuy'] = $limitBuyArr;
//            }
//        }
        //获取商品疗程
//        $goodsTreatment = BaiyangGoodsTreatmentData::getInstance()->getGoodsTreatment($param,false);
//        if(!empty($goodsTreatment)){
//            $promotionArr['treatment'] = $goodsTreatment;
//        }
        //获取可用优惠券
        $param['isMultiGoodsId'] = 1;
        $coupon = $this->CouponList($param);
        if ($coupon['coupon'] && !empty($coupon['coupon'])) {
            $promotionArr['coupon'] = $coupon['coupon'];
        }
//        if(isset($promotionArr['treatment']) || isset($promotionArr['limitBuy'])){
//            $isJoinPromotion = true;
//        }
        //获取商品套餐
//        $goodSet = $this->GoodSet($param);
//        $promotionArr = array_merge($promotionArr,$goodSet);

        $promotionType = 0;  //活动类型，默认没有参加会员价、限时优惠活动
        $discountPrice = $currentGoodsInfo['sku_price']; //较优惠价，默认为原价
        $endTime = 0; //活动结束时间 (主要限时优惠使用)
        $tagName = ''; //标签名(主要是商品标签使用)
        $discountParam = [
            'goodsInfo' => [
                'goods_id' => $currentGoodsInfo['id'],
                'sku_price' => $currentGoodsInfo['sku_price'],
                'goods_number' => 1,
            ],
            'platform' => $param['platform'],
            'user_id' => $param['user_id'],
            'is_temp' => $param['is_temp'],
            'limitTime' => $limitTimeArr,
        ];
        //获取商品最优惠价
        //$discountPromotion = $this->getGoodsDiscountPrice($event,$class,$discountParam);
        $O2OPromotionShopping = new O2OPromotionShopping();
        $discountPromotion = $O2OPromotionShopping->getGoodsDiscountPrice($event, $class, $discountParam);

        if (!empty($discountPromotion['discountPromotion'])) {
            $promotionType = $discountPromotion['discountPromotion']['promotion_type'];
            $discountPrice = $discountPromotion['discountPromotion']['price'];
            //限时优惠价
            if ($promotionType == BaiyangPromotionEnum::LIMIT_TIME) {
                $endTime = $discountPromotion['discountPromotion']['end_time'];
            }
            $tagName = $discountPromotion['discountPromotion']['tag_name'];
        }
        if ($promotionType > 0) {
            $isJoinPromotion = true;
        }
        $promotionArr['discountInfo']['discount_type'] = $promotionType;
        $promotionArr['discountInfo']['goods_price'] = sprintf('%.2f', $discountPrice);
        $promotionArr['discountInfo']['market_price'] = $currentGoodsInfo['sku_market_price'];
        // 计算限时优惠的折扣
        if($promotionType == BaiyangPromotionEnum::LIMIT_TIME){
            $promotionArr['discountInfo']['rebate'] = bcmul(bcdiv($discountPromotion['discountPromotion']['price'],$currentGoodsInfo['sku_market_price'],2),10,1);
        }
        $promotionArr['discountInfo']['end_time'] = $endTime;
        $promotionArr['discountInfo']['tag_name'] = $tagName;
        //缺货状态改为售罄状态 (2 -> 3)
        if ($goodStatus == 2 && $isJoinPromotion) {
            $goodStatus = 3;
        }
        $promotionArr['discountInfo']['stock'] = $stock;
        $promotionArr['discountInfo']['goods_status'] = $goodStatus;
        $promotionArr['discountInfo'] = array_merge($discountInfo, $promotionArr['discountInfo']);
        //保证输出的key一致
        $this->editPromotionKey($promotionArr, true);
        //处理疗程
//        if (!empty($promotionArr['treatment']) && $promotionArr['discountInfo']['discount_type'] != 0) {
//            $this->removeNonPreferentialTreatment($promotionArr);
//        }
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
    }

    /**
     * @desc 根据商品id获取促销活动的标识【商品列表】(已用)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -string  goods_id  商品id，多个以逗号隔开
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getGoodsPromotionSign($event, $class, $param) {
        $promotions = []; //促销活动相关信息
        $promotionArr = []; //促销活动归类
        //保证输出的key一致
        $this->editPromotionKey($promotionArr);
        $goodsInfo = $this->getGoodsDetail($param);
        if (empty($goodsInfo)) {
            return ['error' => 1, 'code' => HttpStatus::NOT_GOOD_INFO, 'data' => $promotionArr];
        }
        //进行中的促销活动
        $param['promotion_type'] = '5,10,15,20,30,40';
        $promotionInfo = $this->getProcessingPromotions($event, $class, $param);
        if (empty($promotionInfo)) {
            return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
        }
        //商品匹配促销活动
        $goodMatchPromotion = $this->goodMatchPromotion($goodsInfo, $promotionInfo);
        if (!empty($goodMatchPromotion)) {
            foreach ($goodMatchPromotion as $key => $value) {
                if ($value['promotionInfo']['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE) {
                    $promotions[BaiyangPromotionEnum::$PromotionTransform[$value['promotionInfo']['promotion_type']]][] = $value['goodsInfo']['id'];
                }
            }
        }
        //数组去重
        if (!empty($promotions)) {
            foreach ($promotions as $key => $value) {
                $promotionArr[$key] = $this->removeDuplicate($value);
            }
        }
        //优惠券
        $param['getStatus'] = 1;
        $coupon = $this->CouponList($param);
        if ($coupon['coupon'] && !empty($coupon['coupon'])) {
            $promotionArr['coupon'] = $coupon['coupon'];
        }
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
    }

    /**
     * @desc 补全缺少的key，保证输出的key一致
     * @param array $promotionArr 所有促销活动
     * @param bool $sign 标识 (兼容商品详情)
     * @return array $promotionArr 处理后的促销活动
     * @author 吴俊华
     */
    private function editPromotionKey(&$promotionArr, $sign = false) {
        if (!isset($promotionArr['fullMinus'])) {
            $promotionArr['fullMinus'] = [];
        }
        if (!isset($promotionArr['fullOff'])) {
            $promotionArr['fullOff'] = [];
        }
        if (!isset($promotionArr['fullGift'])) {
            $promotionArr['fullGift'] = [];
        }
        if (!isset($promotionArr['expressFree'])) {
            $promotionArr['expressFree'] = [];
        }
        if (!isset($promotionArr['increaseBuy'])) {
            $promotionArr['increaseBuy'] = [];
        }
        if (!isset($promotionArr['limitBuy'])) {
            $promotionArr['limitBuy'] = [];
        }
        if (!isset($promotionArr['coupon'])) {
            $promotionArr['coupon'] = [];
        }
        if ($sign) {
            if (!isset($promotionArr['treatment'])) {
                $promotionArr['treatment'] = [];
            }
        }
    }

    /**
     * @desc 获取不同促销活动的key
     * @param array $promotionType 活动类型
     * @return string|bool $promotionTypeKey|false 促销活动的key或false
     * @author 吴俊华
     */
    private function getPromotionKey($promotionType) {
        switch ($promotionType) {
            //满减活动
            case BaiyangPromotionEnum::FULL_MINUS:
                $promotionName = BaiyangPromotionEnum::FULL_MINUS;
                break;
            //满折活动
            case BaiyangPromotionEnum::FULL_OFF:
                $promotionName = BaiyangPromotionEnum::FULL_OFF;
                break;
            //满赠活动
            case BaiyangPromotionEnum::FULL_GIFT:
                $promotionName = BaiyangPromotionEnum::FULL_GIFT;
                break;
            //包邮活动
            case BaiyangPromotionEnum::EXPRESS_FREE:
                $promotionName = BaiyangPromotionEnum::EXPRESS_FREE;
                break;
            //加价购活动
            case BaiyangPromotionEnum::INCREASE_BUY:
                $promotionName = BaiyangPromotionEnum::INCREASE_BUY;
                break;
            //限购活动
            case BaiyangPromotionEnum::LIMIT_BUY:
                $promotionName = BaiyangPromotionEnum::LIMIT_BUY;
                break;
            //限时优惠
            case BaiyangPromotionEnum::LIMIT_TIME:
                $promotionName = BaiyangPromotionEnum::LIMIT_TIME;
                break;
        }
        return isset($promotionName) ? BaiyangPromotionEnum::$PromotionTransform[$promotionName] : '';
    }

    /**
     * @desc 根据活动ID获取参加该促销活动的商品活动信息 (凑单促销列表)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -int     promotion_id  促销活动id
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 促销活动的商品活动信息
     * @author 吴俊华
     */
    public function getPromotionGoodsInfoById($event, $class, $param) {
        //根据活动id查询活动信息(该活动必须是进行中的促销活动)
        $promotionInfo = $this->getProcessingPromotions($event, $class, $param);
        if (empty($promotionInfo)) {
            return ['error' => 1, 'code' => HttpStatus::PROMOTION_HAVE_ENDED, 'data' => []];
        }
        $promotionInfo = $promotionInfo[0];
        //商品信息
        $goodsInfo['single'] = [];
        $goodsInfo['brand'] = [];
        $goodsInfo['category'] = [];
        $goodsInfo['all'] = [];
        //使用范围：全场、品类、品牌、单品
        switch ($promotionInfo['promotion_scope']) {
            case BaiyangPromotionEnum::ALL_RANGE: $rangKey = 'all';
                break;
            case BaiyangPromotionEnum::CATEGORY_RANGE: $rangKey = 'category';
                break;
            case BaiyangPromotionEnum::BRAND_RANGE: $rangKey = 'brand';
                break;
            case BaiyangPromotionEnum::SINGLE_RANGE: $rangKey = 'single';
                break;
            default:
                return ['error' => 1, 'code' => HttpStatus::SYSTEM_ERROR, 'data' => []];
                break;
        }
        //单品id或品牌id或品类id
        $goodsInfo[$rangKey][0]['join'] = $promotionInfo['condition'];
        //排除品类id、品牌id、单品id
        $goodsInfo[$rangKey][0]['except_category_id'] = $promotionInfo['except_category_id'];
        $goodsInfo[$rangKey][0]['except_brand_id'] = $promotionInfo['except_brand_id'];
        $goodsInfo[$rangKey][0]['except_good_id'] = $promotionInfo['except_good_id'];

        // 请求购物车列表计算门槛，更新缓存
        $shoppingCartResult = ShopService::getInstance()->shoppingCart($param);
        $resultInfo = []; //是否达到门槛相关信息
        $changeGroup = []; //换购品列表
        $cacheKey = CacheKey::MAKE_ORDER_PROMOTION . $param['is_temp'] . '_' . $param['user_id'];
        //购物车里所有促销活动
        $cartPromotions = $this->RedisCache->getValue($cacheKey);
        if (!empty($cartPromotions)) {
            foreach ($cartPromotions as $key => $value) {
                if ($value['promotion_id'] == $promotionInfo['promotion_id']) {
                    $resultInfo = $value['resultInfo'];
                    if ($promotionInfo['promotion_type'] == BaiyangPromotionEnum::INCREASE_BUY) {
                        $changeGroup = $resultInfo['change_group'];
                    }
                    break;
                }
            }
        }
        // 促销文案
        $promotionInfo['rule_value'] = json_decode($promotionInfo['rule_value'], true);
        $copywriter = isset($resultInfo['copywriter']) ? $resultInfo['copywriter'] : $this->generatePromotionsCopywriter($promotionInfo);
        $promotionGoodsInfo = [
            'goodsInfo' => $goodsInfo,
            'promotionInfo' => [
                'promotion_id' => $promotionInfo['promotion_id'],
                'promotion_title' => $promotionInfo['promotion_title'],
                'copywriter' => $copywriter,
                'promotion_type' => $promotionInfo['promotion_type'],
                'promotion_scope' => $promotionInfo['promotion_scope'],
                'isCanUse' => isset($resultInfo['isCanUse']) ? $resultInfo['isCanUse'] : false,
                'bought_number' => isset($resultInfo['bought_number']) ? $resultInfo['bought_number'] : 0,
                'lack_number' => isset($resultInfo['lack_number']) ? $resultInfo['lack_number'] : 0,
                'unit' => isset($resultInfo['unit']) ? $resultInfo['unit'] : 0,
                'reduce_price' => isset($resultInfo['reduce_price']) ? $resultInfo['reduce_price'] : 0,
                'pro_message' => isset($resultInfo['pro_message']) ? $resultInfo['pro_message'] : '',
            ],
            'changeGroup' => $changeGroup,
        ];
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionGoodsInfo];
    }

    /**
     * @desc 根据活动类型获取参加该促销活动的商品信息 (商品列表)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -mixed   promotion_type  促销活动类型(多个以逗号隔开)
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 促销活动的商品信息
     * @author 吴俊华
     */
    public function getPromotionGoodsInfoByType($event, $class, $param) {
        //根据活动id查询活动信息(该活动必须是进行中的促销活动)
        $promotionInfo = $this->getProcessingPromotions($event, $class, $param);
        if (empty($promotionInfo)) {
            return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => []];
        }
        //传过来的活动类型没有时，返回空
        $typesArr = explode(',', $param['promotion_type']);
        for ($i = 0; $i < count($typesArr); $i++) {
            if (!in_array($typesArr[$i], array_unique(array_column($promotionInfo, 'promotion_type')))) {
                return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => []];
            }
        }
        //商品信息
        $goodsInfo['single'] = [];
        $goodsInfo['brand'] = [];
        $goodsInfo['category'] = [];
        $goodsInfo['all'] = [];
        foreach ($promotionInfo as $promotion) {
            //使用范围：全场、品类、品牌、单品
            switch ($promotion['promotion_scope']) {
                //case BaiyangPromotionEnum::ALL_RANGE:      $rangKey = 'all'; break;
                case BaiyangPromotionEnum::CATEGORY_RANGE: $rangKey = 'category';
                    break;
                case BaiyangPromotionEnum::BRAND_RANGE: $rangKey = 'brand';
                    break;
                case BaiyangPromotionEnum::SINGLE_RANGE: $rangKey = 'single';
                    break;
                default: $rangKey = 'single';
                    break;
            }
            if ($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE) {
                //单品id或品牌id或品类id
                $goodsInfo[$rangKey][] = [
                    'join' => $promotion['condition'],
                    'except_category_id' => $promotion['except_category_id'],
                    'except_brand_id' => $promotion['except_brand_id'],
                    'except_good_id' => $promotion['except_good_id'],
                ];
            }
        }
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $goodsInfo];
    }

    /**
     * @desc 获取参加促销活动的商品优惠价 (列表页) (已用)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       -array   goodsList  商品列表信息  [二维数组] (商品id为goodsId、商品价格为price)
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 参加促销活动的商品信息
     * @author 吴俊华
     */
    public function getPromotionGoodsPrice($event, $class, $param) {
        //商品列表
        $goodsList = $param['goodsList'];
        //进行中的限时优惠活动
        $limitTime = $this->getProcessingPromotions($event,$class,['platform' => $param['platform'], 'user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        $discountArr = []; //优惠价数组
        foreach ($goodsList as $goodsKey => $goodsDetail) {
            $tagName = ''; //商品标签
            if (!empty($limitTime)) {
                $goodsParam = [
                    'goodsInfo' => [
                        'goods_id' => $goodsDetail['goodsId'],
                        'sku_price' => $goodsDetail['price'],
                        'goods_number' => 1,
                    ],
                    'platform' => $param['platform'],
                    'user_id' => $param['user_id'],
                    'is_temp' => $param['is_temp'],
                    'limitTime' => $limitTime,
                ];
                //判断商品参加限时优惠价或者会员价
                //$discountArr = $this->getGoodsDiscountPrice($event, $class, $goodsParam);
                $O2OPromotionShopping = new O2OPromotionShopping();
                $discountArr = $O2OPromotionShopping->getGoodsDiscountPrice($event, $class, $goodsParam);
            }
            if (isset($discountArr['discountPromotion']) && !empty($discountArr['discountPromotion'])) {
                $discountPrice = $discountArr['discountPromotion']['price'];
                $discountType = $discountArr['discountPromotion']['promotion_type'];
                $tagName = isset($discountArr['discountPromotion']['tag_name']) ? $discountArr['discountPromotion']['tag_name'] : '';
            } else {
                $discountPrice = $goodsDetail['price'];
                $discountType = 0;
            }
            $goodsList[$goodsKey]['price'] = $discountPrice;
            $goodsList[$goodsKey]['discount_type'] = $discountType;
            $goodsList[$goodsKey]['tag_name'] = $tagName;
            $goodsList[$goodsKey]['stock'] = $this->func->getCanSaleStock(['goods_id' => $goodsDetail['goodsId'], 'platform' => $param['platform']]);
        }
        return $goodsList;
    }

    /**
     * @desc 处理限购活动的促销文案
     * @param array $limitBuy 所有进行中的限购活动 [二维数组]
     * @param array $currentGoodsInfo 商品信息 [一维数组]
     * @return array $newLimitBuyList 处理后的限购活动信息
     * @author 吴俊华
     */
    private function handleLimitBuyPromotion($limitBuy, $currentGoodsInfo) {
        foreach ($limitBuy as $key => $value) {
            if ($value['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE ||
                    $value['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE) {
                $rangeKey = 'id';
                if ($value['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE) {
                    $rangeKey = 'brand_id';
                }
                // 品牌限购：取单个品牌的件数来显示
                foreach (json_decode($value['rule_value'], true) as $val) {
                    if ($val['id'] == $currentGoodsInfo[$rangeKey]) {
                        $limitBuy[$key]['limit_unit'] = BaiyangPromotionEnum::UNIT_ITEM;
                        $limitBuy[$key]['limit_number'] = $val['promotion_num'];
                    }
                }
            }
        }
        $limitBuy = $this->sortLimitBuyList($limitBuy);
        $newLimitBuyList = [];
        foreach ($limitBuy as $key => $value) {
            if (!isset($newLimitBuyList[$value['limit_unit']])) {
                $newLimitBuyList[$value['limit_unit']] = $value;
            }
        }
        $newLimitBuyList = $this->generateLimitBuyCopywriter(array_values($newLimitBuyList));
        return $newLimitBuyList;
    }

    /**
     * @desc 生成限购活动的促销文案
     * @param array $limitBuyList 限购活动列表
     * @return array $limitBuyList 生成促销文案后的限购活动列表
     * @author 吴俊华
     */
    private function generateLimitBuyCopywriter($limitBuyList) {
        $newLimitBuyList = [];
        foreach ($limitBuyList as $key => $value) {
            $copywriter = '限购';
            $newLimitBuyList['promotion_id'] = implode(',', array_column($limitBuyList, 'promotion_id'));
            $limitUnit = array_column($limitBuyList, 'limit_unit');
            $limitNumber = array_column($limitBuyList, 'limit_number');
            for ($i = 0; $i < count($limitUnit); $i++) {
                $copywriter .= $limitNumber[$i] . BaiyangPromotionEnum::$LimitBuyUnit[$limitUnit[$i]] . '、';
            }
            $copywriter = rtrim($copywriter, '、');
            $newLimitBuyList['copywriter'] = $copywriter;
        }
        // 返回限购数(件数)
        $newLimitBuyList['limit_number'] = BaiyangPromotionEnum::GOODS_MAX_PURCHASE_NUMBER;
        if (isset($limitBuyList[0]) && $limitBuyList[0]['limit_unit'] == BaiyangPromotionEnum::UNIT_ITEM) {
            $newLimitBuyList['limit_number'] = $limitBuyList[0]['limit_number'];
        }
        return $newLimitBuyList;
    }

    /**
     * @desc 处理促销活动的促销文案 (满减、满折、满赠、包邮、加价购)
     * @param array $promotion 进行中的促销活动 [二维数组]
     * @return array $newLimitBuyList 处理后的限购活动信息
     * @author 吴俊华
     */
    private function handlePromotions($promotion) {
        $newPromotion = [];
        foreach ($promotion as $key => $value) {
            foreach ($value as $ky => $val) {
                $val['rule_value'] = json_decode($val['rule_value'], true);
                $copywriter = $this->generatePromotionsCopywriter($val);
                $newPromotion[$key][$ky]['promotion_id'] = $val['promotion_id'];
                $newPromotion[$key][$ky]['copywriter'] = $copywriter;
            }
        }
        return $newPromotion;
    }

    /**
     * @desc 根据品牌id获取促销活动
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -int     brand_id  品牌id
     *       -string  platform  平台
     *       -int     channel_subid  渠道号
     *       -string  udid      手机唯一标识
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getPromotionsByBrandId($event, $class, $param) {
        $brandId = (int) $param['brand_id'];
        $promotionArr = []; //促销活动
        //保证输出的key一致
        $this->editPromotionKey($promotionArr);
        //进行中的促销活动
        $param['promotion_type'] = '5,10,15,20,30,40';
        $promotionInfo = $this->getProcessingPromotions($event, $class, $param);
        if (empty($promotionInfo) || !in_array(BaiyangPromotionEnum::BRAND_RANGE, array_column($promotionInfo, 'promotion_scope'))) {
            return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
        }
        // 根据品牌id筛选促销活动
        foreach ($promotionInfo as $key => $value) {
            if ($value['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE && in_array($brandId, explode(',', $value['condition']))) {
                $value['rule_value'] = json_decode($value['rule_value'], true);
                $copywriter = $this->generatePromotionsCopywriter($value);
                $promotionArr[BaiyangPromotionEnum::$PromotionTransform[$value['promotion_type']]][] = [
                    'promotion_id' => $value['promotion_id'],
                    'promotion_type' => $value['promotion_type'],
                    'promotion_scope' => $value['promotion_scope'],
                    'copywriter' => $copywriter,
                ];
            }
        }
        //优惠券
        $coupon = $this->getCouponByBrand($param);
        if (isset($coupon['coupon']) && !empty($coupon['coupon'])) {
            $promotionArr['coupon'] = $coupon['coupon'];
        }
        unset($promotionArr['increaseBuy']);
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
    }

    /**
     * @desc 根据使用范围排序 [全场>品类>品牌>单品] （已用）
     * @param array $promotionList 促销活动列表[二维数组]
     * @return array [] 结果信息
     * @author 吴俊华
     */
    private function sortPromotionByRange($promotionList) {
        $sort1 = [];
        $sort2 = [];
        foreach ($promotionList as $key => $value) {
            switch ($value['promotion_scope']) {
                case BaiyangPromotionEnum::SINGLE_RANGE :
                    $sort1[] = 1;
                    break;
                case BaiyangPromotionEnum::MORE_RANGE :
                    $sort1[] = 2;
                    break;
                case BaiyangPromotionEnum::BRAND_RANGE :
                    $sort1[] = 3;
                    break;
                case BaiyangPromotionEnum::CATEGORY_RANGE :
                    $sort1[] = 4;
                    break;
                case BaiyangPromotionEnum::ALL_RANGE :
                    $sort1[] = 5;
                    break;
                default :
                    $sort1[] = 5;
                    break;
            }
            switch ($value['promotion_type']) {
                case '5' : // 满减
                    $sort2[] = 10;
                    break;
                case '10' : // 满折
                    $sort2[] = 20;
                    break;
                case '15' : // 满赠
                    $sortNum = 30;
                    $sort2[] = $sortNum;
                    break;
                case '40' : //加价购
                    $sort2[] = 40;
                    break;
                case '20' : // 包邮
                    $sort2[] = 50;
                    break;
                case '30' : // 限购
                    $sort2[] = 60;
                    break;
                default :
                    $sort2[] = 60;
                    break;
            }
        }
        array_multisort($sort2, SORT_ASC, $sort1, SORT_DESC, $promotionList);
        return $promotionList;
    }

    /**
     * @desc 排除不太优惠的疗程选项  [商品详情]
     * @param array $promotionArr 所有促销活动
     * @return array $promotionArr 处理后的疗程
     * @author 吴俊华
     */
    private function removeNonPreferentialTreatment(&$promotionArr) {
        foreach ($promotionArr['treatment'] as $key => $value) {
            if ($promotionArr['discountInfo']['discount_type'] == BaiyangPromotionEnum::LIMIT_TIME) {
                if ($value['price'] > $promotionArr['discountInfo']['goods_price']) {
                    unset($promotionArr['treatment'][$key]);
                }
            } else {
                if ($value['price'] >= $promotionArr['discountInfo']['goods_price']) {
                    unset($promotionArr['treatment'][$key]);
                }
            }
        }
        $promotionArr['treatment'] = array_values($promotionArr['treatment']);
    }

}
