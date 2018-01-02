<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/21 0021
 * Time: 上午 10:32
 */

namespace Shop\Home\Controllers;

require APP_PATH."/vendor/autoload.php";

use Hprose\Http\Client;
use Hprose\Future;
use Phalcon\Logger\Formatter\Line;
use Shop\Home\Datas\BaseData;
use Shop\Home\Listens\AuthListener;
use Shop\Home\Services\AuthService;
use Shop\Home\Services\MomService;
use Shop\Home\Services\PromotionService;
use Shop\Home\Services\ShopService;
use Shop\Home\Services\SmsService;
use Shop\Libs\Func;
use Shop\Libs as libs;
use Shop\Models\CacheKey;

class ClientController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
//        $a1=array("Horse","Dog","Cat");
//        $a2=array("Cow","Dog","Rat");
//        print_r(array_map(function ($v1,$v2)
//        {
//            if ($v1===$v2)
//            {
//                return "same";
//            }
//            return "different";
//        },$a1,$a2));die;
//        $fullPrice = '1.45';
//        $fullPrice = (int)sprintf("%.0f",$fullPrice);
//        print_r($fullPrice);die;
//        $redis = $this->cache;
//        $redis->selectDb(5);
//        $data = $redis->getValue(CacheKey::EFFECTIVE_PROMOTION);
//        print_r($data);die;
        $this->view->disable();
//        $client = new Client('http://172.16.128.252:81/promotion/promotion',false);
//        $client = new Client('http://192.168.48.135/promotion/promotion',false);
        $client = new Client('http://192.168.48.135/service/sku',false);
//        $client = new Client('http://119.29.180.155:81/service/sku',false);
//        $client = new Client('http://119.29.180.155:81/promotion/promotion',false);
//        $client = new Client('http://10.104.231.72:81/service/sku',false); //运营后台(内外网)
//        $client = new Client('http://10.66.214.120:81/service/sku',false); //负载均衡(生产)
//        $client = new Client('http://10.66.214.120:81/promotion/promotion',false); //负载均衡(生产)
//        $client = new Client('http://10.135.29.205:81/service/sku',false); //soa服务
//        $client = new Client('http://10.104.153.138:81/service/sku',false); //stg
//        $client = new Client('http://10.104.153.138:81/promotion/promotion',false);  //stg
//        $client = new Client('http://119.23.155.139:81/promotion/promotion',false); // 宝岛
//        $client = new Client('http://119.23.154.157:81/service/sku',false); // 宝岛
        $param = [
//            'goods_id' => '8027843,8000777,8000784,4,6,7000005,5000001,5000002,5000003,5000004,5000005,8025518,8027133,8025540',
//            'goods_id' => '9901579,8025338,8017906,8017911,8025347,6032689,5000001,5000002,5000003,5000004,5000005,8025518,8027133,8025540',
//            'goods_id' => 4,
//            'goods_id' => 6033399,
//            'goods_id' => '8020598',
//            'goods_id' => '7000005',
//            'goods_id' => '8017699',
//            'goods_id' => '8003548',
//            'brand_id' => 1,
//                'goods_id' => '8001349',
//                'goods_id' => '8001221',
//                'goods_id' => '8026964',
//                'goods_id' => '8020239',
//                'goods_id' => '8017691',
//                'goods_id' => '8020474',
//                'goods_id' => '8029375',
//                'goods_id' => '8025056',
//                'goods_id' => '8026447',  //限时优惠(有会员标签的)
//                'goods_id' => '8027843',  //限时优惠(有会员标签的)
    //            'goods_id' => '8025024', //青岛测试
    //            'goods_id' => '8027843', //,8028252,8028242
//          'goods_id' => '8028251', // 问题商品 (优惠券)
//                'goods_id' => '8025056',  // 套餐问题
//                'goods_id' => '8027388',  //
//                'goods_id' => '8018277',  //
//                'goods_id' => '8020862',  //
//                'goods_id' => '9904242',  //
//                'goods_id' => '9904243',  //
//                'goods_id' => '8020862',  //
//                'goods_id' => '8000575',  //
//                'goods_id' => '8001280',  //
//            'goods_id' => '8028254',
//            'sku_price' => 300,  // 测试限时优惠价
//            'user_id' => 646839,
//            'user_id' => 610385,  // 测试58的标签价
//            'user_id' => 613912,  // 测试58的标签价
//            'user_id' => 112233,  // 测试58的标签价
//            'user_id' => 24, //
//            'user_id' => 604286, // myself
//            'is_temp' => 0,
            'platform' => 'pc',
            'channel_subid' => 95,
            'udid' => '123213123',

            'userId' => 604286, // myself
            'categoryId' => 584, // myself
            'isTemp' => 0, // myself
            'searchAttr' => '', // myself
            'pageStart' => 1,
            'pageSize' => 10,
            'downPrice' => 1,
            'upPrice' => 100000,
        ];
        // 搜索列表
