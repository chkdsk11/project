<?php
/**
 * 公共函数类
 * Created by PhpStorm.
 * User: Sary
 * Date: 2016/12/30
 * Time: 16:45
 */
namespace Shop\Libs;

use Shop\Home\Datas\BaiyangGoods;
use Shop\Home\Datas\BaiyangGoodsStockBondedData;
use Shop\Home\Datas\BaiyangMerchantGoodsWarehouseRelationData;
use Shop\Home\Datas\BaiyangOrderDetailData;
use Shop\Home\Datas\BaiyangUserSinceShopData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangOrderData;
use Shop\Home\Datas\BaiyangGoodsStockChangeLogData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Models\OrderEnum;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Libs\LibraryBase;
use Shop\Home\Services\SmsService;
use Shop\Datas\BaiyangRoleData;
use Shop\Datas\BaiyangAdminResourceData;
use Shop\Services\AdminRoleService;

class Func extends LibraryBase
{

    /**
     * 是否手机号码
     *
     * @param string|int $number
     * @return int
     */
    public function isPhone($number)
    {
        return preg_match('/^1[3456789][1-9]{1}\d{8}$/', $number);
    }

    /**
     * 是否身份证
     *
     * @param string $number
     * @return bool|int
     */
    public function isIdCard($number)
    {
        $number = (string)$number;
        $number = strtoupper($number);
        $isEighteen = preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/", $number);
        if ($isEighteen) {
            return true;
        }
        return preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/", $number);
    }

    /**
     * 是否正常的时间戳(时间年份大于1970)
     *
     * @param $timestamp
     * @return bool
     */
    public function isTimestamp($timestamp)
    {
        if (strtotime(date('Y-m-d H:i:s', $timestamp)) === $timestamp) {
            return true;
        }
        return false;
    }

    /**
     * 创建一个指定长度的随机密码
     * @param int $pw_length
     * @return string
     */
    public static function create_password($pw_length = 10)
    {
        $texts = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        shuffle($texts);
        $count = count($texts)-1;

        $randpwd = '';
        for ($i = 0; $i < $pw_length; $i++)
        {
            $randpwd .= $texts[mt_rand(0, $count)];
        }
        return $randpwd;
    }

    /**
     * 获取用户昵称
     *
     * @param string $nickName 用户昵称
     * @return string
     */
    public function getNickName($nickName)
    {
        $temp = '';
        if ($nickName) {
            $temp = substr($nickName, 0, 3);
            $temp .= '****';
            $temp .= substr($nickName, -4);
        }
        return $temp;
    }

    /**
     * @desc 推送到中民返利网
     * @param string $orderSn 订单编号
     * @author 吴俊华
     * @return []
     */
    public function sendZmCps($orderSn)
    {
        $baseData = BaseData::getInstance();
        $orderData = BaiyangOrderData::getInstance();
        // 中民返利网配置
        $zmConfig = $this->config->zm_rebate;
        $env = $this->config->environment;
        // 订单使用优惠券信息
        $couponRecord = $baseData->getData([
            'table' => 'Shop\Models\BaiyangCouponRecord cr',
            'join' => 'left join Shop\Models\BaiyangCoupon cp on cp.coupon_sn = cr.coupon_sn',
            'column' => 'cr.coupon_sn, cr.is_used, cr.order_sn, cp.coupon_value, cp.type, cp.condition',
            'where' => 'where cr.order_sn = :order_sn: and cr.is_used = 1',
            'bind' => [
                'order_sn' => $orderSn
            ],
        ]);
        //佣金比率
        $rate = empty($couponRecord) ? 15 : 10.5;

        // 订单信息
        $orderInfo = $orderData->getTheOrder([
            'column' => 'order_sn,total,carriage,status,ad_web_id,add_time',
            'where'=>'order_sn = :order_sn:',
            'bind'=>[
                'order_sn' => $orderSn,
            ]
        ]);
        //订单状态
        switch ($orderInfo['status'])
        {
            case 'paying':
                $status = 1;//下单未支付
                break;
            case 'canceled':
                $status = -1;//取消
                break;
            case 'refund':
                $status = -1;//退款/售后
                break;
            case 'evaluating':
                $status = 3;//确认收货
                break;
            case 'finished':
                $status = 3;//订单完成
                break;
            default:
                $status = 2;
        }
        // 佣金
        $totalprice = $orderInfo['total'] - $orderInfo['carriage'];
        $commission = sprintf('%.2f', bcmul($totalprice, $rate, 2) / 100);
        // post数据
        $postData['orderid'] = $orderInfo['order_sn'];
        $postData['siteid'] = $zmConfig[$env]->zm_siteid;
        $postData['orderdate'] = date('Y-m-d H:i:s', $orderInfo['add_time']);
        $postData['totalprice'] = $orderInfo['total'];
        $postData['commission'] = $commission;
        $postData['status'] = $status;
        $postData['euid'] = $orderInfo['ad_web_id'];
        $sign = $postData['orderid'] . $postData['siteid'] . $zmConfig[$env]->zm_key;
        $postData['sign'] = strtolower(md5($sign));
        $this->curl->sendPost($zmConfig[$env]->zm_cps_url, http_build_query($postData));
    }

