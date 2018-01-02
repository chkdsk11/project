<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Controllers;
use PSX\OpenSsl\Exception;
use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Datas\BaiyangCouponRecordData;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\CouponService;
use Shop\Models\HttpStatus;

class CouponController extends ControllerBase
{
    public function listAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(CouponService::getInstance());
        $hprose->start();
    }
    public function addAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(CouponService::getInstance());
        $hprose->start();
    }
    public function orderAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(CouponService::getInstance());
        $hprose->start();
    }

    /**
     * @desc 领取优惠券接口
     * @param
     *      goods_id 商品id列表
     *      coupon_sn 优惠券码
     *      platform 平台标识
     *      user_id 用户id
     * @return array
     *      code 状态码
     *      result 结果
     * @author 邓永军
     */
    public function ReceiveCouponAction()
    {
        $this->view->disable();
        try{
            if($this->request->isPost()){
                $param = $this->request->getPost();
                if(!isset($param['goods_id']) || empty($param['goods_id'])){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::PARAM_ERROR],HttpStatus::PARAM_ERROR);
                }
                if(!isset($param['coupon_sn']) || empty($param['coupon_sn'])){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::INVALID_COUPON],HttpStatus::INVALID_COUPON);
                }
                if(!isset($param['platform']) || empty($param['platform'])){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::PLATFORM_ERROR],HttpStatus::PLATFORM_ERROR);
                }
                if(!isset($param['user_id']) || empty($param['user_id'])){
                    throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::USERID_ERROR],HttpStatus::USERID_ERROR);
                }
                $res = CouponService::getInstance()->addCoupon($param);
                return $this->response->setJsonContent($res);
            }else{
                throw new \Exception(HttpStatus::$HttpStatusMsg[HttpStatus::SYSTEM_ERROR],HttpStatus::SYSTEM_ERROR);
            }
        }catch (\Exception $e){
            return $this->response->setJsonContent([
                'code'=>$e->getCode(),
                'result'=>$e->getMessage()
            ]);
        }

    }

    /**
     * @desc 判断品牌是否存在优惠券
     * @param $brand_id
     * @return mixed
     * @author 邓永军
     */

    public function IsExistCouponInBrandAction($brand_id)
    {
        $this->view->disable();
        return CouponService::getInstance()->IsExistCouponInBrand($brand_id);
    }

    public function getUserCListAction($user_id)
    {
        $this->view->disable();
        $list = CouponService::getInstance()->UserCenterCouponList([
            'platform' => 'app',
            'user_id' => $user_id,
        ]);
        return $this->response->setJsonContent($list);
    }

    public function unlockActiveCodeAction()
    {
        $this->view->disable();
        echo 1234;
       // $this->request->getPost()
    }

}
