<?php
/**
 * Created byPhpStorm .
 * User: Administrator
 * Date: 2016/9/21 0021
 * Time: 上午 10:17
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\AuthService;
use Shop\Home\Services\GoodsetService;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\LogService;
use Shop\Home\Services\MomService;
use Shop\Home\Services\SkuService;
use Shop\Home\Services\CouponService;
use Shop\Home\Services\OrderService;
use Shop\Home\Services\ShopService;
use Shop\Home\Services\FreightService;
use Shop\Home\Services\PromotionService;
use Shop\Home\Services\ConsigneeService;
use Shop\Home\Services\SearchService;
use Shop\Home\Services\PackageService;


class ServiceController extends \Phalcon\Mvc\Controller
{
    /**
     *  发布商品模块接口
     */
    public function SkuAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(SkuService::getInstance());
        $hprose->start();
    }

    /**
     *  发布优惠券接口
     */
    public function CouponAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(CouponService::getInstance());
        $hprose->start();
    }

    /**
     *  发布订单模块接口
     */
    public function OrderAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(OrderService::getInstance());
        $hprose->start();
    }

    /**
     *  发布购物车模块接口
     */
    public function ShopAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(ShopService::getInstance());
        $hprose->start();
    }

    public function MomAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(MomService::getInstance());
        $hprose->start();
    }

    public function Good_setAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(GoodsetService::getInstance());
        $hprose->start();
    }


    /**
     *  @desc 促销测试
     *  author 吴俊华
     */
    public function promotionAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(PromotionService::getInstance());
        //$hprose->onBeforeInvoke = $this->say();
        $hprose->start();
    }
    
    public function FreightAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(FreightService::getInstance());
        $hprose->start();
    }

    /**
     *  @desc 发布收货地址模块接口
     *  author 吴俊华
     */
    public function consigneeAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(ConsigneeService::getInstance());
        $hprose->start();
    }

    public function logAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(LogService::getInstance());
        $hprose->start();
    }

    public function AuthAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(AuthService::getInstance());
        $hprose->start();
    }

    public function SearchAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(SearchService::getInstance());
        $hprose->start();
    }

    /**
     *  @desc 优惠券大礼包
     *  @author 梁育权
     */
    public function PackageAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(PackageService::getInstance());
        $hprose->start();
    }
}