//        $result = $client->getPcKeywordList(array(
//            'platform' => 'pc',
//            'channel_subid' => 91,
//            'udid' => 123,
//            'searchName' => '0', // myself
//            'userId' => 604286, // myself
//            'isTemp' => 0,
//            'pageStart' => 1,
//            'pageSize' => 10,
//        ));
        // 活动凑单列表  getPcCollectList    getCollectList
//        $result = $client->getPcCollectList(array(
//            'platform' => 'pc',
//            'channel_subid' => 89,
//            'udid' => 123,
////            'categoryId' => 10,
//            'pageStart' => 1,
//            'pageSize' => 10,
//            'promotionId' => 539,
////            'userId' => 1489384202526,
//            'userId' => 604286, // myself
////            'userId' => 437739, // myself
////            'userId' => 542728, // myself
//            'isTemp' => 0,
//            'downPrice' => 0,
//            'upPrice' => null,
//            'typeStatus' => 0,
//            'type' => 'all',
//            'searchAttr' => '',
//        ));
//        print_r($result);die;
//        $result = $client->getPcKeywordList(array(
//            'platform' => 'pc',
//            'channel_subid' => 95,
//            'udid' => 123,
//            'searchName' => '同仁堂', // myself
//            'userId' => 1234, // myself
//            'isTemp' => 0,
//            'pageStart' => 3,
//            'pageSize' => 40,
//        ));
//        print_r($result);die;

//        $result = $client->getPcKeywordList(array(
//            'userId' => 438080,
//            'isTemp' => 0,
//            'searchName' => '1', //血压计
//            'searchAttr' => '',
//            'type' => '',
//            'promotionType' => '5,10',
//            'pageStart' => 1,
//            'pageSize' => 30,
//            'platform' => 'pc',
//            'channel_subid' => 95,
//            'udid' => 9111,
//        ));
//        $result = $client->getPromotionInfoByGoodsId($param);
        $result = $client->getPcCategoryList($param);
//        $result = $client->getCategoryList($param);
//        $result = $client->getDiscountedPrice($param);
//        $result = $client->aaa($param);
//        $result = $client->getLimitTimeInfo($param);
//        $result = $client->getPromotionsByBrandId($param);
        print_r($result);
    }

    public function mamaAction()
    {
        $this->view->disable();
        $client = new Client('http://119.29.180.155:81/promotion/promotion',false);
        $param = array
        (
            //'channel_subid' => 89,
            'platform' => 'app',
            //'udid' => '4b68142e3f915b1366cb5d4f120b763b232',
            'user_id' => 420826,
            'is_temp' => 0,
            'goods_id_list'=> array(
                7000002=>10,8005233=>22,8025056=>322
            )
        );

        /*$client = ShopService::getInstance();
        $result = $client->shoppingCart($param);*/
//        $client = PromotionService::getInstance();
        $result = $client->getGoodsLimitOffer($param);
        echo "<pre>";
        print_r($result);exit;
    }

    /**
     * 短信测试接口
     */
    public function smsAction()
    {
        $this->view->disable();

        $templateId = $this->request->get('template_code');

        $phone = $this->request->get('phone');
        $param = $this->request->get('param');
        $client_code = $this->request->get('client_code');

        $params = [];

        if(empty($param) === false){

            $temp = explode('|',$param);
            foreach ($temp as $item){
                $value = explode(',',$item);
                $params[$value[0]] = $value[1];
            }
        }
       // print_r($_SERVER);exit;
        $environment = [
            'ip' => $this->request->getClientAddress(true),
            'session_id' => $this->request->get('session_id'),
            'user_agent' => $this->request->getUserAgent(),
            'captcha'=> $this->request->get('captcha')
        ];

        $result = SmsService::getInstance()->send($phone,$templateId,$client_code,$environment,$params);

        $this->response->setJsonContent($result)->send();
    }

    public function passwordAction()
    {
        $this->view->disable();
        $fun = function ($pw_length = 8)
        {
            $randpwd = '';
            for ($i = 0; $i < $pw_length; $i++)
            {
                $randpwd .= chr(mt_rand(33, 126));
            }
            return $randpwd;
        };
        print_r($fun(12));
    }

    //测试购物车列表接口
    public function addAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.48.135/service/shop',false);
