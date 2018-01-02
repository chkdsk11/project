<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/27 0027
 * Time: 10:52
 */

namespace Shop\Home\Controllers;

use Shop\Home\Services\GroupService;
use Shop\Home\Services\HproseService;

/**
 * 拼团接口公开
 * @package Shop\Home\Controllers
 */
class GroupController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(GroupService::getInstance());
        $hprose->start();
    }

    public function getOrderDetaildByOrderSnAction()
    {
        $this->view->disable();
        $result = GroupService::getInstance()->getOrderDetaildByOrderSn([]);

        $this->response->setJsonContent($result)->send();
    }

    public function getGroupOrderListAction()
    {
        $this->view->disable();

        $params = [
            'user_id' => 635014,
            'platform' => 'wap',
            'pageStart' => 1,
            'pageSize' => 15,
            'status' => 'all',
            'channel_subid' => 91
        ];
        $result = GroupService::getInstance()->getOrderList($params);

        $this->response->setJsonContent($result)->send();
    }

    public function getGroupActivityDetailedAction()
    {
        $this->view->disable();
        $params = [
            'user_id' => 411464,
            'platform' => 'wap',
            'channel_subid' => 91,
            'act_id' => 104
        ];
        $result = GroupService::getInstance()->getGroupActivityDetailed($params);
        $this->response->setJsonContent($result)->send();
    }

    public function getGroupFightAction()
    {
        $this->view->disable();
        $params = [
            'user_id' => 635014,
            'platform' => 'wap',
            'channel_subid' => 91,
            'fight_id' => 732
        ];
        $result = GroupService::getInstance()->getGroupFight($params);
        $this->response->setJsonContent($result)->send();
    }

    public function getGroupListAction()
    {
        $this->view->disable();
        $params = [
            'user_id' => 635029,
            'platform' => 'wap',
            'channel_subid' => 91,
            'page' => 1,
            'size' => 10
        ];
        $result = GroupService::getInstance()->getGroupList($params);
        $this->response->setJsonContent($result)->send();
    }

    public function getGroupFightListAction()
    {
        $this->view->disable();
        $params = [
            'user_id' => 635014,
            'platform' => 'wap',
            'channel_subid' => 91,
            'page' => 1,
            'size' => 10,
            'pageStart' => 1,
            'pageSize' => 10
        ];
        $result = GroupService::getInstance()->getGroupFightList($params);

        $this->response->setJsonContent($result)->send();
    }
}