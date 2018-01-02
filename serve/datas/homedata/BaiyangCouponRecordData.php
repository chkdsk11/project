<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Datas;
use Shop\Models\HttpStatus;

/**
 * Class BaiyangCouponRecordData
 * @package Shop\Home\Datas
 */
class BaiyangCouponRecordData extends BaseData
{

    protected static $instance=null;

    /**
     * @desc 已经获取优惠券的数量
     * @param $user_id 用户id
     * @param $coupon_sn 优惠券码
     * @return bool|int
     */
    public function countCouponHasBring($user_id,$coupon_sn,$is_used = 0)
    {
        if($is_used == 1){
            $count=$this->countData([
                'table'=>'\Shop\Models\BaiyangCouponRecord',
                'where'=>'where user_id =:user_id: AND coupon_sn = :coupon_sn: AND is_used = 1 ',
                'bind'=>[
                    'user_id'=>$user_id,
                    'coupon_sn'=>$coupon_sn,
                ]
            ]);
        }else{
            $count=$this->countData([
                'table'=>'\Shop\Models\BaiyangCouponRecord',
                'where'=>'where user_id =:user_id: AND coupon_sn = :coupon_sn: AND present_source = 0 ',
                'bind'=>[
                    'user_id'=>$user_id,
                    'coupon_sn'=>$coupon_sn
                ]
            ]);
        }
        return $count;
    }

    /**
     * 领取优惠券详细信息
     * @param $user_id
     * @param $coupon_sn
     * @return int
     */
    public function CouponHasBringInfo($user_id,$coupon_sn)
    {
        $ret = $this->getData([
            'column' => 'add_time,validitytype,relative_validity,end_use_time',
            'table'=>'\Shop\Models\BaiyangCouponRecord',
            'where'=>'where user_id =:user_id: AND coupon_sn = :coupon_sn:',
            'bind'=>[
                'user_id'=>$user_id,
                'coupon_sn'=>$coupon_sn
            ]
        ],1);
        if($ret){
            if($ret['validitytype'] == 1){
                return $ret['end_use_time'];
            }elseif ($ret['validitytype'] == 2){
                return $ret['add_time'];
            }else{
                return 0;
            }
        }else{
            return 0;
        }

    }
    /**
     * @desc 判断优惠券是否已经使用
     * @param $user_id
     * @param $coupon_sn
     * @return int
     */
    public function isCouponUsed($user_id,$coupon_sn)
    {
        $count = $this->countData([
            'table'=>'\Shop\Models\BaiyangCouponRecord',
            'where'=>'where user_id =:user_id: AND coupon_sn = :coupon_sn: AND is_used = :is_used: AND used_time = :used_time:',
            'bind'=>[
                'user_id'=>$user_id,
                'coupon_sn'=>$coupon_sn,
                'is_used' => 0,
                'used_time' => 0
            ]
        ]);
        if($count > 0) return 0;
        return 1;
    }

