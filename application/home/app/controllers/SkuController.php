<?php
/**
 * Created byPhpStorm .
 * User: Administrator
 * Date: 2016/9/21 0021
 * Time: 上午 10:17
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\SkuService;


class SkuController extends \Phalcon\Mvc\Controller
{
    /**
     *  测试hprose
     */
    public function indexAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(SkuService::getInstance());
        $hprose->start();
    }

    public function getSubjectAction()
    {
        $this->view->disable();
        $param = [
            'subject_id' => 25,
            'product_ids' => '80020426,8028249,8034079,8034073',
            'userId' => 0,
            'isTemp' => 1,
            'platform' => 'wap',
            'channel_subid' => 91
        ];
        $reshutl = SkuService::getInstance()->getSubjectTagInfoById($param);
        echo '<pre>';
        print_r($reshutl);
    }

    public function getCommentListAction()
    {
        $this->view->disable();
        $param = [
            'goods_id' => 8027948,
            'pageStart' => 1,
            'pageSize' => 10,
            'type' => 'all',
            'platform'=>'wap',
            'channel_subid'=>91
        ];
        $reshutl = SkuService::getInstance()->getCommentList($param);
        echo '<pre>';
        print_r($reshutl);
    }

}