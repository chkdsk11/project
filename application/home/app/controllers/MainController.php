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
use Shop\Models\CacheGoodsKey;

class MainController extends \Phalcon\Mvc\Controller
{
    public function SkuAction()
    {
        $this->view->disable();
        $client=new Client('http://192.168.17.130/sku',false);
        echo '<pre>';
        $param['sku_id'] = 8000498;
        $param['platform'] = 'pc';
        $param['user_id'] = 26;
        $param['is_temp'] = 1;
        $res = $client->getSku($param);
//        $res = $client->getSkuRule(11,'pc');
//        $res = $client->getRuleSku(11,'aa++dd','pc');
//        $param['goods_id']=array(4,8004703);
//        $param['data'] = 'Recommend';
//          $param['data'] = 'hot';
//        $res = $client->getSkuAll($param);
//        $res = $client->getHotRecommendSku($param);
//        $res = $client->getDetails($param);
//        $res = $client->getRuleAll($param['sku_id'],$param['platform']);
//        $res = $client->getSkuRuleAll($param);
//        $param['recall'] = '18320087680';
//        $param['nickname'] = '18320087680';
//        $param['uid'] = '2222';
//        $param['gid'] = '4';
//        $res = $client->RecallDoc($param);
        print_r($res);die;
//        var_dump($client->getData());
//        var_dump($client->test());
//        var_dump($client->test1());
//        var_dump($client->test2());
    }

    public function testAction()
    {
        $this->view->disable();
        $client = new Client('http://172.16.128.252:81/service/sku',false);
        echo '<pre>';
        $param['sku_id'] = 8611115;
        $param['platform'] = 'pc';
        $param['user_id'] = 26;
        $param['is_temp'] = 1;
        $res = $client->getSku($param);
        print_r($res);die;
    }

}