    /**
     * @desc 获取配置值
     * @param string $configSign 配置标识
     * @author 柯琼远
     * @return string
     */
    public function getConfigValue($configSign) {
        $result = BaseData::getInstance()->getData([
            'table' => 'Shop\Models\BaiyangConfig',
            'column' => 'config_value',
            'where' => 'where config_sign = :configSign:',
            'bind' => [
                'configSign' => $configSign
            ],
        ], true);
        return empty($result) ? '' : $result['config_value'];
    }

    /**
     * @desc 获取物流信息
     * @param string $orderSn 订单编号
     * @author 吴俊华
     * @return array|null 结果信息
     */
    public function getLogisticsData($orderSn)
    {
        // 物流环境变量
        $env = $this->config->environment;
        // 物流接口url
        $url = $this->config->app_url;
        if($orderSn){
            $request_url = $url[$env].'/express/ali_express';
            $postData['order_id'] = $orderSn;
            $jsonData = $this->curl->sendPost($request_url, http_build_query($postData));
            $logisticData = json_decode($jsonData,true);
            if($logisticData['code'] == '200'){
                return $logisticData['data'];
            }
            return null;
        }
        return null;
    }

    /**
     * @remark 将数组拼接成字段
     * @param $param=array() 参数
     * @param $notArr=array() 不需要拼接的参数
     * @return string
     * @author 杨永坚
     */
    public function jointString($param, $notArr)
    {
        $string = '';
        foreach($param as $k=>$v){
            if(!in_array($k, $notArr)){
                $string .= empty($string) ? "{$k}=:{$k}:" : ",{$k}=:{$k}:";
            }
        }
        return $string;
    }

