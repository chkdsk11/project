<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/3/31 0031
 * Time: 9:12
 */
 
namespace Shop\Home\Controllers;

use Shop\Home\Services\HproseService;
use Shop\Home\Services\OfflineService;

class O2ocartController extends ControllerBase
{
    /**
     *
     */
    public function indexAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(OfflineService::getInstance());
        $hprose->start();
    }

    public function cartAction()
    {
        $this->view->disable();
        $data = [
            'user_id'=>539400,//412692,
            'platform'=>'app',
            'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
            'udid'=>13969620308 //手机唯一id   (app必填参数)
        ];

        echo json_encode(OfflineService::getInstance()->shoppingCart($data));
    }

    public function editCartGoodsNumberAction()
    {
        $this->view->disable();
        $data = [
            'goods_id' => '8013282',
            'goods_number' => 2,
            'user_id'=>539400,//412692,
            'platform'=>'app',
            'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
            'udid'=>13969620308 //手机唯一id   (app必填参数)
        ];
        $result = OfflineService::getInstance()->editCartGoodsNumber($data);
        echo json_encode($result);
    }

    public function addGoodsToCartAction()
    {
        $this->view->disable();
        $data = [
            'goods_id' => '8100087',
            'goods_number' => 2,
            'user_id'=>539400,//412692,
            'platform'=>'app',
            'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
            'udid'=>13969620308 //手机唯一id   (app必填参数)
        ];
        $result = OfflineService::getInstance()->addGoodsToCart($data);
        $this->response->setJsonContent($result)->send();

    }

    public function getCartRecommendedProductsAction()
    {
        $this->view->disable();
        $data = [
            'goods_id' => '8021680',
            'page' => 4,
            'size' => 5,
            'user_id'=>412692,//412692,
            'platform'=>'app',
            'is_temp' => 0,
            'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
            'udid'=>13969620308 //手机唯一id   (app必填参数)
        ];
        $result = OfflineService::getInstance()->getCartRecommendedProducts($data);

        $this->response->setJsonContent($result)->send();
    }
}