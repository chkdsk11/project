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

class PromotionGoodsDetail extends BaseListen
{
    /**
     * @desc 根据商品id获取促销活动的信息【商品详情】
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
    public function getPromotionInfoByGoodsId($event,$class,$param)
    {
        $promotions = []; //促销活动相关信息
        $promotionArr = []; //促销活动归类
        $isJoinPromotion = false; //是否参加促销活动 (会员价/疗程/限时优惠/限购，用于标识售罄)
        $goodsInfo = $this->getGoodsDetail($param);
        if(empty($goodsInfo)) {
            return ['error' => 1,'code' => HttpStatus::NOT_GOOD_INFO,'data' => []];
        }
        $currentGoodsInfo = $goodsInfo[0]; //当前商品信息

        //初始化
        $goodStatus = 0;
        $stock = $this->func->getCanSaleStock(['goods_id' => $currentGoodsInfo['id'],'platform' => $param['platform']]);
        //获取可售库存和商品状态 (0:正常 1:下架 2:缺货 3:售罄)
        if($stock < 1){
            $goodStatus = 2;
        }
        if($currentGoodsInfo['sale'] == 0){
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
        // 海外购不查促销
        if($currentGoodsInfo['is_global']){
            $promotionArr['discountInfo']['display_add_cart'] = 1;
            $this->editPromotionKey($promotionArr,true);
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
        }

        //进行中的促销活动
        $promotionInfo = $this->getProcessingPromotions($event,$class,$param);
        if($promotionInfo){
            //商品匹配促销活动
            $goodMatchPromotion = $this->goodMatchPromotion($goodsInfo,$promotionInfo);
            if(!empty($goodMatchPromotion)){
                foreach($goodMatchPromotion as $key => $value){
                    if($value['promotionInfo']['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                        $promotions[] = $value['promotionInfo'];
                    }
                }
            }

            //整合促销活动
            if(!empty($promotions)){
                $promotions = $this->sortPromotionByRange($promotions);
                foreach ($promotions as $promotionTypeKey => $value) {
                    $promotionTypeKey = $this->getPromotionKey($value['promotion_type']);
                    if(empty($promotionTypeKey)){
                        $this->editPromotionKey($promotionArr,true);
                        return ['error' => 1,'code' => HttpStatus::SYSTEM_ERROR,'data' => $promotionArr];
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
            if(isset($promotionArr['limitBuy'])){
                $limitBuyArr = $this->handleLimitBuyPromotion($promotionArr['limitBuy'],$currentGoodsInfo);
                unset($promotionArr['limitBuy']);
            }
            $limitTimeArr = [];
            if(isset($promotionArr['limitTime'])){
                $limitTimeArr = $promotionArr['limitTime'];
                unset($promotionArr['limitTime']);
            }
            //生成满减、满折、满赠、包邮、加价购的促销文案
            $discountInfo = $promotionArr['discountInfo'];
            if(!empty($promotionArr)){
                unset($promotionArr['discountInfo']);
                $promotionArr = $this->handlePromotions($promotionArr);
                if(isset($limitBuyArr)){
                    $promotionArr['limitBuy'] = $limitBuyArr;
                }
            }

            //获取商品疗程
            $goodsTreatment = BaiyangGoodsTreatmentData::getInstance()->getGoodsTreatment($param,false);
            if(!empty($goodsTreatment)){
                $promotionArr['treatment'] = $goodsTreatment;
            }
        }
        //获取可用优惠券
        $param['isMultiGoodsId'] = 1;
        $coupon = $this->CouponList($param);
        if($coupon['coupon'] && !empty($coupon['coupon'])){
            $promotionArr['coupon'] = $coupon['coupon'];
        }
//        if (!$promotionInfo) {
//            $this->editPromotionKey($promotionArr,true);
//            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
//        }
        if(isset($promotionArr['treatment']) || (isset($promotionArr['limitBuy']['promotion_id']) && $promotionArr['limitBuy']['promotion_id'] > 0)){
            $isJoinPromotion = true;
        }
        //获取商品套餐
        $goodSet = $this->GoodSet($param);
        $promotionArr = array_merge($promotionArr,$goodSet);

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
            'limitTime' => isset($limitTimeArr)?$limitTimeArr:[],
        ];
        //获取商品最优惠价
        $discountPromotion = $this->getGoodsDiscountPrice($event,$class,$discountParam);
        if(!empty($discountPromotion['discountPromotion'])){
            $promotionType = $discountPromotion['discountPromotion']['promotion_type'];
            $discountPrice = $discountPromotion['discountPromotion']['price'];
            //限时优惠价
            if($promotionType == BaiyangPromotionEnum::LIMIT_TIME){
                $endTime = $discountPromotion['discountPromotion']['end_time'];
            }
            $tagName = $discountPromotion['discountPromotion']['tag_name'];
        }
        if($promotionType > 0){
            $isJoinPromotion = true;
        }
        $promotionArr['discountInfo']['discount_type'] = $promotionType;
        $promotionArr['discountInfo']['goods_price'] = sprintf('%.2f',$discountPrice);
        $promotionArr['discountInfo']['market_price'] = $currentGoodsInfo['sku_market_price'];
        // 计算限时优惠的折扣
        if($promotionType == BaiyangPromotionEnum::LIMIT_TIME){
            $promotionArr['discountInfo']['rebate'] = bcmul(bcdiv($discountPromotion['discountPromotion']['price'],$currentGoodsInfo['sku_market_price'],2),10,1);
            if($promotionArr['discountInfo']['rebate'] <= 0 ) $promotionArr['discountInfo']['rebate'] = 0.1;
        }
        $promotionArr['discountInfo']['end_time'] = $endTime;
        $promotionArr['discountInfo']['tag_name'] = $tagName;
        //缺货状态改为售罄状态 (2 -> 3)
        if($goodStatus == 2 && $isJoinPromotion){
            $goodStatus = 3;
        }
        $promotionArr['discountInfo']['stock'] = $stock;
        $promotionArr['discountInfo']['goods_status'] = $goodStatus;
		if($promotionInfo){
			$promotionArr['discountInfo'] = array_merge($discountInfo, $promotionArr['discountInfo']);
		}
        //保证输出的key一致
        $this->editPromotionKey($promotionArr,true);
        //处理疗程
        if(!empty($promotionArr['treatment'])){
            $this->handleTreatment($promotionArr);
        }
        $promotionArr['goodsSets'] = $this->getGoodsSets($param['goods_id']);
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
    }

    /**
     * @desc 根据商品id获取促销活动的标识【商品列表】
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
    public function getGoodsPromotionSign($event,$class,$param)
    {
        $promotions = []; //促销活动相关信息
        $promotionArr = []; //促销活动归类
        //保证输出的key一致
        $this->editPromotionKey($promotionArr);
        $goodsInfo = $this->getGoodsDetail($param);
        if(empty($goodsInfo)){
            return ['error' => 1,'code' => HttpStatus::NOT_GOOD_INFO,'data' => $promotionArr];
        }
        //进行中的促销活动
        $param['promotion_type'] = '5,10,15,20,30,40';
        $promotionInfo = $this->getProcessingPromotions($event,$class,$param);
        if(empty($promotionInfo)){
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
        }
        //商品匹配促销活动
        $goodMatchPromotion = $this->goodMatchPromotion($goodsInfo,$promotionInfo);
        if(!empty($goodMatchPromotion)){
            foreach($goodMatchPromotion as $key => $value){
                if($value['promotionInfo']['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                    $promotions[BaiyangPromotionEnum::$PromotionTransform[$value['promotionInfo']['promotion_type']]][] = $value['goodsInfo']['id'];
                }
            }
        }
        //数组去重
        if(!empty($promotions)){
            foreach($promotions as $key => $value){
                $promotionArr[$key] = $this->removeDuplicate($value);
            }
        }
        //优惠券
        //$promotionArr['coupon'] = $this->CouponProductList($param);
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
    }

    /**
     * @desc 补全缺少的key，保证输出的key一致
     * @param array $promotionArr 所有促销活动
     * @param bool $sign 标识 (兼容商品详情)
     * @return array $promotionArr 处理后的促销活动
     * @author 吴俊华
     */
    private function editPromotionKey(&$promotionArr, $sign = false)
    {
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
            if ($this->config->platform == OrderEnum::PLATFORM_PC && !isset($promotionArr['goodsSets'])) {
                $promotionArr['goodsSets'] = [];
            }
        }
    }

    /**
     * @desc 获取不同促销活动的key
     * @param array $promotionType 活动类型
     * @return string|bool $promotionTypeKey|false 促销活动的key或false
     * @author 吴俊华
     */
    private function getPromotionKey($promotionType)
    {
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
        return isset($promotionName) ? BaiyangPromotionEnum::$PromotionTransform[$promotionName] :'';
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
    public function getPromotionGoodsInfoById($event,$class,$param)
    {
        //根据活动id查询活动信息(该活动必须是进行中的促销活动)
        $promotionInfo = $this->getProcessingPromotions($event,$class,$param);
        if(empty($promotionInfo)){
            return ['error' => 1,'code' => HttpStatus::PROMOTION_HAVE_ENDED,'data' => []];
        }
        $promotionInfo = $promotionInfo[0];
        //商品信息
        $goodsInfo['single'] = [];
        $goodsInfo['brand'] = [];
        $goodsInfo['category'] = [];
        $goodsInfo['all'] = [];
        //使用范围：全场、品类、品牌、单品
        switch ($promotionInfo['promotion_scope']) {
            case BaiyangPromotionEnum::ALL_RANGE:      $rangKey = 'all'; break;
            case BaiyangPromotionEnum::CATEGORY_RANGE: $rangKey = 'category'; break;
            case BaiyangPromotionEnum::BRAND_RANGE:    $rangKey = 'brand'; break;
            case BaiyangPromotionEnum::SINGLE_RANGE:   $rangKey = 'single'; break;
            default:
                return ['error' => 1,'code' => HttpStatus::NO_DATA,'data' => []];
                break;
        }
        //单品id或品牌id或品类id
        $goodsInfo[$rangKey][0]['join'] = $promotionInfo['condition'];
        //排除品类id、品牌id、单品id
        $goodsInfo[$rangKey][0]['except_category_id'] = $promotionInfo['except_category_id'];
        $goodsInfo[$rangKey][0]['except_brand_id'] = $promotionInfo['except_brand_id'];
        $goodsInfo[$rangKey][0]['except_good_id'] = $promotionInfo['except_good_id'];

        // 请求购物车列表计算门槛，更新缓存
        $param['promotion_request'] = true; // 凑单列表不调用多品规
        $shoppingCartResult = ShopService::getInstance()->shoppingCart($param);
        $resultInfo = []; //是否达到门槛相关信息
        $changeGroup = []; //换购品列表
        // 初始化活动信息
        $promotionInfo['rule_value'] = json_decode($promotionInfo['rule_value'], true);
        $ruleArr = $this->arraySortByKey($promotionInfo['rule_value'],'full_price','asc');
        $discountRule = $ruleArr[0]; //较为容易达到的门槛
        $resultInfo['lack_number'] = $discountRule['full_price'];
        $resultInfo['unit'] = $discountRule['unit'];
        $resultInfo['pro_message'] = '还差'.$resultInfo['lack_number'].BaiyangPromotionEnum::$FULL_UNIT[$resultInfo['unit']];
        if($promotionInfo['promotion_type'] == BaiyangPromotionEnum::INCREASE_BUY){
            $changeGroup = $this->handleExchangeGoodsData($promotionInfo, $param['platform']);
        }

        $cacheKey = CacheKey::MAKE_ORDER_PROMOTION.$param['is_temp'].'_'.$param['user_id'];
        //购物车里所有促销活动
        $redis = $this->cache;
        $redis->selectDb(2);
        $cartPromotions = $redis->getValue($cacheKey);
        if(!empty($cartPromotions)){
            foreach($cartPromotions as $key => $value){
                if($value['promotion_id'] == $promotionInfo['promotion_id']){
                    $resultInfo = $value['resultInfo'];
                    if($promotionInfo['promotion_type'] == BaiyangPromotionEnum::INCREASE_BUY){
                        $changeGroup = isset($resultInfo['change_group']) ? $resultInfo['change_group'] : [];
                    }
                    break;
                }
            }
        }
        // 促销文案
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
                'unit' => isset($resultInfo['unit']) ? $resultInfo['unit'] : 'yuan',
                'reduce_price' => isset($resultInfo['reduce_price']) ? $resultInfo['reduce_price'] : 0,
                'pro_message' => isset($resultInfo['pro_message']) ? $resultInfo['pro_message'] : '',
            ],
            'changeGroup' => $changeGroup,
        ];
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionGoodsInfo];
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
    public function getPromotionGoodsInfoByType($event,$class,$param)
    {
        //根据活动id查询活动信息(该活动必须是进行中的促销活动)
        $promotionInfo = $this->getProcessingPromotions($event,$class,$param);
        if(empty($promotionInfo)){
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
        }
        //传过来的活动类型没有时，返回空
        $typesArr = explode(',', $param['promotion_type']);
        for ($i = 0;$i < count($typesArr); $i++){
            if(!in_array($typesArr[$i], array_unique(array_column($promotionInfo,'promotion_type')))){
                return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
            }
        }
        //商品信息
        $goodsInfo['single'] = [];
        $goodsInfo['brand'] = [];
        $goodsInfo['category'] = [];
        $goodsInfo['all'] = [];
        foreach($promotionInfo as $promotion){
            //使用范围：全场、品类、品牌、单品
            switch ($promotion['promotion_scope']) {
                //case BaiyangPromotionEnum::ALL_RANGE:      $rangKey = 'all'; break;
                case BaiyangPromotionEnum::CATEGORY_RANGE: $rangKey = 'category'; break;
                case BaiyangPromotionEnum::BRAND_RANGE:    $rangKey = 'brand'; break;
                case BaiyangPromotionEnum::SINGLE_RANGE:   $rangKey = 'single'; break;
                default: $rangKey = 'single';                        break;
            }
            if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                //单品id或品牌id或品类id
                $goodsInfo[$rangKey][] = [
                    'join' => $promotion['condition'],
                    'except_category_id' => $promotion['except_category_id'],
                    'except_brand_id' => $promotion['except_brand_id'],
                    'except_good_id' => $promotion['except_good_id'],
                ];
            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $goodsInfo];
    }

    /**
     * @desc 获取参加促销活动的商品优惠价 (列表页)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       -array   goodsList  商品列表信息  [二维数组] (商品id为goodsId、商品价格为price)
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *       -bool    tag_sign  用户是否绑定标签 (true:绑定 false:未绑定)
     * @return array [] 参加促销活动的商品信息
     * @author 吴俊华
     */
    public function getPromotionGoodsPrice($event,$class,$param)
    {
        //商品列表
        $goodsList = $param['goodsList'];
        //进行中的限时优惠活动
        $limitTime = $this->getProcessingPromotions($event,$class,['platform' => $param['platform'], 'user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        $discountArr = []; //优惠价数组
        foreach($goodsList as $goodsKey => $goodsDetail){
            $tagName = ''; //商品标签
            if(!empty($limitTime)){
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
                    'tag_sign' => isset($param['tag_sign']) ? $param['tag_sign'] : false,
                ];
                //判断商品参加限时优惠价或者会员价
                $discountArr = $this->getGoodsDiscountPrice($event,$class,$goodsParam);
            }
            if(isset($discountArr['discountPromotion']) && !empty($discountArr['discountPromotion'])){
                $discountPrice = $discountArr['discountPromotion']['price'];
                $discountType = $discountArr['discountPromotion']['promotion_type'];
                $tagName = isset($discountArr['discountPromotion']['tag_name']) ? $discountArr['discountPromotion']['tag_name'] : '';
            }else{
                $discountPrice = $goodsDetail['price'];
                $discountType = 0;
            }
            $goodsList[$goodsKey]['price'] = $discountPrice;
            $goodsList[$goodsKey]['discount_type'] = $discountType;
            $goodsList[$goodsKey]['tag_name'] = $tagName;
        }
        if(!empty($goodsList)){
            // 商品可售库存
            $stockArr = $this->func->getCanSaleStock(['goods_id' => implode(',',array_column($goodsList,'goodsId')), 'platform' => $param['platform']]);
            foreach ($goodsList as $key => $value){
                if(!is_array($stockArr)){
                    // 单个商品直接返回库存(int)
                    $goodsList[$key]['stock'] = $stockArr;
                }else{
                    // 多个商品返回数组
                    foreach ($stockArr as $kk => $vv){
                        if($kk == $value['goodsId']){
                            $goodsList[$key]['stock'] = $vv;
                            break;
                        }
                    }
                }
            }
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
    private function handleLimitBuyPromotion($limitBuy,$currentGoodsInfo)
    {
        foreach($limitBuy as $key => $value){
            if($value['promotion_scope'] == BaiyangPromotionEnum::ALL_RANGE || $value['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE  || $value['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE ){
                unset($limitBuy[$key]);
                continue;
            }
            // 限购件数
            $limitBuy[$key]['item_limit_unit'] = 0;
            $limitBuy[$key]['item_limit_number'] = 0;
            $rangeKey = 'id';
            foreach(json_decode($value['rule_value'],true) as $val){
                if($val['id'] == $currentGoodsInfo[$rangeKey]){
                    $limitBuy[$key]['item_limit_unit'] = BaiyangPromotionEnum::UNIT_ITEM;
                    $limitBuy[$key]['item_limit_number'] = $val['promotion_num'];
                    break;
                }
            }
        }
        // 获取最小件数的限购活动 （件和种、次分开处理）
        $limitBuyByItem = $this->sortLimitBuyList($limitBuy, true);
        $limitBuy = $this->sortLimitBuyList($limitBuy);
        $newLimitBuyList = [];
        // 处理种数、次数的限购活动
        foreach($limitBuy as $key => $value){
            if($value['limit_unit'] <= 1){
                unset($limitBuy[$key]);
                continue;
            }
            if(!isset($newLimitBuyList[$value['limit_unit']])){
                $newLimitBuyList[$value['limit_unit']] = $value;
            }
        }
        $newLimitBuyList = array_values($newLimitBuyList);
        $newLimitBuyList = []; // 商品详情里只显示单品和多单品的单品(即件数)
        $newLimitBuyList = $this->generateLimitBuyCopywriter($newLimitBuyList, $limitBuyByItem);
        return $newLimitBuyList;
    }

    /**
     * @desc 生成限购活动的促销文案
     * @param array $limitBuyList 限购活动列表 [二维数组]
     * @param array $limitBuyByItem 限购活动列表(最小件数的限购活动) [一维数组]
     * @return array $limitBuyList 生成促销文案后的限购活动列表
     * @author 吴俊华
     */
    private function generateLimitBuyCopywriter($limitBuyList, $limitBuyByItem)
    {
        $newLimitBuyList = [];
//        $newLimitBuyList['promotion_id'] = 0;
//        $newLimitBuyList['copywriter'] = '';
//        $newLimitBuyList['limit_number'] = BaiyangPromotionEnum::GOODS_MAX_PURCHASE_NUMBER; // 最大限购数(件数)
        foreach($limitBuyList as $key => $value){
            $copywriter = '';
            $newLimitBuyList['promotion_id'] = implode(',',array_column($limitBuyList,'promotion_id'));
            $limitUnit = array_column($limitBuyList,'limit_unit');
            $limitNumber = array_column($limitBuyList,'limit_number');
            for($i = 0;$i < count($limitUnit);$i++){
                $copywriter.= $limitNumber[$i].BaiyangPromotionEnum::$LimitBuyUnit[$limitUnit[$i]].'、';
            }
            $copywriter = rtrim($copywriter,'、');
            $newLimitBuyList['copywriter'] = $copywriter;
        }
        // 促销文案合并件到种、次里
        if(!empty($newLimitBuyList['copywriter'])){
            if(!empty($limitBuyByItem) && $limitBuyByItem['item_limit_number'] <= BaiyangPromotionEnum::GOODS_MAX_PURCHASE_NUMBER){
                $newLimitBuyList['promotion_id'] = $limitBuyByItem['promotion_id'].','.$newLimitBuyList['promotion_id'];
                $newLimitBuyList['copywriter'] = '单品限购'.$limitBuyByItem['item_limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$limitBuyByItem['item_limit_unit']].'、'.$newLimitBuyList['copywriter'];
                $newLimitBuyList['limit_number'] = $limitBuyByItem['item_limit_number'];
            }
//            else{
//                $newLimitBuyList['copywriter'] = '单品限购'.$newLimitBuyList['copywriter'];
//            }
        }else{
            if(!empty($limitBuyByItem) && $limitBuyByItem['item_limit_number'] <= BaiyangPromotionEnum::GOODS_MAX_PURCHASE_NUMBER){
                $newLimitBuyList['promotion_id'] = $limitBuyByItem['promotion_id'];
                $newLimitBuyList['copywriter'] = '单品限购'.$limitBuyByItem['item_limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$limitBuyByItem['item_limit_unit']];
                $newLimitBuyList['limit_number'] = $limitBuyByItem['item_limit_number'];
            }
        }
        return $newLimitBuyList;
    }

    /**
     * @desc 处理促销活动的促销文案 (满减、满折、满赠、包邮、加价购)
     * @param array $promotion 进行中的促销活动 [二维数组]
     * @return array $newLimitBuyList 处理后的限购活动信息
     * @author 吴俊华
     */
    private function handlePromotions($promotion)
    {
        $newPromotion = [];
        foreach($promotion as $key => $value) {
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
    public function getPromotionsByBrandId($event,$class,$param)
    {
        $brandId = (int)$param['brand_id'];
        $promotionArr = []; //促销活动
        //保证输出的key一致
        $this->editPromotionKey($promotionArr);
        //进行中的促销活动
        $param['promotion_type'] = '5,10,15,20,30,40';
        $promotionInfo = $this->getProcessingPromotions($event, $class, $param);
        if(empty($promotionInfo) || !in_array(BaiyangPromotionEnum::BRAND_RANGE, array_column($promotionInfo, 'promotion_scope'))){
            return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
        }
        // 根据品牌id筛选促销活动
        foreach ($promotionInfo as $key => $value){
            if($value['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE && in_array($brandId, explode(',', $value['condition']))){
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
        if(isset($coupon['coupon']) && !empty($coupon['coupon'])){
            $promotionArr['coupon'] = $coupon['coupon'];
        }
        unset($promotionArr['increaseBuy']);
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
    }

    /**
     * @desc 根据使用范围排序 [全场>品类>品牌>单品]
     * @param array $promotionList 促销活动列表[二维数组]
     * @return array [] 结果信息
     * @author 吴俊华
     */
    private function sortPromotionByRange($promotionList)
    {
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
     * @desc 处理疗程选项  [商品详情]
     * @param array $promotionArr 所有促销活动
     * @return array $promotionArr 处理后的疗程
     * @author 吴俊华
     */
    private function handleTreatment(&$promotionArr)
    {
        // 优惠价比疗程价小时，疗程不能选
        foreach ($promotionArr['treatment'] as $key => $value){
            $promotionArr['treatment'][$key]['canSelected'] = 1;
            if($promotionArr['discountInfo']['discount_type'] == BaiyangPromotionEnum::MEMBER_PRICE || $promotionArr['discountInfo']['discount_type'] == BaiyangPromotionEnum::MOM_PRICE){
                // 会员价/辣妈价 <= 疗程时，该疗程不能选
                if($value['price'] >= $promotionArr['discountInfo']['goods_price']){
                    $promotionArr['treatment'][$key]['canSelected'] = 0;
                }
            }else{
                // 原价、限时优惠 < 疗程时，该疗程不能选
                if($value['price'] > $promotionArr['discountInfo']['goods_price']){
                    $promotionArr['treatment'][$key]['canSelected'] = 0;
                }
            }
        }
    }

    /**
     * @desc 获取商品关联组商品  [商品详情，目前pc端才有]
     * @param int $goodsId 商品id
     * @return array $goodSets 商品关联组信息
     * @author 吴俊华
     */
    private function getGoodsSets($goodsId)
    {
        $baseData = BaseData::getInstance();
        $setIdsArr = $baseData->getData([
            'table' => 'Shop\Models\BaiyangGoodsToSets',
            'column' => 'set_id',
            'where' => 'where goods_id = :goods_id:',
            'bind' => ['goods_id' => $goodsId],
        ]);
        $goodSets = [];
        if(!empty($setIdsArr)){
            $setIdsStr = implode(',', array_column($setIdsArr, 'set_id'));
            $goodSets = $baseData->getData([
                'table' => 'Shop\Models\BaiyangGoodsToSets as s',
                'join' => 'left join Shop\Models\BaiyangGoodsSets as se on se.id = s.set_id left join Shop\Models\BaiyangGoods as g on s.goods_id = g.id',
                'order' => 'order by se.update_time desc,s.sort,s.set_id desc',
                'column' => 's.goods_id,s.name goods_name',
                'where' => "where s.set_id in ({$setIdsStr}) and g.is_on_sale = 1", //pc端用is_on_sale
            ]);
            $goodsIds = [];
            // 去重：相同的商品
            foreach($goodSets as $key => $value){
                $goodSets[$key]['selected'] = 0;
                if($goodsId == $value['goods_id']){
                    $goodSets[$key]['selected'] = 1;
                }
                if(in_array($value['goods_id'], $goodsIds)){
                    unset($goodSets[$key]);
                }else{
                    $goodsIds[] = $value['goods_id'];
                }
            }
            $goodSets = array_values($goodSets);
        }
        return $goodSets;
    }

    /**
     * @desc 处理加价购活动的换购品数据 (商品详情、库存、商品状态等)
     * @param array $promotion 单个加价购活动 [一维数组]
     * @param string $platform 平台
     * @return string $copywriter 促销文案
     * @author 吴俊华
     */
    private function handleExchangeGoodsData($promotion, $platform)
    {
        $changeGroup = []; //当前加价购活动的所有换购品信息
        //换购品匹配商品信息
        foreach($promotion['rule_value'] as $ruleValue) {
            $changeGoodsGroup = $ruleValue['reduce_group'];
            $condition = [
                'goods_id' => implode(',',array_column($changeGoodsGroup,'product_id')),
                'platform' => $platform
            ];
            $goodsDetail = $this->getGoodsDetail($condition);
            $stockArr = $this->func->getCanSaleStock($condition);

            // 换购品库存
            foreach ($goodsDetail as $kk => $vv){
                if(is_array($stockArr)){
                    foreach ($stockArr as $k => $v){
                        if($vv['id'] == $k){
                            $goodsDetail[$kk]['stock'] = $v;
                            continue;
                        }
                    }
                }else{
                    $goodsDetail[$kk]['stock'] = $stockArr;
                }
            }
            foreach($changeGoodsGroup as $key => $val){
                foreach($goodsDetail as $kk => $vv){
                    $goodsStatus = 0;
                    //缺货
                    if($vv['stock'] < 1){
                        $goodsStatus = 2;
                    }
                    //下架
                    if($vv['sale'] == 0){
                        $goodsStatus = 1;
                    }
                    if($val['product_id'] == $vv['id']){
                        if($vv['product_type'] != 0){
                            continue;
                        }
                        $changeGroup[] = [
                            'goods_id' => $vv['id'],
                            'goods_name' => $vv['name'],
                            'specifications' => $vv['specifications'],
                            'stock_type' => $vv['is_use_stock'],
                            'drug_type' => $vv['drug_type'],
                            'brand_id' => $vv['brand_id'],
                            'category_id' => $vv['category_id'],
                            'sale' => $vv['sale'],
                            'stock' => $vv['stock'],
                            'goods_image' => $vv['goods_image'],
                            'sku_price' => $vv['sku_price'],
                            'market_price' => $vv['sku_market_price'],
                            'discount_price' => $val['reduce_price'],
                            'product_type' => $vv['product_type'],
                            'selected' => 0,
                            'can_select' =>  0,
                            'goods_status' => $goodsStatus,
                            'full_price' => $ruleValue['full_price'], // 门槛数(金额/件数)
                        ];
                    }
                }
            }
        }
        $changeGroup = $this->sortChangeGroup($changeGroup);
        return $changeGroup;
    }

    /**
     * @desc 根据商品id获取最优惠价、可售库存、商品状态等信息 【商品详情的多品规】
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
    public function getGoodsPromotionInfoById($event,$class,$param)
    {
        $promotions = []; //促销活动相关信息
        $promotionArr = []; //促销活动归类
        $isJoinPromotion = false; //是否参加促销活动 (会员价/疗程/限时优惠/限购，用于标识售罄)
        $goodsInfo = $this->getGoodsDetail($param);
        if(empty($goodsInfo)) {
            return ['error' => 1,'code' => HttpStatus::NOT_GOOD_INFO,'data' => []];
        }
        $currentGoodsInfo = $goodsInfo[0]; //当前商品信息

        //初始化
        $goodStatus = 0;
        $stock = $this->func->getCanSaleStock(['goods_id' => $currentGoodsInfo['id'],'platform' => $param['platform']]);
        //获取可售库存和商品状态 (0:正常 1:下架 2:缺货 3:售罄)
        if($stock < 1){
            $goodStatus = 2;
        }
        if($currentGoodsInfo['sale'] == 0){
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
        // 海外购不查促销
        if($currentGoodsInfo['is_global']){
            $promotionArr['discountInfo']['display_add_cart'] = 1;
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
        }

        //进行中的促销活动 (限时优惠、限购)
        $condition = [
            'platform' => $param['platform'],
            'user_id' => $param['user_id'],
            'is_temp' => $param['is_temp'],
            'promotion_type' => BaiyangPromotionEnum::LIMIT_BUY.','.BaiyangPromotionEnum::LIMIT_TIME,
        ];
        $promotionInfo = $this->getProcessingPromotions('','',$condition);
        if(empty($promotionInfo)){
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
        }
        //商品匹配促销活动
        $goodMatchPromotion = $this->goodMatchPromotion($goodsInfo,$promotionInfo);
        if(!empty($goodMatchPromotion)){
            foreach($goodMatchPromotion as $key => $value){
                if($value['promotionInfo']['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                    $promotions[] = $value['promotionInfo'];
                }
            }
        }

        //整合促销活动
        if(!empty($promotions)){
            foreach ($promotions as $promotionTypeKey => $value) {
                $promotionTypeKey = $this->getPromotionKey($value['promotion_type']);
                if(empty($promotionTypeKey)){
                    return ['error' => 1,'code' => HttpStatus::SYSTEM_ERROR,'data' => $promotionArr];
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
        $limitTimeArr = [];
        if(isset($promotionArr['limitTime'])){
            $limitTimeArr = $promotionArr['limitTime'];
            unset($promotionArr['limitTime']);
        }
        $discountInfo = $promotionArr['discountInfo'];
        if(!empty($promotionArr)){
            unset($promotionArr['discountInfo']);
        }
        if(isset($promotionArr['limitBuy']['promotion_id']) && $promotionArr['limitBuy']['promotion_id'] > 0){
            $isJoinPromotion = true;
        }
        unset($promotionArr['limitBuy']);

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
        $discountPromotion = $this->getGoodsDiscountPrice($event,$class,$discountParam);
        if(!empty($discountPromotion['discountPromotion'])){
            $promotionType = $discountPromotion['discountPromotion']['promotion_type'];
            $discountPrice = $discountPromotion['discountPromotion']['price'];
            //限时优惠价
            if($promotionType == BaiyangPromotionEnum::LIMIT_TIME){
                $endTime = $discountPromotion['discountPromotion']['end_time'];
            }
            $tagName = $discountPromotion['discountPromotion']['tag_name'];
        }
        if($promotionType > 0){
            $isJoinPromotion = true;
        }
        $promotionArr['discountInfo']['discount_type'] = $promotionType;
        $promotionArr['discountInfo']['goods_price'] = sprintf('%.2f',$discountPrice);
        $promotionArr['discountInfo']['market_price'] = $currentGoodsInfo['sku_market_price'];
        // 计算限时优惠的折扣
        if($promotionType == BaiyangPromotionEnum::LIMIT_TIME){
            $promotionArr['discountInfo']['rebate'] = bcmul(bcdiv($discountPromotion['discountPromotion']['price'],$currentGoodsInfo['sku_market_price'],2),10,1);
            if($promotionArr['discountInfo']['rebate'] <= 0 ) $promotionArr['discountInfo']['rebate'] = 0.1;
        }
        $promotionArr['discountInfo']['end_time'] = $endTime;
        $promotionArr['discountInfo']['tag_name'] = $tagName;
        //缺货状态改为售罄状态 (2 -> 3)
        if($goodStatus == 2 && $isJoinPromotion){
            $goodStatus = 3;
        }
        $promotionArr['discountInfo']['stock'] = $stock;
        $promotionArr['discountInfo']['goods_status'] = $goodStatus;
        $promotionArr['discountInfo'] = array_merge($discountInfo, $promotionArr['discountInfo']);
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $promotionArr];
    }


    /**
     * @desc 根据商品id获取最优惠价
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -int|string   goods_id  商品id
     *       -string       platform  平台【pc、app、wap】
     *       -int          user_id   用户id (临时用户或真实用户id)
     *       -int          is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getDiscountedPrice($event,$class,$param)
    {
        $goodsList = $this->getGoodsDetail($param);
        if(empty($goodsList)) {
            return ['error' => 1,'code' => HttpStatus::NOT_GOOD_INFO,'data' => []];
        }
        $param['tag_sign'] = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $param['user_id'], 'is_temp' => $param['is_temp']]);
        //进行中的限时优惠活动
        $limitTime = $this->getProcessingPromotions($event,$class,['platform' => $param['platform'], 'user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        $promotionArr = []; //结果数组
        $discountArr = []; //优惠价数组
        foreach($goodsList as $goodsKey => $goodsDetail){
            if(!empty($limitTime)){
                $goodsParam = [
                    'goodsInfo' => [
                        'goods_id' => $goodsDetail['id'],
                        'sku_price' => $goodsDetail['sku_price'],
                        'goods_number' => 1,
                    ],
                    'platform' => $param['platform'],
                    'user_id' => $param['user_id'],
                    'is_temp' => $param['is_temp'],
                    'limitTime' => $limitTime,
                    'tag_sign' => isset($param['tag_sign']) ? $param['tag_sign'] : false,
                ];
                //判断商品参加限时优惠价或者会员价
                $discountArr = $this->getGoodsDiscountPrice($event,$class,$goodsParam);
            }
            if(isset($discountArr['discountPromotion']) && !empty($discountArr['discountPromotion'])){
                $promotionArr[$goodsDetail['id']] = $discountArr['discountPromotion']['price'];
            }else{
                $promotionArr[$goodsDetail['id']] = $goodsDetail['sku_price'];
            }
        }
        return ['error' => 0, 'code' => HttpStatus::SUCCESS, 'data' => $promotionArr];
    }

}