    /**
     * @remark 将数组拼接成字段
     * @param int $length 随机数长度
     * @return string
     * @author 柯琼远
     */
    function getRandChar($length = 30){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        for($i = 0; $i < $length; $i++){
            $str.=$strPol[rand(0, (strlen($strPol) - 1))];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * @desc 构造选中的字段数组
     * @param string $list       需要构造的数组
     * @param string $value      选中的值
     * @param string $fieldName  字段，默认:id
     * @author 柯琼远
     * @return string
     */
    public function arrayFieldSelected ($list, $value, $fieldName = 'id') {
        $info = array();
        foreach ($list as $key => $item) {
            if ($item[$fieldName] == $value) {
                $list[$key]['selected'] = 1;
                $info = $item;
            } else {
                $list[$key]['selected'] = 0;
            }
        }
        if (empty($info) && !empty($list)) {
            $list[0]['selected'] = 1;
            $info = $list[0];
            $value = $info[$fieldName];
        }
        return [
            'list'  => $list,
            'info'  => $info,
            'value' => $value,
        ];
    }

    /**
     * @desc 获取商品可售库存
     * @param array $param
     *       -string goods_id 商品单个或多个id【多个以逗号隔开】
     *       -string platform 平台【pc、app、wap】
     * @return array|int $canSaleStockArr 商品可售库存或0
     * @author 吴俊华
     */
    public function getCanSaleStock($param)
    {
        // 商品可售库存
        $canSaleStockArr = [];
        // 商品详情
        $skuData = BaiyangSkuData::getInstance();
        $goodsInfo = [];
        $goodsIdArr = explode(',', $param['goods_id']);
        for ($i = 0; $i < count($goodsIdArr); $i++) {
            $goodsInfo[] = $skuData->getSkuInfoLess($goodsIdArr[$i], $param['platform']);
        }
        if(count($goodsIdArr) == 1 && empty($goodsInfo[0])){
            return 0;
        }
        // 商品锁定库存
        $goodsStockChangeLog = BaiyangGoodsStockChangeLogData::getInstance();
        $goodsStockChange = $goodsStockChangeLog->getGoodsStockChange($param);

        if(!empty($goodsInfo)){
            if(!empty($goodsStockChange)){
                foreach($goodsInfo as $key => $value){
                    foreach($goodsStockChange as $kk => $vv){
                        if($value['id'] == $vv['goods_id']){
                            //统一真实库存
                            if($value['is_use_stock'] == 1 && $vv['stock_type'] == 1){
                                $goodsInfo[$key]['sku_stock'] = $goodsInfo[$key]['sku_stock'] + $vv['change_num'];
                            }
                            //统一虚拟库存
                            if($value['is_use_stock'] == 2 && $vv['stock_type'] == 2){
                                $goodsInfo[$key]['sku_stock'] = $goodsInfo[$key]['sku_stock'] + $vv['change_num'];
                            }

                            //虚拟分端库存
                            if($value['is_use_stock'] == 3 && $vv['stock_type'] == 3){
                                switch ($param['platform']) {
                                    case 'pc':
                                        if($vv['channel'] == 95){
                                            $goodsInfo[$key]['sku_stock'] = $goodsInfo[$key]['sku_stock'] + $vv['change_num'];
                                        }
                                        break;
                                    case 'app':
                                        if($vv['channel'] == 89 || $vv['channel'] == 90){
                                            $goodsInfo[$key]['sku_stock'] = $goodsInfo[$key]['sku_stock'] + $vv['change_num'];
                                        }
                                        break;
                                    case 'wap':
                                        if($vv['channel'] == 91 || $vv['channel'] == 85){
                                            $goodsInfo[$key]['sku_stock'] = $goodsInfo[$key]['sku_stock'] + $vv['change_num'];
                                        }
                                        break;
                                    default:
                                        return 0;
                                        break;
                                }

                            }
                        }
                    }
                }
            }
            //单个商品的可售库存
            if(count($goodsInfo) == 1){
                return (int)$goodsInfo[0]['sku_stock'] >= 0 ? (int)$goodsInfo[0]['sku_stock'] : 0;
            }
            //多个商品的可售库存
            foreach ($goodsInfo as $key => $value) {
                $canSaleStockArr[$goodsInfo[$key]['id']] = ((int)$goodsInfo[$key]['sku_stock'] >= 0) ? (int)$goodsInfo[$key]['sku_stock'] : 0;
            }
        }
        if(count(explode(',',$param['goods_id'])) == 1){
            return 0;
        }
        $goodsIdList = explode(',', $param['goods_id']);
        //商品默认库存为0
        $goodsDefaultStock = [];
        foreach($goodsIdList as $goodsId){
            $goodsDefaultStock[$goodsId] = 0;
        }
        return array_replace($goodsDefaultStock, $canSaleStockArr);
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
     * @desc 根据父分类id获取所有子分类id
     * @param string $pid 分类id (多个以逗号隔开)
     * @return string $cid 该分类下的所有子分类id (多个以逗号隔开)
     * @author 吴俊华
     */
    public function getAllCategoryId($pid)
    {
        $allChildCategory = $this->getAllChildCategory($pid);
        if(!empty($allChildCategory)){
            $tempChildCategory = [];
            foreach ($allChildCategory as $key => $value){
                // 只保留3级分类
                if($value['level'] == 3){
                    $tempChildCategory[] = $value['id'];
                }
            }
            if(!empty($tempChildCategory)){
                $strCategorys = implode(',', $tempChildCategory);
                return rtrim($pid, ',') . ','.$strCategorys;
            }
        }
        return $pid;
    }

    /**
     * @desc 递归：根据父分类id获取所有子分类
     * @param string $pid 分类id (多个以逗号隔开)
     * @return array $data|[] 该分类下的所有子分类
     * @author 吴俊华
     */
    public function getAllChildCategory($pid)
    {
        $data = BaseData::getInstance()->getData([
            'table' => 'Shop\Models\BaiyangCategory',
            'column' => 'id,pid,level,has_child',
            'where' => "where pid in ({$pid})",
        ]);
        if(!empty($data)){
            $cid = [];
            foreach ($data as $value){
                if($value['level'] < 3){
                    $cid[] = $value['id'];
                }
            }
            if(!empty($cid)){
                $cid = implode(',', $cid);
                $data = array_merge($data,$this->getAllChildCategory($cid));
            }
        }
        return $data;
    }

    /**
     * @desc 获取处方药商品是否显示"提交需求"
     * @param string $platform 平台
     * @return int 0|1 结果信息(1:显示 0:不显示)
     * @author 吴俊华
     */
    public function getDisplayAddCart($platform)
    {
        switch ($platform) {
            case OrderEnum::PLATFORM_PC:
                $configSign = 'displayPcAddCart';
                break;
            case OrderEnum::PLATFORM_APP:
                $configSign = 'displayAddCart';
                break;
            case OrderEnum::PLATFORM_WAP:
                $configSign = 'displayWapAddCart';
                break;
            case OrderEnum::PLATFORM_WECHAT:
                $configSign = 'displayWebchatAddCart';
                break;
        }
        return $this->getConfigValue($configSign);
    }

    /**
     * @desc 修改商品参加/互斥的促销活动数组
     * @param array  $mutexList 互斥数组
     * @param array  $joinList  商品参加活动的数组
     * @param int    $goods_id   商品id
     * @param string $mutex     互斥活动类型[多个以逗号隔开]
     * @param int    $promotion_type 活动类型
     * @return void
     * @author 柯琼远
     */
    public function editJoinMutex(&$mutexList, &$joinList, $goods_id, $mutex, $promotion_type)
    {
        // 互斥的数组
        if (!empty($mutex)) {
            $tempMutex = explode(',', $mutex);
            if (!isset($mutexList[$goods_id])) {
                $mutexList[$goods_id] = $tempMutex;
            } else {
                $mutexList[$goods_id] = array_values(array_unique(array_merge($mutexList[$goods_id], $tempMutex)));
            }
        }
        // 参加过的数组
        $tempJoin = array($promotion_type);
        if (!isset($joinList[$goods_id])) {
            $joinList[$goods_id] = $tempJoin;
        } else {
            $joinList[$goods_id] = array_values(array_unique(array_merge($joinList[$goods_id], $tempJoin)));
        }
    }

    /**
     * @desc 判断套餐是否满足促销活动条件
     * @param array $promotion 促销活动信息
     * @param array $groupInfo 套餐信息 [group_id=>int,groupGoodsList=>array]
     * @param array $mutexList 商品的互斥数组,默认为空  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组,默认为空  ['7000800'=>[15,20]]
     * @return bool true|false 满足返回true,不满足返回false
     * @author 吴俊华
     */
    public function isRelatedGroup($promotion, $groupInfo, $mutexList = [], $joinList = [])
    {
        foreach ($groupInfo['groupGoodsList'] as $value) {
            if (!$this->isRelatedGoods($promotion, $value)) {
                return false;
            }
        }
        // 其他活动互斥当前活动
        if(isset($mutexList['g'.$groupInfo['group_id']]) && !empty($mutexList['g'.$groupInfo['group_id']])) {
            if (in_array($promotion['promotion_type'], $mutexList['g'.$groupInfo['group_id']])) {
                return false;
            }
        }
        // 当前活动互斥其他活动
        if(isset($joinList['g'.$groupInfo['group_id']]) && !empty($joinList['g'.$groupInfo['group_id']])) {
            if (array_intersect($joinList['g'.$groupInfo['group_id']], explode(',', $promotion['promotion_mutex']))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @desc 判断商品是否满足促销活动条件
     * @param array $promotion 促销活动信息
     * @param array $goodsInfo 商品信息
     * @param array $mutexList 商品的互斥数组,默认为空  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组,默认为空  ['7000800'=>[15,20]]
     * @return bool true|false 满足返回true,不满足返回false
     * @author 吴俊华
     */
    public function isRelatedGoods($promotion, $goodsInfo, $mutexList = [], $joinList = [])
    {
        // 使用范围：全场、品类、品牌、单品、多单品
        switch ($promotion['promotion_scope']) {
            case BaiyangPromotionEnum::ALL_RANGE:      $keyIdName = ''; break;
            case BaiyangPromotionEnum::CATEGORY_RANGE: $keyIdName = 'category_id'; break;
            case BaiyangPromotionEnum::BRAND_RANGE:    $keyIdName = 'brand_id'; break;
            case BaiyangPromotionEnum::SINGLE_RANGE:   $keyIdName = 'goods_id'; break;
            case BaiyangPromotionEnum::MORE_RANGE:     $keyIdName = 'goods_id'; break;
            default: $keyIdName = ''; break;
        }
        // 排除虚拟商品
//        if (!isset($goodsInfo['drug_type'])) {
//            $skuInfo = BaiyangSkuData::getInstance()->getSkuInfo($goodsInfo['goods_id'], 'pc');
//            $goodsInfo['drug_type'] = $skuInfo['drug_type'];
//        }
//        if ($goodsInfo['drug_type'] == 5) {
//            return false;
//        }
        // 排除海外购商品
        if(isset($goodsInfo['is_global']) && $goodsInfo['is_global']){
            return false;
        }
        // 排除非自营商品
        if (isset($goodsInfo['supplier_id']) && $goodsInfo['supplier_id'] != 1 && in_array($promotion['promotion_type'], [BaiyangPromotionEnum::FULL_GIFT, BaiyangPromotionEnum::INCREASE_BUY])) {
            return false;
        }
        // 排除不满足条件的商品
        if ($keyIdName != '' && !in_array($goodsInfo[$keyIdName], explode(',', $promotion['condition']))) {
            return false;
        }
        // 其他活动互斥当前活动
        if(isset($mutexList[$goodsInfo['goods_id']]) && !empty($mutexList[$goodsInfo['goods_id']])) {
            if (in_array($promotion['promotion_type'], $mutexList[$goodsInfo['goods_id']])) {
                return false;
            }
        }
        // 当前活动互斥其他活动
        if(isset($joinList[$goodsInfo['goods_id']]) && !empty($joinList[$goodsInfo['goods_id']])) {
            if (array_intersect($joinList[$goodsInfo['goods_id']], explode(',', $promotion['promotion_mutex']))) {
                return false;
            }
        }
        // 排除的商品ID
        if(in_array($goodsInfo['goods_id'], explode(',', $promotion['except_good_id']))) {
            return false;
        }
        // 排除的品牌ID
        if(in_array($goodsInfo['brand_id'], explode(',', $promotion['except_brand_id']))) {
            return false;
        }
        // 获取该分类下的所有子分类(主要是3级分类)
        if(!empty($promotion['except_category_id'])){
            $promotion['except_category_id'] = $this->getAllCategoryId($promotion['except_category_id']);
        }
        // 排除的品类ID
        if(in_array($goodsInfo['category_id'], explode(',', $promotion['except_category_id']))) {
            return false;
        }
        return true;
    }

    /**
     * @desc 判断字符串是否是base64格式
     * @param string $str 字符串
     * @return bool
     * @author 柯琼远
     */
    function isBase64($str){
        if($str==base64_encode(base64_decode($str))){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证APP签名方法
     * @param $post_data
     * @param string $sign_key
     * @return string
     */
    function checkSign($post_data, $sign_key){
        if(!is_array($post_data) || empty($post_data) || !isset($post_data['nonce_str']) || empty($sign_key)){
            return false;
        }
        //根据规则进行数组排序
        $first_str = ord($post_data['nonce_str']);
        if ($first_str % 2 ==1)krsort($post_data);
        else ksort($post_data);
        //串联post数据
        $post_string = '';
        foreach($post_data as $key => $value){
            if (isset($value) && $key != 'sign'){
                $post_string .=$key .'=' .$value .'&';
            }
        }
        $post_string .= 'key=' . $sign_key;
        return strtolower(md5($post_string));
    }

    /**
     * 调用推送易复诊接口
     * @param string $unionUserId  用户表union_user_id
     * @param string $orderSn      订单编码
     * @param string $status       paid：支付成功,paying：待支付,finished：已完成,cancel：失效
     * @param int    $isMatch      提交订单=1,否则 =0，默认：0
     * @return string
     */
    function prescriptionMatchOrder($unionUserId, $orderSn, $status, $isMatch = 0){
        $domain = $this->config->wap_url[$this->config->environment];
        $apiUrl = "{$domain}/wap/order/prescription_match_order";
        $result = json_decode($this->curl->sendPost($apiUrl, http_build_query([
            'union_user_id' =>  $unionUserId,
            'order_sn'      =>  $orderSn,
            'is_match'      =>  $isMatch,
            'status'        =>  $status,
        ])), true);
        return $result;
    }

    /**
     * 判断是否自提地址
     * @param array $addressInfo
     *  -  city     城市ID
     *  -  address  地址详情
     * @return string
     */
    function isZitiAddress($addressInfo){
        $sinceShopAddress = BaiyangUserSinceShopData::getInstance()->getData([
            'column'=>'id,province,city,county,address,trade_name',
            'table'=>'\Shop\Models\BaiyangUserSinceShop'
        ]);

        $result = false;
        if($sinceShopAddress){
            foreach ( $sinceShopAddress as $item) {
                if($addressInfo['city'] == $item['city']){
                    if (strpos($addressInfo['address'], $item['address']) !== false) {
                        $result = $item['id'];
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 子订单分配主订单价格
     * @param array $orderList 子订单列表，必须包含promotion_total字段
     * @param array $money     分配的金额
     * @param array $field     分配给子订单的字段名称
     * @param string $fixField 根据哪个字段来分配价格
     * @return string
     */
    function childAllotParent($orderList, $money, $field, $fixField = "costPrice") {
        $total = '0.00';
        foreach ($orderList as $key => $value) {
            $total = bcadd($total, $value[$fixField], 2);
        }
        $temp_total = '0.00';
        foreach ($orderList as $key => $value) {
            if (count($orderList) <= $key + 1) {
                $orderList[$key][$field] = bcsub($money, $temp_total, 2);
            } else {
                $child_total = bcdiv(bcmul($money, $value[$fixField], 4), $total, 2);
                $temp_total = bcadd($temp_total, $child_total, 2);
                $orderList[$key][$field] = $child_total;
            }
        }
        return $orderList;
    }

    /**
     * 发送短信公关方法
     * @param string $phone 手机号
     * @param string $templateCode 模板编号
     * @param string $client_code 客户端编号
     * @param array $setting 客户端环境变量
     * @param array $params 短信模板中的变量替换
     * @return array|bool
     * @author Chensonglu
     */
    function sendSms($phone, $templateCode, $setting = [], $client_code = 'pc', $params = []) {
        $environment = [
            'ip'=> $_SERVER['REMOTE_ADDR'],	        //否	string	如果不填，可能无法应用IP相关过滤规则
            'session_id'=>  session_id(),	        //否	string	如果不填，无法应用Session过滤规则
            'user_agent'=>  '',	                    //否	string	无
            'user_id'	=>  0,                      //否	int	用户的ID
            'remark'	=>  '',                     //否	string	自定义备注
            'captcha'	=>  true,                 //否	bool	如果客户端对用户启用了验证码，则必填
        ];
        $environment = array_merge($environment,$setting);
        return SmsService::getInstance()->send($phone, $templateCode, $client_code, $environment, $params);
    }


    function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 余额充值创建签名
     * @param $post_data
     * @return string
     */
    public function make_rsa_sign($params){

        $private_content = @file_get_contents(APP_PATH .'/common/resource/cert/balance/rsa_private_key.pem');
        $private_key=openssl_get_privatekey($private_content);

        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, 'UTF-8');
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }


        openssl_sign($stringToBeSigned,$sign,$private_key);
        openssl_free_key($private_key);
        $sign=base64_encode($sign);//最终的签名　
        return $sign;
    }


    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {

        if (!empty($data)) {
            $fileType = 'UTF-8';
            if (strcasecmp($fileType, $targetCharset) != 0) {

                $data = mb_convert_encoding($data, $targetCharset);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }


    /**
     * 根据操作方法检查是否有权限
     * @param $controller
     * @param $action
     * @return bool|int
     * @author Chensonglu
     */
    public function checkActionAuth($controller, $action){
        if (!$controller || !$action) {
            return false;
        }
        $privilege = AdminRoleService::getInstance()->getOneCahce($this->session->get('role_id'));
        if (isset($privilege['is_super']) && $privilege['is_super']) {
            return true;
        }
        $resourceAll = BaiyangAdminResourceData::getInstance()->getDefaultAll();
        $resourceId = 0;
        if ($privilege && $resourceAll) {
            foreach ($resourceAll as $val) {
                if (($val['controller'] === $controller) && ($val['action'] === $action)) {
                    $resourceId = $val['id'];
                    break;
                }
            }
            return isset($privilege['rules']) && in_array($resourceId, explode(',', $privilege['rules'])) ? true : false;
        }
        return false;
    }

    /**
     * [Md5Sha1 加密]
     * @param [type] $str [description]
     * @param string $key [description]
     */
    function Md5Sha1($str, $key = '')
    {
        return md5(sha1($str) . $key);
    }

    ///---------------------------------------------------user

    /**
     * 存储redis
     *
     * @return bool
     */
    function set_redis_cache($name,$param,$miao)
    {
//        $redis = $this->cache;
//        $redis->selectDb();
//        $redis->setValue();
//        $redis->getValue();
//        $redis->delete();
        $this->cache->setValue($name,$param,$miao);
    }

    /**
     * 查看redis
     *
     * @return bool
     */
    function show_redis_cache($name)
    {
        print_r($this->cache->getValue($name));
    }

    /**
     * 获取渠道名称
     *
     * @return bool
     */
    function channel_name()
    {
        $user_agent = $this->request->getUserAgent();
        if($this->is_wap()){
            return 'wap';
        }
        if($user_agent){
            $result = preg_match('/\bandroid_[0-9a-zA-Z]+\b/i', $user_agent, $match);
            if($result)
            {
                return $match[0];
            }
            if(stripos($user_agent,'iOS') || stripos($user_agent,'ios')){
                return 'ios';
            }
            return 'wap';
        }
        return '';
    }

    /**
     * 判断当前控制器是否为wap版本
     * @return bool
     */
    function is_wap()
    {

        $directory = substr($_SERVER['PHP_SELF'],0,-1);
        return $directory == 'wap' ? true : false;
    }

    /**
     * 获取当前页面完整URL地址
     */
    function get_url() {
        //$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        //return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
        return $relate_url;
    }

    /**
     * 获取app版本
     * @notice 如果是wap则默认为1.6版本
     */
    function get_app_version() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (!empty($user_agent) && preg_match('/BaiYangStore\/(.*) \(/', $user_agent, $match_result)) {
            $version = str_replace('.', '', trim($match_result[1]));
            $version = substr($version, 0, 1) . '.' . substr($version, 1);
            return $version;
        } else {
            return '1.0';
        }
    }

    /**
     * 检查手机号码格式
     * @param $mobile
     * @return bool
     */
    function check_is_mobile($mobile) {
        if (!preg_match("/^1[34578]\d{9}$/", $mobile)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 密码加密
     * @param string $password
     * @return string
     */
    function password_encode($password=''){
        if(empty($password)){
            return '';
        }
        $return_password=md5($password);
        return $return_password;
    }

    /**
     * 获取宝岛token
     * @return string
     */
    function get_baodao_token(){
        $baodao_token = $this->cache->getValue('baodao_token');
        if(!$baodao_token['expireTime'] || (($baodao_token['expireTime']-time())<60)){
            $baodao_token = $this->curl_baodao_token();
        }
        return $baodao_token['token'];
    }

    /**
     * @desc curl 宝岛token
     * @return array
     * @author 朱耀昆
     */
    public function curl_baodao_token() {
        $param = array(
            'appId'=>'2280853032323ds4f45f24bgx004e1012df858800',
            'appSecret'=>'3fy8un548fnsk843n39e32nu83kfyaaiy383jj48ejri',
            'sign'=>md5('2280853032323ds4f45f24bgx004e1012df8588003fy8un548fnsk843n39e32nu83kfyaaiy383jj48ejri'),
        );

        $url = 'http://omsj.joojtech.com/Api2/V1/Auth/index';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'BaiYangStore/3.0.1 (iPhone; iOS 9.3; Scale/2.00)');
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        #print_r($output);die;

        $token = array(
            'token'=>json_decode($output)->data->token,
            'expireTime'=>json_decode($output)->data->expireTime
        );
        $this->cache->setValue('baodao_token',$token,7200);
        return $token;
    }

    /**
     * @desc curl post
     * @param array $param
     * @return array
     * @author 朱耀昆
     */
    public function curl_post($param,$url,$code=0) {

        #更新获取宝岛token
        $url .= '?token='.$this->get_baodao_token();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'BaiYangStore/3.0.1 (iPhone; iOS 9.3; Scale/2.00)');
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($param)))
        );

        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        if($code==3){
            print_r($url);
            print_r(json_encode($param));
            print_r($output);die;
        }

        return json_decode($output);
    }

    public function curl_do($param,$url,$do=0) {

        if(!empty($param)){
            $url .= '?';
            foreach($param as $k=>$val){
                $url .= "$k=".urlencode($val)."&";
            }
            $url = trim($url,'&');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'BaiYangStore/3.0.1 (iPhone; iOS 9.3; Scale/2.00)');
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $output = curl_exec($ch);
        curl_close($ch);

        //打印获得的数据
        if($do==3){
            print_r($url);
            echo "<br />";
            print_r($param);
            echo "<br />";
            print_r($output);die;
        }

        return json_decode($output);
    }

    /**
     * 获取商品有效的仓库ID [按照排序优先级获取正在使用中并且满足数量要求的仓库]
     * @param string $goods_id  商品ID
     * @param string $num  默认为1
     * @return bool|string
     */
    public function getUsingGoodsWhId($goods_id='',$num='')
    {
        if (empty($goods_id)) return false;
        $stockType = BaiyangGoods::getInstance()->getGoodsStockType($goods_id);
        $compareStock = $num ? $num : 1;
        $whIds = BaiyangMerchantGoodsWarehouseRelationData::getInstance()->getUsingGoodsWhIds($goods_id);
        $res = '';
        #如果是真实库存类型则寻找符合数量要求的仓库
        if($stockType['is_use_stock'] == 1)
        {
            #按排序筛选出有效的仓库ID
            foreach((array)$whIds as $v)
            {
                $stock = BaiyangGoodsStockBondedData::getInstance()->getGoodsWhFinalStock($goods_id, $v['warehouse_id']);
                $changeStock = BaiyangGoodsStockChangeLogData::getInstance()->getGoodsNotSynRealStock($goods_id,$v['warehouse_id']);
                $totalStock = $stock['final_stock'] + $changeStock[0]['change_sum'];
                if ($totalStock >= $compareStock) {
                    $res = $v['warehouse_id'];
                    break;
                }
            }
        }
        #虚拟库存和没有符合数量要求的真实库存类型情况下,选取排序靠前的仓库
        if(empty($res))
        {
            $res = $whIds[0]['warehouse_id'];
        }
        return $res;
    }
}