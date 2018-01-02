<?php
/**
 * @author: 文和
 * @copyright: 2017/5/22 10:23
 * @link chenxudaren.com
 * @internal
 * @license
 */

namespace Shop\Home\Listens;

use Phalcon\Http\Client\Exception;
use Phalcon\Mvc\User\Component;
//use Shop\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangBrandsData;
use Shop\Home\Datas\BaiyangGroupFightBData;
use Shop\Home\Datas\BaiyangGroupFightOrderData;
use Shop\Home\Datas\BaiyangUserConsigneeData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Models\BaiyangBrand;
use Shop\Models\CacheKey;
use Shop\Models\HttpStatus;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaseData;

class BaseGroupfight extends Component
{
    protected $data = [];
    protected $requiredParam = []; //必须的请求参数
    protected $key = 'bb03ac0b7d03a95a4a165bb7fd7fda86';
    protected static $instance = null;

    public function __construct()
    {
        bcscale(2);
    }

    /**
     * 单例
     * @return static
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
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
    public function verifyRequiredParam(array $param)
    {
        if (empty($this->requiredParam) or empty($param)) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }

        foreach ($this->requiredParam as $k => $v) {

            //检测必填参数
            if ($v['require'] and isset($param[$k]) === false) {
                throw new Exception('', HttpStatus::PARAM_ERROR);
            }

            //检测参数的值在不在规定的数组中
            if ( isset($param[$k]) and isset($v['value'])) {
                if (is_array($v['value']) and in_array($param[$k], $v['value']) === false) {
                    throw new Exception('', HttpStatus::PARAM_ERROR);
                }
            }

            //过滤参数
            if(isset($param[$k]) and isset($v['filter'])){
                switch ($v['filter']){
                    case 'number':
                        if(isset($param[$k])){
                            if(is_numeric($param[$k]) === false){
                                throw new Exception('', HttpStatus::PARAM_ERROR);
                            }
                        }else{
                            $param[$k] = 0; //没设置  给设置默认值 0 ; 这里的 0 后边的看看 0 是不是都代表空
                        }
                        break;
                    default:
                        if(isset($param[$k]) === false){
                            $param[$k] = '';
                        }
                }
            }else{
                if(isset($param[$k]) === false){
                    $param[$k] = '';
                }
            }
        }

        // 公共参数存进配置里
        $this->config->platform = $param['platform'];
        $this->config->channel_subid = $param['channel_subid'];
        return $param;
    }

    protected function getSn()
    {
        return md5($this->data['param']['group_id'] . $this->data['param']['user_id'] . $this->data['param']['is_open'] . $this->key);
    }

    protected function chkSn()
    {
        if (empty($this->data['param']['sn'])) {
            return false;
        }
        if ($this->data['param']['sn'] != $this->getSn()) {
            return false;
        }
        return true;
    }

    /**获取拼团数据
     * @param $groupId
     * @throws Exception
     */
    protected function getGroupData($groupId)
    {
        if (intval($groupId) === 0) {
            throw  new Exception('', HttpStatus::GROUP_ID_ERROR);
        }
        $this->data['group'] = [];

        if ($this->data['param']['is_open']) { //开团
            $gfaId = $groupId;
        } else { //参团
            $this->data['group'] = BaiyangGroupFightBData::getInstance()->getGroupFightOne($groupId);
            $gfaId = $this->data['group']['gfa_id'];
        }

        //获取拼团活动数据
        $fightAct = BaiyangGroupFightBData::getInstance()->getGroupFightActOne($gfaId);

        $this->data['group'] = array_merge($this->data['group'], $fightAct);
        if($this->data['group']){
            isset($this->data['group']['goods_image']) === false and $this->data['group']['goods_image'] = '';
            $this->data['group']['first_image'] = '';

            if (
                isset($this->data['group']['goods_slide_images'])
                and $this->data['group']['goods_slide_images']
            ) {
                $row = @json_decode($this->data['group']['goods_slide_images'], true);
                if ($row and is_array($row) and empty($row[0]) === false) {
                    $this->data['group']['goods_image'] = $row[0];
                    $this->data['group']['first_image'] = $row[0];
                }
            }
        }

    } //end getGroupData


    protected function chkGroup()
    {

        $this->chkGroupPublic();

        if ($this->data['param']['is_open']) {

            $this->chkGroupOpen();

        } else {  //如果是参团的话

            $this->chkGroupJoin();
        }
        $this->chkGroupAllowNum();

    }

    private function chkGroupPublic()
    {

        //活动不存在
        if (empty($this->data['group'])) {
            throw new Exception('', HttpStatus::GROUP_NOT_HAS);
        }

        //活动 未开始
        if ($this->data['group']['gfa_starttime'] > time()) {
            throw new Exception('', HttpStatus::GROUP_ACT_NOT_START);
        }

        if (empty($this->data['group']['goods_id']) or is_numeric($this->data['group']['goods_id']) === false) {
            throw new Exception('', HttpStatus::GROUP_PARAM_ERROR);
        }

    }

