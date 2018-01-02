<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/17
 * Time: 上午 11:16
 */
namespace Shop\Home\Listens;

use Phalcon\Mvc\User\Component;
use Shop\Home\Datas\BaiyangCouponData;
use Shop\Home\Datas\BaiyangCouponRecordData;
use Shop\Home\Datas\BaiyangGoodSetData;
use Shop\Home\Datas\BaiyangOrderData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangOrderPromotionData;
use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\CacheGoodsKey;
use Shop\Models\CacheKey;
use Shop\Models\HttpStatus;
use Shop\Models\BaiyangCouponEnum;
use Shop\Home\Datas\BaiyangPromotionData;
use Shop\Home\Datas\BaiyangUserGoodsPriceTagData;
use Shop\Home\Datas\BaiyangGoodsTreatmentData;
use Shop\Home\Datas\BaiyangMomApplyData;
use Shop\Home\Datas\BaiyangMomGetGiftData;
use Shop\Home\Datas\BaiyangMomGiftActivityData;
use Shop\Home\Datas\BaiyangMomGiftReportData;
use Shop\Home\Datas\BaiyangGoodsPrice;
use Shop\Models\OrderEnum;

class BaseListen extends Component
{
    /**
     * @过滤去除不要的数据
     * @param string $str       //需要的字段,用","号隔开
     * @param array  $param     //需过滤的数据
     * @return array
     * @author  梁伟
     * @date    2016-10-11
     */
    protected function filterData($str='',$param=array())
    {
        if( !empty($str) && !empty($param) && is_array($param) ){
            $string = ','.$str.',';
            $array = array();
            foreach( $param as $k=>$v ){
                if( strstr($string,','.$k.',') !== false){
                    $array[$k] = $v;
                }else if( !empty($v) && is_array($v) ){
                    $array[$k] = $this->filterData($str,$v);
                }
            }
            return $array;
        }
        return $param;
    }

    /**
     * @desc将二维数组按某字段进行排列
     * @param array $array       需要进行排序的数组
     * @param string $sort_key   排序的字段
     * @param string $sort_type  排序的类型
     * @return array $array      排序处理好的数组
     */
    protected function arraySortByKey($array = array(), $sort_key, $sort_type = 'asc')
    {
        $sort_array = array();
        if (($sort_type != 'asc') && ($sort_type != 'desc'))
        {
            return $array;
        }
        if(!is_array($array)){
            return $array;
        }
        foreach ($array as $arr)
        {
            $sort_array[] = $arr[$sort_key];
        }
        switch ($sort_type)
        {
            case 'desc':
                array_multisort($sort_array, SORT_DESC, $array);
                break;
            case 'asc':
            default:
                array_multisort($sort_array, SORT_ASC, $array);
        }
        return $array;
    }

    /**
     * @desc 根据各个平台商品id获取商品详情 【促销活动】
     * @param array $param
     *       -string goods_id 商品单个或多个id【多个以逗号隔开】
     *       -string platform 平台【pc、app、wap】
     * @return array [] 结果信息
     * @author 吴俊华
     */
    protected function getGoodsDetail($param)
    {
        //商品详情
        $goodsInfo = [];
        $goods_idArr = explode(',', $param['goods_id']);
        $countgoods_id = count($goods_idArr);
        if(isset($param['goods_number']) && !empty($param['goods_number'])){
            $goodsNumberArr = explode(',', $param['goods_number']);
            $countGoodsNumber = count($goodsNumberArr);
            if($countgoods_id !== $countGoodsNumber){
                return false;
            }
        }
        $skuData = BaiyangSkuData::getInstance();
        for ($i = 0; $i < $countgoods_id; $i++) {
            $skuInfo = $skuData->getSkuInfoLess($goods_idArr[$i], $param['platform']);
            if ($skuInfo) {
                $goodsInfo[] = $skuInfo;
            } else {
                $this->log->error("该商品不存在，商品ID：{$goods_idArr[$i]}");
            }
        }
        //商品数量
        if(isset($goodsNumberArr) && isset($countGoodsNumber) && !empty($goodsNumberArr) && !empty($countGoodsNumber)){
            for ($i = 0; $i < $countGoodsNumber; $i++) {
                $goodsInfo[$i]['goods_number'] = $goodsNumberArr[$i];
            }
        }
        return $goodsInfo;
    }

    /**
     * @desc 根据商品id获取其处方药类型
     * @param $param
     *          - goods_id 商品id
     *          - platform 平台标识  pc app wap
     * @author 邓永军
     */
    protected function getDrugType($param)
    {
        $drug_type=BaiyangSkuData::getInstance()
            ->getDrugType($param);
        return $drug_type;
    }

    /**
     * @desc 判断是否新用户
     * @param $param
     *          - user_id 用户id
     * @author 邓永军
     * @return int
     */
    protected function isNewUser($param)
    {
        $is_new_user=BaiyangOrderData::getInstance()
            ->isNewUser($param['user_id']);
        return $is_new_user;
    }

    /**
     * @desc 根据参数获取优惠券列表
     * @param $param
     *         - platform : String 平台类型 pc app wap | 必填
     *         - user_id ：String 用户id 用于区分权限和获取指定用户号码 | 可填
     * @return mixed
     * @author 邓永军
     */
    protected function getMyCouponList($param,$goods_id = 0)
    {
        $list = BaiyangCouponData::getInstance()
            ->getCouponList($param,$goods_id);
        return $list;
    }

    /**
     * @desc 通过user_id查询用户手机号码
     * @param $param
     *          - user_id 用户id
     * @return mixed
     * @author 邓永军
     */
    public function getPhone($param)
    {
        $phone = BaiyangUserData::getInstance()->getPhone($param['user_id']);
        return $phone;
    }

    /**
     * @desc 通过商品ID获取品类树
     * @param $goods_id
     * @return mixed
     * @author 邓永军
     */
    public function getSkuCategoryPath($goods_id,$platform)
    {
        $path = BaiyangSkuData::getInstance()
            ->getSkuCategoryPath($goods_id,$platform);
        return $path;
    }

    /**
     * @desc 通过商品ID获取品牌ID
     * @param $goods_id
     * @return mixed
     * @author 邓永军
     */
    public function getSkuBrandId($goods_id,$platform)
    {
        $brandId=BaiyangSkuData::getInstance()
            ->getSkuBrandId($goods_id,$platform);
        return $brandId;
    }

    /**
     * @desc 通过用户id和优惠券码获取已经领取数量
     * @param $user_id
     * @param $coupon_sn
     * @return mixed
     * @author 邓永军
     */
    public function countCouponHasBring($user_id,$coupon_sn)
    {
        $count=BaiyangCouponRecordData::getInstance()->countCouponHasBring($user_id,$coupon_sn);
        return $count;
    }

    /**
     * @desc 数组去重
     * @param array $array 二维数组
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function removeDuplicate($array)
    {
        $result = [];
        for($i=0; $i<count($array); $i++){
            $source = $array[$i];
            if(array_search($source,$array) == $i && $source != "" ){
                $result[] = $source;
            }
        }
        return $result;
    }

    /**
     * @desc 排除超过单用户参加次数的促销活动 【促销活动】
     * @param array $param
     * @param array $promotionInfo 进行中的促销活动
     * @return array $promotionInfo 处理后的促销活动
     * @author 吴俊华
     */
    protected function removeOverUserPromotion($param,$promotionInfo)
    {
        //单用户参加促销活动次数超过后台设置的数量时，排除该活动
        $promotionIds = '';
        foreach($promotionInfo as $key => $value){
            $promotionIds .= $value['promotion_id'].',';
        }
        if(!empty($promotionIds)){
            //单个或多个促销活动id
            $promotionIds = substr($promotionIds,0,strlen($promotionIds)-1);
            //用户参加过的促销活动
            $userPromotion = BaiyangOrderPromotionData::getInstance()->getUserPromotions($param,$promotionIds);
            //排除超过单用户参加次数的促销活动
            if(!empty($userPromotion)){
                foreach($userPromotion as $key => $value){
                    foreach($promotionInfo as $kk => $vv){
                        if($value['promotion_id'] == $vv['promotion_id'] && $vv['join_times'] != 0 && $vv['join_times'] <= $value['counts']){
                            array_splice($promotionInfo,$kk,1);
                        }
                    }
                }
            }

        }
        return $promotionInfo;
    }