//        $client = new Client('http://172.16.128.252:81/service/shop',false);
//        $client = new Client('http://192.168.48.135/service/order',false);
//        $client = new Client('http://10.104.153.138:81/service/shop',false);  //stg
//        $client = new Client('http://10.104.153.138:81/service/order',false); //stg
//        $client = new Client('http://10.104.179.210:81/service/shop',false); //stg
//        $client = new Client('http://10.104.179.210:81/service/order',false); //stg
//        $client = new Client('http://10.66.214.120:81/service/shop',false); //负载均衡(生产)
//        $client = new Client('http://10.66.214.120:81/service/order',false); //负载均衡(生产)
        $param = [
            'platform' => 'wap',
//            'user_id' => 634973,
//            'user_id' => 539768,  // 58库的
//            'user_id' => 604286,  // myself
            'user_id' => 543351,  // yu
            'is_temp' => 0,
            'channel_subid' => 91,
        ];
        //$result = $client->getPromotionInfoByGoodsId($param);
         //购物车列表  getShoppingCartGoodsCounts    shoppingCart
        $result = $client->shoppingCart([
//            'user_id' => 542728,
//            'user_id' => 646832,
            'user_id' => 604286, // myself
//            'user_id' => 586639,
//            'user_id' => 1499394155928,
//            'user_id' => 24, // myself
//            'user_id' => 610385, // long
            'is_temp'=> 0,
            'type'=>2,
            'platform'=>'pc',
            'channel_subid'=> '89',
            'udid'=> '13213213',
        ]);
        print_r($result);die;

        // 确认订单  confirmGlobalOrder  confirmOrder
//        $result = $client->confirmOrder([
////            'user_id' => 542728,
////            'user_id' => 604286, //myself
//            'user_id' => 765679, //
////            'user_id' => 543351, //yu
////            'user_id' => 594477, //
////            'user_id' => 405414, // huo hai
////            'user_id' => 24, // huo hai
////            'user_id' => 543351, // yuan
//            'is_temp'=> 0,
////            'record_id'=> '81090',
////            'address_id'=> '7990',
//            'express_type'=> 0,
//            'is_balance'=> '0',
//            'shop_id'=> '6',
//            'payment_id'=> '3',
////            'o2o_time'=> '1488178800',
//            'is_first'=> '0',
//            'platform'=> 'wap',
//            'channel_subid'=> '91',
//            'udid'=> '95'
//        ]);
//        print_r($result);die;

        // 提交订单
//        $result = $client->commitOrder([
//            'user_id' => 604286,//542728,604286,586639
//            'is_temp' => 0,
//            'address_id'=> 72431,//7990,72677 72317-stg  72431-58
////            'buyer_message'=> 'fdsafdsa',
////            'coupon_sn'=> '3333',
//            'coupon_sn'=> '',
//            'record_id'=> '',
//            'is_global'=> 0,
//            'payment_id'=> 3,
//            'express_type'=> 0,
////            'o2o_time'=> 1482663600,
//            'callback_phone'=> 13527327311,
////            'shop_id'=> 3,
//            'invoice_type'=> 0,
////            'invoice_header'=> "",
//            'invoice_content_type'=> 0,
//            'is_balance'=> 0,
////            'pay_password'=> md5('123456'),
//            'pay_password'=> 'c33367701511b4f6020ec61ded352059',
////            'callback_phone'=> '13450223260',
//            'platform'=> 'pc',
//            'channel_subid'=> '95',
//            'udid'=> 'c9b31154-fd5a-35ff-8a27-5f4a3f3bb278',
//        ]);

        // 提交订单2
