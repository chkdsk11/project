<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/26 1504
 */
namespace Shop\Home\Datas;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\CacheKey;

class BaiyangO2OPromotionData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 根据各个平台获取进行中的促销活动信息 【促销活动】
     * @param array $param [一维数组]
     *       -string platform 平台【pc、app、wap】 (必填)
     *       -int  user_id 用户id (可填)
     *       -int  is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     * @param string $promotionType 活动类型 (多个以逗号隔开)
     * @param int $promotionId 活动id (兼容购物车里的促销列表)
     * @return array|bool [] 进行中的促销活动|false
     * @author 吴俊华
     */
    public function getPromotionsInfo($param, $promotionType = '', $promotionId = 0)
    {
        $param['is_temp'] = isset($param['is_temp']) ? $param['is_temp'] : 0;
        if(empty($param['platform'])){
            return [];
        }
        $redis = $this->cache;
        $redis->selectDb(5);
        $promotionCache = $redis->getValue(CacheKey::EFFECTIVE_PROMOTION);
        // 缓存的促销活动
        if(!empty($promotionCache)){
            $param['promotion_cache'] = $promotionCache;
            return  $this->getCacheEffectivePromotion($param, $promotionType, $promotionId);
        }
        $memberTag = 0; //标签id(不指定为0)
        $where = 'where pro.promotion_platform_'.$param['platform'].' = :platform: and pro.promotion_status != :promotion_status: and pro.promotion_start_time < :nowTime: and pro.promotion_end_time > :nowTime:';
        $promotionCondition = [
            'table' => '\Shop\Models\BaiyangPromotion as pro',
            'join' => 'left join \Shop\Models\BaiyangPromotionRule as rules on pro.promotion_id = rules.promotion_id',
            'column' => 'pro.promotion_id,pro.promotion_title,pro.promotion_copywriter,pro.promotion_type,pro.promotion_scope,pro.promotion_mutex,pro.promotion_end_time,pro.promotion_for_users,pro.promotion_is_real_pay,rules.condition,rules.rule_value,rules.except_category_id,rules.except_brand_id,rules.except_good_id,rules.join_times,rules.is_superimposed,rules.offer_type,rules.limit_unit,rules.limit_number,pro.promotion_platform_pc,pro.promotion_platform_app,pro.promotion_platform_wap,pro.promotion_platform_wechat,rules.member_tag',
            'bind' => [
                'platform' => 1,
                'nowTime' => time(),
                'promotion_status' => BaiyangPromotionEnum::PROMOTION_CANCEL,
                'default_people' => 0, //限购和限时优惠使用
                'all_people' => BaiyangPromotionEnum::ALL_PEOPLE,
                'new_user' => BaiyangPromotionEnum::HAVE_NOT_SHOPPING, // 默认新用户
            ],
            'where' => $where." and pro.promotion_for_users in(:default_people:,:all_people:,:new_user:) and rules.member_tag in({$memberTag})"
        ];

        //判断是否老用户
        if(isset($param['user_id']) && !empty($param['user_id']) && $param['is_temp'] == 0){
            // 判断用户是否绑定标签
            $tagSign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $param['user_id'], 'is_temp' => $param['is_temp']]);
            if(!empty($tagSign)){
                //会员标签
                $userTagCondition = [
                    'table' => '\Shop\Models\BaiyangUserGoodsPriceTag as a',
                    'column' => 'a.tag_id',
                    'join' => 'left join \Shop\Models\BaiyangGoodsPriceTag as b on a.tag_id = b.tag_id',
                    'bind' => [
                        'user_id' => $param['user_id'],
                    ],
                    'where' => 'where a.user_id = :user_id: and b.status = 1 and a.status = 1 group by a.tag_id'
                ];
                $userTagInfo = $this->getData($userTagCondition);
                if(!empty($userTagInfo)){
                    $tagIds = ''; //会员标签id
                    foreach($userTagInfo as $key => $value){
                        $tagIds .= $value['tag_id'].',';
                    }
                    //促销活动的会员标签多个值
                    if(!empty($tagIds)){
                        $memberTag = $memberTag.','.substr($tagIds,0,strlen($tagIds)-1);
                    }
                }
            }

            //新用户增加用户绑定的标签
            $promotionCondition['where'] = $where." and pro.promotion_for_users in(:default_people:,:all_people:,:new_user:) and rules.member_tag in({$memberTag})";
            //用户订单信息
            $userOrderCondition = [
                'table' => '\Shop\Models\BaiyangOrder',
                'column' => 'id',
                'bind' => [
                    'user_id' => $param['user_id'],
                ],
                'where' => 'where user_id = :user_id: and payment_id > 0'
            ];
            $userOrderInfo = $this->countData($userOrderCondition);
            //订单已支付的用户为老用户
            if(!empty($userOrderInfo)){
                unset($promotionCondition['bind']['new_user']);
                $promotionCondition['bind']['old_user'] = BaiyangPromotionEnum::HAVE_SHOPPING;
                $promotionCondition['where'] = $where." and pro.promotion_for_users in(:default_people:,:all_people:,:old_user:) and rules.member_tag in({$memberTag})";
            }
        }
        //获取指定类型的促销活动
        if(!empty($promotionType)){
            $promotionCondition['where'] .= " and pro.promotion_type in({$promotionType})";
        }
        //根据活动id查活动信息
        if($promotionId > 0){
            $promotionCondition['bind']['promotion_id'] = $promotionId;
            $promotionCondition['where'] .= ' and pro.promotion_id = :promotion_id:';
        }
        $processingPromotion = $this->getData($promotionCondition);