    /**
     * @desc 根据标签id获取商品id
     * @param $tagId 标签id
     * @return mixed
     * @author 邓永军
     */
    public function getgoods_idFromTagId($tagId)
    {
        $goods_id_arr=BaiyangSkuData::getInstance()->getgoods_idFromTagId($tagId);
        return $goods_id_arr;
    }

    /**
     * @desc 根据参数获取套餐信息
     * @param $param
     *      -platform 【pc、app、wap】 平台
     *      -goods_id 多个个商品id
     * @return mixed
     * @author 邓永军
     */
    public function GoodSetList($param)
    {
        $result= BaiyangGoodSetData::getInstance()->getGoodSetList($param);
        return $result;
    }

    /**
     * @desc 根据套餐组id和对应商品id获取对应套餐的数量和价格
     * @param $group_id
     * @param $sku_id
     * @return mixed
     * @author 邓永军
     */
    public function FavourableInfo($group_id,$sku_id)
    {
        $result = BaiyangGoodSetData::getInstance()->getFavourableInfo($group_id,$sku_id);
        return $result;
    }

    /**
     * @desc 商品匹配促销活动
     * @param array $goodsInfo 商品信息
     * @param array $promotionInfo 进行中的促销活动
     * @param bool $refactor 重构数组的开关 (false为关，true为开，默认为false)
     * @return array|bool $goodsPromotion|false 促销活动商品或false
     * @author 吴俊华
     */
    protected function goodMatchPromotion($goodsInfo,$promotionInfo,$refactor = false)
    {
        $goodsPromotion = []; //商品满足促销活动
        if(!empty($promotionInfo)){
            //商品匹配促销活动
            foreach ($promotionInfo as $promotionKey => $promotionValue) {
                foreach ($goodsInfo as $goodsKey => $goodsValue) {
                    //兼容商品id为goods_id或id
                    if(!isset($goodsValue['goods_id'])){
                        $goodsValue['goods_id'] = $goodsValue['id'];
                    }
                    if($this->func->isRelatedGoods($promotionValue, $goodsValue)){
                        $goodsPromotion[] = [
                            'goodsInfo' => $goodsValue,
                            'promotionInfo' => $promotionValue
                        ];
                    }
                }
            }
        }
        //重构数组：一个商品对应多个促销活动
        if($refactor == true){
            $newGoodsPromotion = [];
            if(!empty($goodsPromotion)){
                foreach($goodsPromotion as $key => $value){
                    if(isset($newGoodsPromotion[$value['goodsInfo']['id']]['goodsInfo']) && !empty($newGoodsPromotion[$value['goodsInfo']['id']]['goodsInfo'])){
                        $newGoodsPromotion[$value['goodsInfo']['id']]['promotionList'][] = $value['promotionInfo'];
                    }else{
                        $newGoodsPromotion[$value['goodsInfo']['id']]['goodsInfo'] = $value['goodsInfo'];
                        $newGoodsPromotion[$value['goodsInfo']['id']]['promotionList'][] = $value['promotionInfo'];
                    }
                }
                $newGoodsPromotion = array_values($newGoodsPromotion);
            }
            return !empty($newGoodsPromotion) ? $newGoodsPromotion : false;
        }
        return !empty($goodsPromotion) ? $goodsPromotion : false;
    }
    /**
     * @param $param
     *
     * @return mixed
     */
    public function getCouponByBrand($param)
    {
        $result = BaiyangSkuData::getInstance()->getSkuByBrand($param);
        return $result;
    }

    /**
     * @desc 优惠券标签
     * @param $param
     * @author 邓永军
     */
    public function CouponProductList($param)
    {
        if(!empty($param['goods_id'])){
            $skuData = BaiyangSkuData::getInstance();
            $baseData = BaseData::getInstance();
            $goodsIdArr = explode( ',' , $param['goods_id']);
            $couponRelateGoodsInfo = [];
            foreach ($goodsIdArr as $goodsId)
            {
                $skuInfo = $skuData->getSkuInfo($goodsId,$param['platform']);
                //通过goods_id获取对应品牌
                $brandId = $skuInfo['brand_id'];
                //通过goods_id获取对应品类
                $categoryId = $skuInfo['category_id'];
                $count = $baseData->countData([
                    'table' => '\Shop\Models\BaiyangCoupon',
                    'where' => 'where use_range = :use_range: OR FIND_IN_SET(:goods:,product_ids) OR FIND_IN_SET(:brand:,brand_ids) OR FIND_IN_SET(:category:,category_ids)',
                    'bind' => [
                        'use_range' => 'all',
                        'goods' => $goodsId,
                        'brand' => $brandId,
                        'category' => $categoryId
                    ]
                ]);
                if($count > 0){
                    $couponRelateGoodsInfo[] = $goodsId;
                }
            }
            return $couponRelateGoodsInfo;
        }
    }