//        $result = $client->commitOrder([
//            'user_id' => 646832,//542728,604286,586639
//            'is_temp' => 0,
//            'address_id'=> 72128,//7990,72677 72317-stg  72431-58
//            'buyer_message'=> '',
//            'record_id'=> 0,
//            'is_global'=> 0,
//            'payment_id'=> 0,
//            'express_type'=> 3,
//            'o2o_time'=> 1492444800,
//            'shop_id'=> 0,
//            'invoice_type'=> 0,
//            'invoice_header'=> "",
//            'invoice_content_type'=> 0,
//            'is_balance'=> 1,
//            'pay_password'=> '4297f44b13955235245b2497399d7a93',
//            'callback_phone'=> 0,
//            'ordonnance_photo'=> '',
//            'machine_sn'=> '',
//            'channel_name'=> '',
//            'platform'=> 'wap',
//            'channel_subid'=> '91',
//            'udid'=> 'c9b31154-fd5a-35ff-8a27-5f4a3f3bb278',
//        ]);


         //加入购物车
        $result = $client->addGoodsToCart([
//            'user_id' => 539768,
//            'user_id' => 604286, //myself
            'user_id' => 765679, //
//            'user_id' => 112233,
            'is_temp'=> 0,
//            'goods_id'=> 8001349,
//            'goods_id'=> 7000002,
//            'goods_id'=> 8025518,
//            'goods_id'=> 8001221,
//            'goods_id'=> 8013282,
            'goods_id'=> 8017907, //8025026
            'goods_number' => 1,
            'platform'=> 'pc',
            'channel_subid'=> '95'
        ]);
        //$result = $client->test($param);
        print_r($result);
    }

    //测试购物车的促销列表接口
    public function goodsAction()
    {
        $this->view->disable();
//        $client = new Client('http://192.168.48.135/service/sku',false);
//        $client = new Client('http://192.168.48.135/promotion/promotion',false);
//        $client = new Client('http://119.23.155.139:81/service/sku',false); // 宝岛
        $client = new Client('http://119.23.154.157:81/service/sku',false); // 宝岛
        $param = [
            'sku_id' => 8030553,
//            'goods_id' => 7000005,
            //'promotion_type' => '5,10,15,20,30,35,40',
            'platform' => 'wap',
            'user_id' => 10011,
            'is_temp' => 0,
            'channel_subid' => 95,
            'udid' => 11,
        ];
//        $result = $client->getCollectList(array(
//            'userId' => 542728,
//            'isTemp' => 0,
//            'platform' => 'app',
//            'channel_subid' => 90,
//            'udid' => 11,
//            'type' => 'hot',
//            'typeStatus' => 0,
//            'pageStart' => 1,
//            'pageSize' => 6,
////            'promotionId' => 119,
//            'promotionId' => 107,
//        ));
//        $result = $client->getPromotionInfoByGoodsId($param);
        //$result = $client->getPromotionGoodsList($param);
//        $result = $client->getPromotionGoodsInfoById($param);
        $result = $client->getSku($param);
        print_r($result);
    }

    //测试购物车列表接口
    public function testAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.48.135/service/shop',false);
        $param = [
            'platform' => 'pc',
            'ids'=> '44590,44592,44588,44587,44589',
            'user_id' => 634973,
            'is_temp' => 0,
        ];
        $result = $client->shoppingCart($param);
