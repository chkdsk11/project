<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangCouponCodeData;
use Shop\Home\Datas\BaiyangCouponData;
use Shop\Home\Datas\BaiyangCouponRecordData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Listens\PromotionCoupon;
use Phalcon\Events\Manager as EventsManager;
use Shop\Models\BaiyangCouponEnum;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\HttpStatus;
use Shop\Home\Listens\PromotionShopping;
use Shop\Home\Listens\PromotionGetGoodsDiscountPrice;
use Shop\Home\Listens\PromotionCalculate;
use Shop\Home\Listens\PromotionLimitBuy;

/**
 * Class CouponService
 * @package Shop\Home\Services
 * @todo 写注释
 */
class CouponService extends BaseService
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new CouponService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('couponList', new PromotionCoupon());
        $eventsManager->attach('promotion', new PromotionShopping());
        $eventsManager->attach('promotion', new PromotionGetGoodsDiscountPrice());
        $eventsManager->attach('promotion', new PromotionLimitBuy());
        $eventsManager->attach('promotion', new PromotionCalculate());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * @desc 优惠券 in 商品详情
     * @param $param
     * @return mixed
     */
    public function getCouponList($param)
    {
        $require = $this->judgeRequireParam($param, 'goods_id,platform');
        if (!empty($require)) return $require;
        $list = $this->_eventsManager->fire('couponList:getCouponList', $this, $param);
        if (isset($param['format']) && !empty($param['format']) && $param['format'] == 1) {
            return $this->uniteReturnResult($list['code'], $list['data']);
        }
        return $list;
    }

    public function getCouponState($param)
    {
        $require = $this->judgeRequireParam($param, 'goods_id,platform');
        if (!empty($require)) return $require;

    }

    /**
     * @DESC 优惠券 领取
     * @param $param
     *         - goods_id 商品id
     *         - coupon_sn 优惠券码
     *         - platform 平台标识 pc app wap
     *         - channel_subid : int 渠道号
     *         - udid : string 手机唯一id(app only)
     *         - user_id 用户id
     * @return mixed
     */
    public function addCoupon($param)
    {
        $require = $this->judgeRequireParam($param, 'platform,user_id,coupon_sn');
        if (!empty($require)) return $require;
        $BaiyangCouponRecordData = BaiyangCouponRecordData::getInstance();
        $detail = $BaiyangCouponRecordData->pureAddCouponRecord($param);
        return $this->uniteReturnResult($detail['code'], $detail);
    }


    /**
     * @desc 激活码兑换优惠券
     * @param
     *      - code 激活码
     *      - user_id 用户id
     * @author 邓永军
     * @return array 兑换结果
     *              - code
     *              - result
     */
    public function exchangeCouponCode($param)
    {
        $require = $this->judgeRequireParam($param, 'code,user_id');
        if (!empty($require)) return $require;
        $BaiyangCoponCodeData = BaiyangCouponCodeData::getInstance();
        $detail = $BaiyangCoponCodeData->exchangeCode($param['code'], $param['user_id']);
        $code = $detail['code'];
        if(!empty($detail['data'])){
            return $this->uniteReturnResult($code, $detail['data']);
        }else{
            return $this->responseResult($code, $detail['result']);
        }
    }

    /**
     * @desc 个人中心优惠券
     * @param $detail
     * @param $coupon_status
     * @return mixed
     */
    private function UserCouponListHandler($detail,$coupon_status)
    {
        $nowTime = time();
        foreach ($detail as $key => $data)
        {
            if(isset($data) && !empty($data))
            {
                $data['validitytype'] == 2 ? $expiration = $data['record_add_time'] + $data['relative_validity'] * 86400 : $expiration = $data['end_use_time'];
                if($data['validitytype'] == 2){
                    if($data['record_add_time'] - $nowTime > 0){
                        $detail[$key]['distance_start_time'] = $data['record_add_time'] - $nowTime;
                    }else{
                        $detail[$key]['distance_start_time'] = 0;
                    }
                }else{
                    if($data['start_use_time'] - $nowTime > 0){
                        $detail[$key]['distance_start_time'] = $data['start_use_time'] - $nowTime;
                    }else{
                        $detail[$key]['distance_start_time'] = 0;
                    }
                }
                $detail[$key]['remain_time'] = $expiration - $nowTime;
                $detail[$key]['server_time'] = $nowTime;
                $data['coupon_number'] > $data['bring_number'] ? $detail[$key]['is_over'] = 0 : $detail[$key]['is_over'] = 1;
                if($detail[$key]['is_used'] == 0) $coupon_status = 0;
                if($detail[$key]['is_used'] == 1) $coupon_status = 2;
                if($detail[$key]['is_used'] == 2) $coupon_status = 3;
                if($detail[$key]['is_used'] == 3) $coupon_status = 4;
                if($detail[$key]['remain_time'] < 0){
                    $coupon_status = 1;
                }
                /*if($detail[$key]['distance_start_time'] > 0){
                    $coupon_status = 0;
                }*/
                if($detail[$key]['distance_start_time'] > 0 && $detail[$key]['is_used'] == 2){
                    $coupon_status = 3;
                }
                if($detail[$key]['distance_start_time'] > 0 && $detail[$key]['is_used'] == 3){
                    $coupon_status = 4;
                }
                $detail[$key]['coupon_status'] = $coupon_status;
                $detail[$key]['id'] = $this->mcrypt_encrypt_sign($detail[$key]['id']);
                if($detail[$key]['coupon_type'] == 1)$detail[$key]['coupon_value'] = (int) $detail[$key]['coupon_value'];
                unset($detail[$key]['is_used']);
            }
        }
        return $detail;
    }

    private function mcrypt_encrypt_sign($str)
    {
        $key = 'a@#SDF@asd&*k79safhK232HI90)_+asd*)dff23(^Rw';
        $crypttext = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $str, MCRYPT_MODE_CBC, md5(md5($key))));
        $crypttext = base64_encode($crypttext);
        return trim(str_replace(array('+','/','='),array('-','_',''),$crypttext));
    }

    private function ShowDuplicateCoupon($coupon)
    {
        if(!empty($coupon)){
            $record_data = BaiyangCouponRecordData::getInstance();
            $couponList = [];
            foreach ($coupon as $data){
                $got_num = $record_data->countCouponHasBring($data['user_id'],$data['coupon_sn']);
                $data['got_num'] = $got_num;

                if($data['coupon_type'] == 1)$data['coupon_value'] = (int) $data['coupon_value'];
                $couponList[] = $data;
            }
            $couponList = $this->arraySortByKey($couponList,'record_add_time','desc');
            return $couponList;
        }else{
            return $coupon;
        }
    }
    /**
     * @desc 用户个人中心优惠券列表
     * @param $param
     *          - platform 平台标识
     *          - user_id 用户id
     *         - channel_subid : int 渠道号
     *         - udid : string 手机唯一id(app only)
     *         - isShowCount : 是否展示可用优惠券数量
     * @author 邓永军
     * @date 2017-2-17
     * @return array
     */
    public function UserCenterCouponList($param)
    {
        $result = [];
        $BaseData = BaseData::getInstance();
        $platform = $param['platform'];
        $data[$platform . "_platform"] = 1;
        $nowTime = time();
        if(isset($param['isShowCount']) && !empty($param['isShowCount'])){
            $isShowCount = 1;
        }else{
            $isShowCount = 0;
        }
        //未使用
        $noUsed = $this->UserCouponListHandler(array_merge($BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used = :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND record.start_use_time < :start_use_time: AND record.end_use_time > :end_use_time:',
            'bind' => [
                'is_used' => 0,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 1,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ]),$BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used = :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND record.add_time < :start_use_time: AND (record.add_time + record.relative_validity * 86400) > :end_use_time:',
            'bind' => [
                'is_used' => 0,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 2,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ])),0);
        $result['coupon']['no_used']['data'] = $this->ShowDuplicateCoupon($noUsed);
        $result['coupon']['no_used']['count'] = count($this->ShowDuplicateCoupon($noUsed));
        //已领取还没到使用时间
        $noGotTime = $this->UserCouponListHandler(array_merge($BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used = :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND record.start_use_time > :start_use_time: AND coupon.start_provide_time < :end_use_time:',
            'bind' => [
                'is_used' => 0,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 1,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ]),$BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used = :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND record.add_time > :start_use_time: AND coupon.start_provide_time < :end_use_time:',
            'bind' => [
                'is_used' => 0,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 2,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ])),0);
        $result['coupon']['no_arrive_time']['data'] = $this->ShowDuplicateCoupon($noGotTime);
        $result['coupon']['no_arrive_time']['count'] = count($this->ShowDuplicateCoupon($noGotTime));
        //已使用
        $hasUsed = $this->UserCouponListHandler(array_merge($BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used >= :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND record.start_use_time < :start_use_time: AND record.end_use_time > :end_use_time:',
            'bind' => [
                'is_used' => 1,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 1,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ]),$BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used >= :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND record.add_time < :start_use_time: AND (record.add_time + record.relative_validity * 86400) > :end_use_time:',
            'bind' => [
                'is_used' => 1,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 2,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ])),2);
        $result['coupon']['has_used']['data'] = $this->ShowDuplicateCoupon($hasUsed);
        $result['coupon']['has_used']['count'] = count($this->ShowDuplicateCoupon($hasUsed));
        //已过期
        $hasOverTime = $this->UserCouponListHandler(array_merge($BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used != :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND ( record.start_use_time >= :start_use_time: OR record.end_use_time <= :end_use_time: )',
            'bind' => [
                'is_used' => 1,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 1,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ]),$BaseData->getData([
            'column' => 'record.id,record.user_id,record.start_use_time,record.end_use_time,record.add_time as record_add_time,record.validitytype,record.relative_validity,coupon.coupon_sn,coupon.coupon_name,coupon.coupon_value,coupon.coupon_number,coupon.limit_number,coupon.min_cost,coupon.coupon_type,coupon.bring_number,coupon.use_range,coupon.coupon_description,coupon.start_provide_time,coupon.end_provide_time,coupon.'.$platform.'_url as url,record.present_time,record.present_source,coupon.is_present,record.is_used',
            'table' => '\Shop\Models\BaiyangCouponRecord as record',
            'where' => 'where record.is_used != :is_used: AND record.user_id = :user_id: AND coupon.'.$platform.'_platform = :platform: AND coupon.is_cancel = :is_cancel: AND record.validitytype = :validitytype: AND ( record.add_time >= :start_use_time: OR (record.add_time + record.relative_validity * 86400) <= :end_use_time: )',
            'bind' => [
                'is_used' => 1,
                'user_id' => $param['user_id'],
                'platform' => $data[$platform . "_platform"],
                'is_cancel' => 0,
                'validitytype' => 2,
                'start_use_time' => $nowTime,
                'end_use_time' => $nowTime
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as coupon ON coupon.coupon_sn = record.coupon_sn'
        ])),1);
        
        $hasOverTimeData = $this->ShowDuplicateCoupon($hasOverTime);
       //print_r($hasOverTimeData);die;

        $noGotTimeData = $this->ShowDuplicateCoupon($noGotTime);
        foreach ($noGotTimeData as $notime_item)
        {
            foreach ($hasOverTimeData as $key => $overtime_item){
                if($notime_item['coupon_sn'] == $overtime_item['coupon_sn']) unset($hasOverTimeData[$key]);
            }
        }

        //print_r($hasOverTimeData);die;
        $result['coupon']['has_overtime']['data'] = array_values($hasOverTimeData);
        $result['coupon']['has_overtime']['count'] = count($hasOverTimeData);
        //不可用
        //print_r($hasOverTime);die;
        $hasUnableUsed = array_merge($hasUsed,$hasOverTimeData);
        
        $hasUnableUsed = $this->arraySortByKey($hasUnableUsed,'record_add_time','desc');

        $result['coupon']['unabled_coupon']['data'] = $hasUnableUsed;
        $result['coupon']['unabled_coupon']['count'] = count($hasUnableUsed);
        if($isShowCount == 1){
            $countArray['coupon']['count'] = $result['coupon']['no_used']['count'] + $result['coupon']['no_arrive_time']['count'];
            return $this->uniteReturnResult(HttpStatus::SUCCESS, $countArray['coupon']);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result['coupon']);
    }

    /**
     * @desc 优惠券融合个人中心列表
     * @param $list
     * @param $user_id
     * @param $node
     * @param $is_normal
     *          当未开始状态时候设置为0 默认为 1
     * @param $is_overtime
     *          当未开始状态时候设置为0 默认为 1
     * @return array
     */
    public function list_merge($list, $user_id, $node, $is_normal = 1, $is_overtime = 1)
    {
        if (!empty($list)) {
            $result = [];
            foreach ($list as $k => $item) {
                $idsOnUseRange = $this->getIdsFromUseRange($item['use_range']);
                if (isset($list[$k]) && !empty($list[$k])) {
                    $list[$k]["is_got"] = $this->isHasBrought($list, $k, $user_id, $item["coupon_sn"]);
                    if ($list[$k]["is_got"] < 1 && $is_normal == 1 && $is_overtime == 1) {
                        unset($list[$k]);
                    }
                }

                //判断领取db里是否存在本优惠券本人可领取数量
                if (isset($list[$k]) && !empty($list[$k])) {
                    $list[$k]["is_over_bring_limit"] = $this->isOverBringLimit($user_id, $item["coupon_sn"], $item['limit_number']);
                }

                if (isset($list[$k]) && !empty($list[$k])) {
                    $type = 1;
                    $got_num = $list[$k]['is_got'];
                    $is_over_bring_limit = $list[$k]["is_over_bring_limit"];
                }

                if (isset($list[$k]) && !empty($list[$k])) {
                    $result['coupon'][$node][] = [
                        'id' => $item['id'],
                        'coupon_sn' => $item['coupon_sn'],
                        'coupon_name' => $item['coupon_name'],
                        'start_provide_time' => $item['start_provide_time'],
                        'end_provide_time' => $item['end_provide_time'],
                        'coupon_value' => $item['coupon_value'],
                        'coupon_type' => $item['coupon_type'],
                        'min_cost' => $item['min_cost'],
                        'discount_unit' => $item['discount_unit'],
                        'coupon_number' => $item['coupon_number'],
                        'got_num' => $got_num,
                        'is_over_bring_limit' => $is_over_bring_limit,//是否超过用户领取限制
                        'type' => $type, //已登陆权限
                        'use_range' => $item['use_range'],
                        $item['use_range'] => $idsOnUseRange != '' ? $item[$idsOnUseRange] : '',
                        'validitytype' => $item['validitytype'],
                        'relative_validity' => $item['relative_validity'],
                        'start_use_time' => $item['start_use_time'],
                        'end_use_time' => $item['end_use_time']
                    ];
                }
            }

            return $result;

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
        switch ($type) {
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
     * @desc 已经领取数量
     * @param $list
     * @param $k
     * @param $user_id
     * @param $coupon_sn
     * @return int|mixed
     * @author 邓永军
     */
    private function isHasBrought($list, $k, $user_id, $coupon_sn)
    {
        if (isset($list[$k]) && !empty($list[$k])) {
            $count = $this->countCouponHasBring($user_id, $coupon_sn);
            if ($count > 0) {
                return $count;
            } else {
                return 0;
            }
        }
    }

    /**
     * @desc 是否超过限制
     * @param $user_id
     * @param $coupon_sn
     * @param $limit_number
     * @return int
     * @author 邓永军
     */
    private function isOverBringLimit($user_id, $coupon_sn, $limit_number)
    {
        if ($this->countCouponHasBring($user_id, $coupon_sn) >= $limit_number) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @desc 通过用户id和优惠券码获取已经领取数量
     * @param $user_id
     * @param $coupon_sn
     * @return mixed
     * @author 邓永军
     */
    public function countCouponHasBring($user_id, $coupon_sn)
    {
        $count = BaiyangCouponRecordData::getInstance()->countCouponHasBring($user_id, $coupon_sn);
        return $count;
    }

    /**
     * @desc 用户个人中心优惠券数据方法
     * @param $param
     *          - platform 平台标识
     *          - user_id 用户id
     *          - mode 1 进行中 mode 2 未开始 mode 3 已过期
     *         - channel_subid : int 渠道号
     *         - udid : string 手机唯一id(app only)
     * @author 邓永军
     * @date 2016-11-29
     */
    public function UserCenterCouponList_Data($param)
    {
        $data = [];
        $where = '';
        if (!isset($param['mode']) || empty($param['mode'])) {
            $where .= ' start_provide_time < :start_provide_time: AND end_provide_time > :end_provide_time: ';
        } else {
            if ($param['mode'] == '1') {
                $where .= ' start_provide_time < :start_provide_time: AND end_provide_time > :end_provide_time: ';
            } elseif ($param['mode'] == '2') {
                $where .= ' start_provide_time > :start_provide_time: AND end_provide_time > :end_provide_time: ';
            } else {
                $where .= ' end_provide_time < :end_provide_time:  AND  start_provide_time < :start_provide_time: ';
            }
        }
        $data['start_provide_time'] = time();
        $data['end_provide_time'] = time();
        $data[$param['platform'] . "_platform"] = 1;
        $where .= 'AND ' . $param['platform'] . '_platform = :' . $param['platform'] . '_platform: ';
        $data['is_cancel'] = 1;
        $where .= ' AND is_cancel <> :is_cancel: ';
        $where .= 'AND bring_number <= coupon_number ';
        $result = BaseData::getInstance()->getData([
            'column' => 'id,coupon_sn,coupon_name,coupon_value,coupon_number,limit_number,min_cost,start_use_time,end_use_time,start_provide_time,end_provide_time,start_use_time,end_use_time,validitytype,relative_validity,coupon_type,discount_unit,ban_join_rule,bring_number,use_range,brand_ids,category_ids,product_ids,goods_tag_id,pc_url,app_url,wap_url',
            'table' => '\Shop\Models\BaiyangCoupon',
            'where' => 'where ' . $where,
            'bind' => $data
        ]);
        return $result;
    }

    /**
     * @desc 用户切换优惠券
     * @param array $param
     *       -int user_id 用户id
     *       -string platform 平台【pc、app、wap】
     *       -coupon_sn string 用户选中的优惠券编码
     * @return array $result 商品限购信息
     * @author 吴俊华
     */
    public function changeCoupon($param)
    {
        $param['action'] = 'coupon';
        $ret = $this->commonOrderPromotionInfo($param);
        if ($ret['status'] != HttpStatus::SUCCESS) {
            return $ret;
        }
        // 返回数据格式
        $result = [
            'couponList' => $ret['data']['couponList'],
            'giftList' => $ret['data']['giftList'],
            'couponPrice' => 0,
            'isExpressFree' => false
        ];
        // 优惠券
        foreach ($result['couponList'] as $value) {
            if ($value['selected'] == 1) {
                $result['couponPrice'] = $value['coupon_price'];
                $result['isExpressFree'] = $value['coupon_type'] == 3 ? true : false;
                break;
            }
        }
        // 包邮活动
        if (!$result['isExpressFree']) {
            foreach ($ret['data']['availPromotionList'] as $value) {
                if ($value['promotion_type'] == 20) {
                    $result['isExpressFree'] = true;
                    break;
                }
            }
        }
        // 全是虚拟商品包邮
        if (!$result['isExpressFree']) {
            $temp = true;
            foreach ($ret['data']['goodsList'] as $key => $value) {
                if ($value['group_id'] == 0) {
                    if ($value['drug_type'] != 5) {
                        $temp = false;
                        break;
                    }
                } else {
                    foreach ($value['goodsList'] as $k => $v) {
                        if ($v['drug_type'] != 5) {
                            $temp = false;
                            break;
                        }
                    }
                    if (!$temp) break;
                }
            }
            $result['isExpressFree'] = $temp;
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 判断品牌是否存在优惠券
     * @param $brand_id
     * @return int
     * @author 邓永军
     */
    public function IsExistCouponInBrand($brand_id,$platform)
    {
        $count = BaiyangCouponData::getInstance()->IsExistCouponInBrandData($brand_id,$platform);
        if ($count > 0) {
            return 1;
        }
        return 0;
    }

    public function getBrandCoupon($param){
        $result = BaiyangSkuData::getInstance()->getSkuByBrand($param);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /*
     * @desc 注册时优惠券列表
     *         - platform 平台标识 pc app wap
     *         - channel_subid : int 渠道号
     *         - udid : string 手机唯一id(app only)
     *         - user_id 用户id
     *         - invite_uid 邀请人id
     */
    public function regBeforegetCouponList($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($param['user_id']) || !$this->verifyRequiredParam($param)) {
            $msg = "coupon_error: param：".print_r($param,1)."，msg：".HttpStatus::PARAM_ERROR."，line：656，(CouponService：not enough param!)";
            $this->log->error($msg);
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        $baseData = BaseData::getInstance();
        $nowTime = time();
        $baseParam = $param;
        if (isset($param['invite_uid']) && !empty($param['invite_uid'])){
            $environment = $this->config->environment;
            switch ($environment)
            {
                case 'dev':
                    $prize_coupon_sn = '170510007615';
                    break;
                case 'stg':
                    $prize_coupon_sn = '170510005267';
                    break;
                case 'pro':
                    $prize_coupon_sn = '170509003466';
                    break;
            }
            if(!empty($prize_coupon_sn)){
                $addCouponFromInviteInfo = BaiyangCouponRecordData::getInstance()->pureAddCouponRecord([
                    'user_id' => $param['invite_uid'],
                    'coupon_sn' => $prize_coupon_sn
                ]);
                if(isset($addCouponFromInviteInfo['code']) && $addCouponFromInviteInfo['code'] == 200){
                    $baseData->addData([
                        'table' => '\Shop\Models\BaiyangNewuserInvite',
                        'bind' => [
                            'user_id' => $param['user_id'],
                            'inviter_id' => $param['invite_uid'],
                            'couponid' => $prize_coupon_sn,
                            'add_time' => $nowTime
                        ]
                    ]);
                }
            }
        }
        $phone = BaiyangUserData::getInstance()->getPhone($param['user_id']);
        $msg = "coupon_tips: param：".print_r($param,1)."，phone_result：".$phone."，line：697，(CouponService：BaiyangUserData::getInstance()->getPhone!)";
        $this->log->error($msg);
        if($phone != ''){
            $cpsUser = BaseData::getInstance()->getData([
                'column' => 'cpsuser.channel_id',
                'table' =>  '\Shop\Models\BaiyangCpsInviteLog as cpslog',
                'where' => 'where cpslog.user_id = :user_id: ',
                'bind' => [
                    'user_id' => $phone
                ],
                'join' => 'INNER JOIN \Shop\Models\BaiyangCpsUser as cpsuser on cpslog.cps_id = cpsuser.cps_id'
            ],1);
            if(isset($cpsUser['channel_id']) && !empty($cpsUser['channel_id'])){
                $channel_sql = ' and ( channel_id = 0 OR channel_id = :channel_id: ) ';
                $channel_id = $cpsUser['channel_id'];
            }else{
                $channel_sql = ' and channel_id = :channel_id: ';
                $channel_id = 0;
            }

        }else{
            $channel_sql = ' and channel_id = :channel_id: ';
            $channel_id = 0;
        }
        //获取注册时赠送给用户的优惠券,并且领取
        $where = 'where start_provide_time < :start_provide_time: and end_provide_time > :end_provide_time: and register_bonus = :register_bonus: and group_set < :group_set: and '.$param['platform'].'_platform = :platform: and is_cancel = :is_cancel: and provide_type = :provide_type: and goods_tag_id = :goods_tag_id:'.$channel_sql;
        $bind = [
            'start_provide_time' => $nowTime,
            'end_provide_time' => $nowTime,
            'register_bonus' => 1,
            'group_set' => 3,
            'platform' => 1,
            'is_cancel' => 0,
            'provide_type' => 1,
            'channel_id' => $channel_id,
            'goods_tag_id' => 0
        ];
        $getNewMemberCouponInfo = $baseData->getData([
            'column' => 'coupon_sn,limit_number,coupon_number',
            'table' => '\Shop\Models\BaiyangCoupon',
            'where' => $where,
            'bind' => $bind
        ]);
        if(isset($getNewMemberCouponInfo[0]['coupon_sn']) && !empty($getNewMemberCouponInfo[0]['coupon_sn'])){
            $msg = "coupon_tips: param：".print_r($param,1)."，result：".print_r($getNewMemberCouponInfo,1)."，line：741，(CouponService：get new member coupon info!)";
            $this->log->error($msg);
            foreach ($getNewMemberCouponInfo as $couponInfo){
                $couponSN = $couponInfo['coupon_sn'];
                $param['coupon_sn'] = $couponSN;
                $param['is_reg'] = 1;
                $limit_number = (int) $couponInfo['limit_number'];
                $coupon_number = (int) $couponInfo['coupon_number'];
                if($coupon_number == 0 || ($coupon_number > 0 && $coupon_number > $limit_number)){
                    $msg = "coupon_tips: param：".print_r($param,1)."，method：pureAddCouponRecord，line：750，(CouponService：pureAddCouponRecord!)";
                    $this->log->error($msg);
                    for ($i=0;$i < $limit_number;$i++){
                        BaiyangCouponRecordData::getInstance()->pureAddCouponRecord($param);
                    }
                }else{
                    $msg = "coupon_error: param：".print_r($param,1)."，coupon_sn：".$couponSN."，couponInfo：".print_r($couponInfo,1)."，line：756，(CouponService：add coupon_record condition not match!)";
                    $this->log->error($msg);
                }
            }
        }else{
            $msg = "coupon_error: param：".print_r($param,1)."，where：".$where."，bind：".print_r($bind,1)."，line：759，(CouponService：getNewMemberCouponInfo data is empty!)";
            $this->log->error($msg);
        }
        // usleep(500000);
        //未注册时赠送转换为实体优惠券
        $userInfo = $baseData->getData([
           'column' => 'email,phone',
           'table' => '\Shop\Models\BaiyangUser',
            'where' => 'where id = :user_id:',
            'bind' =>[
                'user_id' => $param['user_id']
            ]
        ],1);
        $where = 'where ';
        if(!empty($userInfo)){
           $email = $userInfo['email'];
           if(!empty($email)){
               $where.='remark = :email: ';
               $binder['email'] =  $email;
           }
           $phone = $userInfo['phone'];
            if(!empty($phone)){
                if(!empty($email)){
                    $where.=' OR remark = :phone: ';
                }else{
                    $where.=' remark = :phone: ';
                }
                $binder['phone'] =  $phone;
            }


           $ids_arr = $baseData->getData([
               'column' => 'id',
               'table' => '\Shop\Models\BaiyangCouponRecord',
               'where' => $where,
               'bind' => $binder
           ]);
           if(!empty($ids_arr)){
               foreach ($ids_arr as $coupon_record){
                   $baseData->updateData([
                       'column' => 'user_id = :user_id: ,remark = null ',
                       'table' => '\Shop\Models\BaiyangCouponRecord',
                       'where' => 'where id = :coupon_record_id: ',
                       'bind' =>[
                           'coupon_record_id' => $coupon_record['id'],
                           'user_id' => $param['user_id']
                       ]
                   ]);
               }
           }
        }
        // usleep(500000);
        //调用个人中心优惠券
        $couponList = $this->UserCenterCouponList($baseParam);
        $coupon = $couponList['data']['no_used']['data'];
        foreach ($coupon as $key => $item)
        {
            if($item['validitytype'] == 2){
                $coupon[$key]['end_use_time'] = $item['remain_time'] + $item['server_time'];
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $coupon);
    }

    public function UserAllCoupon($basicParam)
    {
        $user_id = $basicParam['user_id'];
        $platform = $basicParam['platform'];
        $nowTime = time();
        //获取当前用户的优惠券
        $UserAllCoupon = BaiyangCouponData::getInstance()->getCurrentUserCoupon($user_id,$platform);
        $couponList = BaiyangCouponData::getInstance()->getCurrentUserCouponInTime($UserAllCoupon,$nowTime);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $couponList);
    }

    public function tagInfo($param,$goods_id)
    {
        $tagInfo = BaseData::getInstance()->getData([
            'table' => '\Shop\Models\BaiyangGoodsPrice as a',
            'column' => 'b.tag_id,a.goods_id',
            'bind' => [
                'user_id' => $param['user_id'],
                'goods_id' => $goods_id,
                'platform' => 1
            ],
            'where' => 'where a.goods_id = :goods_id: AND b.user_id = :user_id: AND a.platform_'.$param['platform'].' = :platform:',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangUserGoodsPriceTag as b on b.tag_id = a.tag_id'
        ],1);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $tagInfo);
    }

    public function timeService()
    {
        return  $this->uniteReturnResult(HttpStatus::SUCCESS, [
           'date' => date('Y-m-d H:i:s',time())
        ]);
    }
    /**
     * @author 邓永军
     * @desc 520活动,安利分享用户列表
     * @param $param
     *          invite_uid 当前登录用户id
     * @return \array[]
     */
    public function sharedNewUser($param)
    {
        $inviter_id = $param['invite_uid'];
        $userInfo = BaseData::getInstance()->getData([
            'column' => 'ni.user_id,ni.inviter_id,ni.couponid,u.headimgurl,u.nickname,u.username',
            'table' => '\Shop\Models\BaiyangNewuserInvite as ni',
            'where' => 'where inviter_id = :inviter_id: ORDER BY ni.add_time DESC',
            'bind' => [
                'inviter_id' => $inviter_id
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangUser as u on u.id = ni.user_id'
        ]);
        foreach ($userInfo as $key => $user)
        {
			if($userInfo[$key]['nickname']){
				$userInfo[$key]['username'] = '**'.mb_substr($user['nickname'],2);
				unset($userInfo[$key]['nickname']);
			}else{
				$userInfo[$key]['username'] = '**'.mb_substr($user['username'],2);
				unset($userInfo[$key]['nickname']);
			}
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $userInfo);
    }

    /**
     * @author 邓永军
     * @desc 520活动,获取安利优惠券列表
     * @param $param
     *      invite_uid 当前登录用户id
     * @return \array[]
     */
    public function getInviteCoupon($param)
    {
        $user_id = $param['invite_uid'];
        $couponList = BaseData::getInstance()->getData([
            'column' => 'ni.user_id,ni.inviter_id,ni.couponid,c.coupon_value,c.coupon_name,c.coupon_description',
            'table' => '\Shop\Models\BaiyangNewuserInvite as ni',
            'where' => 'where user_id = :user_id: ORDER BY ni.add_time DESC',
            'bind' => [
                'user_id' => $user_id
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as c on c.coupon_sn = ni.couponid'
        ]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $couponList);
    }

    /**
     * @author 邓永军
     * @desc 520活动,安利排行榜
     * @return \array[]
     */
    public function getInviteRankList()
    {
        $userInfo = BaseData::getInstance()->getData([
            'column' => 'ni.user_id,ni.inviter_id,ni.couponid,u.nickname,u.username,COUNT(ni.inviter_id) as invite_count',
            'table' => '\Shop\Models\BaiyangNewuserInvite as ni',
            'where' => ' GROUP BY ni.inviter_id ORDER BY invite_count DESC LIMIT 10',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangUser as u on u.id = ni.inviter_id '
        ]);
		foreach ($userInfo as $key => $user)
        {
			if($userInfo[$key]['nickname']){
				$userInfo[$key]['username'] = '**'.mb_substr($user['nickname'],2);
				unset($userInfo[$key]['nickname']);
			}else{
				$userInfo[$key]['username'] = '**'.mb_substr($user['username'],2);
				unset($userInfo[$key]['nickname']);
			}
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $userInfo);
    }
}