    /**
     * @desc 优惠券基础列表
     * @param $data
     *         - goods_id ：Int 商品id | 必填
     *         - platform : String 平台类型 pc app wap | 必填
     *         - user_id ：String 用户id 用于区分权限和获取指定用户号码 | 可填
     *         - getStatus ：Bool 如果是1获取商品列表状态
     *         - is_multigoods_id ： Bool 如果是1获取商品详情列表
     *         - is_temp : Bool 是否临时用户
     *         - channel_subid : int 渠道号
     *         - udid : string 手机唯一id(app only)
     * @return array
     *         - coupon_sn ：String 优惠券码
     *         - coupon_name ： String 优惠券名字
     *         - tips ：String 优惠信息提示（商品详情页面小标签）
     *         - start_provide_time : timestamp 优惠券开始领取时间
     *         - end_provide_time : timestamp 优惠券结束领取时间
     *         - coupon_type : Int 优惠券类型 1 满减券 2 折扣券 3 包邮券
     *         - coupon_value : String 优惠券优惠金额或折扣
     *         - min_cost : String 优惠券满足条件
     *         - coupon_number ：Int 优惠券数量
     *         - got_num ：Int 已经领取数量
     *         - is_over_bring_limit ：Int 是否超过可领取优惠券的数量 是 1 否 0
     *         - type ：Int 1 登陆 2 游客
     *         - use_range : Int 使用范围 all 全场 category 品类 brand 品牌 single 单品
     *         - 如果使用范围是all全场的 则为空Column 如果为single单品 brand品牌 category品类 则为对应的ids
     *         - validitytype 有效期类型 1 绝对有效期 2 相对有效期
     *         - relative_validity 相对有效天数(相对有效期时有效)
     *         - start_use_time 优惠券活动开始时间 (绝对有效期时有效)
     *         - end_use_time 优惠券活动结束时间 (绝对有效期时有效)
     *         - is_over 是否已领取完
     *         - expiration 有效期
     *         - user_limit 用户限制领取数量
     * @author 邓永军
     */
    public function CouponList($data)
    {
        if(isset($data['goods_id']) && !empty($data['goods_id']) && isset($data['platform']) && !empty($data['platform'])){
            $goods_id_arr = explode( ',' , $data['goods_id']);
            $result = [];
            if( isset($data["user_id"]) && !empty($data["user_id"]) && ( !isset($data['is_temp']) || empty($data['is_temp']) || $data['is_temp'] == 0 )){
                $is_new_user = $this->isNewUser($data);
            }
            foreach ( $goods_id_arr as $key => $goods_id){
                //判断是否处方药 1 是 0 否
                $is_DrugType = $this->getDrugType(['goods_id' => $goods_id ,'platform' => $data['platform'] ]) == 1 ? 1 : 0;
                if(isset($data["user_id"]) && !empty($data["user_id"]) && ( !isset($data['is_temp']) || empty($data['is_temp']) || $data['is_temp'] == 0)){
                    $param=array_merge(
                        ['drug_type' => $is_DrugType],
                        ['is_new_user' => $is_new_user],//判断是否新用户 1 是 0 否
                        $data
                    );
                }else{
                    $param=array_merge(
                        ['drug_type'=>$is_DrugType],
                        $data
                    );
                }

                //获取优惠券列表
                $list=$this->getMyCouponList($param,$goods_id);

                $sku = $this->RedisCache->getValue(CacheGoodsKey::SKU_INFO.$goods_id);
                $sku_info = BaiyangSkuData::getInstance()->getSkuSpu($sku[0]['spu_id']);
                if(!empty($list)){
                    foreach ($list as $k => &$item){
                        $ban_join_rule=json_decode($item['ban_join_rule'],true);
                        //是否已领取完
                        if($item['coupon_number'] == 0){
                            $item['is_over'] = 0;
                        }else{
                            if($item['coupon_number'] - $item['bring_number'] > 0 ){
                                $item['is_over'] = 0;
                            }else{
                                $item['is_over'] = 1;
                            }
                        }

                        //排除操作
                        if($this->excludeUseRange($ban_join_rule,$goods_id,$data['platform']) == 0){
                            unset($list[$k]);
                        }
                        //过滤操作
                        switch ($item['use_range'])
                        {
                            case BaiyangCouponEnum::SINGLE_RANGE:
                                $pdArr = explode(',',$item['product_ids']);
                                if(!in_array($goods_id,$pdArr)){
                                    unset($list[$k]);
                                    unset($pdArr);
                                }
                                break;
                            case BaiyangCouponEnum::BRAND_RANGE:
                                $pdArr = explode(',',$item['brand_ids']);
                                if(!in_array($sku_info['brand_id'],$pdArr)){
                                    unset($list[$k]);
                                    unset($pdArr);
                                }
                                break;
                            case BaiyangCouponEnum::CATEGORY_RANGE:
                                $pdArr = explode(',',$item['category_ids']);
                                if(!in_array($sku_info['category_id'],$pdArr)){
                                    unset($list[$k]);
                                    unset($pdArr);
                                }
                                break;

                        }
                        //优惠券提示
                        if(isset($list[$k]) && !empty($list[$k])){
                            $item['tips'] = $this->getCouponTips($list,$k,$item);
                        }
                        $idsOnUseRange = $this->getIdsFromUseRange($item['use_range']);

                        $type = 2;
                        $got_num = 0;
                        $is_over_bring_limit = 0;
                        if(isset($data["user_id"]) && !empty($data["user_id"]) && ( !isset($data['is_temp']) || empty($data['is_temp']) || $data['is_temp'] == 0)){
                            //已经领取该优惠券次数
                            if(isset($list[$k]) && !empty($list[$k])){
                                $item["is_got"] = $this->isHasBrought($list,$k,$data["user_id"],$item["coupon_sn"]);
                            }
                            //判断领取db里是否存在本优惠券本人可领取数量
                            if(isset($list[$k]) && !empty($list[$k])){
                                $item["is_over_bring_limit"] = $this->isOverBringLimit($data["user_id"],$item["coupon_sn"],$item['limit_number']);
                            }
                            if(isset($list[$k]) && !empty($list[$k])){
                                $type = 1;
                                $got_num = $item['is_got'];
                                $is_over_bring_limit = $item["is_over_bring_limit"];
                            }
                        }

                        if($item['validitytype'] == 1){
                            $expiration = $item['end_use_time'];
                            $expiration_tips = '有效期至'.date('Y-m-d',$expiration);
                        }else{
                            $relative_time = BaiyangCouponRecordData::getInstance()->CouponHasBringInfo($data["user_id"],$item['coupon_sn']);
                            if($relative_time > 0){
                                $expiration = $relative_time + ($item['relative_validity'] * 24 * 3600);
                            }else{
                                $expiration = $relative_time;
                            }
                            $expiration_tips = '有效期自领取'.$item['relative_validity'].'天有效';
                        }
                        if($item['coupon_type'] == 1)$item['coupon_value'] = (int) $item['coupon_value'];
                        if(isset($list[$k]) && !empty($list[$k])){
                            if($list[$k]['is_over'] == 0){
                                $result[$goods_id][]=[
                                    'id'=>$item['id'],
                                    'coupon_sn'=>$item['coupon_sn'],
                                    'coupon_name'=>$item['coupon_name'],
                                    'tips'=>$list[$k]['tips'],
                                    'start_provide_time'=>$item['start_provide_time'],
                                    'end_provide_time'=>$item['end_provide_time'],
                                    'coupon_value'=> $item['coupon_value'],
                                    'coupon_type'=>$item['coupon_type'],
                                    'min_cost'=>$item['min_cost'],
                                    'discount_unit' => $item['discount_unit'],
                                    'coupon_number'=>$item['coupon_number'],
                                    'got_num'=>$got_num,
                                    'is_over_bring_limit'=>$is_over_bring_limit,//是否超过用户领取限制
                                    'type'=>$type, //已登陆权限
                                    'use_range'=>$item['use_range'],
                                    $item['use_range'] => $idsOnUseRange!=''?$item[$idsOnUseRange]:'',
                                    'validitytype' => $item['validitytype'],
                                    'relative_validity' => $item['relative_validity'],
                                    'start_use_time' => $item['start_use_time'],
                                    'end_use_time' => $item['end_use_time'],
                                    'ban_join_rule' => $item['ban_join_rule'],
                                    'url' => $item[strtolower($data['platform']).'_url'],
                                    'is_over'=> $list[$k]['is_over'],
                                    'expiration' => $expiration,
                                    'user_limit' => $item['limit_number'],
                                    'remain_time' => $expiration - time(),
                                    'server_time' => time(),
                                    'coupon_description' => $item['coupon_description'],
                                    'expiration_tips' => $expiration_tips,
                                    'rest_got_num' => $item['limit_number'] - $got_num
                                ];
                            }
                        }
                    }
                }
            }

            $couponStatus_arr['coupon'] = [];
            foreach ($result as $k => $res)
            {
                $arr = $res;
                unset($result[$k]);
                $result[$k]['id'] = $k;
                $result[$k]['data'] = $arr;
                $couponStatus_arr['coupon'][] = $result[$k]['id'];
                /*if($arr['is_over'] == 1){
                    unset($result[$k]);
                }*/
            }
            if(isset($data['getStatus']) && !empty($data['getStatus']) && $data['getStatus'] == 1){
                return $couponStatus_arr;
            }else{
                if(isset($data['isMultiGoodsId']) && !empty($data['isMultiGoodsId']) && $data['isMultiGoodsId'] == 1){
                    if(isset($data['format']) && !empty($data['format']) && $data['format'] == 1){
                        $format = 1;
                    }else{
                        $format = 0;
                    }
                    $result = array_values($result);
                    $coupon_list = [];
                    foreach ($result as $item){
                        foreach ($item['data'] as $data){
                            if(!isset($coupon_list[$data['coupon_sn']]) || empty($coupon_list[$data['coupon_sn']])){
                                $coupon_list[$data['coupon_sn']] = $data;
                            }

                        }
                    }

                    $coupon['coupon'] = array_values($coupon_list);
                    //领取排序
                    $coupon['coupon'] = $this->arraySortByKey($coupon['coupon'],'got_num');
                    /*$isOverArr = [];
                    $gotNumArr = [];
                    $couponTypeArr = [];
                    $useRangeArr = [];
                    $mkArr = [];
                    $cutDownArr = [];
                    foreach ($coupon['coupon'] as $key => $Cdata){
                        $isOverArr[$key] = $Cdata['is_over'];
                        $gotNumArr[$key] = $Cdata['rest_got_num'];
                        $couponTypeArr[$key] = $Cdata['coupon_type'];
                        switch ($Cdata['use_range']){
                            case "all":
                                $useRangeArr[$key] = 1;
                                break;
                            case "category":
                                $useRangeArr[$key] = 2;
                                break;
                            case "brand":
                                $useRangeArr[$key] = 3;
                                break;
                            case "single":
                                $useRangeArr[$key] = 4;
                                break;
                        }
                        if($Cdata['coupon_type'] == 1){
                            $mkArr[$key] = $Cdata['coupon_value'] / $Cdata['min_cost'];
                            $cutDownArr[$key] = $Cdata['min_cost'] - $Cdata['coupon_value'];
                        }elseif ($Cdata['coupon_type'] == 2){
                            $mkArr[$key] = ((1 - ($Cdata['coupon_value'] / 10)) * $Cdata['min_cost']) / $Cdata['min_cost'];
                            $cutDownArr[$key] = $Cdata['min_cost'] - ((1 - ($Cdata['coupon_value'] / 10)) * $Cdata['min_cost']);
                        }else{
                            $mkArr[$key] = 0;
                            $cutDownArr[$key] = 0;
                        }

                    }
                    array_multisort($isOverArr,SORT_ASC,$gotNumArr,SORT_DESC,$couponTypeArr,SORT_ASC,$useRangeArr,SORT_DESC,$mkArr,SORT_ASC,$cutDownArr,SORT_ASC,$coupon['coupon']);*/
                    if($format == 1)
                    {
                        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $coupon['coupon']];
                    }else{
                        return $coupon;
                    }
                }else{
                    return $result;
                }
            }
        }else{
            if(isset($data['platform']) && !empty($data['platform'])){
                return ['error' => 1,'code' => HttpStatus::NO_DATA,'data' => HttpStatus::$HttpStatusMsg[HttpStatus::NO_DATA]];
            }else{
                return ['error' => 1,'code' => HttpStatus::NOT_GOOD_INFO,'data' => '平台参数缺失'];
            }
        }
    }


    /**
     * @desc 根据使用范围返回对应要获取的数据库字段
     * @param $type 使用范围的值
     * @return string
     * @author 邓永军
     */
    private function getIdsFromUseRange($type)
    {
        switch ($type)
        {
            case BaiyangCouponEnum::ALL_RANGE:
                return '';
                break;
            case BaiyangCouponEnum::SINGLE_RANGE:
                return 'product_ids';
                break;
            case BaiyangCouponEnum::BRAND_RANGE:
                return 'brand_ids';
                break;
            case BaiyangCouponEnum::CATEGORY_RANGE:
                return 'category_ids';
                break;
        }
    }

    /**
     * @desc 获取优惠信息提示（商品详情页面小标签）
     * @param $list 优惠券列表
     * @param $k
     * @param $item
     * @return string
     *          - 1 满减
     *          - 2 满折
     *          - 3 包邮
     * @desc 邓永军
     */
    private function getCouponTips($list,$k,$item)
    {
        if(isset($list[$k]) && !empty($list[$k])){
            switch ($item['coupon_type'])
            {
                case '1':
                    return '满'.$item['min_cost'].'元减'.$item['coupon_value'].'元';
                    break;
                case '2':
                    if($item['discount_unit'] == 1){
                        $unit='元';
                    }else{
                        $unit='件';
                    }
                    return '满'.$item['min_cost'].$unit.'享'.$item['coupon_value'].'折';
                    break;
                case '3':
                    return '满'.$item['min_cost'].'元包邮';
                    break;
            }
        }
    }

    /**
     * @desc  过滤 【品类 品牌 单品】
     * @param $goods_id
     * @param $item
     * @return int
     * @author 邓永军
     */
    private function assignUseRange($goods_id,$item,$platform)
    {
        $tmp=1;
        if($tmp == 1){
            switch ($item["use_range"])
            {
                case BaiyangCouponEnum::CATEGORY_RANGE:
                    //通过获取指定的商品的品类树,如果优惠券的范围选择是品类 不在范围内就释放这个record

                    if(!in_array($item["category_ids"],explode('/',$this->getSkuCategoryPath($goods_id,$platform)))){
                        $tmp = 0;
                    }
                    break;
                case BaiyangCouponEnum::BRAND_RANGE:
                    //通过获取指定的商品所属的品牌,如果优惠券的范围选择是品牌 不在范围内就释放这个record
                    if(!in_array($this->getSkuBrandId($goods_id,$platform),explode(',',$item["brand_ids"]))){
                        $tmp = 0;
                    }
                    break;
                case BaiyangCouponEnum::SINGLE_RANGE:
                    //如果优惠券的范围选择是单品 不在范围内就释放这个record
                    if(!in_array($goods_id,explode(',',$item["product_ids"]))){
                        $tmp = 0;
                    }
                    break;
            }
        }
        return $tmp;
    }

    public function inArray($item, $array) {
        $flipArray = array_flip($array);
        return isset($flipArray[$item]);
    }
    /**
     * @desc 排除 【品类 品牌 单品】
     * @param $ban_join_rule
     * @param $goods_id
     * @param $platform
     * @return int
     *          - 1 需要去释放内存 删除指定值的list
     *          - 0 不需要操作
     * @desc 邓永军
     */
    public function excludeUseRange($ban_join_rule,$goods_id,$platform)
    {
        $tmp=1;
        //排除品类
        if(isset($ban_join_rule["category"]) && !empty($ban_join_rule["category"])){
            $category_path=explode('/',$this->getSkuCategoryPath($goods_id,$platform));
            $category_ban_rule=explode(',',$ban_join_rule["category"]);
            foreach ($category_ban_rule as $v){
                if(in_array($v,$category_path)){
                    $tmp = 0;
                }
            }
        }

        //排除品牌
        if(isset($ban_join_rule['brand']) && !empty($ban_join_rule['brand'])){
            $brand_ban_rule=explode(',',$ban_join_rule['brand']);
            if(in_array($this->getSkuBrandId($goods_id,$platform),$brand_ban_rule)){
                $tmp = 0;
            }
        }

        //排除单品
        if(isset($ban_join_rule['single']) && !empty($ban_join_rule['single'])){
            $single_ban_rule=explode(',',$ban_join_rule['single']);
            if(in_array($goods_id,$single_ban_rule)){
                $tmp = 0;
            }
        }
        return $tmp;
    }

    /**
     * @desc 已经领取数量
     * @param $list
     * @param $k
     * @param $user_id
     * @param $coupon_sn
     * @return int|mixed
     * @author 邓永军
     */
    private function isHasBrought($list,$k,$user_id,$coupon_sn)
    {
        if(isset($list[$k]) && !empty($list[$k])){
            $count = $this->countCouponHasBring($user_id,$coupon_sn);
            if($count > 0){
                return $count;
            }else{
                return 0;
            }
        }
    }

    /**
     * @desc 根据会员标签过滤商品
     * @param $goods_id
     * @param $goods_tag_id
     * @return int
     * @author 邓永军
     */
    private function excludeGoodsTags($goods_id,$goods_tag_id)
    {
        $tmp = 1;
        if($goods_tag_id > 0){
            $goods_id_arr=$this->getgoods_idFromTagId($goods_tag_id);
            if(!in_array($goods_id,$goods_id_arr)){
                $tmp = 0;
            }
        }
        return $tmp;
    }

    /**
     * @desc 是否超过限制
     * @param $user_id
     * @param $coupon_sn
     * @param $limit_number
     * @return int
     * @author 邓永军
     */
    private function isOverBringLimit($user_id,$coupon_sn,$limit_number)
    {
        if($limit_number == 0 ){
            return 0;
        }
        if($this->countCouponHasBring($user_id,$coupon_sn)>=$limit_number){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * @desc 获取套餐列表
     * @param $data_all
     *          -platform 【pc、app、wap】 平台
     *          -goods_id 多个个商品id
     * @return array
     *          -data
     *              -id 套餐id
     *              -group_name 套餐名字
     *              -group_introduction 套餐介绍
     *              -start_time 开始时间
     *              -end_time 结束时间
     *              -sku_info
     *                  -goods_name 商品名字
     *                  -medicine_type 处方药类型
     *                  -goods_image 商品图片
     *                  -favourable_price 价格
     *                  -favourable_goods_number 数量
     *                  -specifications 规格
     *                  -original_price 原价
     *                  -on_shelf 上架状态 1 上架 0 下架
     *                  -is_stockout 缺货
     *             -total_favourable_price 总套餐价格
     *             -total_original_price 总原价
     *          -max_save_money 最大省钱数
     * //rx_exist
     */
    public function GoodSet($data_all)
    {
        $data['platform'] = $data_all['platform'];
        $gid_arr=explode(',',$data_all['goods_id']);
        $all_result=[];
        $sku_redis_connection = BaiyangSkuData::getInstance();
        foreach ($gid_arr as $g_key=>$gid){
            $data['goods_id']=$gid;
            $result=$this->GoodSetList($data);
            if(!empty($result) && count($result) > 0){

                $save_money_array=[];

                foreach ($result as $key => $list){
                    $id_arr=explode(',',$list['g_ids']);
                    $result[$key]['status'] = 0;
                    foreach ($id_arr as $sku_id){
                        //$sku_id为商品id
                        $group_id=$list['id'];
                        $favourable_info=$this->getFavourableInfo($group_id,$sku_id);
                        $base_sku_info=$sku_redis_connection->getSkuInfo($sku_id,$data['platform']);
                        $return = $this->getReturn($base_sku_info,$favourable_info);
                        $result[$key]['sku_info'][]=$return;
                        if($return['on_shelf'] == 0)$result[$key]['status'] = 1;
                        $stock = $this->func->getCanSaleStock([
                            'goods_id' => $sku_id,
                            'platform' => $data['platform']
                        ]);
                        if(is_array($stock) && !empty($stock)){
                            $stockNum = $stock[$sku_id] ?? 0;
                        }else{
                            $stockNum = $stock ?? 0;
                        }
                        if($stockNum < 1 || $return['favourable_goods_number'] > $stockNum)$result[$key]['status'] = 2;
                        $s = 0;
                        if(isset($result[$key]['rx_exist']) && !empty($result[$key]['rx_exist'])){
                            if($result[$key]['rx_exist'] == 1){
                                $result[$key]['rx_exist'] = 1;
                                $s = 1;
                            }
                        }
                        if($s == 0) $result[$key]['rx_exist'] = $return['medicine_type'] == 1 ? 1 : 0;
                        //优惠总价
                        if(!isset($result[$key]['total_favourable_price']) || empty($result[$key]['total_favourable_price'])){
                            $result[$key]['total_favourable_price'] = bcmul($return['favourable_price'],$return['favourable_goods_number'],2);
                        }else{
                            $result[$key]['total_favourable_price'] = bcadd($result[$key]['total_favourable_price'] , bcmul($return['favourable_price'],$return['favourable_goods_number'],2) , 2);
                        }

                        //总原价
                        if(!isset($result[$key]['total_original_price']) || empty($result[$key]['total_original_price'])){
                            $result[$key]['total_original_price'] = bcmul($return['original_price'] , $return['favourable_goods_number'],2);
                        }else{
                            $total_original_price = bcmul($return['original_price'] , $return['favourable_goods_number'],2);
                            $result[$key]['total_original_price'] = bcadd($result[$key]['total_original_price'] , $total_original_price , 2);
                        }
                        //立减价格
                        $minus_price = bcsub($result[$key]['total_original_price'],$result[$key]['total_favourable_price'],2);
                        if($minus_price > 0){
                            $result[$key]['minus_price'] = $minus_price;
                        }else{
                            $result[$key]['minus_price'] = 0;
                        }
                    }
                    $save_money_array[]=bcsub($result[$key]['total_original_price'] , $result[$key]['total_favourable_price'] ,2)  ;
                }

                $all_result[$g_key]['goods_id']=$gid;
                $all_result[$g_key]['data']=$result;
                $all_result[$g_key]['max_save_money']=$save_money_array[array_search(max($save_money_array),$save_money_array)];
                $all_result[$g_key]['code']=200;
            }else{
                $all_result[$g_key]['goods_id']=$gid;
                $all_result[$g_key]['data']=[];
                $all_result[$g_key]['max_save_money']=0;
                $all_result[$g_key]['code']=400;
            }
        }
        $Result = $this->GoodsSetForResult($all_result);
        $result_goodset = $Result['result_goodset'];
        $sma =$Result['sma'];//省钱数目信息存放数组
        $goodset['good_set']['list'] = array_values($result_goodset);
        $goodset['good_set']['max_save_money'] = $sma[array_search(max($sma),$sma)];

        if(isset($data_all['format']) && !empty($data_all['format']) && $data_all['format'] == 1){
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $goodset];
        }
        return $goodset;
    }

    /**
     * 输出套餐最大优惠
     * @param array $all_result
     * @return array
     * @author 邓永军
     */
    protected
    function GoodsSetForResult(array $all_result)
    {
        $result_goodset = [];
        $sma =[];//省钱数目信息存放数组
        foreach ($all_result as $list){
            if(count($list['data']) > 0){
                foreach ($list['data'] as $data){
                    if(!isset($result_goodset[$data['id']]) || empty($result_goodset[$data['id']]))
                        $result_goodset[$data['id']] = $data;
                    $sma[]=bcsub($data['total_original_price'] , $data['total_favourable_price'] ,2)  ;
                }
            }else{
                $sma[] = 0;
            }
        }
        return [
           'result_goodset' => $result_goodset,
            'sma' => $sma
        ];
    }
    /**
     * @desc 获取套餐信息
     * @param $group_id
     * @param $sku_id
     * @return mixed
     * @author 邓永军
     */
    private function getFavourableInfo($group_id,$sku_id)
    {
        return $this->FavourableInfo($group_id,$sku_id);
    }

    /**
     * @desc 合成套餐信息和商品信息
     * @param $base_sku_info 商品基本信息
     * @param $favourable_info 套餐信息
     * @return array
     * @author 邓永军
     */
    private function getReturn($base_sku_info,$favourable_info)
    {
        $return =[];
        //商品id
        $return['goods_id'] = $base_sku_info['id'];
        //商品名字分端
        $return['goods_name'] = $base_sku_info['name'];
        //处方药类型
        $return['medicine_type'] = $base_sku_info['drug_type'];
        //商品图片
        $return['goods_image'] = $base_sku_info['goods_image'];
        //优惠价
        $return['favourable_price'] = $favourable_info['favourable_price'];
        //优惠商品数量
        $return['favourable_goods_number'] = $favourable_info['goods_number'];
        //优惠商品规格
        $return['specifications'] = $base_sku_info['specifications'];
        //价格
        $return['original_price'] = $base_sku_info['sku_price'];
        //上架状态 1 为 上架 0 为下架
        $return['on_shelf'] = $base_sku_info['sale'];
        //商品数量 判断是否缺货 1 为缺货 0 为有货
        $return['is_stockout'] =$base_sku_info['sku_stock'] > 0 ? 0 : 1;
        //是否海外购
        $return['is_global'] = $base_sku_info['is_global'];
        return $return;
    }

    /**
     * @desc 根据品牌id获取商品id （用于促销互斥活动的获取商品id）
     * @param $brand_id
     * @return bool
     * @author 邓永军
     */
    public function getProductIdFromBrandId($brand_id)
    {
        $except_brand_id_arr = explode(',',$brand_id);
        $temp_arr =[];
        foreach ($except_brand_id_arr as $ebi)
        {
            $brand_arr = BaseData::getInstance()->getData([
                'column'=> 'b.id',
                'table' => '\Shop\Models\BaiyangSpu as a',
                'where' => 'where a.brand_id = :brand_id:',
                'bind' => [
                    'brand_id' => $ebi
                ],
                'join' => 'LEFT JOIN \Shop\Models\BaiyangGoods as b ON a.spu_id = b.spu_id '
            ]);
            if(!empty($brand_arr)){
                foreach ($brand_arr as $tmp){
                    $temp_arr[] = $tmp['id'];
                }
            }else{
                return false;
            }
        }
        $ids =implode(',',$temp_arr);
        return $ids;
    }

    /**
     * @desc 根据品类id获取商品id
     * @param $cid
     * @return bool
     * @author 邓永军
     */
    public function getProductIdFromCategoryId($cid)
    {
        $except_category_id_arr = explode(',',$cid);
        $temp_arr =[];
        foreach ($except_category_id_arr as $eci)
        {
            $cat_arr = BaseData::getInstance()->getData([
                'column'=> 'b.id',
                'table' => '\Shop\Models\BaiyangSpu as a',
                'where' => 'where a.category_id = :category_id:',
                'bind' => [
                    'category_id' => $eci
                ],
                'join' => 'LEFT JOIN \Shop\Models\BaiyangGoods as b ON a.spu_id = b.spu_id '
            ]);
            if(!empty($cat_arr)){
                foreach ($cat_arr as $tmp){
                    $temp_arr[] = $tmp['id'];
                }
            }else{
                return false;
            }
        }
        $ids =implode(',',$temp_arr);
        return $ids;
    }

    /**
     * @desc 判断在不在互斥列表下
     * @param $mutex_product_ids
     * @param $detail
     * @param $is_allRange
     * @return int
     * @author 邓永军
     */
    protected function run_remove_mutex($mutex_product_ids,$detail,$is_allRange)
    {
        if($is_allRange == 1){
            $mutex_product_ids_arr = explode(',',$mutex_product_ids);
            if(!in_array($detail['id'],$mutex_product_ids_arr)){
                return 1;
            }else{
                return 0;
            }
        }else{
            $mutex_product_ids_arr = explode(',',$mutex_product_ids);
            if(in_array($detail['id'],$mutex_product_ids_arr)){
                return 1;
            }else{
                return 0;
            }
        }
    }

    /**
     * @desc 去除优惠券互斥列表
     * @param $use_range
     * @param $promotion_mutex_list
     * @param $detail
     * @param $kk
     * @return array
     * @author 邓永军
     */
    protected function remove_mutex_coupon($use_range,$promotion_mutex_list,$detail,$kk)
    {
        $remove_seed = [];
        switch ($use_range)
        {
            case BaiyangCouponEnum::ALL_RANGE:
                //当选择全场时候 只有互斥商品id可以使用优惠券
                foreach ($promotion_mutex_list as $list){
                    if($list['promotion_scope'] == BaiyangCouponEnum::ALL_RANGE ){
                        if($this->run_remove_mutex($list['mutex_product_ids'],$detail,1) == 1){
                            $remove_seed[] = $kk;
                        }
                    }
                }
                break;
            //当选择 非 全场 互斥id不能使用优惠券
            case BaiyangCouponEnum::CATEGORY_RANGE:
                foreach ($promotion_mutex_list as $list){
                    if($list['promotion_scope'] == BaiyangCouponEnum::CATEGORY_RANGE){
                        if($this->run_remove_mutex($list['mutex_product_ids'],$detail,2) == 1){
                            $remove_seed[] = $kk;
                        }
                    }
                }
                break;
            case BaiyangCouponEnum::BRAND_RANGE:
                foreach ($promotion_mutex_list as $list){
                    if ($list['promotion_scope'] == BaiyangCouponEnum::BRAND_RANGE){
                        if($this->run_remove_mutex($list['mutex_product_ids'],$detail,2) == 1){
                            $remove_seed[] = $kk;
                        }
                    }
                }
                break;
            case BaiyangCouponEnum::SINGLE_RANGE:
                foreach ($promotion_mutex_list as $list){
                    if($list['promotion_scope'] == BaiyangCouponEnum::SINGLE_RANGE){
                        if($this->run_remove_mutex($list['mutex_product_ids'],$detail,2) == 1){
                            $remove_seed[] = $kk;
                        }
                    }
                }
                break;
        }
        return $remove_seed;
    }

    /**
     * @desc 验证获取商品的限时优惠信息
     * @param array
     *   [goodsInfo]
     *       - int    goods_id     商品id
     *       - double sku_price    商品价格
     *   [platform] -string  平台【pc、app、wap】
     * @param array $limitTime 该商品参加的限时优惠活动 [一维数组]
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function verifyLimitTime($param,$limitTime)
    {
        $limitInfo = []; //商品限时优惠信息
        $discountKey = 'offers'; //默认优惠价类型
        $offers = $param['goodsInfo']['sku_price']; //默认优惠价
        $rebate = 10.0; //默认折扣
        //匹配商品限时优惠价
        foreach(json_decode($limitTime['rule_value'],true) as $ruleValue){
            if(isset($ruleValue['id']) && $ruleValue['id'] == $param['goodsInfo']['goods_id']){
                if(isset($ruleValue['type']) && !empty($ruleValue['type'])){
                    //折扣类型
                    if($limitTime['offer_type'] == BaiyangPromotionEnum::DISCOUNT_TYPE){
                        $discountKey = 'discount';
                        $offers = 10.0; //默认不打折
                    }
                    //统一优惠、折扣
                    if($ruleValue['type'] == 1){
                        $offers = sprintf('%.2f',$ruleValue[$discountKey]);
                    }
                    //分端优惠、折扣
                    if($ruleValue['type'] == 2){
                        foreach($ruleValue[$discountKey] as $kk => $vv){
                            if($kk == $param['platform']){
                                $offers = sprintf('%.2f',$ruleValue[$discountKey][$kk]);
                            }
                        }
                    }
                    //折扣类型要做乘除法运算
                    if($limitTime['offer_type'] == BaiyangPromotionEnum::DISCOUNT_TYPE){
                        $rebate = $offers;
                        $offers = bcmul($param['goodsInfo']['sku_price'], bcdiv($offers, 10,2),2);
                    }
                    $limitInfo = [
                        'id' => $limitTime['promotion_id'],
                        'limit_time_title' => $limitTime['promotion_title'],
                        'goods_id' => $param['goodsInfo']['goods_id'],
                        'price' => $offers,
                        'offer_type' => $limitTime['offer_type'],
                        'rebate' =>sprintf('%.1f',$rebate),
                        'end_time' => $limitTime['promotion_end_time'],
                        'mutex' =>isset($limitTime['promotion_mutex'])?$limitTime['promotion_mutex']:'',
                        'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME,
                        'sort' => 4,
                    ];
                }
            }
        }
        return $limitInfo;
    }

    /**
     * @desc 获取进行中的促销活动
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param [一维数组]
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *       -string  promotion_type 指定查询的活动类型[多个以逗号隔开]  (可填)
     *       -string  promotion_type 指定查询的活动类型[多个以逗号隔开]  (可填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getProcessingPromotions($event,$class,$param)
    {
        $promotionType = isset($param['promotion_type']) ? $param['promotion_type'] : '';
        $promotionId = isset($param['promotion_id']) ? (int)$param['promotion_id'] : 0;
        //进行中的促销活动
        $promotionInfo = BaiyangPromotionData::getInstance()->getPromotionsInfo($param, $promotionType, $promotionId);
        //当用户为登录用户
        if(!empty($param['user_id']) && $param['is_temp'] == 0){
            //单用户参加促销活动次数超过后台设置的数量时，排除该活动
            if(!empty($promotionInfo)){
                $promotionInfo = $this->removeOverUserPromotion($param,$promotionInfo);
            }
        }
        return $promotionInfo;
    }

    /**
     * @desc 获取商品的最优惠价(计算对比：限时优惠、会员价、疗程、辣妈价)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *   [goodsInfo] array [一维数组]
     *       - int    goods_id     商品id
     *       - double sku_price    商品价格
     *       - double goods_number 商品数量
     *   [platform]   -string  平台【pc、app、wap】
     *   [user_id]    -int     用户id (临时用户或真实用户id)
     *   [is_temp]    -int     是否为临时用户 (1为临时用户、0为真实用户)
     *   [limitTime]  -array [二维数组]  所有进行中的限时优惠活动 (可填)
     *   [tag_sign]   -bool  tag_sign 用户是否绑定标签 (可填)
     * @return array [] 结果信息
     *       - int     goods_id      商品id
     *       - float   sku_price     商品价格
     *       - int     goods_number  商品数量
     *       - float   promotion_price   优惠单价
     *       - float   discount_price    只参加疗程、会员价、限时优惠的优惠单价
     *       - float   promotion_total   优惠总价
     *       - float   discount_total    只参加疗程、会员价、限时优惠的优惠总价
     *       - array   discountPromotion 商品参加最优惠活动信息(限时优惠或会员价或疗程价) [一维数组]
     * @author 吴俊华
     */
    public function getGoodsDiscountPrice($event,$class,$param)
    {
        $param['limitTime'] = (isset($param['limitTime']) && !empty($param['limitTime'])) ? $param['limitTime'] : [];
        $param['goodsInfo']['goods_number'] = isset($param['goodsInfo']['goods_number']) ? (int)$param['goodsInfo']['goods_number'] : 1;
        $param['handleMom'] = isset($param['handleMom']) ? $param['handleMom'] : false; // 兼容辣妈领取记录
        $promotionArr = []; //商品参加的限时优惠、会员价、疗程活动
        $condition = [
            'goods_id' => (int)$param['goodsInfo']['goods_id'],
            'goods_number' => $param['goodsInfo']['goods_number'],
            'user_id' => (int)$param['user_id'],
            'is_temp' => isset($param['is_temp']) ? (int)$param['is_temp'] : 0,
            'platform' => $param['platform']
        ];
        if(isset($param['tag_sign'])) $condition['tag_sign'] = $param['tag_sign'];
        //辣妈价(app平台才有) [暂时屏蔽辣妈]
//        if($param['platform'] == OrderEnum::PLATFORM_APP){
//            $giftIsNotReport = BaiyangMomGetGiftData::getInstance()->getMomGiftIsNotReport($param['user_id']);
//            $goodsMomPrice = array();
//            if (!$giftIsNotReport) {
//                $goodsMomPrice = BaiyangGoodsPrice::getInstance()->getUserTagPriceList($param['user_id'], $param['goodsInfo']['goods_id']);
//            }
//            if(!empty($goodsMomPrice)){
//                //防用户刷辣妈商品
//                if ($param['handleMom']) {
//                    BaiyangMomGetGiftData::getInstance()->checkHadAddGiftGoods(array(
//                        'user_id' => $param['user_id'],
//                        'goods_id' => $param['goodsInfo']['goods_id'],
//                        'goods_number' => $param['goodsInfo']['goods_number'],
//                        'price' => $param['goodsInfo']['sku_price'],
//                        'mom_tag_price' => $goodsMomPrice[$param['goodsInfo']['goods_id']]
//                    ));
//                }
//                $goodsMomPrice = array_values($goodsMomPrice)[0];
//                $goodsMomPrice['promotion_type'] = BaiyangPromotionEnum::MOM_PRICE;
//                //折扣类型
//                if($goodsMomPrice['type'] == 2) {
//                    $goodsMomPrice['price'] = bcmul($param['goodsInfo']['sku_price'], bcdiv((float)$goodsMomPrice['rebate'], 10, 2), 2);
//                }
//                $goodsMomPrice['sort'] = 1;
//                $promotionArr[] = $goodsMomPrice;
//            }
//        }
        //商品标签价 (除辣妈价以外)
        $goodsTagPrice = BaiyangUserGoodsPriceTagData::getInstance()->getUserGoodsPriceTag($condition);
        if(!empty($goodsTagPrice)){
            foreach($goodsTagPrice as $key => $value){
                //折扣类型
                if($value['type'] == 2){
                    $goodsTagPrice[$key]['price'] = bcmul($param['goodsInfo']['sku_price'], bcdiv((float)$value['rebate'], 10,2),2);
                }
            }
            //取最优惠的标签价
            $goodsTagPrice = $this->arraySortByKey($goodsTagPrice,'price');
            $goodsTagPrice = $goodsTagPrice[0];
            $goodsTagPrice['promotion_type'] = BaiyangPromotionEnum::MEMBER_PRICE;
            $goodsTagPrice['sort'] = 2;
            $promotionArr[] = $goodsTagPrice;
        }
        //商品疗程价
        if($condition['goods_number'] > 1){
            $goodsTreatment = BaiyangGoodsTreatmentData::getInstance()->getGoodsTreatment($condition);
            if(!empty($goodsTreatment)){
                $goodsTreatment['promotion_type'] = BaiyangPromotionEnum::TREATMENT;
                $goodsTreatment['sort'] = 3;
                $promotionArr[] = $goodsTreatment;
            }
        }
        //商品限时优惠价
        $goodsLimitTime = $this->getLimitTimeGoodsInfo($param);
        if(!empty($goodsLimitTime)){
            $promotionArr[] = $goodsLimitTime;
        }
        //排序促销活动
        $promotionArr = $this->sortOfferPromotion($promotionArr);

        //计算返回最优惠价的活动
        if(!empty($promotionArr)){
            $mutexStr = ''; //互斥活动
            $selectedPromotion = $promotionArr[0];
            //最优惠价相同时，要把互斥活动相加。
            foreach($promotionArr as $key => $value){
                if($value['price'] == $selectedPromotion['price']){
                    $mutexStr .= $value['mutex'].',';
                }
            }
            unset($selectedPromotion['sort']);
            if($mutexStr){
                $selectedPromotion['mutex'] = rtrim($mutexStr,',');
            }
            // 优惠价比原价大时，返回原价
            if($selectedPromotion['price'] > $param['goodsInfo']['sku_price']){
                return $param['goodsInfo'];
            }
            $selectedPromotion['tag_name'] = isset($selectedPromotion['tag_name']) ? $selectedPromotion['tag_name'].'价' : '';
            $param['goodsInfo']['discountPromotion'] = $selectedPromotion;
            $param['goodsInfo']['promotion_price'] = $param['goodsInfo']['discount_price'] = $selectedPromotion['price'];
            $param['goodsInfo']['promotion_total'] = $param['goodsInfo']['discount_total'] = bcmul($param['goodsInfo']['promotion_price'], $param['goodsInfo']['goods_number'],2);
            // 限时优惠求出剩余时间戳
            if($selectedPromotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
                $endTime = $selectedPromotion['end_time'] - time();
                $param['goodsInfo']['discountPromotion']['end_time'] = ($endTime > 0) ? $endTime : 0;
            }
        }
        // 最优惠价为辣妈时，要改变商品数量
        if(isset($selectedPromotion) && !empty($selectedPromotion)){
            if($selectedPromotion['promotion_type'] == BaiyangPromotionEnum::MOM_PRICE){
                if($param['goodsInfo']['goods_number'] > $selectedPromotion['limit_number']){
                    $param['goodsInfo']['goods_number'] = $selectedPromotion['limit_number'];
                    $param['goodsInfo']['promotion_total'] = $param['goodsInfo']['discount_total'] = bcmul($param['goodsInfo']['promotion_price'], $param['goodsInfo']['goods_number'],2);
                }
            }
        }
        return $param['goodsInfo'];
    }

    /**
     * @desc 根据最优惠价、活动优先级排序 (优惠价相等时，会员价>疗程价>限时优惠)
     * @param array $promotionList 促销活动列表
     * @return array $promotionList 排序后的促销活动列表
     * @author 吴俊华
     */
    protected function sortOfferPromotion($promotionList)
    {
        if (empty($promotionList)) {
            return [];
        }
        $sort1 = [];
        $sort2 = [];
        //根据优惠价和序号进行排序
        foreach ($promotionList as $key => $value) {
            $sort1[] = $value['price'];
            $sort2[] = $value['sort'];
        }
        array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $promotionList);
        return $promotionList;
    }

    /**
     * @desc 获取商品的限时优惠价
     * @param array
     *   [goodsInfo]
     *       - int    goods_id     商品id
     *       - double sku_price    商品价格
     *   [platform]   -string  平台【pc、app、wap】
     *   [user_id]    -int     用户id (临时用户或真实用户id)
     *   [is_temp]    -int     是否为临时用户 (1为临时用户、0为真实用户)
     *   [limitTime]  -array [二维数组]  所有进行中的限时优惠活动
     * @return array [] 结果信息
     * @author 吴俊华
     */
    protected function getLimitTimeGoodsInfo($param)
    {
        //进行中的限时优惠活动
        if(!empty($param['limitTime'])){
            $limitTime = $param['limitTime'];
        }else{
            $limitTime = $this->getProcessingPromotions('','',['platform' => $param['platform'], 'user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        }
        $limitInfo = []; //商品限时优惠信息
        foreach($limitTime as $key => $value){
            $condition = explode(',',$value['condition']);
            if(in_array($param['goodsInfo']['goods_id'],$condition)){
                $limitInfo = $this->verifyLimitTime($param,$value);
            }
        }
        return $limitInfo;
    }

    /**
     * @desc 促销活动按优先级排序
     * @param $promotionList
     * @return array
     * @author 柯琼远
     */
    protected function sortPromotion($promotionList)
    {
        if (empty($promotionList)) {
            return array();
        }
        $sort0 = array();
        $sort1 = array();
        $sort2 = array();
        foreach ($promotionList as $key => $value) {
            switch ($value['promotion_scope']) {
                case 'single' :
                    $sort1[] = 1;
                    break;
                case 'more' :
                    $sort1[] = 2;
                    break;
                case 'brand' :
                    $sort1[] = 3;
                    break;
                case 'category' :
                    $sort1[] = 4;
                    break;
                case 'all' :
                    $sort1[] = 5;
                    break;
                default :
                    $sort1[] = 5;
                    break;
            }
            switch ($value['promotion_type']) {
                case '5' : // 满减
                    $sort2[] = 10;
                    $sort0[] = 0;//满减比满折优先
                    break;
                case '10' : // 满折
                    $sort2[] = 10;
                    $sort0[] = 1;
                    break;
                case '40' : //加价购
                    $sort2[] = 30;
                    $sort0[] = 1;
                    break;
                case '15' : // 满赠
                    $sortNum = $value['promotion_is_real_pay'] == 0 ? 20 : 40; //实付与非实付优先级不一样
                    $sort2[] = $sortNum;
                    $sort0[] = 1;
                    break;
                case '20' : // 包邮
                    $sort2[] = 50;
                    $sort0[] = 1;
                    break;
                case '30' : // 限购
                    $sort2[] = 60;
                    $sort0[] = 1;
                    break;
                default :
                    $sort2[] = 60;
                    $sort0[] = 1;
                    break;
            }
        }
        array_multisort($sort2, SORT_ASC, $sort1, SORT_ASC, $sort0, SORT_ASC, $promotionList);
        return $promotionList;
    }

    /**
     * @desc 对限购活动进行排序 (对单位为件、种、次的限购活动排序)
     * @param array $limitBuyList 限购活动列表
     * @param bool $item 是否为件数单位的限购活动 [兼容处理单位为件的限购活动]
     * @return array $promotionList|$limitBuy  排序后的限购活动列表(二维数组)|最小件数的限购活动(一维数组)
     * @author 吴俊华
     */
    protected function sortLimitBuyList($limitBuyList,$item = false)
    {
        if (empty($limitBuyList)) {
            return [];
        }
        $column1 = 'limit_unit';
        $column2 = 'limit_number';
        if($item){
            $column1 = 'item_limit_unit';
            $column2 = 'item_limit_number';
            foreach ($limitBuyList as $key => $value) {
                if($value['item_limit_number'] == 0){
                    unset($limitBuyList[$key]);
                }
            }
            if (empty($limitBuyList)) {
                return [];
            }
        }

        $sort1 = [];
        $sort2 = [];
        $sort3 = [];
        //根据字段进行排序
        foreach ($limitBuyList as $key => $value) {
            $sort1[] = $value[$column1];
            $sort2[] = $value[$column2];
            switch ($value['promotion_scope']) {
                case 'single' :
                    $sort3[] = 1;
                    break;
                case 'more' :
                    $sort3[] = 2;
                    break;
                case 'brand' :
                    $sort3[] = 3;
                    break;
                case 'category' :
                    $sort3[] = 4;
                    break;
                case 'all' :
                    $sort3[] = 5;
                    break;
                default :
                    $sort3[] = 5;
                    break;
            }
        }
        array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $sort3, SORT_ASC, $limitBuyList);
        if($item){
            return $limitBuyList[0];
        }
        return $limitBuyList;
    }

    /**
     * @desc 生成促销活动的促销文案 (满减、满折、满赠、包邮、加价购)
     * @param array $promotion 单个促销活动 [一维数组]
     * @return string $copywriter 促销文案
     * @author 吴俊华
     */
    protected function generatePromotionsCopywriter($promotion)
    {
        $copywriter = '';
        // 限购、限时优惠不能生成促销文案
        if(!in_array($promotion['promotion_type'],[5,10,15,20,40])){
            return $copywriter;
        }
        // 根据门槛排序
        $ruleArr = $this->arraySortByKey($promotion['rule_value'],'full_price','asc');
//        $discountRule = $ruleArr[0]; //较为容易达到的门槛
        // 促销文案
        if(!empty($promotion['promotion_copywriter'])){
            $copywriter = $promotion['promotion_copywriter'];
        }else{
            $copywriterArr = [];
            foreach ($ruleArr as $rule) {
                // 数据异常不能生成促销文案
                if(empty($rule['full_price']) || empty($rule['unit'])){
//                    return $copywriter;
                    continue;
                }
                $copywriterVal = '满'.$rule['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$rule['unit']];
                switch ($promotion['promotion_type']) {
                    //满减活动
                    case BaiyangPromotionEnum::FULL_MINUS:
                        $copywriterVal .= '减'.$rule['reduce_price'].'元';
                        break;
                    //满折活动
                    case BaiyangPromotionEnum::FULL_OFF:
                        $copywriterVal .= '享'.$rule['discount_rate'].'折';
                        break;
                    //满赠活动
                    case BaiyangPromotionEnum::FULL_GIFT:
                        $copywriterVal .= '送赠品';
                        break;
                    //包邮活动
                    case BaiyangPromotionEnum::EXPRESS_FREE:
                        $copywriterVal .= '包邮';
                        break;
                    //加价购活动
                    case BaiyangPromotionEnum::INCREASE_BUY:
                        $copywriterVal .= '，即可换购商品';
                        break;
                }
                $copywriterArr[] = $copywriterVal;
            }
            $copywriter = implode('，', $copywriterArr);
        }
        return $copywriter;
    }

    /**
     * @desc 根据条件进行排序
     * @param array $changeList 换购品列表
     * @return array $changeList 排序后的换购品列表
     * @author 吴俊华
     */
    protected function sortChangeGroup($changeList)
    {
        if (empty($changeList)) {
            return [];
        }
        /*****
            1.换购门槛越低排前面；
            2.门槛相同，换购价格低的排前面；
            3.价格相同，品类id越低的排前面；
            4.品类相同，品牌id越低的排前面；
            5.品牌相同，id小的排前面。
        *****/
        $sort1 = $sort2 = $sort3 = $sort4 = $sort5 = [];
        foreach ($changeList as $key => $value) {
            $sort1[] = $value['full_price'];
            $sort2[] = $value['discount_price'];
            $sort3[] = $value['category_id'];
            $sort4[] = $value['brand_id'];
            $sort5[] = $value['goods_id'];
        }
        array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $sort3, SORT_ASC, $sort4, SORT_ASC, $sort5, SORT_ASC, $changeList);
        return $changeList;
    }

}