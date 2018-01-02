<?php

/**
 * 预警通知
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class SmsNotifyService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * 预警通知列表
     * @param type $param
     * @return type array
     */
    public function getAll($param) {
        $where = "user_state = 0";
        $paramCount = [];
        if (!empty($param['option']['content'])) {
            $paramCount['content'] = $param['option']['content'];
            $where .= ' AND (user_name = :content: OR phone = :content:)';
        }
        //数量
        $count = BaseData::getInstance()->count('\Shop\Models\BaiyangSmsAlarmNotify', $paramCount, $where);
        if (!$count) {
            return array(
                'res' => 'err',
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
            'table' => "\Shop\Models\BaiyangSmsAlarmNotify",
            'where' => empty($where) ? $where : 'WHERE ' . $where,
            'bind' => $paramCount,
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
     * 删除预警联系人
     * @param type $param
     * @return type array
     */
    public function delUser($param) {
        if (!empty($param['notify_user_id'])) {
            $whereStr = "notify_user_id = :notify_user_id:";
            $result = BaseData::getInstance()->delete('\Shop\Models\BaiyangSmsAlarmNotify', $param, $whereStr);
            return $result ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
        }
        return $this->arrayData('不存在该联系人', '', [], 'error');
    }

    /**
     * 添加预警通知人
     * @param type $param
     * @return type
     */
    public function addUser($param) {
        if (is_array($param) && empty($param) == false) {
            if (empty($param['user_name'])) {
                return $this->arrayData('通知人不可为空', '', [], 'error');
            }
            $isMob = "/^1[3,4,5,7,8]{1}[0-9]{9}$/";
            if (!preg_match($isMob, $param['phone'])) {
                return $this->arrayData('手机号格式不正确', '', [], 'error');
            }
            //判断是否已添加该手机号
            $res = array(
                'column' => '*',
                'table' => "\Shop\Models\BaiyangSmsAlarmNotify",
                'where' => "WHERE phone = :phone:",
                'bind' => array(
                    'phone' => $param['phone']
                )
            );
            $user = BaseData::getInstance()->getData($res, true);
            if ($user) {
                return $this->arrayData('该手机号已存在', '', [], 'error');
            }
            //添加数据
            $param['user_state'] = 0;
            $param['create_time'] = date('Y-m-d H:i:s');
            $param['create_at'] = $_SESSION['user_id'];
            $result = BaseData::getInstance()->insert('\Shop\Models\BaiyangSmsAlarmNotify', $param);
            return $result ? $this->arrayData('添加成功', '/SmsNotify/list') : $this->arrayData('添加失败', '', [], 'error');
        }
        return $this->arrayData('无添加数据', '', [], 'error');
    }

    /**
     * 获取短信预警开关
     * @return array
     */
    public function getStatus() {
        $param = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangConfig',
            'where' => 'WHERE config_sign = :config_sign:',
            'bind' => array('config_sign' => 'smsAlarm')
        );
        $result = BaseData::getInstance()->getData($param, true);
        return $result;
    }

    /**
     * 更新短信预警通知状态
     * @return array
     */
    public function updateStatus() {
        $param = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangConfig',
            'where' => 'WHERE config_sign = :config_sign:',
            'bind' => array('config_sign' => 'smsAlarm')
        );
        $result = BaseData::getInstance()->getData($param, true);
        if ($result) { //有则修改状态
            $columStr = "config_value = :config_value:";
            $whereStr = "id = :id:";
            $param1 = array(
                'id' => $result['id'],
                'config_value' => $result['config_value'] == 1 ? 0 : 1
            );
            $return = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangConfig', $param1, $whereStr);
        } else { //无则添加启动
            $param1 = array(
                'config_sign' => 'smsAlarm',
                'config_name' => '短信预警通知是否开启',
                'config_value' => '0',
                'explain' => '0开启 ，1不开启',
                'sort' => 0
            );
            $return = BaseData::getInstance()->insert('\Shop\Models\BaiyangConfig', $param1);
        }
        return empty($return) ? $this->arrayData('更新失败', '', '', 'error') : $this->arrayData('更新成功');
    }

}
