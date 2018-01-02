<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/11 0011
 * Time: 上午 10:08
 */

namespace Shop\Home\Services;

use Phalcon\Mvc\User\Component;
use Shop\Home\Datas\BaiyangGoodsTreatmentData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangGoodsStockBondedData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangGoodsStockChangeLogData;
use Shop\Models\HttpStatus;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Services\PromotionService;
use Shop\Home\Datas\BaiyangShoppingCartData;

class O2OBaseService extends Component
{
    protected static $instance=null;

    /**
     * 实例化对象
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    /**
     *  构造方法验证平台
     */
    /*public function __construct()
    {
        $platform = $this->request->get('plarform','string');
        $platform = in_array($platform,['pc','app','wap']) ? $platform : 'pc';
        define(PLATFORM,$platform);
    }*/

    /**
     * 禁止克隆
     */
    protected function __clone()
    {
        // TODO: Implement __clone() method.
    }


    /**
     * @desc 服务端返回各个端的结果信息
     * @param int $status      状态码，在Shop\Models\HttpStatus.php中定义
     * @param string $explain  提示说明
     * @param array $data      成功后返回数据
     * @param array $tipsData  提示语里面的变量
     * @return array []
     * @author  吴俊华
     * @date    2016-10-11
     */
    public function responseResult($status, $explain = '', $data = [], $tipsData = [])
    {
        $data = empty($data) ? null : $data;
        if (empty($tipsData)) {
            return ['status' => $status, 'explain' => $explain, 'data' => $data];
        }
        $this->regularReplace($explain, $tipsData);
        return ['status' => $status, 'explain' => $explain, 'data' => $data, 'tipsData' => $tipsData];
    }

    /**
     * @desc 提示语的正则替换(把??替换成实际值)
     * @param string $explain  提示说明
     * @param array $tipsData  提示语里面的变量
     * @author  吴俊华
     */
    public function regularReplace(&$explain, $tipsData)
    {
        $par = "/\?\?/";
        foreach($tipsData as $val){
            $explain = preg_replace($par,$val,$explain,1);
        }
    }

    /**
     * @过滤去除不要的数据
     * @param string $str       //需要的字段,用","号隔开
     * @param array  $param     //需过滤的数据
     * @return array
     * @author  梁伟
     * @date    2016-10-11
     */
    public function filterData($str='',$param=array())
    {
        if( !empty($str) && !empty($param) && is_array($param) ){
            $string = ','.$str.',';
            $array = array();
            foreach( $param as $k=>$v ){
                if( strstr($string,','.$k.',') !== false){
                    $array[$k] = $v;
                }else if( is_numeric($k) && !empty($v) && is_array($v) ){
                    foreach( $v as $k1=>$v1 ){
                        if( strstr($string,','.$k1.',') !== false){
                            $array[$k][$k1] = $v1;
                        }
                    }
                }
            }
            return $array;
        }
        return $param;
    }

    /**
     * @param $min
     * @param $max
     * @author  康涛
     * @date 2016-10-20
     * 浮点数随机生成器
     * @notice  此生成器不会出现$min或$max
     */
    protected function randomFloat($min,$max)
    {
        return ($min+lcg_value()*(abs($max-$min)));
    }

    /**
     * @desc 判断接口传入必填参数
     * @param array $param 所有参数数组
     * @param string $str 验证必填参数 (多个以逗号隔开)
     * @return array|false $result 必填参数错误信息或false
     * @author 吴俊华
     */
    protected function judgeRequireParam($param,$str = '')
    {
        //必填参数
        $requireArr = [];
        if(!empty($str)){
            //unset($param['platform']);
            $requireArr = explode(',',$str);
            //必填参数数量
            $requireNum = count($requireArr);
            //交集数组
            $intersection = array_intersect_key(array_flip($requireArr),$param);
            //交集数量
            $intersectionNum = count($intersection);
            if(empty($intersection) || $intersectionNum != $requireNum){
                return $this->responseResult(HttpStatus::PARAM_ERROR,HttpStatus::$HttpStatusMsg[HttpStatus::PARAM_ERROR]);
            }
        }

        foreach($param as $key => $value){
            if(!empty($requireArr)){
                if(in_array($key,$requireArr) && empty($value)){
                    return $this->responseResult(HttpStatus::PARAM_ERROR,HttpStatus::$HttpStatusMsg[HttpStatus::PARAM_ERROR]);
                }
            }else{
                if(empty($value)){
                    return $this->responseResult(HttpStatus::PARAM_ERROR,HttpStatus::$HttpStatusMsg[HttpStatus::PARAM_ERROR]);
                }
            }
        }
        return false;
    }

