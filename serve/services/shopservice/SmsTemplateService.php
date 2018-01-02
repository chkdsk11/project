<?php

/**
 * 短信模板
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class SmsTemplateService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * 短信模板列表
     * @param type $param
     * @return type
     */
    public function getList($param) {
        //获取签名列表
        $param2 = array(
            'column' => 'signature',
            'table' => "\Shop\Models\BaiyangSmsTemplate",
            'where' => "GROUP BY signature"
        );
        $signs = BaseData::getInstance()->getData($param2);
        $signatures = array();
        if (!empty($signs)) {
            foreach ($signs as $k => $v) {
                $signatures[] = $v['signature'];
            }
        }
        //查询条件
        $where = '';
        $paramCount = [];
        if (!empty($param['option']['signature'])) {
            $where .= empty($where) ? "signature = :signature:" : " AND signature = :signature:";
            $paramCount['signature'] = $param['option']['signature'];
        }
        if (!empty($param['option']['contents'])) {
            $where .= empty($where) ? "(template_name LIKE :contents: OR content LIKE :contents:)" : " AND (template_name LIKE :contents: OR content LIKE :contents:)";
            $paramCount['contents'] = '%' . $param['option']['contents'] . '%';
            $param['option']['contents'] = '%' . $param['option']['contents'] . '%';
        }
        $count = BaseData::getInstance()->count('\Shop\Models\BaiyangSmsTemplate', $paramCount, $where);
        if (!$count) {
            return array(
                'res' => 'err',
                'list' => [],
                'page' => '',
                'signatures' => $signatures
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
            'table' => "\Shop\Models\BaiyangSmsTemplate",
            'where' => empty($where) ? $where : 'WHERE ' . $where,
            'bind' => $paramCount,
            'limit' => "LIMIT {$page['record']},{$page['psize']}"
        );
        $result = BaseData::getInstance()->getData($param1);
        return array(
            'status' => 'success',
            'list' => $result,
            'page' => $page['page'],
            'signatures' => $signatures
        );
    }

    /**
     *  获取模板详情
     * @param type $template_id
     * @return type
     */
    public function getInfo($template_id) {
        $param = array(
            'column' => '*',
            'table' => "\Shop\Models\BaiyangSmsTemplate",
            'where' => "WHERE template_id = :template_id:",
            'bind' => array(
                'template_id' => $template_id,
            )
        );
        $result = BaseData::getInstance()->getData($param, true);
        if ($result) {
            return array('status' => 'success', 'row' => $result);
        }
        return array('status' => 'err', 'info' => '无该模板');
    }

    /**
     * 修改模板
     * @param type $param
     * @return type
     */
    public function editTemplate($param) {
        if (empty($param['template_id']) || empty($param['signature']) || empty($param['content'])) {
            return $this->arrayData('都不可为空', '', '', 'error');
        }
        $param['modify_time'] = date('Y-m-d H:i:s');
        $param['modify_at'] = $_SESSION['user_id'];
        $columStr = $this->jointString($param, array('template_id'));
        $where = 'template_id=:template_id:';
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangSmsTemplate', $param, $where);
        return $result ? $this->arrayData('操作成功！') : $this->arrayData('操作失败！', '', '', 'error');
    }

}
