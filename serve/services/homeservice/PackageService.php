<?php
/**
 * @author 梁育权
 */
namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangCouponRecordData;
use Shop\Home\Datas\BaiyangPackageShareData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaiyangPackageRecordData;
use Shop\Home\Datas\BaiyangPromotionData;
use Shop\Home\Datas\BaiyangPackageData;
use Shop\Home\Datas\BaiyangCouponData;
use Shop\Models\HttpStatus;
use Think\Exception;


/**
 * Class CouponService
 * @package Shop\Home\Services
 * @todo 写注释
 */
class PackageService extends BaseService
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new PackageService();
        }
        /*$eventsManager = new EventsManager();
        $eventsManager->attach('promotion', new PromotionShopping());
        static::$instance->setEventsManager($eventsManager);*/
        return static::$instance;
    }

    /**
     * @desc 是否获取优惠大礼包
     * @param $param
     *          - user_id 用户ID
     *          - package_id 优惠大礼包ID
     *          - platform 平台
     * @return mixed
     */
   /* public function isGetPackage($param)
    {
        $require = $this->judgeRequireParam($param, 'package_id,phone,platform,mobile_code');
        if (!empty($require)) return $require;
        $cacheKey = 'mobile_package_'.$param['phone'];
        $mobile_code = $this->cache->getValue($cacheKey);
        if($mobile_code != $param['mobile_code']){
            return $this->uniteReturnResult(HttpStatus::VERIFY_CODE_ERROR, $param);
        }
        $success = true;
        if (!BaiyangPackageRecordData::getInstance()->insertPackageRecord($param)) $success = false;//插入订单
        if (!$success) {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $param);
        }

        $list['data'] = BaiyangPackageRecordData::getInstance()->getPackageRecordInfoByPhone($param['phone'],$param['package_id']);
        if(!empty($list['data'])){
            return $this->uniteReturnResult(HttpStatus::COUPON_RECORD_EXSIT, $list['data']);
        }
        $this->cache->delete($cacheKey);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $list['data']);
    }*/

    /**
     * @desc 领取优惠大礼包
     * @param $param
     *          - user_id 用户ID
     *          - package_id 优惠大礼包ID
     *          - platform 平台
     * @return mixed
     */
    public function getPackage($param)
    {
        $require = $this->judgeRequireParam($param, 'package_id,user_id,platform');
        if (!empty($require)) return $require;
        $package_info = BaiyangPackageData::getInstance()->getPackageInfo($param['package_id']);
        if(empty($package_info)){
            return $this->uniteReturnResult(HttpStatus::PACKAGE_NOT_EXSIT, $param);
        }
        $coupon_list = BaiyangCouponData::getInstance()->getCouponListById($package_info['coupon_ids']);
        $coupon_sn_arr = array_column($coupon_list,'coupon_sn');
        $user_info = BaiyangUserData::getInstance()->getUserInfo($param['user_id'],'phone');
        if(empty($user_info)){
            return $this->uniteReturnResult(HttpStatus::USER_NOT_EXIST, $param);
        }

        $list['data'] = BaiyangPackageRecordData::getInstance()->getPackageRecordInfo($user_info['phone'],$param['package_id']);
        $this->dbWrite->begin();
        $success = true;
        if(!empty($list['data'])){
            if (!BaiyangPackageRecordData::getInstance()->updatePackageRecord($param)) $success = false;//更新
        }else{
            $list['data'] = BaiyangPackageRecordData::getInstance()->getPackageRecordInfo($param['user_id'],$param['package_id']);
            if(!empty($list['data'])){
                return $this->uniteReturnResult(HttpStatus::COUPON_RECORD_EXSIT, $list['data']);
            }
            if (!BaiyangPackageRecordData::getInstance()->insertPackageRecord($param)) $success = false;//插入
        }
        if (!BaiyangPackageData::getInstance()->updatePackagePeopleNumber($param)) $success = false;//更新
        foreach ($coupon_sn_arr as $k => $v){
            $coupon_sn['coupon_sn'] = $v;
            $coupon_sn['user_id'] = $param['user_id'];
            if (! BaiyangCouponRecordData::getInstance()->pureAddCouponRecord($coupon_sn)) $success = false;continue;//插入优惠券记录
        }
        if (!$success) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $param);
        }
        $this->dbWrite->commit();
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $list['data']);
    }
    /**
     * @desc 领取优惠大礼包-通过手机号码
     * @param $param
     *          - phone 用户手机
     *          - package_id 优惠大礼包ID
     *          - platform 平台
     * @return mixed 返回用户信息
     */
    public function getPackageByPhone($param)
    {
        $require = $this->judgeRequireParam($param, 'package_id,platform,phone,mobile_code');
        if (!empty($require)) return $require;
        $user_is_belong_phone = $user_is_belong = '';
        $cacheKey = 'mobile_package_'.$param['phone'];
        $mobile_code = $this->cache->getValue($cacheKey);
        if($mobile_code != $param['mobile_code']){
            return $this->uniteReturnResult(HttpStatus::VERIFY_CODE_ERROR, $param);
        }

        $package_info = BaiyangPackageData::getInstance()->getPackageInfo($param['package_id']);
        if(empty($package_info)){
            return $this->uniteReturnResult(HttpStatus::PACKAGE_NOT_EXSIT, $param);
        }
        $coupon_list = BaiyangCouponData::getInstance()->getCouponListById($package_info['coupon_ids']);
        $coupon_sn_arr = array_column($coupon_list,'coupon_sn');

        $user_info = BaiyangUserData::getInstance()->getUserInfoByPhone($param['phone'],'id');
        $param['user_id'] = isset($user_info['id'])?$user_info['id']:0;
        $user_is_belong_phone = BaiyangPackageRecordData::getInstance()->getPackageRecordInfoByPhone($param['phone'],$param['package_id']);
        if ( $param['user_id']==0 ) {$coupon_sn['remark'] = $param['phone'];}
        else{ $user_is_belong = BaiyangPackageRecordData::getInstance()->getPackageRecordInfo($param['user_id'],$param['package_id']); }

        if(!empty($user_is_belong_phone) || !empty($user_is_belong)){
            return $this->uniteReturnResult(HttpStatus::COUPON_RECORD_EXSIT, $param);
        }
        $this->dbWrite->begin();
        $success = true;
        if (!BaiyangPackageRecordData::getInstance()->insertPackageRecord($param)) $success = false;//插入订单

        foreach ($coupon_sn_arr as $k => $v){
            $coupon_sn['coupon_sn'] = $v;
            $coupon_sn['user_id'] = $param['user_id'];
            $coupon_sn['limit_number'] = 1;
            if (! BaiyangCouponRecordData::getInstance()->NotRegisterAddCouponRecord($coupon_sn)) $success = false;continue;//插入优惠券记录
        }
        if (!$success) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $param);
        }
        $this->dbWrite->commit();
        $this->cache->delete($cacheKey);
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }
    /**
     * @desc 获取大礼包详情
     * @param $param
     *          - package_id 优惠大礼包ID
     *          - platform 平台
     * @return mixed
     */
    public function getOnePackage($param){
        $require = $this->judgeRequireParam($param, 'package_id,platform');
        if (!empty($require)) return $require;
        $list['data'] = BaiyangPackageData::getInstance()->getPackageInfo($param['package_id']);
        if(empty($list['data'])){
            return $this->uniteReturnResult(HttpStatus::PACKAGE_NOT_EXSIT, $list['data']);
        }
        $list['data']['coupon_data'] = BaiyangCouponData::getInstance()->getCouponListById($list['data']['coupon_ids']);
        //$list['data']['promotion_data'] = BaiyangPromotionData::getInstance()->getPromotionsInfo($param,'',$list['data']['promotion_id']);

        return $this->uniteReturnResult(HttpStatus::SUCCESS, $list['data']);
    }

    /**
     * @desc 发送短信
     * @param $param
     *          - phone 手机号码
     *          - platform 平台
     * @return mixed
     */
    public function sendPackage($param){
        $require = $this->judgeRequireParam($param, 'phone,platform');
        if (!empty($require)) return $require;
        $code = rand(1000,9999);
        $params = [
            'code'=>$code,
        ];
        $result = $this->func->sendSms($param['phone'],'shop_bind_phone',[],'wap', $params);
        if(empty($result)){
            return $this->uniteReturnResult(HttpStatus::SEND_FAILED, $param);
        }
        $cacheKey = 'mobile_package_'.$param['phone'];
        $this->cache->setValue($cacheKey, $code, 3600);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 验证短信验证码
     * @param $param
     *          - phone 手机号码
     *          - mobile_code 验证码
     *          - platform 平台
     * @return mixed
     */
   /* public function verifySendCode($param){
        $require = $this->judgeRequireParam($param, 'phone,platform,mobile_code');
        if (!empty($require)) return $require;
        $cacheKey = 'mobile_package_'.$param['phone'];
        $mobile_code = $this->cache->getValue($cacheKey);
        if($mobile_code != $param['mobile_code']){
            return $this->uniteReturnResult(HttpStatus::VERIFY_CODE_ERROR, $param);
        }

        $success = true;
        if (!BaiyangPackageRecordData::getInstance()->insertPackageRecord($param)) $success = false;//插入订单
        if (!$success) {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $param);
        }
        $this->cache->delete($cacheKey);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $param);
    }*/

    /**
     * @desc 分享
     * @param $param
     *          - user_id 用户ID
     *          - package_id 优惠大礼包ID
     *          - platform 平台
     * @return mixed
     */
    public function sharePackage($param){
        $require = $this->judgeRequireParam($param, 'user_id,platform,package_id');
        if (!empty($require)) return $require;
        $success = true;
        $result = BaiyangPackageShareData::getInstance()->insertPackageShare($param);
        if (!$result) $success = false;//插入订单
        if (!$success) {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $param);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['share_id'=>$result]);
    }
    /**
     * @desc 注册绑定大礼包关系
     * @param $param
     *          - user_id 用户ID
     *          - package_id 优惠大礼包ID
     *          - platform 平台
     *          - phone 电话号码
     * @return mixed
     */
    public function registerBingPackage($param){
        $require = $this->judgeRequireParam($param, 'phone,platform,package_id,user_id');
        if (!empty($require)) return $require;
        $list = BaiyangPackageRecordData::getInstance()->getPackageRecordInfoByPhone($param['phone'],$param['package_id']);
        if (!$list) {
            return $this->uniteReturnResult(HttpStatus::PACKAGE_BIND_ERROR, $param);
        }
        if($list['user_id']==$param['user_id']){
            return $this->uniteReturnResult(HttpStatus::PACKAGE_BIND_EXIST, $param);
        }
        $success = true;
        if (!BaiyangPackageRecordData::getInstance()->updatePackageRecord($param)) $success = false;//插入订单
        if (!$success) {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $param);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $list);
    }
}