    /**
     * @desc 领取优惠券
     * @param $param
     *          user_id 用户id
     *          coupon_sn 优惠券码
     * @return array
     * @author 邓永军
     */
    public function pureAddCouponRecord($param)
    {
        $msg = "coupon_tips: param：".print_r($param,1)."，line：109，(BaiyangCouponRecordData：code start!)";
        $this->log->error($msg);
        try{
            //判断是否新用户
            if(!isset($param['is_reg']) || empty($param['is_reg'])){
                $is_new_user=BaiyangOrderData::getInstance()->isNewUser($param['user_id']);
                $msg = "coupon_tips: param：".print_r($param,1)."，is_new_user：".$is_new_user."，line：115，(BaiyangCouponRecordData：check user new or old!)";
                $this->log->error($msg);
            }else{
                $is_new_user = 1;
            }
            $couponInfo = $this->getData([
                'column' => 'bring_number,coupon_number,limit_number,validitytype,relative_validity,start_use_time,'
                    . 'end_use_time,group_set,start_provide_time,end_provide_time',
                'table' => '\Shop\Models\BaiyangCoupon',
                'where' => 'where coupon_sn = :coupon_sn: and is_cancel = :is_cancel: AND provide_type = :provide_type: ',
                'bind' => [
                    'coupon_sn' => $param['coupon_sn'],
                    'provide_type' => 1,
                    'is_cancel' => 0
                ]
            ],1);
            if(!isset($couponInfo) || empty($couponInfo)){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
            }
            $currentTime = time();
            if ($couponInfo['start_provide_time'] > $currentTime) {
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_NOT_ISSUED],HttpStatus::COUPON_NOT_ISSUED);
            }
            if ($couponInfo['end_provide_time'] < $currentTime) {
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_END_TO_ISSUE],HttpStatus::COUPON_END_TO_ISSUE);
            }
            if(!isset($param['is_reg']) || empty($param['is_reg'])){
                if($is_new_user == 0 && $couponInfo['group_set'] == 1){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_OLD_USER_BRING_FAIL],HttpStatus::COUPON_OLD_USER_BRING_FAIL);
                }
            }
            if($couponInfo['validitytype'] == 1){
                $validitytype = 1;
                $start_use_time = $couponInfo['start_use_time'];
                $end_use_time = $couponInfo['end_use_time'];
                $relative_validity = 0;
            }else{
                $validitytype = 2;
                $start_use_time = 0;
                $end_use_time = 0;
                $relative_validity = $couponInfo['relative_validity'];
            }
            if((int)$couponInfo['coupon_number'] > 0 && (int)$couponInfo['bring_number'] >= (int)$couponInfo['coupon_number']){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_IS_LIMMITOFF],HttpStatus::COUPON_IS_LIMMITOFF);
            }
            $msg = "coupon_tips: param：".print_r($param,1)."，line：152，(BaiyangCouponRecordData：code center!)";
            $this->log->error($msg);
            if((int) $this->countCouponHasBring($param['user_id'],$param['coupon_sn']) < (int)$couponInfo['limit_number']){
                //查询优惠券信息
                $insert_data = [
                    'user_id' => $param['user_id'],
                    'coupon_sn' => $param['coupon_sn'],
                    'is_used' => 0,
                    'is_overdue' => 0,
                    'order_sn' => '',
                    'start_use_time' => $start_use_time,
                    'end_use_time' => $end_use_time,
                    'add_time' => time(),
                    'used_time' => 0,
                    'validitytype' => $validitytype,
                    'relative_validity' => $relative_validity,
                    'code_sn' => '',
                    'remark' => isset($param['remark'])?$param['remark']:'',
                ];
                $res = $this->addData([
                    'table'=>'\Shop\Models\BaiyangCouponRecord',
                    'bind'=>$insert_data
                ],1);
                if($res > 0){
                    $msg = "coupon_tips: param：".print_r($param,1)."，insert_data：".print_r($insert_data,1)."，line：176，(BaiyangCouponRecordData：insert user coupon record success!)";
                    $this->log->error($msg);
                    $bring_number = $this->getData([
                        'column' => 'bring_number',
                        'table' => '\Shop\Models\BaiyangCoupon',
                        'where' => 'where coupon_sn = :coupon_sn:',
                        'bind' => [
                            'coupon_sn' => $param['coupon_sn']
                        ]
                    ],1)['bring_number'];
                    $updateParam = [
                        'column' => 'bring_number = :bring_number:,coupon_number = :coupon_number:',
                        'table' => '\Shop\Models\BaiyangCoupon',
                        'where' => 'where coupon_sn = :coupon_sn: ',
                        'bind' => [
                            'bring_number' => $bring_number+1,
                            'coupon_number' => $couponInfo['coupon_number'],
                            'coupon_sn' => $param['coupon_sn']
                        ]
                    ];
                    $ret = $this->updateData($updateParam);
                    if($ret !==false){
                        $msg = "coupon_tips: param：".print_r($param,1)."，update_param：".print_r($updateParam,1)."，line：198，(BaiyangCouponRecordData：update coupon success!)";
                        $this->log->error($msg);
                        //查询是否已经领取完
                        return ['code'=>HttpStatus::SUCCESS,'result'=>HttpStatus::$HttpStatusMsg[HttpStatus::SUCCESS]];
                    }else{
                        throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                    }
                }else{
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                }
            }else{
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_OVER_QUANTITY],HttpStatus::COUPON_RECEIVE_OVER_QUANTITY);
            }
        }catch (\Exception $e){
            $msg = "coupon_error: param：".print_r($param,1)."，code：".$e->getCode()."，msg：".$e->getMessage()."，file：".$e->getFile()."，line：".$e->getLine();
            $this->log->error($msg);
            return ['code' => $e->getCode(),'result' => $e->getMessage()];
        }
    }
    /**
     * @param $param
     *         - coupon_sn ：String 优惠券码
     *         - is_over_bring_limit ：Int 是否超过可领取优惠券的数量 是 1 否 0
     *         - validitytype 有效期类型 1 绝对有效期 2 相对有效期
     *         - relative_validity 相对有效天数(相对有效期时有效)
     *         - start_use_time 优惠券活动开始时间 (绝对有效期时有效)
     *         - end_use_time 优惠券活动结束时间 (绝对有效期时有效)
     * @return array
     */
    public function addCouponRecord($param)
    {
        try{
            if(isset($param['detail']) && !empty($param['detail'])){
                foreach ($param['detail'] as $detail){
                    foreach ($detail['data'] as $data){
                        if($data['coupon_sn'] == $param['coupon_sn']){
                            $param['start_use_time'] = $data['start_use_time'];
                            $param['end_use_time'] = $data['end_use_time'];
                            $param['validitytype'] = $data['validitytype'];
                            $param['is_over_bring_limit'] = $data['is_over_bring_limit'];
                            $param['relative_validity'] = $data['relative_validity'];
                            $param['coupon_number'] = $data['coupon_number'];
                        }
                    }
                    unset($param['detail']);
                }
                $this->dbWrite->begin();
                $getCoupon = $this->getData([
                    'column' => 'bring_number',
                    'table' => '\Shop\Models\BaiyangCoupon',
                    'where' => 'where coupon_sn = :coupon_sn:',
                    'bind' => [
                        'coupon_sn' => $param['coupon_sn']
                    ]
                ],1);
                if(!isset($getCoupon['bring_number']) || empty($getCoupon['bring_number'])){
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
                }
                $bring_number = $getCoupon['bring_number'];
                if($bring_number >= $param['coupon_number'] && $param['coupon_number'] > 0 && $param['coupon_number'] != 0){
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_IS_LIMMITOFF],HttpStatus::COUPON_IS_LIMMITOFF);
                }
                if($param['is_over_bring_limit'] == 0){
                    //查询优惠券信息
                    $insert_data = [
                        'user_id' => $param['user_id'],
                        'coupon_sn' => $param['coupon_sn'],
                        'is_used' => 0,
                        'is_overdue' => 0,
                        'order_sn' => '',
                        'start_use_time' => $param['validitytype'] == 1? $param['start_use_time']: 0,
                        'end_use_time' => $param['validitytype'] == 1? $param['end_use_time']: 0,
                        'add_time' => time(),
                        'used_time' => 0,
                        'validitytype' => $param['validitytype'],
                        'relative_validity' => $param['relative_validity'],
                        'code_sn' => ''
                    ];
                    $res = $this->addData([
                        'table'=>'\Shop\Models\BaiyangCouponRecord',
                        'bind'=>$insert_data
                    ],1);
                    if($res > 0){

                        $bring_number = $this->getData([
                            'column' => 'bring_number',
                            'table' => '\Shop\Models\BaiyangCoupon',
                            'where' => 'where coupon_sn = :coupon_sn:',
                            'bind' => [
                                'coupon_sn' => $param['coupon_sn']
                            ]
                        ],1)['bring_number'];

                        $ret = $this->updateData([
                            'column' => 'bring_number = :bring_number:',
                            'table' => '\Shop\Models\BaiyangCoupon',
                            'where' => 'where coupon_sn = :coupon_sn: ',
                            'bind' => [
                                'bring_number' => $bring_number+1,
                                'coupon_sn' => $param['coupon_sn']
                            ]
                        ]);

                        if($ret !==false){
                            $this->dbWrite->commit();
                            return ['code'=>HttpStatus::SUCCESS,'result'=>HttpStatus::$HttpStatusMsg[HttpStatus::SUCCESS]];
                        }else{
                            $this->dbWrite->rollback();
                            throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                        }
                    }else{
                        $this->dbWrite->rollback();
                        throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                    }
                }else{
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_OVER_QUANTITY],HttpStatus::COUPON_RECEIVE_OVER_QUANTITY);
                }
            }else{
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
            }
        }catch (\Exception $e){
            return ['code' => $e->getCode(),'result' => $e->getMessage() ];
        }


    }


    /**
     * @desc 优惠券核销
     * @param $order_id 订单id
     * @param $trade_type 交易类型 1 支付 2 退款
     * @param $user_id 用户id
     * @param $coupon_sn 优惠券码
     * @author 邓永军
     */
    public function TradeToUpdateCoupon($order_id, $user_id, $coupon_sn ,$trade_type = 1)
    {
        try{
            $base_data = BaseData::getInstance();
            $coupon_record = $base_data->getData([
                'column' => 'coupon_sn',
                'table' => '\Shop\Models\BaiyangCouponRecord',
                'where' => 'where user_id = :user_id: AND coupon_sn = :coupon_sn: AND is_used = :is_used:',
                'bind' => [
                    'user_id' => $user_id ,
                    'coupon_sn' => $coupon_sn,
                    'is_used' => 0
                ]
            ],true);
            if(isset($coupon_record) && !empty($coupon_record) ){
                $this->dbWrite->begin();
                $getUpdateOne = $base_data->getData([
                    'column' => 'id',
                    'table' => '\Shop\Models\BaiyangCouponRecord',
                    'where' => 'where user_id = :user_id: AND coupon_sn = :coupon_sn: AND is_used = :is_used:',
                    'bind' => [
                        'user_id' => $user_id ,
                        'coupon_sn' => $coupon_sn,
                        'is_used' => 0,
                    ]
                ],1);
                if(!isset($getUpdateOne) || empty($getUpdateOne)){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
                }
                //优惠券记录变更
                $updateRes = $base_data->updateData([
                    'column' => 'order_sn = :order_sn: , is_used = 1 ,used_time = :used_time:',
                    'table' => '\Shop\Models\BaiyangCouponRecord',
                    'where' => 'where id = :rid:',
                    'bind' => [
                        'rid' => $getUpdateOne['id'],
                        'order_sn' => $order_id,
                        'used_time' => time()
                    ]
                ]);
                    if($updateRes !== false){
                        $this->dbWrite->commit();
                        return ['code' => 200 ,'error'=> '' ];
                }else{
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECORD_CHANGE_FAIL],HttpStatus::COUPON_RECORD_CHANGE_FAIL);
                }
            }else{
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
            }
        }catch (\Exception $e){
            return ['code' => $e->getCode() ,'error'=> $e->getMessage() ];
        }

    }

    /**
     * @desc 批量-领取优惠券
     * @param $param
     *          user_id 用户id
     *          coupon_sn 优惠券码
     * @return array
     * @author 梁育权
     */
    public function batchAddCouponRecord($param)
    {
        try{
            //判断是否新用户
            if(!isset($param['is_reg']) || empty($param['is_reg'])){
                $is_new_user=BaiyangOrderData::getInstance()->isNewUser($param['user_id']);
            }else{
                $is_new_user = 1;
            }
            $couponInfo = $this->getData([
                'column' => 'bring_number,coupon_number,limit_number,validitytype,relative_validity,start_use_time,end_use_time,group_set',
                'table' => '\Shop\Models\BaiyangCoupon',
                'where' => 'where coupon_sn = :coupon_sn: and is_cancel = :is_cancel: AND provide_type = :provide_type: ',
                'bind' => [
                    'coupon_sn' => $param['coupon_sn'],
                    'provide_type' => 1,
                    'is_cancel' => 0
                ]
            ],1);
            if(!isset($couponInfo) || empty($couponInfo)){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
            }
            if(!isset($param['is_reg']) || empty($param['is_reg'])){
                if($is_new_user == 0 && $couponInfo['group_set'] == 1){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_OLD_USER_BRING_FAIL],HttpStatus::COUPON_OLD_USER_BRING_FAIL);
                }
            }
            if($couponInfo['validitytype'] == 1){
                $validitytype = 1;
                $start_use_time = $couponInfo['start_use_time'];
                $end_use_time = $couponInfo['end_use_time'];
                $relative_validity = 0;
            }else{
                $validitytype = 2;
                $start_use_time = 0;
                $end_use_time = 0;
                $relative_validity = $couponInfo['relative_validity'];
            }
            if((int)$couponInfo['coupon_number'] > 0 && (int)$couponInfo['bring_number'] >= (int)$couponInfo['coupon_number']){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_IS_LIMMITOFF],HttpStatus::COUPON_IS_LIMMITOFF);
            }
            if((int) $this->countCouponHasBring($param['user_id'],$param['coupon_sn']) < (int)$couponInfo['limit_number']){
                //查询优惠券信息
                $insert_data = [
                    'user_id' => $param['user_id'],
                    'coupon_sn' => $param['coupon_sn'],
                    'is_used' => 0,
                    'is_overdue' => 0,
                    'order_sn' => '',
                    'start_use_time' => $start_use_time,
                    'end_use_time' => $end_use_time,
                    'add_time' => time(),
                    'used_time' => 0,
                    'validitytype' => $validitytype,
                    'relative_validity' => $relative_validity,
                    'code_sn' => ''
                ];
                $res = $this->addData([
                    'table'=>'\Shop\Models\BaiyangCouponRecord',
                    'bind'=>$insert_data
                ],1);
                if($res > 0){
                    $bring_number = $this->getData([
                        'column' => 'bring_number',
                        'table' => '\Shop\Models\BaiyangCoupon',
                        'where' => 'where coupon_sn = :coupon_sn:',
                        'bind' => [
                            'coupon_sn' => $param['coupon_sn']
                        ]
                    ],1)['bring_number'];
                    $ret = $this->updateData([
                        'column' => 'bring_number = :bring_number:,coupon_number = :coupon_number:',
                        'table' => '\Shop\Models\BaiyangCoupon',
                        'where' => 'where coupon_sn = :coupon_sn: ',
                        'bind' => [
                            'bring_number' => $bring_number+1,
                            'coupon_number' => $couponInfo['coupon_number'],
                            'coupon_sn' => $param['coupon_sn']
                        ]
                    ]);
                    if($ret !==false){
                        //查询是否已经领取完
                        return ['code'=>HttpStatus::SUCCESS,'result'=>HttpStatus::$HttpStatusMsg[HttpStatus::SUCCESS]];
                    }else{
                        throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                    }
                }else{
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                }
            }else{
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_OVER_QUANTITY],HttpStatus::COUPON_RECEIVE_OVER_QUANTITY);
            }
        }catch (\Exception $e){
            return ['code' => $e->getCode(),'result' => $e->getMessage() ];
        }
    }

    /**
     * @desc 未注册用户领取优惠券
     * @param $param
     *          user_id 用户id
     *          coupon_sn 优惠券码
     * @return array
     * @author 梁育权
     */
    public function NotRegisterAddCouponRecord($param)
    {
        try{
            //判断是否新用户
            if(!isset($param['is_reg']) || empty($param['is_reg'])){
                $is_new_user=BaiyangOrderData::getInstance()->isNewUser($param['user_id']);
            }else{
                $is_new_user = 1;
            }
            $couponInfo = $this->getData([
                'column' => 'bring_number,coupon_number,limit_number,validitytype,relative_validity,start_use_time,end_use_time,group_set',
                'table' => '\Shop\Models\BaiyangCoupon',
                'where' => 'where coupon_sn = :coupon_sn: and is_cancel = :is_cancel: AND provide_type = :provide_type: ',
                'bind' => [
                    'coupon_sn' => $param['coupon_sn'],
                    'provide_type' => 1,
                    'is_cancel' => 0
                ]
            ],1);
            if(!isset($couponInfo) || empty($couponInfo)){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
            }
            if(!isset($param['is_reg']) || empty($param['is_reg'])){
                if($is_new_user == 0 && $couponInfo['group_set'] == 1){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_OLD_USER_BRING_FAIL],HttpStatus::COUPON_OLD_USER_BRING_FAIL);
                }
            }
            if($couponInfo['validitytype'] == 1){
                $validitytype = 1;
                $start_use_time = $couponInfo['start_use_time'];
                $end_use_time = $couponInfo['end_use_time'];
                $relative_validity = 0;
            }else{
                $validitytype = 2;
                $start_use_time = 0;
                $end_use_time = 0;
                $relative_validity = $couponInfo['relative_validity'];
            }
            if((int)$couponInfo['coupon_number'] > 0 && (int)$couponInfo['bring_number'] >= (int)$couponInfo['coupon_number']){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_IS_LIMMITOFF],HttpStatus::COUPON_IS_LIMMITOFF);
            }
            if( ((int)$param['limit_number'] <= (int)$couponInfo['limit_number']) || ($couponInfo['limit_number']==0)){
                //查询优惠券信息
                $insert_data = [
                    'user_id' => $param['user_id'],
                    'coupon_sn' => $param['coupon_sn'],
                    'is_used' => 0,
                    'is_overdue' => 0,
                    'order_sn' => '',
                    'start_use_time' => $start_use_time,
                    'end_use_time' => $end_use_time,
                    'add_time' => time(),
                    'used_time' => 0,
                    'validitytype' => $validitytype,
                    'relative_validity' => $relative_validity,
                    'code_sn' => '',
                    'remark' => isset($param['remark'])?$param['remark']:'',
                ];
                $res = $this->addData([
                    'table'=>'\Shop\Models\BaiyangCouponRecord',
                    'bind'=>$insert_data
                ],1);
                if($res > 0){
                    $bring_number = $this->getData([
                        'column' => 'bring_number',
                        'table' => '\Shop\Models\BaiyangCoupon',
                        'where' => 'where coupon_sn = :coupon_sn:',
                        'bind' => [
                            'coupon_sn' => $param['coupon_sn']
                        ]
                    ],1)['bring_number'];
                    $ret = $this->updateData([
                        'column' => 'bring_number = :bring_number:,coupon_number = :coupon_number:',
                        'table' => '\Shop\Models\BaiyangCoupon',
                        'where' => 'where coupon_sn = :coupon_sn: ',
                        'bind' => [
                            'bring_number' => $bring_number+1,
                            'coupon_number' => $couponInfo['coupon_number'],
                            'coupon_sn' => $param['coupon_sn']
                        ]
                    ]);
                    if($ret !==false){
                        //查询是否已经领取完
                        return ['code'=>HttpStatus::SUCCESS,'result'=>HttpStatus::$HttpStatusMsg[HttpStatus::SUCCESS]];
                    }else{
                        throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                    }
                }else{
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_FAIL],HttpStatus::COUPON_RECEIVE_FAIL);
                }
            }else{
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_RECEIVE_OVER_QUANTITY],HttpStatus::COUPON_RECEIVE_OVER_QUANTITY);
            }
        }catch (\Exception $e){
            return ['code' => $e->getCode(),'result' => $e->getMessage() ];
        }
    }
}