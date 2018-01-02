<?php
/**
 *
 *
 *
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\LogService;
use Shop\Home\Services\OrderService;
use Shop\Home\Services\TestService;
require APP_PATH."/vendor/autoload.php";
use Hprose\Http\Client;
use Hprose\Future;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->disable();
        //$testService=TestService::getInstance();
        //$data=$testService->getData();
//        $testService=TestService::getInstance();
//        $data=$testService->testPromotion();
//        print_r($data);
    }

    public function getPromotionAction()
    {
        $this->view->disable();
//        $data = TestService::getInstance()->getPromotionListByGoodId();
//        print_r($data);
    }

    public function testAction()
    {
        $this->view->disable();
        $order_service =OrderService::getInstance();
        $res = $order_service->confirmOrder([
            'user_id' => 542728,
            'is_temp'=> 0,
            'coupon_sn'=> '170217009729',
            'address_id'=> '7990',
            'express_type'=> '1',
            'is_use_balance'=> '0',
            'shop_id'=> '6',
            'payment_id'=> '0',
            'o2o_time'=> '1488330000',
            'is_first'=> '0',
            'platform'=> 'pc',
            'channel_subid'=> '95'
        ]);
        print_r($res);
    }
}

