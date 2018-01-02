<?php
/**
 * @desc 兑换激活码
 * @author 邓永军
 */
namespace Shop\Home\Datas;

use Shop\Models\CacheKey;
use Shop\Models\HttpStatus;

class BaiyangCouponCodeData extends BaseData
{
    protected static $instance=null;

    private function getExpirationInfo($user_id,$validitytype,$coupon_sn,$end_use_time = 0,$relative_validity = 0)
    {
        if($validitytype == 1){
            $expiration = $end_use_time;
            return  $expiration;
        }else{
            $relative_time = BaiyangCouponRecordData::getInstance()->CouponHasBringInfo($user_id,$coupon_sn);
            if($relative_time > 0){
                $expiration = $relative_time + ($relative_validity * 24 * 3600);
                return $expiration;
            }else{
                $expiration = $relative_time;
                return $expiration;
            }
        }
    }
    /**
     * @desc 兑换激活码
     * @param $code
     * @param $user_id
     * @author 邓永军
     */
    public function exchangeCode($code,$user_id)
    {
        try{
            $key = CacheKey::EXCHANGE_COUPON_FAIL.$user_id;
            $failTime = $this->RedisCache->getValue($key);
            if($failTime >= 5){
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::OVER_ACTIVATION_NUM],HttpStatus::OVER_ACTIVATION_NUM);
            }
            $this->dbWrite->begin();
            $exchange_info = $this->getData([
                'column' => 'id,code_sn,coupon_sn,validitytype,is_exchange,relative_validity,start_use_time,end_use_time',
                'table' => '\Shop\Models\BaiyangCouponCode',
                'where' => 'where code_sn = :code_sn: AND start_provide_time < :start_provide_time: AND end_provide_time > :end_provide_time:',
                'bind' =>[
                    'code_sn' => $code,
                    'start_provide_time' =>time(),
                    'end_provide_time' => time()
                ]
            ],1);
            if(isset($exchange_info['id']) && !empty($exchange_info['id']) && $exchange_info['is_exchange'] == 1){
                $this->dbWrite->rollback();
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_EXCHANGE_HADACTIVED],HttpStatus::COUPON_EXCHANGE_HADACTIVED);
            }
			if(isset($exchange_info['id']) && !empty($exchange_info['id'])){
                $isNewUser = BaiyangOrderData::getInstance()->isNewUser($user_id);
                $couponOne = $this->getData([
                    'column' => 'provide_type,limit_number,coupon_number,bring_number,group_set,tels',
                    'table' => '\Shop\Models\BaiyangCoupon',
                    'where' => 'where coupon_sn = :coupon_sn: and is_cancel = 0 ',
                    'bind' => [
                        'coupon_sn' => $exchange_info['coupon_sn']
                    ]
                ],1);
                if(!isset($couponOne['provide_type']) || empty($couponOne['provide_type'])){
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
                }
                if($couponOne['coupon_number'] < 0 || $couponOne['coupon_number'] <= $couponOne['bring_number']){
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_IS_LIMMITOFF],HttpStatus::COUPON_IS_LIMMITOFF);
                }
                $group_set = $couponOne['group_set'];
                switch ($group_set){
                    case 1:
                        if($isNewUser != 1){
                            $this->dbWrite->rollback();
                            throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_NEW_USER_ONLY],HttpStatus::COUPON_NEW_USER_ONLY);
                        }
                        break;
                    case 2:
                        if($isNewUser != 0){
                            $this->dbWrite->rollback();
                            throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_OLD_USER_ONLY],HttpStatus::COUPON_OLD_USER_ONLY);
                        }
                        break;
                    case 3:
                        $tels = $couponOne['tels'];
                        if (empty($tels)){
                            $this->dbWrite->rollback();
                            throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
                        }
                        $phoneArr = explode(',',$tels);
                        $phone = BaiyangUserData::getInstance()->getPhone($user_id);
                        if($phone == ''){
                            $this->dbWrite->rollback();
                            throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
                        }else{
                            if(!in_array($phone,$phoneArr)){
                                $this->dbWrite->rollback();
                                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_PRESCRIBED_USER_ONLY],HttpStatus::COUPON_PRESCRIBED_USER_ONLY);
                            }
                        }
                        break;
                }
                if($couponOne['provide_type'] != 2){
                    //获取当前用户在当前时间段内使用券码数量是否大于限制
                    $count = $this->countData([
                        'table' => '\Shop\Models\BaiyangCouponCode',
                        'where' => 'where coupon_sn = :coupon_sn: AND exchange_user = :exchange_user: AND start_provide_time < :start_provide_time: AND end_provide_time > :end_provide_time:',
                        'bind' =>[
                            'coupon_sn' => $exchange_info['coupon_sn'],
                            'exchange_user' => $user_id,
                            'start_provide_time' =>time(),
                            'end_provide_time' => time()
                        ]
                    ]);
                }else{
                    //统一码当前用户数量
                    $keyName = 'soa_unitecode_'.$exchange_info['coupon_sn'].'_'.$user_id;
                    if(!$this->RedisCache->getValue($keyName)){
                        $this->RedisCache->setValue($keyName,0);
                    }
                    $count = $this->RedisCache->getValue($keyName);
                }
				if(!isset($couponOne['limit_number']) || empty($couponOne['limit_number'])){
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_MISSING],HttpStatus::COUPON_MISSING);
                }
                $limit_number = intval($couponOne['limit_number']);
				if($count >= $limit_number){
					$this->dbWrite->rollback();
					throw new \Exception('领取券码失败,您已领取'.$limit_number.'张，每个用户限领'.$limit_number.'张',HttpStatus::COUPON_EXCHANGE_OVER_LIMIT); 
				}
			}else{
				$this->dbWrite->rollback();
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_EXCHANGE_UNDEFINDED],HttpStatus::COUPON_EXCHANGE_UNDEFINDED);
			}
         
            if(isset($exchange_info['id']) && !empty($exchange_info['id'])){
                $couponOne = $this->getData([
                    'column' => 'provide_type,limit_number',
                    'table' => '\Shop\Models\BaiyangCoupon',
                    'where' => 'where coupon_sn = :coupon_sn:',
                    'bind' => [
                        'coupon_sn' => $exchange_info['coupon_sn']
                    ]
                ],1);
                if($couponOne['provide_type'] != 2){
                    $set = $this->updateData([
                        'column' => 'is_exchange = :is_exchange: , exchange_time = :exchange_time: , exchange_user = :exchange_user:',
                        'table' => '\Shop\Models\BaiyangCouponCode',
                        'where' => 'where code_sn = :code_sn:',
                        'bind' => [
                            'code_sn' => $code,
                            'is_exchange' => 1,
                            'exchange_time' => time() ,
                            'exchange_user' => $user_id
                        ]
                    ]);
                }else{
                    $keyName = 'soa_unitecode_'.$exchange_info['coupon_sn'].'_'.$user_id;
                    $num = $this->RedisCache->getValue($keyName) + 1;
                    $this->RedisCache->setValue($keyName,$num);
                    $set = $this->RedisCache->getValue($keyName);
                }
                if($set !== false){
                    $insert_id = $this->addData([
                        'table' => '\Shop\Models\BaiyangCouponRecord',
                        'bind' => [
                            'user_id' => $user_id,
                            'coupon_sn' => $exchange_info['coupon_sn'],
                            'order_sn' => '',
                            'is_used' => 0,
                            'is_overdue' => 0,
                            'start_use_time' => $exchange_info['validitytype'] == 1? $exchange_info['start_use_time']: 0,
                            'end_use_time' => $exchange_info['validitytype'] == 1? $exchange_info['end_use_time']: 0,
                            'add_time' => time(),
                            'used_time' => 0,
                            'validitytype' => $exchange_info['validitytype'],
                            'relative_validity' => $exchange_info['relative_validity'],
                            'code_sn' => $code
                        ]
                    ],1);
                    if($insert_id > 0){
                        $bring_number = $this->getData([
                            'column' => 'bring_number',
                            'table' => '\Shop\Models\BaiyangCoupon',
                            'where' => 'where coupon_sn = :coupon_sn:',
                            'bind' => [
                                'coupon_sn' => $exchange_info['coupon_sn']
                            ]
                        ],1)['bring_number'];
                        $ret = $this->updateData([
                            'column' => 'bring_number = :bring_number:',
                            'table' => '\Shop\Models\BaiyangCoupon',
                            'where' => 'where coupon_sn = :coupon_sn: ',
                            'bind' => [
                                'bring_number' => $bring_number+1,
                                'coupon_sn' => $exchange_info['coupon_sn']
                            ]
                        ]);
                        if($ret !==false){
                            $this->dbWrite->commit();
                            $coupon_detail = $this->getData([
                                'column' => 'coupon_name,relative_validity,validitytype,coupon_value,coupon_type,group_set,use_range,end_use_time,start_use_time,is_cancel,limit_number',
                                'table' => '\Shop\Models\BaiyangCoupon',
                                'where' => 'where coupon_sn = :coupon_sn:',
                                'bind' => [
                                    'coupon_sn' => $exchange_info['coupon_sn']
                                ]
                            ],1);
                            $is_used = $this->countData([
                                'table' => '\Shop\Models\BaiyangCouponRecord',
                                'where' => 'where user_id = :user_id: AND coupon_sn = :coupon_sn: AND code_sn = :code_sn: AND is_used = :is_used:',
                                'bind' =>[
                                    'user_id' => $user_id,
                                    'coupon_sn' => $exchange_info['coupon_sn'],
                                    'code_sn' => $code,
                                    'is_used' => 1
                                ]
                            ]);

                            $couponInfo=[
                                'record_id' => $insert_id,
                                'coupon_id' => $exchange_info['coupon_sn'],
                                'use_time' => $exchange_info['validitytype'] == 1? $exchange_info['end_use_time']: 0,
                                'for_users' => $coupon_detail['group_set'],
                                'indate_day' => $exchange_info['validitytype'] == 1? 0: $exchange_info['relative_validity'],
                                'is_cancel' => $coupon_detail['is_cancel'],
                                'is_use' => $is_used > 0 ? 1:0,
                                'start_time' => $exchange_info['validitytype'] == 1? $exchange_info['start_use_time']: 0,
                                'user_limit' => $coupon_detail['limit_number'],
                                'coupon_name' =>  $coupon_detail['coupon_name'],
                                'coupon_range' => $coupon_detail['use_range'],
                                'coupon_sn' => $exchange_info['coupon_sn'],
                                'coupon_type' => $coupon_detail['coupon_type'],
                                'coupon_value' => $coupon_detail['coupon_value'],
                                'expiration' => $this->getExpirationInfo($user_id,$coupon_detail['validitytype'],$exchange_info['coupon_sn'],$coupon_detail['end_use_time'],$coupon_detail['relative_validity'])
                            ];
                            $this->RedisCache->delete($key);
                            return [ 'code' => HttpStatus::SUCCESS , 'result' => '领取券码成功','data'=>$couponInfo ];
                        }else{
                            $this->dbWrite->rollback();
                            throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_EXCHANGE_UPDATE_FAIL],HttpStatus::COUPON_EXCHANGE_UPDATE_FAIL);
                        }
                    }else{
                        $this->dbWrite->rollback();
                        throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_EXCHANGE_ADDTORECORD_FAIL],HttpStatus::COUPON_EXCHANGE_ADDTORECORD_FAIL);
                    }

                }else{
                    $this->dbWrite->rollback();
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_EXCHANGE_STATUS_FAIL],HttpStatus::COUPON_EXCHANGE_STATUS_FAIL);
                }
            }else{
                $this->dbWrite->rollback();
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::COUPON_EXCHANGE_UNDEFINDED],HttpStatus::COUPON_EXCHANGE_UNDEFINDED);
            }
        }catch (\Exception $e){
            $key = CacheKey::EXCHANGE_COUPON_FAIL.$user_id;
            $this->RedisCache->getValue($key) > 0 ? $this->RedisCache->setValue($key,$this->RedisCache->getValue($key) + 1): $this->RedisCache->setValue($key,1);
            $this->RedisCache->setKeyExpireTime($key,86400);
            return [ 'code' => $e->getCode() , 'result' => $e->getMessage() ];
        }

    }
}