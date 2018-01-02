<?php
/**
 * 导入数据
 */

namespace Shop\Home\Controllers;

use Shop\Home\Datas\BaseData;

class ImportController extends \Phalcon\Mvc\Controller
{
    /**
     * 关于易复诊同步数据保存到redis的方案
     * 1、选择redis 数据库6
     * 2、使用redis 列表存储，根据订单状态作为列表的键名,值对应订单编号
     * 订单状态有：paying(待支付),shipping(支付成功),shipped (已发货),finished(已完成),canceled(取消订单)
     * 如：$redis->lPush('paying','892017********') 或 $redis->rPush('paying','892017********')
     */
    public function importOrderDataToRedisAction() {
        $this->view->disable();
        return;
        $this->cache->selectDb(6);
        $this->cache->flushDb(6);
        $whereParam = array(
            'column' => "order_sn,status",
            'table'  => "\\Shop\\Models\\BaiyangOrder",
        );
        $list = BaseData::getInstance()->getData($whereParam);
        foreach ($list as $value) {
            $this->cache->rPush($value['status'], $value['order_sn']);
        }
        echo 'success!';
    }
}