    /**开团活动判断
     * @throws Exception
     */
    private function chkGroupOpen()
    {
        //活动 已取消 , 在开团的时候判断活动是否  已经取消 , 参团的时候不判断
        if ($this->data['group']['gfa_state'] == 3) {
            throw new Exception('', HttpStatus::GROUP_ACT_IS_CANCEL);
        }

        //活动 已结束 , 在开团 的时候判断 活动是否已经结束  , 参团的时候不判断
        if ($this->data['group']['gfa_endtime'] < time()) {
            throw new Exception('', HttpStatus::GROUP_ACT_IS_OVER);
        }
    }

    /**拼团活动允许参加次数判断
     * @throws Exception
     */
    private function chkGroupAllowNum()
    {

        if ($this->data['group']['gfa_allow_num'] > 0) {
            //判断 整个活动参加得次数( 不包括 未付款的 )
            $condition = [
                'user_id' => $this->data['param']['user_id'],
                'gfu_state' => [1, 2], //1 拼团中 , 2 已成团 , 未付款的不算次数 , 未付款的在支付的时候再次判断参加次数
            ];
            $condition['gfa_id'] = $this->data['group']['gfa_id'];

            //判断参加活动次数 是否超过允许的次数
            $join_number = BaiyangGroupFightBData::getInstance()->getGroupFightBuyCount($condition);

            if ($join_number >= $this->data['group']['gfa_allow_num']) {
                //你已经参加过该活动 , 拼团中, 拼团成功
                $text_param = [$this->data['group']['gfa_allow_num']];
                throw new Exception(json_encode($text_param), HttpStatus::GROUP_JOIN_MAX_ERROR);
            }
            unset($condition);

            $payingOrder = BaiyangGroupFightBData::getInstance()->getFightBuyExpCancelCount($this->data['param']['user_id'], $this->data['group']['gfa_id']);
//echo $this->data['group']['gfa_allow_num'], $payingOrder;
            if ($payingOrder + $join_number >= $this->data['group']['gfa_allow_num']) {
                //你已经参加过该活动(未付款 , 未取消)
                $text_param = [$this->data['group']['gfa_allow_num'], $payingOrder];
                throw new Exception(json_encode($text_param), HttpStatus::GROUP_JOIN_MAX_PAYING_ERROR);
            }
        }
    }

    /**参团活动判断
     * @throws Exception
     */
    private function chkGroupJoin()
    {
        //判断允许参加的用户类型 : 0 不限制 , 1新用户可参团
        if ($this->data['group']['gfa_user_type']) {
            //只有新用户可参团 , 老带新
            //如只允许新用户参团  且  该用户是老用户 则  跳出 . 提示: 老用户不可以参加
            if ( BaiyangGroupFightBData::getInstance()->isOldUser($this->data['param']['user_id'])) {
                throw new Exception('', HttpStatus::GROUP_IS_OLDUSER);
            }
        }

        //如 参团 , 一个已开的某活动团 , 一个用户只允许参加一次
        $condition = [
            'user_id' => $this->data['param']['user_id'],
            'gfu_state' => [0, 1, 2], //1 拼团中 , 2 已成团 ,
        ];
        $condition['gf_id'] = $this->data['group']['gf_id'];
        //判断参加活动次数 是否超过允许的次数
        if (BaiyangGroupFightBData::getInstance()->getGroupFightBuyCount($condition) >= 1) {
            //你已经参加过该活动 , 未开/参团 , 拼团中, 拼团成功
            throw new Exception('', HttpStatus::GROUP_JOINED);
        }
        unset($condition);


        //if ($this->data['group']['gf_state'] != 1) {
          //  throw new Exception('', HttpStatus::GROUP_IS_OVER);//该拼团已结束
        //}

        if ($this->data['group']['gf_state'] == 0) {
            throw new Exception('', HttpStatus::GROUP_NOT_OPEN);//该拼团 未开团
        }

        //已成团
        if ($this->data['group']['gf_state'] == 2) {
            throw new Exception('', HttpStatus::GROUP_ACT_IS_SUCCESS);
        }

        //已经 拼团失败
        if ($this->data['group']['gf_state'] >= 3) {
            throw new Exception('', HttpStatus::GROUP_IS_FAIL);
        }

        /*该团  已结束  不能参团
         * 1. 拼团到了结束时间  未成团   上边已经改了
         * 2. 拼团到了结束时间 已成团
         * gf_end_time < 当前时间 ,  gf_join_num < gfa_user_count ,  gf_state = 1 的 是拼团结束 失败, 在前边已经修改状态
         * 这里再成立的话  那就是 拼团结束 gf_state = 1  且 拼团成功 , 这里就出现异常, 是在 支付完成拼团成功的时候出错了 ， 但是这里不能叫他再参团了
         */
        if ($this->data['group']['gf_end_time'] < time()) {
            throw new Exception('', HttpStatus::GROUP_IS_OVER);
        }

        /* 参团人数已达到限制 , 已成团
         * 这里如果成立 则是 拼团状态 gf_state = 1“拼团中” ， 参团人数 == 成团人数， 拼团状态没改 （拼团状态没改 , 订单状态可能也没改 ,等一系列的操作都出错），
         * 这里也是  出现异常 ， 支付完成拼团成功的时候出错了 ，但是这里不能叫他再参团了
         */
        if (
            $this->data['group']['gf_state'] == 1
            and $this->data['group']['gf_join_num'] >= $this->data['group']['gfa_user_count']
        ) {
            throw new Exception('', HttpStatus::GROUP_ACT_IS_SUCCESS);
        }
    }