//        foreach ($processingPromotion as $key => $value){
//            $processingPromotion[$key]['tag_goods_id'] = [];
//        }
//        //进行中的促销活动匹配会员标签商品
//        if(!empty($userTagInfo) && !empty($processingPromotion)){
//            $this->matchUserTagGoodsId($processingPromotion, $userTagInfo);
//        }
        return $processingPromotion;
    }

    /**
     * @desc 指定用户标签的促销活动匹配会员标签商品
     * @param array $processingPromotion 进行中的促销活动
     * @param array $userTagInfo 用户的会员标签商品信息
     * @return array [] 匹配后的促销活动
     * @author 吴俊华
     */
    private function matchUserTagGoodsId(&$processingPromotion, $userTagInfo)
    {
        $tagGoodsId = [];
        foreach ($userTagInfo as $key => $value){
            $tagGoodsId[$value['tag_id']][] = $value['goods_id'];
        }
        foreach ($processingPromotion as $key => $value){
            if($value['member_tag'] > 0){
                foreach ($tagGoodsId as $kk => $vv){
                    if($value['member_tag'] == $kk){
                        $processingPromotion[$key]['tag_goods_id'] = array_unique($vv);
                        break;
                    }
                }
            }
        }
    }

    /**
     * @desc 根据条件获取缓存中的促销活动
     * @param array $param [一维数组]
     *       -string platform 平台【pc、app、wap】 (必填)
     *       -int  user_id 用户id (可填)
     *       -int  is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *       -array promotion_cache 缓存的促销活动
     * @param string $promotionType 活动类型 (多个以逗号隔开)
     * @param int $promotionId 活动id (兼容购物车里的促销列表)
     * @return array [] 匹配后的促销活动
     * @author 吴俊华
     */
    private function getCacheEffectivePromotion($param, $promotionType = '', $promotionId = 0)
    {
        $promotionCache = $param['promotion_cache'];
        $memberTag = 0; // 标签id(不指定为0)
        $peopleKey = 'new'; // 默认新会员+所有人
        $nowTime = time();
        $processingPromotion = [];
        $condition = [
            'platform' => $param['platform'],
            'now_time' => $nowTime,
            'promotion_type' => $promotionType,
            'promotion_id' => $promotionId,
        ];

        //判断是否老用户
        if(isset($param['user_id']) && !empty($param['user_id']) && $param['is_temp'] == 0){
            // 判断用户是否绑定标签
            $tagSign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $param['user_id'], 'is_temp' => $param['is_temp']]);
            if(!empty($tagSign)){
                //会员标签
                $userTagCondition = [
                    'table' => '\Shop\Models\BaiyangUserGoodsPriceTag as a',
                    'column' => 'a.tag_id',
                    'join' => 'left join \Shop\Models\BaiyangGoodsPriceTag as b on a.tag_id = b.tag_id',
                    'bind' => [
                        'user_id' => $param['user_id'],
                    ],
                    'where' => 'where a.user_id = :user_id: and b.status = 1 and a.status = 1 group by a.tag_id'
                ];
                $userTagInfo = $this->getData($userTagCondition);
                if(!empty($userTagInfo)){
                    $tagIds = ''; //会员标签id
                    foreach($userTagInfo as $key => $value){
                        $tagIds .= $value['tag_id'].',';
                    }
                    //促销活动的会员标签多个值
                    if(!empty($tagIds)){
                        $memberTag = $memberTag.','.substr($tagIds,0,strlen($tagIds)-1);
                    }
                }
            }

            //用户订单信息
            $userOrderCondition = [
                'table' => '\Shop\Models\BaiyangOrder',
                'column' => 'id',
                'bind' => [
                    'user_id' => $param['user_id'],
                ],
                'where' => 'where user_id = :user_id: and payment_id > 0'
            ];
            $userOrderInfo = $this->countData($userOrderCondition);
            if(!empty($userOrderInfo)){
                $peopleKey = 'old';
            }
        }
        $memberTagArr = array_unique(explode(',',$memberTag));
        $condition['people_key'] = $peopleKey;
        $condition['member_tag'] = $memberTagArr;
        // 判断是否满足活动
        foreach ($promotionCache as $key => $value){
            if($this->isSatisfyPromotion($value, $condition)){
                $processingPromotion[] = $value;
            }
        }
        return $processingPromotion;
    }

    /**
     * @desc 判断是否满足条件的促销活动
     * @param array $promotion 单个促销活动 [一维数组]
     * @param array $condition 必要条件
     * @return bool true|false 满足:true 不满足:false
     * @author 吴俊华
     */
    private function isSatisfyPromotion($promotion, $condition)
    {
        // 必要条件
        $platform = $condition['platform'];
        $memberTagArr = $condition['member_tag'];
        $peopleKey = $condition['people_key'];
        $nowTime = $condition['now_time'];
        $promotionType = $condition['promotion_type'];
        $promotionId = $condition['promotion_id'];
        // 新老会员
        $people = [
            'new' => [0,BaiyangPromotionEnum::ALL_PEOPLE,BaiyangPromotionEnum::HAVE_NOT_SHOPPING],
            'old' => [0,BaiyangPromotionEnum::ALL_PEOPLE,BaiyangPromotionEnum::HAVE_SHOPPING],
        ];
        // 进行中的促销活动
        if($promotion['promotion_start_time'] < $nowTime && $promotion['promotion_end_time'] > $nowTime){
            // 查询的活动平台与当前活动平台不匹配
            if(!$promotion['promotion_platform_'.$platform]){
                return false;
            }
            // 查询的会员标签与当前会员标签不匹配
            if(!in_array($promotion['member_tag'], $memberTagArr)){
                return false;
            }
            // 查询的适用人群与当前适用人群不匹配
            if(!in_array($promotion['promotion_for_users'], $people[$peopleKey])){
                return false;
            }
            // 查询的活动类型与当前活动类型不匹配
            if(!empty($promotionType) && !in_array($promotion['promotion_type'],explode(',',$promotionType))){
                return false;
            }
            // 查询的活动id与当前活动id不匹配
            if(!empty($promotionId) && !in_array($promotion['promotion_id'],explode(',',$promotionId))){
                return false;
            }
        }else{
            return false;
        }
        return true;
    }

    /**
     * @desc 插入订单的促销日志
     * @param $param
     * @return bool
     * @author 柯琼远
     */
    public function insertPromotionDetailLog($param) {
        foreach ($param['availPromotionList'] as $value) {
            // 满减，满折，加价购，满赠，包邮
            if ($value['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY) {
                $discount_type = 0;
                $discount_money = 0;
                switch ($value['promotion_type']) {
                    case BaiyangPromotionEnum::FULL_MINUS :
                        $discount_type = 4;
                        $discount_money = $value['resultInfo']['reduce_price'];
                        break;
                    case BaiyangPromotionEnum::FULL_GIFT :
                        $discount_type = 5;
                        break;
                    case BaiyangPromotionEnum::EXPRESS_FREE :
                        $discount_type = 6;
                        break;
                    case BaiyangPromotionEnum::FULL_OFF :
                        $discount_type = 7;
                        $discount_money = $value['resultInfo']['reduce_price'];
                        break;
                    case BaiyangPromotionEnum::INCREASE_BUY :
                        $discount_type = 8;
                        break;
                }
                $addData = array(
                    'table' => '\Shop\Models\BaiyangOrderPromotionDetail',
                    'bind'  => array(
                        'order_sn'           => $param['orderSn'],
                        'user_id'            => $param['userId'],
                        'discount_type'      => $discount_type,
                        'promotion_id'       => $value['promotion_id'],
                        'promotion_name'     => $value['promotion_title'],
                        'promotion_remark'   => $value['resultInfo']['copywriter'],
                        'discount_money'     => $discount_money,
                        'promotion_range'    => $value['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE ? 'product' : $value['promotion_scope'],
                        'create_time'        => date('Y-m-d H:i:s'),
                    )
                );
                if (!$this->addData($addData)) {
                    return false;
                }

                // 插入赠品日志
                if ($value['promotion_type'] == BaiyangPromotionEnum::FULL_GIFT) {
                    foreach ($value['resultInfo']['premiums_group'] as $val) {
                        $addData = array(
                            'table' => '\Shop\Models\BaiyangOrderPromotionDetailGift',
                            'bind'  => array(
                                'order_sn'           => $param['orderSn'],
                                'promotion_id'       => $value['promotion_id'],
                                'promotion_name'     => $value['promotion_title'],
                                'gift_code'          => $val['goods_id'],
                                'gift_name'          => $val['goods_name'],
                                'gift_quantity'      => $val['goods_number'],
                                'gift_value'         => 0,
                                'gift_type'          => 1,
                                'create_time'        => date('Y-m-d H:i:s'),
                            )
                        );
                        if (!$this->addData($addData)) {
                            return false;
                        }
                    }
                }
            }
        }
        foreach ($param['goodsList'] as $value) {
            // 限时优惠
            if (isset($value['discountPromotion']) && !empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME) {
                $limitTimeInfo = $value['discountPromotion'];
                $addData = array(
                    'table' => '\Shop\Models\BaiyangOrderPromotionDetail',
                    'bind'  => array(
                        'order_sn'           => $param['orderSn'],
                        'user_id'            => $param['userId'],
                        'discount_type'      => 3,
                        'promotion_id'       => $limitTimeInfo['id'],
                        'promotion_name'     => $limitTimeInfo['limit_time_title'],
                        'promotion_remark'   => $limitTimeInfo['limit_time_title'],
                        'discount_money'     => bcsub($value['sku_total'], $value['discount_total'], 2),
                        'promotion_range'    => 'product',
                        'create_time'        => date('Y-m-d H:i:s'),
                    )
                );
                if (!$this->addData($addData)) {
                    return false;
                }
            }
            // 限购
            if (isset($value['limitBuyIdList']) && !empty($value['limitBuyIdList'])) {
                foreach ($value['limitBuyIdList'] as $promotion_id) {
                    $addData = array(
                        'table' => '\Shop\Models\BaiyangLimitedLog',
                        'bind'  => array(
                            'user_id'        => $param['userId'],
                            'order_sn'       => $param['orderSn'],
                            'goods_id'       => $value['goods_id'],
                            'brand_id'       => $value['brand_id'],
                            'category_id'    => $value['category_id'],
                            'goods_number'   => $value['goods_number'],
                            'activity_id'    => $promotion_id,
                            'is_delete'      => 0,
                            'add_time'       => time(),
                        )
                    );
                    if (!$this->addData($addData)) {
                        return false;
                    }
                }
            }
        }
        // 优惠券
        if (!empty($param['couponInfo'])) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrderPromotionDetail',
                'bind'  => array(
                    'order_sn'           => $param['orderSn'],
                    'user_id'            => $param['userId'],
                    'discount_type'      => 1,
                    'promotion_id'       => $param['couponInfo']['coupon_sn'],
                    'promotion_name'     => $param['couponInfo']['coupon_name'],
                    'promotion_remark'   => $param['couponInfo']['coupon_name'],
                    'discount_money'     => $param['couponInfo']['coupon_price'],
                    'promotion_range'    => $param['couponInfo']['coupon_range'] == BaiyangPromotionEnum::SINGLE_RANGE ? 'product' : $param['couponInfo']['coupon_range'],
                    'create_time'        => date('Y-m-d H:i:s'),
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        return true;
    }

}