    /**
     * @desc 返回结果信息到客户端
     * @param int $httpCode http状态码
     * @param array $data 数据
     * @param array $tipsData  提示语里面的变量  eg:[5]  收货地址不能超过5个 (这个5是在后台/程序设置的)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function uniteReturnResult($httpCode, $data = [], $tipsData = [])
    {
        if(!empty($httpCode)){
            return $this->responseResult($httpCode, HttpStatus::$HttpStatusMsg[$httpCode], $data, $tipsData);
        }
        return $this->responseResult(HttpStatus::FAILED, HttpStatus::$HttpStatusMsg[HttpStatus::FAILED], $data, $tipsData);
    }

    /**
     * @desc 根据各个平台商品id获取商品信息 【促销活动】
     * @param array $param
     *       -string goods_id 商品单个或多个id【多个以逗号隔开】
     *       -string platform 平台【pc、app、wap】
     * @param bool $returnOne 返回单条数据开关,true返回单条,false返回多条。默认为false
     * @return array [] 结果信息
     * @author 吴俊华
     */
    protected function getGoodsDetail($param,$returnOne = false)
    {
        $skuData = BaiyangSkuData::getInstance();
        $goodsInfo = [];
        $goodsIdArr = explode(',', $param['goods_id']);
        for ($i = 0; $i < count($goodsIdArr); $i++) {
            //返回单条数据
            if($returnOne == true){
                $goodsInfo = $skuData->getSkuInfo($goodsIdArr[$i], $param['platform']);
            }else{
                //返回多条数据
                $goodsInfo[] = $skuData->getSkuInfo($goodsIdArr[$i], $param['platform']);
            }
        }
        if(count($goodsIdArr) == 1 && $goodsInfo[0] == false){
            return [];
        }
        return $goodsInfo;
    }

    /**
     * -------------------------------------------------
     * 将二维数组按某字段进行排列
     * -------------------------------------------------
     * @param array $array       需要进行排序的数组
     * @param string $sort_key   排序的字段
     * @param string $sort_type   排序的类型
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
     * 读写行为设置
     * @param $lockKey string
     * @return string
     * @author  康涛
     */
    protected function switchOrderDb($lockKey)
    {
        if($this->cache->getValue($lockKey)){
            return \Shop\Models\BaseModelEnum::DB_WRITE;
        }
        return \Shop\Models\BaseModelEnum::DB_READ;
    }

