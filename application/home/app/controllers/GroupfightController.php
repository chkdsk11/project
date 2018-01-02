<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/27 0027
 * Time: 10:52
 */

namespace Shop\Home\Controllers;

use Shop\Home\Services\GroupFightOrderDetailService;
use Shop\Home\Services\GroupfightService;
use Shop\Home\Services\HproseService;


/**
 * 拼团接口公开
 * @package Shop\Home\Controllers
 */
class GroupfightController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(GroupfightService::getInstance());
        $hprose->start();
    }

    public function orderdetailAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(GroupFightOrderDetailService::getInstance());
        $hprose->start();
    }

    public function getGroupDetailedAction()
    {
        $this->view->disable();
        $params = [
            'user_id' => 614056,
            'platform' => 'wap',
            'channel_subid' => 91,
            'act_id' => 104
        ];
        $result = GroupfightService::getInstance()->getGroupDetailed($params);
        $this->response->setJsonContent($result)->send();
    }

    public function detailAction(){
        $this->view->disable();
        $params = [
            'user_id' => 635014,
            'platform' => 'wap',
            'channel_subid' => 91,
            'order_sn' => 9020170505550812209
        ];
        $result = GroupfightService::getInstance()->getOrderDetail($params);
        print_r($result);
    }

}