//        $result = $client->confirmOrder($param);
        print_r($result);
    }

    //测试删除订单接口
    public function deleteOrderAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.11.128/order/index', false);
        $param = [
            'order_sn'=>'9120160723311741943',
            'user_id'=>420525,
        ];
        $res=$client->deleteOrder($param,'pc');
        echo '<pre>';
        print_r($res);
    }

    //测试取消订单接口
    public function cancelOrderAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.11.128/order/index', false);
        $param = [
            'order_sn'=>'9120160723311741943',
            'user_id'=>420525,
        ];
        $res=$client->cancelOrder($param,'pc');
        echo '<pre>';
        print_r($res);
    }

    //测试切换优惠券接口
    public function changeCouponAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.48.135/service/coupon',false);
        $param = [
            'platform' => 'pc',
            'ids'=> '44590,44592,44588,44587,44589',
            'user_id' => 634973,
            'coupon_sn' => '',
        ];
        //$result = $client->getPromotionInfoByGoodsId($param);
        $result = $client->changeCoupon($param);
        print_r($result);
    }

    //测试评论列表接口
    public function commAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.48.135/service/sku',false);
        $param = [
            'platform' => 'pc',
            'goods_id'=> '8013106',
            'page'=> '1',
            'pageSize'=> '10',
            'type'=> 'all',
        ];
        $result = $client->getCommentList($param);
        print_r($result);
    }

    //测试pc同类推荐接口
    public function sameAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.48.135/service/sku',false);
        $param = [
            'categoryId' => '21',
        ];
        $result = $client->getSameSku($param);
        print_r($result);
    }

    //测试订单相关接口
    public function orderAction()
    {
        $this->view->disable();
//        $client = new Client('http://172.16.128.252:81/service/order',false);
//        $client = new Client('http://172.16.128.252:84/service/order',false);
        $client = new Client('http://192.168.48.135/service/order',false);
//        $client = new Client('http://192.168.48.135/promotion/promotion',false);
//        $client = new Client('http://119.29.180.155:81/service/order',false);
//        $client = new Client('http://10.104.153.138:81/service/order',false); //stg
//        $client = new Client('http://10.66.214.120:81/service/order',false); //负载均衡
//        $client = new Client('http://10.104.231.72:81/service/order',false); //运营后台(内外网)
//        $client = new Client('http://10.135.29.205:81/service/order',false); //soa(只能连内网)
//        $client = new Client('http://10.29.255.125:81/service/order',false); //宝岛
//        $client = new Client('http://10.30.3.228:81/service/order',false); //宝岛
//        $client = new Client('http://119.23.155.139:81/service/order',false); //宝岛

//        $result = $client->confirmOrder([
//            'user_id' => 542728,//542728,
//            'is_temp'=> 0,
//            'record_id'=> '460737',
//            'address_id'=> '174134',//'174272',
//            'express_type'=> 3,
//            'is_balance'=> '1',
//            'payment_id'=> '0',
//            'o2o_time'=> '1488178800',
//            'is_first'=> '0',
//            'platform'=> 'pc',
//            'channel_subid'=> '89',
//            'udid'=> '95'
//        ]);
//        print_r($result);die;

        //海外购提交订单 commitGlobalOrder   commitOrder
//        $result = $client->commitOrder([
//            'user_id' => 10001,
//            'payment_id'    =>  0,
//            'express_type'  =>  0,
//            'invoice_content_type'  =>  16,
//            'invoice_type'  =>  1,
//            'is_balance'  =>  1,
//            'pay_password'=> md5('123456'),
//            'coupon_sn' =>  '',
//            'platform'=>'app',
//            'channel_subid'=> '89',
//            'address_id'    =>  '72695',
//            'udid'    =>  '72695',
////            'allowComment'  =>  1,
//            // 'time' => 1485349200
//        ]);
//        print_r($result);die;

        // 确认订单页面  confirmOrder confirmGlobalOrder
//        $result = $client->confirmOrder([
////            'user_id' => 542728,
////            'user_id' => 543351, //yu
//            'user_id' => 604286, //
//            'is_temp'=> 0,
////            'coupon_sn'=> '161029004075',
////            'address_id'=> '7990',
//            'express_type'=> '1',
//            'is_use_balance'=> '0',
//            'shop_id'=> '6',
//            'payment_id'=> '0',
//            'o2o_time'=> '1488330000',
//            'is_first'=> '1',
//            'platform'=> 'app',
//            'channel_subid'=> '91',
//            'udid'=> '911111',
//        ]);
//        print_r($result);die;

        $param = [
//            'user_id' => 634973,
//            'user_id' => 614053, //ting  18011905296
//            'user_id' => 437739, //hu  15975363936
//            'user_id' => 634904, //ming  15920526709
//            'user_id' => 614056, //zhen  18269286499
//            'user_id' => 543351, //yuan  15975363936
//            'user_id' => 542593, //跨境物流信息
//            'user_id' => 591218,  //hui
//            'user_id' => 601749,  //物流
//            'user_id' => 610397,  //li yu ting 18014725836 58
//            'user_id' => 582097,  //luo 13928775702
//            'user_id' => 405565,  //ping
//            'user_id' => 614028,  //chen ying 17727698819
//            'user_id' => 28,  //
//            'user_id' => 542728,  // ke
//            'user_id' => 543351,  //yu  15767678572
//            'user_id' => 438080,  //yu
//            'user_id' => 646739,  //hong 取消订单测试
//            'user_id' => 372129,  //chen juan
//            'user_id' => 1018215,  //
//            'user_id' => 635047,  //yu ting 15987654322
//            'user_id' => 405565,  //
//            'user_id' => 600617,  //deng
//            'user_id' => 610385,  //
            'user_id' => 372129,  //
//            'user_id' => 647013,  //ma wei hua 13560466484   58
//            'user_id' => 647005,  //ma wei hua 13560466484  stg
//            'user_id' => 602644,  //li yu ting 13824454968  stg
//            'user_id' => 10001,  //baodao test
//            'user_id' => 582097,  // luo 13928775702
//            'user_id' => 604286,  //myself
//            'user_id' => 463244,  //
//            'user_id' => 102010,  //
//            'user_id' => 591218,
//            'user_id' => 542593,
//            'user_id' => 614028,   // 17727698819
//            'order_sn' => '95170310140708882156',
//            'order_sn' => '952017062219332261843',
//            'order_sn' => 'G95170213102814201822',
//            'order_sn' => '1512050000002109',
//            'order_sn' => 'G951607271408169013',
//            'order_sn' => 'G95170313152535026135', //物流信息 542593
//            'order_sn' => '9020160811500609294', // 370640
//            'order_sn' => '9120170222502834210', // 438080
//            'order_sn' => '95170221090637371233',  // 物流信息 591218
//            'order_sn' => '9020160929023933944',  // 物流信息 601749
//            'order_sn' => '9020160811184690464',  //
//            'order_sn' => '892017052416134527164',  // 614056
//            'order_sn' => '912017052417233476207',  // 614056
//            'order_sn' => '9120170302485806773',  // 614056
//            'order_sn' => '9120170301262997983',  //
//            'order_sn' => '9120170215035676998',  //610397
//            'order_sn' => '9120170308310741251',  //
//            'order_sn' => '952017052714400481115',  //582097
//            'order_sn' => '952017053109520049312',  //582097
//            'order_sn' => '892017053115321524373',  //647013
//            'order_sn' => '892017060115512095389',  //
//            'order_sn' => '912017052714343861154',  // 405565
//            'order_sn' => '892017060314432567373',  // 28
//            'order_sn' => '952017060216321642968',  //
//            'order_sn' => '912017060510094045701',  // yu 635047
//            'order_sn' => '912017060514482682308',  //
//            'order_sn' => '952017060519005707158',  // 582097
//            'order_sn' => '912017060811462758791',  // 635047
            'order_sn' => 'G95170704175831060439',  //
            'userId' => '370640',  // 614056
            'orderId' => '9020160618230085700',  // 614056
            'isGlobal' => '0',  // 614056
//            'order_sn' => '9120170309113727518',  // 610397 yu
//            'order_sn' => '95170223113709329945', //hui
//            'order_sn' => '9120170223562827288',
//            'order_sn' => '912017032314101741705',
//            'order_sn' => '95170330181445571716', // 取消订单测试
//            'order_sn' => '952017041315591899694', // 物流信息测试
//            'order_sn' => '8520170316375915244', //
//            'order_sn' => 'G95170217140719874835', //
//            'order_sn' => '912017052714343864483', //
//            'order_sn' => '912017052714343861154', //
//            'order_sn' => '912017052714343846788', //
            'reason' => '价格波动',
//            'user_id' => '646830',
            'service_sn' => '1706223746429',
            'explain' => '哎哎，价格变了',
            'images' => '',
            'platform' => 'pc',
            'channel_subid' => 89,
            'udid' => '12312321',
            'pageStart' => 1,
            'pageSize' => 10,
            'status' => 'all',  //evaluating
        ];

        /*$param = [
            'user_id' => "543351",
            'channel_subid' => 91,
            'platform'=> "wap",
            'pageStart' => 1,
            'pageSize'=> 10,
            'status' =>"all",
        ];*/
//        $result = $client->cancelOrder($param);
//        $result = $client->orderApplyRefund($param);
//        $result = $client->cancelRefundApply($param);
//        $result = $client->checkRefundApply($param);
//        $result = $client->getOrderDetail($param);
//        $result = $client->getOrderListByStatus($param);
        $result = $client->getOrderListByStatusV2($param);
//        $result = $client->getShopDetail($param);
//        $result = $client->searchOrder($param);
//        $result = $client->getBaskComemnt($param); //移动端评价晒单
//        $result = $client->getOrderNumberByStatus($param);
//        $result = $client->getOrderNumberByStatusV2($param);
//        $result = $client->changeOrderStatus($param);
//        $result = $client->getOrderLogistics($param);
//        $result = $client->remindDeliveryOrder($param);
        print_r($result);
    }

    //测试订单相关接口
    public function addrAction()
    {
        $this->view->disable();
        $client = new Client('http://192.168.48.135/service/consignee',false);
//        $client = new Client('http://172.16.128.252:81/service/consignee',false);
//        $client = new Client('http://119.29.180.155:81/service/consignee',false);
//        $client = new Client('http://10.66.214.120:81/service/consignee',false);
        /**
         * @desc 添加收货地址信息
         * @param array $param
         *      -int user_id 用户id
         *      -string platform  平台
         *      -string consignee 收货人姓名
         *      -int province 省id
         *      -int city 市id
         *      -int county 区id
         *      -string address 详细地址
         *      -string telphone 联系电话
         *      -string fix_line 固定电话
         *      -string email 电子邮件
         *      -string zipcode 邮政编码
         * @return array [] 结果信息
         * @author 吴俊华
         */
        $param = [
            "platform" =>  "pc",
            "channel_subid" => 95,
            "user_id" => 2711,
            "consignee" => "dtfdsafa",
            "province" =>  "4",
            "city" =>  "53",
            "county" =>  "518",
            "idCard" => "440981198905041938",
            "address" => "wertyui",
            "telphone" => "17620820220",
            "fix_line" => "",
            "email" => "",
            "zipcode" => "",
            "default_addr" =>  "",
            "action" =>  "add",
        ];

//        $param = [
////            'user_id' => 2,
////            'user_id' => 604286,
////            'user_id' => 437739, //598792 437739
////            'user_id' => 405565, //萍姐1
//            'user_id' => 420372, //萍姐2
////            'action' => 'edit',
////            'consignee_id' => 9836,
////            'consignee_id' => 72431,
//            'consignee_id' => 72298, //72498 72298
//            'consignee' => '龚群娣', //焦泽生 龚群娣
//            'province' => 1,
//            'city' => 10,
//            'county' => 23,
//            'address' => '这是一个详细地址123456',
//            'telphone' => '13254354323',
//            'fix_line' => '020-21312132',
//            'email' => '32131221@fmail.com',
//            'zipcode' => 511340,
//            'platform' => 'app',
//            'channel_subid' => 89,
//            'udid' => '123213123',
//            'pid' => '57',
//            'default_addr' => 0,
//            'tag_id' => 4,
//            'idCard' => '44098219920505304X', //441283199008292390 44098219920505304X
//        ];
//        $result = $client->getConsigneeList($param);
//        $result = $client->editDefaultConsignee($param);
//        $result = $client->addOrEditConsignee($param);
//        $result = $client->getConsigneeInfo($param);
//        $result = $client->getChildZone($param);
//        $result = $client->deleteConsignee($param);
        $result = $client->getRegionList($param);
        print_r($result);die;
    }

    public function momAction()
    {
        $this->view->disable();
        $client = MomService::getInstance();
        //$client = new Client('http://172.16.128.252:81/service/mom',false);
        $data = $client->momApply(array(
            'user_id' =>'532035',
            'platform' => 'app',
            'udid' => "3C31FC9C-BA92-4CDC-A5B7-331E74FC4942",
            'idcard' => '452226199205015507',
            'birth_time' => 1488297599,
            'user_name' => 'aa',
            'mobile_channel' => 'app',
            'download_channel' => '89',
            'upload_image' => base64_encode(file_get_contents(__DIR__ . '/abc.jpg'))
        ));
        var_dump($data);exit;
    }

    public function sayAction()
    {
        $this->view->disable();
        //$client = MomService::getInstance();
        $client = new Client('http://172.16.128.252:81/service/mom',false);
        /*$param = array(
        "channel_subid" => 89,
        'from' => 'mom',
        "gift_id" => 10,
        "goods_id" => 7000466,
        "goods_number" => 1,
        "is_temp" => 0,
        'platform' => 'app',
        'tagPriceLimitCheck' => 0,
        'udid' => "3C31FC9C-BA92-4CDC-A5B7-331E74FC4942",
        "user_id" => '420826',
            'yfz_prescription_id'=>'00150225'
        );*/

        $param = array(
            'user_id' => '587913',
            'gift_id' => '11',
            'platform' => 'app'
        );
        $data = $client->getMomGiftDetail($param);
        print_r($data);exit;
    }

    public function authAction()
    {
        $this->view->disable();
        //$client = MomService::getInstance();
        $client = new Client('http://119.29.180.155:81/service/auth',false);
        //$client = new Client('http://www.home.baiy.local/service/auth',false);
        //$client = new Client('http://172.16.128.252:81/service/auth',false);
        $param = array(
            'user_id' => '587945',
            'username' => '何珍惠',
            'platform' => 'app',
            'idCard' => '452226199205015507'
        );

        /*$param = array(
            'user_id' => '58751',
            'username' => '王恒',
            'platform' => 'app',
            'idCard' => '360429199309151737'
        );*/
        $data = $client->idCardVerify($param);
        print_r($data);exit;
    }

    //测试优惠券接口
    public function couponAction()
    {
        $this->view->disable();
//        $client = new Client('http://192.168.48.135/service/shop',false);
        $client = new Client('http://192.168.48.135/service/coupon',false);
//        $client = new Client('http://119.29.180.155:81/service/shop',false);
        // 领取优惠券
        $result = $client->addCoupon([
//            'user_id' => 542728,
            'user_id' => 604286, //myself
            'is_temp'=> 0,
            'coupon_sn'=> '170323003777',
            'platform'=> 'pc',
            'channel_subid'=> '95',
            'udid'=> '95'
        ]);
        print_r($result);
    }

    //测试优惠券接口
    public function stockAction()
    {
        $this->view->disable();
//        $client = new Client('http://192.168.48.135/service/shop',false);
//        $client = new Client('http://192.168.48.135/service/sku',false);
//        $client = new Client('http://119.29.180.155:81/service/sku',false);
        $client = new Client('http://10.104.153.138:81/service/sku',false); //stg
//        $client = new Client('http://172.16.128.252:81/service/sku',false);
//        $client = new Client('http://10.66.214.120:81/service/sku',false); //负载均衡(生产)
        // 获取商品可售库存
        $result = $client->getGoodsCanSaleStock([
            'goods_id'=> '8020465',
//            'goods_id'=> '8028245',
            'platform'=> 'wap',
            'channel_subid'=> '95',
            'udid'=> '891321312'
        ]);
        print_r($result);die;
    }

}