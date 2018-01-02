<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/16 0016
 * Time: 下午 2:14
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\OrderService;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\OrderDetailService;
use Shop\Models\BaiyangOrder;

class OrderController extends ControllerBase
{
    /**
     * 发布orderservice类
     */
    public function indexAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(OrderService::getInstance());
        $hprose->start();
    }

    public function getallAction()
    {
        $this->view->disable();
        $orderServie=OrderService::getInstance();
        $data=$orderServie->getOrderList([
            'user_id'=>41,
            'page'=>1,
            'limit'=>20,
        ],'pc');
        echo '<pre>';
        print_r($data);
    }

    public function allAction()
    {
        $this->view->disable();
        $orderService=OrderService::getInstance();
        $data=$orderService->getOrderListByStatus([
            'user_id'=>41,
            'page'=>1,
            'limit'=>5,
           'status'=>'shipped',
        ],'pc');
        echo '<pre>';
        var_dump($data);
    }

    public function testAction()
    {
        $this->view->disable();

    }

    public function getorderAction()
    {
        $this->view->disable();
        $orderService=OrderService::getInstance();
        $orderInfo=$orderService->getOrderDetail([
            'order_sn'=>'852017122214231860882',
            'user_id'=>11019,
            'platform'=>'wap',
            'channel_subid'=>91,
        ]);
        echo '<pre>';
        print_r($orderInfo);
    }

    public function countAction()
    {
        $this->view->disable();
        $orderService=OrderService::getInstance();
        $ret=$orderService->countOrderByStatus([
            'user_id'=>41,
            'status'=>'all'
        ],'pc');
        var_dump($ret);
    }

    public function commitAction()
    {
        $this->view->disable();
        $orderService=OrderService::getInstance();
        $ret=$orderService->commitOrder([
            'user'=>[
                'user_id'=>41,
            ],
            'address'=>[
                'address_id'=>11736,
            ],
            'cart'=>[
                120287,
                120288,
            ],
            'express'=>[
                'express_type'=>1,
            ],
            'pay'=>[
                'pay_type'=>1,
            ],
            'order_prefix'=>'95',
        ],'pc');
        var_dump($ret);
    }

    public function searchAction()
    {
        $this->view->disable();
        $orderService=OrderService::getInstance();
        $ret=$orderService->searchOrder([
            'order_sn'=>'1601160000005642',
            'user_id'=>41
        ]);
        var_dump($ret);
    }

    public function getAction()
    {
       $this->view->disable();
        $cookieFile=APP_PATH.'/static/cookie/cookie.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.epaylinks.cn/paycenter/v2.0/getoi.do?base64_memo=sNnR8y22qbWlseC6xTpHOTUxNzAxMDMxODU0NTgxNTgyMzM%3D&certId=335902764374016746163578510918738695929360058949&currency_type=RMB&notify_url=http%3A%2F%2F127.0.0.1%2FShop%2FEpaylinks%2Fnotify%2Fpay_id%2FwxNative.html&out_trade_no=G95170103185458158233&partner=EC130422C0003&pay_id=wxNative&return_url=http%3A%2F%2F127.0.0.1%2FShop%2FPay%2FglobalDonePay%2Forder_sn%2FG95170103185458158233.html&sign_type=SHA256withRSA&time_limit=2880&time_out=20170105190118&total_fee=0.01&version=4.0&sign=AIEwpepItFYnuCfPLBEHm3GtXswniqba92V7hp%2BWgYYAy7Uyxi1ShCDQ0RU7Psp%2FjC2kdjJxAYJU3DoP%2Fw0TrsSY8m04%2BoQbJS2ey8utKIE6PdVN8s74VwfGfntIKU20BmLC9AMIw0boSwGBvI3Yjsu7gd5Zo9bGMLbWvxwcQI7MRUZZW0qalECUkkSgJoAYVv%2BRNAgQZ7VVJYIUCREZwg4%2B4jufnUeL1TX2CvbtpBAdJK4d90SoKKdJwPQCnnKpJmt16D4dR9q4B2UZ%2BoxKviO3jG0kMPpD7ut4yuNdIHqsch0MWAq%2BQkPDHnuyq8wbAdV8ftLPwIF9wMacmf%2F98Q%3D%3D');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        $headerSize = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
        $header=substr($data,0,$headerSize);
        $ret=explode("\r\n",$header);
        $ret=explode(":",$ret[4]);
        $ret=explode(";",$ret[1]);
        $ret=ltrim($ret[0]);
        curl_close($ch);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.epaylinks.cn/paycenter/v2.0/payType.do?selPayType=wechat&rBankId=wxNative');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieFile);
        curl_setopt($ch,CURLOPT_COOKIEFILE,$cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        $ret=simplexml_load_string($data);
        var_dump($ret);
    }

    public function deleteOrderAction()
    {
        $this->view->disable();

        $param = [
            'order_sn'=>'G91220160803585782917',
            'user_id'=>543280
        ];
        $order = OrderService::getInstance();
        $res=$order->deleteOrder($param,'app');
        echo '<pre>';
        print_r($res);
    }

    public function cancelOrderAction()
    {
        $this->view->disable();

        $param = [
            'order_sn'=>'9120160922014297775',
            'user_id'=>610401,
            'cancel_reason'=>'ddd',
            'platform' => 'wap',
            'channel_subid'=>91
        ];
        $order = OrderService::getInstance();
        $res=$order->cancelOrder($param);
        echo '<pre>';
        print_r($res);
    }
}