    /**获取开团结束时间
     * @return int
     */
    protected function gfEndTime($time = null)
    {
        $endtime = (is_null($time) ? time() : $time) + $this->data['group']['gfa_cycle'] * 60 * 60;
        $endtime > $this->data['group']['gfa_endtime'] and $endtime = $this->data['group']['gfa_endtime'];
        return $endtime;
    }


    protected function getPorduct($column = null)
    {
        $product =  BaiyangSkuData::getInstance()->getSkuInfo($this->data['group']['goods_id'], $this->data['param']['platform']);

        if($product){
            if(!$product['sale']){
                throw new Exception('', HttpStatus::GROUP_NOT_GOODS);
            }
            $product['brand_name'] = '';
            if(isset($product['brand_id'])){
                if($brand = BaiyangBrandsData::getInstance()->getBrandRow($product['brand_id'])){
                    $product['brand_name'] = $brand['brand_name'];
                }
            }

            if(empty($column) === false  and is_array($column)){
                $keys = array_fill_keys($column, '');
                $product =  array_replace($keys, array_intersect_key($product, $keys));
                unset($keys);
            }
        }
        return $product;
    }

    protected function chkStock()
    {

        $stock = $this->func->getCanSaleStock([
            'goods_id' => $this->data['group']['goods_id'],
            'platform' => $this->data['param']['platform']
        ]);

        if ($stock < $this->data['param']['goods_num'] or $stock < 1) {
            throw new Exception(@json_encode([$this->data['product']['goods_name']]), HttpStatus::GROUP_NOSTOCK_GOODS);
        }
    }

    /**
     * @param array $param
     *               userId      必填
     *               address_id   地址id 选填
     *
     * @return \array[]
     */
    protected function getConsigneeInfo(array $param)
    {
        $condition = [
            'column' => 'id, consignee, consignee_id, address, province, city, county, telphone, zipcode,identity_confirmed,default_addr,tag_id',
            'where' => ' user_id = :user_id:',
            'bind' => ['user_id' => $param['user_id']]
        ];
        if (isset($param['address_id'])) {
            $condition['where'] .= ' and id = :id:';
            $condition['bind']['id'] = $param['address_id'];
        } else {
            $condition['where'] .= ' order by default_addr desc,id desc';
        }

        if($row =  BaiyangUserConsigneeData::getInstance()->getConsigneeInfo($condition, true)){
            $row['expressType'] = 0; //配送方式:0-普通快递,1-顾客自提,
        }
        return $row;
    }

    /**
     * 利用redis生成订单号
     * @author
     * @return bool|string
     * $machine_type  1 触屏机
     */
    protected function makeOrderSn($machine_type = null) {
        // 前缀
        $prefix =  "" ;
        $machineId = $machine_type == 1 ? 1 : ''; //触屏机的话订单号 在渠道号后边多个1
        //生成订单号
        $order_sn = $prefix . $this->config->channel_subid . $machineId . date('YmdHis') . substr(microtime(), 2, 5);

        $cacheKey = CacheKey::ORDER_SN . $order_sn;
        $redis = $this->cache;
        $redis->selectDb(2);
        $ret = $redis->getValue($cacheKey);
        if ($ret) {
            return $this->makeOrderSn($machine_type);
        }
        // 设置5秒缓存有效期
        $redis->setValue($cacheKey, 1, 5);
        return $order_sn;
    }


    protected function getUserInfo($userId, $column = null){

        $user = BaiyangUserData::getInstance()->getUserInfo($userId);

        if($user){
            if(empty($column) === false  and is_array($column)){
                $keys = array_fill_keys($column, '');
                $user =  array_replace($keys, array_intersect_key($user, $keys));
                unset($keys);
            }
        }

        return $user;
    }

    /**
     * @desc 服务端返回json信息
     * @param int $status      状态码，在Shop\Models\HttpStatus.php中定义
     * @param string $explain  提示说明
     * @param array $data      成功后返回数据
     * @param array $tipsData  提示语里面的变量
     * @return json array []
     * @author  秦亮
     * @date    2017-05-19
     */
    public function responseResultJson($status, $explain = '', $data = [], $tipsData = [])
    {
        header('Content-Type:application/json; charset=utf-8');
        $data = empty($data) ? null : $data;
        if (empty($tipsData)) {
            echo json_encode(['status' => $status, 'explain' => $explain, 'data' => $data]);
            exit;
        }
        $this->regularReplace($explain, $tipsData);
        echo json_encode(['status' => $status, 'explain' => $explain, 'data' => $data, 'tipsData' => $tipsData]);
        exit;
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
}