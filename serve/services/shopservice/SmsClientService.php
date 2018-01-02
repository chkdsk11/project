<?php

/**
 * 短信或图形验证启用设置
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangSmsRelationship;
use Shop\Home\Listens\SmsCaptchaClientListener;
use Phalcon\Events\Manager as EventsManager;

class SmsClientService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    public static function getInstance() {
        parent::getInstance();
        if (empty(static::$instance)) {
            static::$instance = new SmsClientService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('sms_relationship', new SmsCaptchaClientListener());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * 短信\图形验证启用列表
     * @param type $param
     * @return array
     */
    public function getList($param) {
        //获取客户端渠道
        $client_table = "\Shop\Models\BaiyangSmsClient";
        $param1 = array(
            'column' => 'client_id,client_name,client_code,client_state',
            'table' => $client_table,
        );
        $client = BaseData::getInstance()->getData($param1);

        //数量
        $conditions = [];
        $whereStr = "";
        if (!empty($param['option']['template_name'])) {
            $whereStr .= 'template_name LIKE :template_name:';
            $conditions['template_name'] = '%' . $param['option']['template_name'] . '%';
        }
        $count = BaseData::getInstance()->count('\Shop\Models\BaiyangSmsTemplate', $conditions, $whereStr);
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
        //获取模板列表
        $param2 = array(
            'column' => 'template_id,template_name',
            'table' => '\Shop\Models\BaiyangSmsTemplate',
            'where' => empty($whereStr) ? $whereStr : 'WHERE ' . $whereStr,
            'bind' => $conditions,
            'limit' => "LIMIT {$page['record']},{$page['psize']}"
        );
        $result = BaseData::getInstance()->getData($param2);
        //获取模板关联验证
        $relation = [];
        if (!empty($result)) {
            $strids = "";
            foreach ($result as $k => $v) {
                $strids .= $v['template_id'] . ",";
            }
            $strids = rtrim($strids, ',');
            $param3 = array(
                'column' => 'template_id,client_id,is_enable_captcha,is_enable_client',
                'table' => '\Shop\Models\BaiyangSmsRelationship',
                'where' => empty($strids) ? $strids : "WHERE template_id IN ({$strids})",
            );
            $rels = BaseData::getInstance()->getData($param3);
            if (!empty($rels)) {
                foreach ($rels as $k => $v) {
                    $relation[$v['template_id']][$v['client_id']] = $v;
                }
            }
        }
        return array(
            'status' => 'success',
            'list' => $result,
            'client' => $client,
            'relation' => $relation,
            'page' => $page['page']
        );
    }

    /**
     *  修改启用禁用
     * @param type $param
     * @return type
     */
    public function editClient($param) {
        //判断该客户端验证是否开启
        $arr1 = array(
            'column' => 'client_id,client_state',
            'table' => '\Shop\Models\BaiyangSmsClient',
            'where' => 'WHERE client_id = :client_id:',
            'bind' => array(
                'client_id' => (int) $param['client_id']
            )
        );
        $client = BaseData::getInstance()->getData($arr1, true);
        if (!$client || $client['client_state'] == 1) {
            return $this->arrayData('该客户端验证已关闭，不可修改', '', '', 'err');
        }
        $relationship = BaiyangSmsRelationship::findFirst("template_id = {$param['template_id']} AND client_id = {$param['client_id']}");
        if ($relationship != false) { //修改，不要修改修改时间，修改时间为自动启用用
            if ($param['data_type'] == 'is_enable_captcha') {
                $relationship->is_enable_captcha = (int) $param['status'];
            } else {
                $relationship->is_enable_client = (int) $param['status'];
            }
            $result = $relationship->save();
        } else {
            $relationship = new BaiyangSmsRelationship();
            $relationship->template_id = (int) $param['template_id'];
            $relationship->client_id = (int) $param['client_id'];
            $relationship->create_time = date('Y-m-d H:i:s');
            $relationship->create_at = $_SESSION['user_id'];
            if ($param['data_type'] == 'is_enable_captcha') {
                $relationship->is_enable_captcha = (int) $param['status'];
            } else {
                $relationship->is_enable_client = (int) $param['status'];
            }
            $result = $relationship->create();
        }
        if ($result) { //创建或修改成功
            //获取该类处理监听
            $rel = BaiyangSmsRelationship::findFirst("id = {$relationship->id}");
            $this->_eventsManager->fire('sms_relationship:handle', $this, $rel);
            return $this->arrayData('更新成功', '', '', 'success');
        }
        return $this->arrayData('更新失败,请刷新重试', '', '', 'err');
    }

    /**
     * 修改短信\图形验证
     * @param type $param
     * @return array
     */
    public function editClient1($param) {
        //判断该客户端验证是否开启
        $arr1 = array(
            'column' => 'client_id,client_state',
            'table' => '\Shop\Models\BaiyangSmsClient',
            'where' => 'WHERE client_id = :client_id:',
            'bind' => array(
                'client_id' => (int) $param['client_id']
            )
        );
        $client = BaseData::getInstance()->getData($arr1, true);
        if (!$client || $client['client_state'] == 1) {
            return $this->arrayData('该客户端验证已关闭，不可修改', '', '', 'err');
        }

        //修改--有则修改，无则添加
        $arr2 = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangSmsRelationship',
            'where' => 'WHERE template_id = :template_id: AND client_id = :client_id:',
            'bind' => array(
                'template_id' => (int) $param['template_id'],
                'client_id' => (int) $param['client_id'],
            )
        );
        $relation = BaseData::getInstance()->getData($arr2, true);
        //处理数据
        var_dump($param);
        die;
        $relationship = new BaiyangSmsRelationship();
//        $res = [];
//        $columStr = "";
//        $whereStr = "";
        if ($param['data_type'] == 'is_enable_captcha') {
            $relationship->is_enable_captcha = (int) $param['status'];
//            $res['is_enable_captcha'] = $param['status'];
//            $columStr = "is_enable_captcha = :is_enable_captcha:";
        } else {
            $relationship->is_enable_client = (int) $param['status'];
//            $res['is_enable_client'] = $param['status'];
//            $columStr = "is_enable_client = :is_enable_client:";
        }
//        $res['template_id'] = (int) $param['template_id'];
//        $res['client_id'] = (int) $param['client_id'];

        if ($relation) { //修改
            $whereStr .= "template_id = :template_id: AND client_id = :client_id:";
            $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangSmsRelationship', $res, $whereStr);
        } else { //添加
            $relationship->template_id = (int) $param['template_id'];
            $relationship->client_id = (int) $param['client_id'];
            if ($param['data_type'] == 'is_enable_captcha') { //首次添加切为图形验证码时设置短信为关闭
                $relationship->is_enable_client = 1;
                //$res['is_enable_client'] = 1;
            }
            $result = $relationship->create();
            //$result = BaseData::getInstance()->insert('\Shop\Models\BaiyangSmsRelationship', $res);
        }
        if ($result) {
            return $this->arrayData('更新成功', '', '', 'success');
        }
        return $this->arrayData('更新失败,请刷新重试', '', '', 'err');
    }

}