    /**
     * @desc 获取商品满足或不满足促销活动信息 [相关信息有：优惠价、赠品、换购品等]
     * @param array  $goodsPromotionList  商品参加促销活动信息
     * @param array  $basicParam  基本参数[一维数组]
     *   [platform] -string  平台【pc、app、wap】
     *   [user_id]  -int     用户id (临时用户或真实用户id)
     *   [is_temp]  -int     是否为临时用户 (1:临时用户 0:真实用户)
     * @param string $action 动作类型(shoppingCart:购物车列表展示 orderInfo:购物车结算 commitOrder:提交订单 coupon:切换优惠券)
     * @param string $recordId 优惠券领取id
     * @return array [] 处理后的信息
     * @author 吴俊华
     */
    protected function getGoodsPromotionInfo($goodsPromotionList,$basicParam,$action = 'shoppingCart',$recordId = '')
    {
        $shoppingCartGoodsInfo = $goodsPromotionList['goodsList']; //购物车商品、活动信息
        $joinList = $goodsPromotionList['joinList'];  //商品参加活动列表
        $mutexList = $goodsPromotionList['mutexList']; //商品参加活动后的互斥列表
        //$increaseBuyList = $goodsPromotionList['increaseBuyList']; //加价购商品列表
        $increaseBuyList = [];
        $allPromotionInfo = isset($goodsPromotionList['allPromotionInfo']) ? $goodsPromotionList['allPromotionInfo'] : [] ; //全场促销活动列表
        $allTempPromotionInfo[0]['promotionInfo'] = $allPromotionInfo;
        // 初始化所有促销活动(所有参加的活动加入resultInfo，生成促销文案)
        $shoppingCartGoodsInfo = $this->_eventsManager->fire('promotion:initializePromotions',$this,['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo]);
        $allTempPromotionInfo = $this->_eventsManager->fire('promotion:initializePromotions',$this,['shoppingCartGoodsInfo' => $allTempPromotionInfo]);
        $allPromotionInfo = $allTempPromotionInfo[0]['promotionInfo'];
        unset($allTempPromotionInfo);

        if($action == 'shoppingCart'){
            //获取购物车商品状态 (0:正常 1:下架 2:缺货 3:售罄)
            $shoppingCartGoodsInfo = $this->_eventsManager->fire('promotion:getGoodsStatus',$this,['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'basicParam' => $basicParam]);
        }
        //获取以优惠券为断点，分割后的活动数组
        $promotionGoods = $this->_eventsManager->fire('promotion:getSplitPromotions',$this,['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'basicParam' => $basicParam]);
        $beforeCoupon = $promotionGoods['beforeCoupon'];
        $afterCoupon = $promotionGoods['afterCoupon'];
        //优先级在优惠券之前的促销活动
        $joinGoodsPromotion1 = $this->promotionToListen($beforeCoupon,$increaseBuyList,$mutexList,$joinList);
        //参加满减/满折、非实付满赠、加价购后，商品信息(若参加过满减/满折，优惠价已改变)
        $joinGoodsList1 = $this->getJoinGoodsList($joinGoodsPromotion1,$mutexList,$joinList);
        if(!empty($joinGoodsList1)){
            $changePromotionTotalArr = $this->_eventsManager->fire('promotion:changeGoodsPromotionTotal',$this,['joinGoodsList' => $joinGoodsList1,'shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'afterCoupon' => $afterCoupon]);
            $shoppingCartGoodsInfo = $changePromotionTotalArr['shoppingCartGoodsInfo'];
            $afterCoupon = $changePromotionTotalArr['afterCoupon'];
        }

        //购物车结算、提交订单、切换优惠券操作时，需要调优惠券侦听器
        $couponList = [];
        if($action != 'shoppingCart'){
            $usableCouponsList = $this->getUsableCouponsList($shoppingCartGoodsInfo,$basicParam,$mutexList,$joinList,$recordId,$action);
          if(isset($usableCouponsList['couponList']) && !empty($usableCouponsList['couponList'])){
                $couponList = $usableCouponsList['couponList'];
                $afterCouponParam = [
                    'promotionList' => $afterCoupon,
                    'couponGoodsList' => $usableCouponsList['goodsList'],
                    'mutexList' => $mutexList,
                    'joinList' => $usableCouponsList['joinList'],
                ];
                //排除互斥商品且修改优惠价(promotion_total)
                $afterCoupon = $this->_eventsManager->fire('promotion:excludeGoods',$this,$afterCouponParam);
            }
        }
        //优先级在优惠券之后的促销活动
        $joinGoodsPromotion2 = $this->promotionToListen($afterCoupon,$increaseBuyList,$mutexList,$joinList);
        //商品参加所有促销活动后结果信息
        $promotionGoods = array_merge($joinGoodsPromotion1,$joinGoodsPromotion2);
        //购物车匹配侦听器参加活动后的信息 (不管是否满足门槛都匹配)
        $shoppingCartGoodsInfo = $this->_eventsManager->fire('promotion:shoppingCartMatchPromotion',$this,['promotionGoods' => $promotionGoods,'shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'open' => true]);
        //删除购物车无效的换购品(加价购活动已过期)
        if(!empty($increaseBuyList)){
            $deleteResult = $this->_eventsManager->fire('promotion:deleteInvalidChangeGoods',$this,['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'increaseBuyList' => $increaseBuyList]);
        }
        //校验后的换购品列表
        $increaseBuyList = $this->_eventsManager->fire('promotion:checkSelectedChangeGoods',$this,['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'increaseBuyList' => $increaseBuyList]);

        //全场范围的活动匹配参加活动后的信息
        $tempAllPromotionInfo[0]['promotionInfo'] = $allPromotionInfo;
        $allPromotionInfo = $this->_eventsManager->fire('promotion:shoppingCartMatchPromotion',$this,['promotionGoods' => $promotionGoods,'shoppingCartGoodsInfo' => $tempAllPromotionInfo,'open' => false]);
        if($action == 'shoppingCart'){
            //购物车列表匹配限购文案
            $shoppingCartGoodsInfo = $this->_eventsManager->fire('promotion:handleLimitBuyPromotion',$this,['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo]);
            $allPromotionInfo = $this->_eventsManager->fire('promotion:handleLimitBuyAllPromotion',$this,['allPromotionInfo' => $allPromotionInfo]);
        }
        //释放促销活动无用变量
        $shoppingCartGoodsInfo = $this->_eventsManager->fire('promotion:freedUselessData',$this,['promotionsInfo' => $shoppingCartGoodsInfo]);
        $allPromotionInfo = $this->_eventsManager->fire('promotion:freedUselessData',$this,['promotionsInfo' => $allPromotionInfo]);
        //计算、匹配完促销活动的购物车列表所有信息
        $shoppingCartGoodsPromotionList = [
            'goodsList' => $shoppingCartGoodsInfo,
            'mutexList' => $mutexList,
            'joinList'  => $joinList,
            'increaseBuyList'  => $increaseBuyList,
            'allPromotionInfo'  => $allPromotionInfo[0]['promotionInfo'],
        ];
        //购物车结算和提交订单需要返回优惠券信息
        if($action != 'shoppingCart'){
            $shoppingCartGoodsPromotionList['couponList'] = $couponList;
        }
        return $shoppingCartGoodsPromotionList;
    }

    /**
     * @desc 根据活动类型映射侦听器
     * @param array $promotionGoods
     *      - array [一维数组] promotion 单个促销活动信息
     *      - array [二维数组] goodsList 商品列表信息
     * @param array $increaseBuyList 用户选中的换购品 [二维数组]
     * @param array $mutexList 商品的互斥数组，默认为空  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组，默认为空  ['7000800'=>[15,20]]
     * @return bool true|false 满足返回true,不满足返回false
     * @author 吴俊华
     */
    private function promotionMappingListen($promotionGoods,$increaseBuyList = [],$mutexList = [],$joinList = [])
    {
        switch ($promotionGoods['promotion']['promotion_type']) {
            //满减活动
            case BaiyangPromotionEnum::FULL_MINUS:   $methodName = 'fullMinus';   break;
            //满折活动
            case BaiyangPromotionEnum::FULL_OFF:     $methodName = 'fullMinus';   break;
            //满赠活动
            case BaiyangPromotionEnum::FULL_GIFT:    $methodName = 'fullGift';    break;
            //包邮活动
            case BaiyangPromotionEnum::EXPRESS_FREE: $methodName = 'expressFree'; break;
            //加价购活动
            case BaiyangPromotionEnum::INCREASE_BUY: $methodName = 'increaseBuy'; break;
            default:$methodName = '';break;
        }
        if(empty($methodName)){
            return false;
        }
        $param = [
            'promotion' => $promotionGoods['promotion'],
            'goodsList' => $promotionGoods['goodsList'],
            'platform' => $promotionGoods['platform'],
        ];
        //加价购活动需要换购品列表参数
        if($methodName == 'increaseBuy'){
            $param['increaseBuyList'] = $increaseBuyList;
        }
        //满赠、包邮侦听器需要互斥数组和商品参加活动的数组
        if($methodName == 'fullGift' || $methodName == 'expressFree'){
            $param['mutexList'] = $mutexList;
            $param['joinList'] = $joinList;
        }
        //映射对应的侦听器
        $result = $this->_eventsManager->fire('promotion:'.$methodName,$this,$param);
        return $result;
    }

    /**
     * @desc 促销活动调用对应的侦听器，匹配活动门槛返回的信息
     * @param array $promotionGoods 商品、活动信息
     *      - array [一维数组] promotion 单个促销活动信息
     *      - array [二维数组] goodsList 商品列表详细信息
     * @param array $increaseBuyList 用户选中的换购品 [二维数组]
     * @param array $mutexList 商品的互斥数组，默认为空  ['7000800'=>[15,20]]
     * @param array $joinList  商品参加过的活动数组，默认为空  ['7000800'=>[15,20]]
     * @return array $promotionGoods 商品参加促销活动后的结果信息
     * @author 吴俊华
     */
    private function promotionToListen($promotionGoods, $increaseBuyList = [], &$mutexList, &$joinList)
    {
        //调用侦听器
        foreach ($promotionGoods as $key => $value) {
            foreach ($value['goodsList'] as $kk => $vv) {
                //排除互斥的套餐
                if ($vv['group_id'] > 0) {
                    if (!$this->func->isRelatedGroup($value['promotion'], $vv, $mutexList, $joinList)) {
                        unset($promotionGoods[$key]['goodsList'][$kk]);
                    }
                } else {
                    //排除互斥活动商品
                    if (!$this->func->isRelatedGoods($value['promotion'], $vv, $mutexList, $joinList)) {
                        unset($promotionGoods[$key]['goodsList'][$kk]);
                    }
                }
            }
            $promotionGoods[$key]['goodsList'] = array_values($promotionGoods[$key]['goodsList']);
            //调侦听器
            $result = $this->promotionMappingListen($promotionGoods[$key], $increaseBuyList);
            if ($result['isCanUse']) {
                //满足门槛时需要修改joinList和mutexList
                foreach ($value['goodsList'] as $kk => $vv) {
                    //套餐
                    if ($vv['group_id'] > 0) {
                        $this->func->editJoinMutex($mutexList, $joinList, 'g'.$vv['group_id'], $value['promotion']['promotion_mutex'], $value['promotion']['promotion_type']);
                    } else {
                        //商品
                        $this->func->editJoinMutex($mutexList, $joinList, $vv['goods_id'], $value['promotion']['promotion_mutex'], $value['promotion']['promotion_type']);
                    }
                }
                if ($value['promotion']['promotion_type'] == BaiyangPromotionEnum::FULL_MINUS
                    || $value['promotion']['promotion_type'] == BaiyangPromotionEnum::FULL_OFF
                ) {
                    foreach ($promotionGoods as $kk => $vv) {
                        foreach ($vv['goodsList'] as $goodsKey => $goodsInfo) {
                            //满足满减/满折活动时，需要把参加其他促销活动对应商品id的优惠价改变
                            foreach ($result['goodsList'] as $k => $v) {
                                if (($v['group_id'] > 0 && $v['group_id'] == $promotionGoods[$kk]['goodsList'][$goodsKey]['group_id']) || ($v['group_id'] == 0 && $v['goods_id'] == $promotionGoods[$kk]['goodsList'][$goodsKey]['goods_id'])) {

                                    $promotionGoods[$kk]['goodsList'][$goodsKey]['promotion_total'] = $v['promotion_total'];
                                }
                            }
                        }
                    }
                }
            }

            //促销活动匹配侦听器返回的结果信息
            $promotionGoods[$key]['promotion']['resultInfo']['isCanUse'] = $result['isCanUse'];
            $promotionGoods[$key]['promotion']['resultInfo']['bought_number'] = $result['bought_number'];
            $promotionGoods[$key]['promotion']['resultInfo']['lack_number'] = $result['lack_number'];
            $promotionGoods[$key]['promotion']['resultInfo']['unit'] = $result['unit'];
            $promotionGoods[$key]['promotion']['resultInfo']['copywriter'] = $result['copywriter'];
            $promotionGoods[$key]['promotion']['resultInfo']['message'] = $result['message'];
            $promotionGoods[$key]['promotion']['resultInfo']['pro_message'] = $result['pro_message'];
            if (isset($result['reduce_price'])) {
                $promotionGoods[$key]['promotion']['resultInfo']['reduce_price'] = $result['reduce_price'];
            }
            if (isset($result['premiums_group'])) {
                $promotionGoods[$key]['promotion']['resultInfo']['premiums_group'] = $result['premiums_group'];
            }
            if (isset($result['change_group'])) {
                $promotionGoods[$key]['promotion']['resultInfo']['change_group'] = $result['change_group'];
            }
            if (isset($result['increaseBuyList'])) {
                $promotionGoods[$key]['promotion']['resultInfo']['increaseBuyList'] = $result['increaseBuyList'];
            }
            if (isset($result['full_number'])) {
                $promotionGoods[$key]['promotion']['resultInfo']['full_number'] = $result['full_number'];
            }
        }
        return $promotionGoods;
    }

    /**
     * @desc 满足门槛的促销活动返回已参加活动的商品信息
     * @param array $joinGoodsPromotion 已参加促销活动的商品、活动信息
     *      - array [一维数组] promotion 单个促销活动信息
     *      - array [二维数组] goodsList 商品列表详细信息
     * @return array $joinGoodsList 已参加活动的商品信息
     * @author 吴俊华
     */
    private function getJoinGoodsList($joinGoodsPromotion)
    {
        $joinTempGoodsList = [];
        $joinGoodsList = [];
        foreach($joinGoodsPromotion as $value){
            if(isset($value['goodsList'])){
                foreach($value['goodsList'] as $vv){
                    $joinTempGoodsList[] = $vv;
                }
            }
        }
        //根据优惠价排序
        $joinTempGoodsList = $this->arraySortByKey($joinTempGoodsList,'promotion_total','asc');
        foreach($joinTempGoodsList as $key => $value){
            //去重，商品相同时，留下优惠价较低的商品
            if(!isset($joinGoodsList[$value['group_id'].$value['goods_id']])){
                $joinGoodsList[$value['group_id'].$value['goods_id']] = $value;
            }
        }
        $joinGoodsList = array_values($joinGoodsList);
        return $joinGoodsList;
    }

    /**
     * @desc 获取可用优惠券列表
     * @param array $shoppingCartGoodsInfo 购物车信息
     * @param array $basicParam 基础信息
     * @param array $mutexList 商品的互斥数组  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组  ['7000800'=>[15,20]]
     * @param string $recordId 优惠券领取id
     * @param string $action 动作类型(orderInfo:购物车结算 commitOrder:提交订单 coupon:切换优惠券)
     * @return array $usableCouponsList 可用优惠券列表
     * @author 吴俊华
     */
    private function getUsableCouponsList($shoppingCartGoodsInfo,$basicParam,$mutexList,$joinList,$recordId ='',$action = 'orderInfo')
    {
        $shoppingCartGoodsList = []; //购物车所有商品信息
        //组装优惠券需要的商品信息(若商品参加过满减/满折，优惠价已改变)
        foreach($shoppingCartGoodsInfo as $value){
            if($value['group_id'] == 0){
                //商品
                $shoppingCartGoodsList[] = [
                    'goods_id' => $value['goods_id'],
                    'group_id' => $value['group_id'],
                    'goods_number' => $value['goods_number'],
                    'brand_id' => $value['brand_id'],
                    'category_id' => $value['category_id'],
                    'drug_type' => $value['drug_type'],
                    'promotion_price' => $value['promotion_price'],
                    'discount_price' => $value['discount_price'],
                    'promotion_total' => $value['promotion_total'],
                    'discount_total' => $value['discount_total'],
                ];
            }else{
                //套餐
                $shoppingCartGoodsList[] = [
                    'goods_id' => 0,
                    'group_id' => $value['group_id'],
                    'goods_number' => $value['goods_number'],
                    'promotion_total' => $value['promotion_total'],
                    'discount_total' => $value['discount_total'],
                    'groupGoodsList' =>  $value['groupGoodsList'],
                ];
            }
        }
        $usableParam = [
            'basicParam' => $basicParam,
            'shoppingCartGoodsList' => $shoppingCartGoodsList,
            'joinList' => $joinList,
            'mutexList' => $mutexList,
            'recordId' => $recordId,
            'action' => $action,
        ];//print_r($usableParam);
        $usableCouponsList = $this->_eventsManager->fire('promotion:getCouponList',$this,$usableParam);
        $couponList = [];
       /* if(isset($usableCouponsList['couponList']) && !empty($usableCouponsList['couponList'])){
            $coupon = [];
            foreach($usableCouponsList['couponList'] as $v){
                if($coupon){
                    if(in_array($v['coupon_sn'], array_column($coupon,'coupon_sn')) === false){
                        $coupon[] = $v;
                    }
                }else{
                    $coupon[] = $v;
                }

            }
            $shoppingCartGoodsPromotionList['couponList'] = $coupon;

        }*/
//print_r($usableCouponsList['couponList']);

        if(isset($usableCouponsList['couponList']) && !empty($usableCouponsList['couponList'])){
            foreach($usableCouponsList['couponList'] as $value){
                if($couponList){
                    if(in_array($value['coupon_sn'], array_column($couponList,'coupon_sn')) === false){
                        $couponList[] = [
                            'coupon_sn' => $value['coupon_sn'],
                            'record_id' => $value['record_id'],
                            'coupon_name' => $value['coupon_name'],
                            'coupon_type' => $value['coupon_type'],
                            'coupon_range' => $value['use_range'],
                            'coupon_price' => $value['discount'],
                            'coupon_value' => $value['coupon_value'],
                            'expiration' => $value['expiration'],
                            'selected' => $value['is_selected'],
                        ];
                    }
                }else{
                    $couponList[] = [
                        'coupon_sn' => $value['coupon_sn'],
                        'record_id' => $value['record_id'],
                        'coupon_name' => $value['coupon_name'],
                        'coupon_type' => $value['coupon_type'],
                        'coupon_range' => $value['use_range'],
                        'coupon_price' => $value['discount'],
                        'coupon_value' => $value['coupon_value'],
                        'expiration' => $value['expiration'],
                        'selected' => $value['is_selected'],
                    ];
                }

            }
            $usableCouponsList['couponList'] = $couponList;
        }
        return $usableCouponsList;
    }
	
	/**
	 * 判断跨境商品信息
	 * @param array $param
	 * @return \array[]
	 */
	protected function globalOrderPromotionInfo ( array $param)
	{
		// 格式化参数
		$userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
		$isTemp = 0;
		$platform = isset($param['platform']) ? (string)$param['platform'] : "";
		$action = $param['action'];
		// 判断参数是否合法
		if ($userId < 1 || !$this->verifyRequiredParam($param))
		{
			return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
		}
		// 获取购物车商品列表
		$whereParam = ['user_id'=> $userId,'is_temp'=> $isTemp,'is_global'=> 1,'selected'=>1];
		$list = BaiyangShoppingCartData::getInstance()->getShoppingCart($whereParam);
		// 查询商品库存去除库存不足的商品
		foreach ($list as $k => &$item)
		{
			$stock = $this->func->getCanSaleStock(['goods_id'=> $item['goods_id'], 'platform'=> $platform]);;
			$bond = BaiyangGoodsStockBondedData::getInstance()->getGoodsBondId($item['goods_id']);
			$goodsExtend = BaiyangSkuData::getInstance()->getGoodsExtend($item['goods_id']);
			$goodsInfo = BaiyangSkuData::getInstance()->getGlobalGoods($item['goods_id']);
			if ($stock <= 0) {
				$item['goods_number'] = 1;
				$item['selected'] = 0;//库存不足不能选中
			} elseif ($item['goods_number'] > $stock) {
				$item['goods_number'] = $stock;
			}
			$item['stock'] = $stock;
			$item['rate'] = $goodsExtend['tax_rate'];
			$item['goods_custom_name'] = $goodsExtend['goods_custom_name'];
			$item['code_ts'] = $goodsExtend['hs_code'];
			$item['item_record_no'] = $goodsExtend['item_record_no'];
			$item['goods_unit'] = $goodsExtend['goods_unit'];
			$item['goods_name'] = $goodsInfo['name'];
			$item['goods_price'] = $goodsInfo['sku_price'];
			$item['sku_price'] = $goodsInfo['sku_price'];
			$item['discount_price'] = $goodsInfo['sku_price'];
			$item['market_price'] = $goodsInfo['sku_market_price'];
			$item['specifications'] = $goodsInfo['specifications'];
			$item['goods_image'] = $goodsInfo['goods_image'];
			$item['brand_id'] = $goodsInfo['brand_id'];
			$item['drug_type'] = $goodsInfo['drug_type'];
			$item['goods_tax_amount'] = bcmul( ($item['sku_price'] * $item['goods_number']) , ($item['rate'] * 0.01), 2);
			$item['bond'] = $bond;
		}
		// 去除因各种原因没选中的商品
		$result['goodsList'] = $this->_eventsManager->fire('promotion:delNotSelectGoods',$this,$list);
		if (empty($result['goodsList']))
		{
			return $this->uniteReturnResult(HttpStatus::NOT_SELECT_GOODS, ['param'=> $param]);
		}
		// 验证库存
		$ret = $this->_eventsManager->fire('promotion:verifyGlobalOrderGoods', $this, $result);
		if ($ret['status'] != HttpStatus::SUCCESS)
		{
			return $ret;
		}
		$bonds = array_unique(array_column ($result[ 'goodsList' ], 'bond'));
		if ( ($action == 'commitOrder') && (count ($bonds) > 1) )
		{
			return $this->uniteReturnResult (HttpStatus::NOT_SAME_BOND, ['param' => $param]);
		}
		$result['bond'] = $bonds;
		if ($action == "orderInfo")
		{
			// 梳理结算页面所需求的字段
			$result = $this->_eventsManager->fire('promotion:getGlobalInfoField',$this,$result);
			if ($result['goodsTotalPrice'] > 2000) { return $this->uniteReturnResult(HttpStatus::OVER_2000, $result); }
		} else {
			// 梳理提交订单所需求的字段
			$result = $this->_eventsManager->fire('promotion:getCommitGlobalOrderField',$this,$result);
		}
		return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }
    
    /**
     * @desc 确认订单/提交订单
     * @param array $param
     * @author 柯琼远
     * @return array
     */
    protected function orderPromotionInfo($param) {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = 0;
        $record_id = isset($param['record_id']) ? (string)$param['record_id'] : '';
        $platform = isset($param['platform']) ? (string)$param['platform'] : "";
        $action = $param['action'];
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        // 获取购物车商品列表
        $list = BaiyangShoppingCartData::getInstance()->getShoppingCart([
            'user_id'   => $userId,
            'is_temp'   => $isTemp,
            'is_global' => 0,
            'selected'  =>1
        ]);
        $cartList = array();  // 普通商品列表
        $increaseBuyList = array();// 加价购商品列表
        foreach ($list as $value) {
            if ($value['increase_buy'] == 0) $cartList[] = $value;
            else $increaseBuyList[] = $value;
        }
        // 套餐/限时优惠/疗程/会员价
        $result = $this->_eventsManager->fire('promotion:getGoodsDiscountInfo',$this,[
            'cartGoodsList'=> $cartList,
            'platform'=> $platform,
            'userId'=> $userId,
            'isTemp'=> $isTemp
        ]);
        // 加价购的商品特殊处理
        $result['increaseBuyList'] = $increaseBuyList;
        // 显示促销活动
        $result = $this->_eventsManager->fire('promotion:getCartPromotion',$this,[
            'shoppingCartInfo'=> $result,
            'userId'=> $userId,
            'isTemp'=> $isTemp
        ]);
        // 验证限购
        $listenParam = array_merge($this->_eventsManager->fire('promotion:getLimitBuyParam',$this,$result), ['platform'=>$platform, 'user_id'=>$userId, 'is_temp'=>0]);
        $ret = $this->_eventsManager->fire('promotion:limitBuy',$this,$listenParam);
        if ($ret['error'] == 1) {
            return $this->uniteReturnResult($ret['code'], ['param'=>$param], $ret['data']);
        }
        // 计算活动门槛
        $result = $this->getGoodsPromotionInfo($result, ['platform' => $platform, 'user_id' => $userId, 'is_temp' => $isTemp], $action, $record_id);
        // 去除因各种原因没选中的商品
        $result['goodsList'] = $this->_eventsManager->fire('promotion:delNotSelectGoods',$this,$result['goodsList']);
        $result['increaseBuyList'] = $this->_eventsManager->fire('promotion:delNotSelectGoods',$this,$result['increaseBuyList']);
        if (empty($result['goodsList'])) {
            return $this->uniteReturnResult(HttpStatus::NOT_SELECT_GOODS, ['param'=> $param]);
        }
        // 验证库存
        $ret = $this->_eventsManager->fire('promotion:verifyOrderGoods', $this, $result);
        if ($ret['status'] != HttpStatus::SUCCESS) {
            return $ret;
        }
        // 获取到达门槛的活动列表
        $result['availPromotionList'] = $this->_eventsManager->fire('promotion:getCanUsePromotion',$this,$result);
        if ($action == "orderInfo" || $action == "coupon") {
            // 显示换购品和赠品
            $result = $this->_eventsManager->fire('promotion:getIncreaseBuyGiftShow',$this,$result);
            // 梳理结算页面所需求的字段
            $result = $this->_eventsManager->fire('promotion:getOrderInfoField',$this,$result);
            if ($action == "coupon" && !empty($record_id) && empty($result['couponInfo'])) {
                return $this->uniteReturnResult(HttpStatus::INVALID_COUPON, ['param'=> $param]);
            }
            unset($result['couponInfo']);
        } else {
            // 梳理提交订单所需求的字段
            $result = $this->_eventsManager->fire('promotion:getCommitOrderField',$this,$result);
            if (!empty($record_id) && empty($result['couponInfo'])) {
                return $this->uniteReturnResult(HttpStatus::INVALID_COUPON, ['param'=> $param]);
            }
        }
        // 判断处方药能不能加到购物车
        if ($result['rxExist'] == 1 && $this->func->getDisplayAddCart($platform) == 0) {
            return $this->uniteReturnResult(HttpStatus::RX_CANNOT_ADD_TO_CART, ['param'=> $param]);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }
    
    /**
     * @desc 验证必填参数
     * @param array $param 必填参数 [一维数组]
     * @param  - string platform 平台      (公共必填参数)
     * @param  - int channel_subid  渠道号 (公共必填参数)
     * @param  - string udid  手机唯一id   (app必填参数)
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function verifyRequiredParam($param)
    {
        if(!isset($param['platform']) || !isset($param['channel_subid'])){
            return false;
        }
        if (!in_array($param['platform'], ['pc', 'app', 'wap', 'wechat'])) {
            return false;
        }
        if (!in_array($param['channel_subid'], [89, 90, 85, 91, 95])) {
            return false;
        }
        if($param['platform'] == 'app'){
            if(!isset($param['udid']) || empty($param['udid'])){
                return false;
            }
        }
        // 公共参数存进配置里
        $this->config->platform = $param['platform'];
        $this->config->channel_subid = $param['channel_subid'];
        return true;
    }

    /**
     * @remark 转移图片
     * @param array $param [使用base64_encode()之后的数据,使用base64_encode()之后的数据]
     * @return string 图片路径|错误信息
     * @author 梁伟
     */
    public function moveImg($img)
    {
        $imgTmp = '/tmp/'.time();
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<10;$i++){
            $imgTmp.=$strPol[rand(0,$max)];
        }
        $imgTmp.='.jpg';
        $a = file_put_contents($imgTmp, base64_decode($img));
        if(!$a){
            @unlink($imgTmp);
            return $this->uniteReturnResult(HttpStatus::COMMENT_IMG_UPLOAD_FAILED);
        }
        if($a > 1024*1024*2){
            @unlink($imgTmp);
            return $this->uniteReturnResult(HttpStatus::COMMENT_IMG_SIZE);
        }
        $url = $this->FastDfs->uploadByFilename($imgTmp,2,'G1');
        @unlink($imgTmp);
        return $this->config['domain']['img'].$url;
    }

}