<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/21 0021
 * Time: 上午 10:17
 */

namespace Shop\Home\Controllers;

use Shop\Home\Services\PromotionService;
use Shop\Home\Services\ShopService;
use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\GoodsService;
use Shop\Home\Services\ConsigneeService;

class PromotionController extends \Phalcon\Mvc\Controller
{
    /**
     * @desc 调试方法
     * @author 吴俊华
     */
    public function infoAction()
    {
        $this->view->disable();
        echo phpinfo();die;
    }

    /**
     * @desc 调试方法
     * @author 吴俊华
     */
    public function testAction()
    {
        $this->view->disable();
        $param = [
            'platform' => 'app',
            'channel_subid' => 90,
            'udid' => '13123123213',
        ];
        $data = ConsigneeService::getInstance()->getRegionList($param);
        print_r($data);die;
    }

    /**
     * @desc 调试方法
     * @author 吴俊华
     */
    public function proAction()
    {
        $this->view->disable();
        $param = [
            'promotion_type' => '35',
            'platform' => 'pc',
            'channel_subid' => 95,
            'udid' => '13123123213',
        ];
        $data = PromotionService::getInstance()->getPromotionGoodsInfoByType($param);
        print_r($data);die;
    }

    /**
     * @desc 调试方法
     * @author 吴俊华
     */
    public function addrAction()
    {
        $this->view->disable();
        $param = [
            'pid' => 1,
            'platform' => 'app',
            'channel_subid' => 90,
            'udid' => '13123123213',
        ];
        $data = ConsigneeService::getInstance()->getChildZone($param);
        print_r($data);die;
    }

    /**
     *  @desc 发布PromotionService对象
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

    public function sayAction()
    {
        // 导出excel
        $this->view->disable();
        $arr = [1,2,4];
        $arr1 = [[1,2,3]];
        $this->excel->exportExcel($arr,$arr1,111,'xls');
    }

    public function shopAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(ShopService::getInstance());
        //$hprose->onBeforeInvoke = $this->say();
        $hprose->start();
    }

}