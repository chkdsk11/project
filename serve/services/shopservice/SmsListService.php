<?php

/**
 * 黑白名单设置
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Models\BaiyangSmsList;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Home\Listens\SmsBlackWhiteListListener;
use Phalcon\Events\Manager as EventsManager;

class SmsListService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    public static function getInstance() {
        if (empty(static::$instance)) {
            static::$instance = new SmsListService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('sms_list', new SmsBlackWhiteListListener());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * 黑白名单列表
     * @param type $param
     * @return array
     */
    public function getAll($param) {
        $arr = array();
        $where = "list_type = :list_type:";
        $arr['list_type'] = $param['option']['list_type'];
        if (!empty($param['option']['content'])) {
            $arr['content'] = $param['option']['content'];
            $where .= " AND (ip_address = :content: OR phone = :content:)";
        }
        //处理时间
        if (!empty($param['option']['selecttime'])) {
            if ($param['option']['selecttime'] == 1) { //最近一个月
                $starttime = date('Y-m-d H:i:s', strtotime('-1 month'));
            } else if ($param['option']['selecttime'] == 2) { //最近三个月
                $starttime = date('Y-m-d H:i:s', strtotime('-3 month'));
            } else if ($param['option']['selecttime'] == 3) { //最近半年
                $starttime = date('Y-m-d H:i:s', strtotime('-6 month'));
            } else if ($param['option']['selecttime'] == 4) { //最近一年
                $starttime = date('Y-m-d H:i:s', strtotime('-12 month'));
            } else if ($param['option']['selecttime'] == 5) { //自选时间
                if (!empty($param['option']['starttime'])) {
                    $starttime = $param['option']['starttime'];
                }
                if (!empty($param['option']['endtime'])) {
                    $endtime = $param['option']['endtime'];
                }
            }
            //条件
            if (isset($starttime) && $starttime) {
                $arr['starttime'] = $starttime;
                $where .= " AND create_time >= :starttime:";
            }
            if (isset($endtime) && $endtime) {
                $arr['endtime'] = $endtime;
                $where .= " AND create_time <= :endtime:";
            }
        }

        $count = BaseData::getInstance()->count('\Shop\Models\BaiyangSmsList', $arr, $where);
        if (!$count) {
            return array(
                'status' => 'error',
                'list' => [],
                'page' => ''
            );
        }
        //分页
        $pages['page'] = $param['page']; //当前页
        $pages['counts'] = $count;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $page = $this->page->pageDetail($pages);
        //获取列表
        $param1 = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangSmsList',
            'where' => "WHERE " . $where,
            'bind' => $arr,
            'order' => 'ORDER BY create_time desc',
            'limit' => "LIMIT {$page['record']},{$page['psize']}"
        );
        $result = BaseData::getInstance()->getData($param1);
        return array(
            'status' => 'success',
            'list' => $result,
            'page' => $page['page']
        );
    }

    /**
     * 添加黑白名单
     * @param type $param
     * @return type
     */
    public function addList($param) {
        if (!empty($param['ip_address']) || !empty($param['phone'])) {
            $whereStr = "list_type = :list_type:";
            $conditions['list_type'] = $param['list_type'];
            if (!empty($param['ip_address'])) {
                $regIp = "/((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/";
                if (!preg_match($regIp, $param['ip_address'])) {
                    return $this->arrayData('IP格式不正确', '', [], 'error');
                }
                $whereStr .= " AND ip_address = :ip_address:";
                $conditions['ip_address'] = $param['ip_address'];
            }
            if (!empty($param['phone'])) {
                $isMob = "/^1[3,4,5,7,8]{1}[0-9]{9}$/";
                if (!preg_match($isMob, $param['phone'])) {
                    return $this->arrayData('手机号格式不正确', '', [], 'error');
                }
                $whereStr .= " AND phone = :phone:";
                $conditions['phone'] = $param['phone'];
            }
            //两个都存在的时候
            if (!empty($param['ip_address']) && !empty($param['phone'])) {
                $whereStr = "list_type = :list_type: AND (ip_address = :ip_address: OR phone = :phone:)";
            }
            //验证是否已经添加
            $row = BaseData::getInstance()->select('*', '\Shop\Models\BaiyangSmsList', $conditions, $whereStr);
            if ($row) {
                return $this->arrayData('该名单已存在', '', [], 'error');
            }
            //添加数据
            $result = new BaiyangSmsList();
            $result->list_type = $param['list_type'];
            $result->ip_address = $param['ip_address'];
            $result->phone = $param['phone'];
            $result->list_info_type = 1;
            $result->create_time = date('Y-m-d H:i:s');
            $result->create_at = $_SESSION['user_id'];
            if ($result->create()) {
                //处理监听
                if ($param['list_type'] == 'black') {
                    $this->_eventsManager->fire('sms_list:addBlackAfter', $this, $result);
                } else {
                    $this->_eventsManager->fire('sms_list:addWhiteAfter', $this, $result);
                }
                return $this->arrayData('添加成功');
            } else {
                return $this->arrayData('添加失败！', '', '', 'error');
            }
        }
        return $this->arrayData('IP或手机号至少填写一项', '', '', 'error');
    }

    /**
     * 解除黑白名单
     * @param type $list_id
     * @return array
     */
    public function delList($list_id) {
        $list_id = (int) $list_id;
        $object = BaiyangSmsList::findFirst([
                    'list_id = :list_id:',
                    'bind' => array(
                        'list_id' => $list_id
                    )
        ]);
        if ($object) {
            if ($object->delete()) {
                //处理监听
                if ($object->list_type == 'black') {
                    $this->_eventsManager->fire('sms_list:deleteBlackAfter', $this, $object);
                } else {
                    $this->_eventsManager->fire('sms_list:deleteWhiteAfter', $this, $object);
                }
                return $this->arrayData('解除成功！');
            }
            return $this->arrayData('解除失败！', '', '', 'error');
        }
        return $this->arrayData('无该名单人员', '', '', 'error');
    }

}
