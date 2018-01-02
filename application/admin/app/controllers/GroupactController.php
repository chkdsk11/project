<?php
namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\GroupactService;
use Shop\Services\BaseService;

/**
 * 拼团活动Class
 * Created by PhpStorm.
 * User: yanbo
 * Date: 2017/5/19
 * Time: 11:12
 */

class GroupactController extends ControllerBase{

    public function initialize(){
        parent::initialize();
        $this->setTitle('拼团活动管理');
        // 给模板引擎添加自定义函数
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        /** @var \Phalcon\Mvc\View\Engine\Volt\Compiler $compiler */
        $compiler = $volt->getCompiler();
        $compiler->addFunction('intval', 'intval');
        $compiler->addFunction('is_array', 'is_array');
    }

    /**
     * 活动列表
     */
    public function listAction(){
        $seaData['gfastate'] = $this->getParam('gfastate','trim','');
        $seaData['goods'] = $this->getParam('goods','trim','');
        $seaData['gfanum'] = $this->getParam('gfanum','trim','');
        $seaData['gfa_user_type'] = $this->getParam('gfa_user_type','trim','');
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/groupact/list',
            'seaData' => $seaData
        );
        $result = GroupactService::getInstance()->getList($param);
        $this->view->setVars(array(
                'data' => $result,
                'seaData' => $seaData,
                'wap_url' => $this->config['wap_base_url'][$this->config['environment']]
            )
        );
        $this->view->pick('groupact/list');
    }

    /**
     * 添加活动
     * @return mixed
     */
    public function addAction(){
        if($this->request->isPost() || $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $res = GroupactService::getInstance()->addAct($param);
            return $this->response->setJsonContent($res);
        }
    }

    /**
     * 编辑活动
     */
    public function editAction(){
        if($this->request->isPost() || $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $res = GroupactService::getInstance()->editAct($param);
            return $this->response->setJsonContent($res);
        }else{
            $id = $this->getParam('id','int','');
            $show = $this->getParam('show','int','');
            $copy = $this->getParam('copy','int','');
            if(empty($id)){
                return $this->success( '参数缺失','/groupact/list','error',3);
            }
            $row = GroupactService::getInstance()->getAct($id);
            if(empty($row)){
                return $this->success( '该活动不存在','/groupact/list','error',3);
            }
            if($row['gfa_state'] == 3 && $show != 0 && $copy != 0){
                return $this->success( '取消的活动不能编辑','/groupact/list','error',3);
            }
            if ($show == 0 && $copy == 0) {
                if ($row['gfa_starttime'] <= time() && $row['gfa_endtime'] > time()) {
                    return $this->success( '不能编辑,活动已经开始！','/groupact/list','error',3);
                }
                if ($row['gfa_endtime'] <= time()) {
                    return $this->success( '不能编辑,活动已经结束！','/groupact/list','error',3);
                }
            }
            $row['goods_slide_images'] = json_decode($row['goods_slide_images']);
            $this->view->setVars([
                'data' => $row,
                'show' => intval($show),
                'copy' => intval($copy)
            ]);
        }
    }

    /**
     * 删除活动
     * @return mixed
     */
    public function delAction(){
        if($this->request->isPost() || $this->request->isAjax()) {
            $id = $this->request->getPost('id', 'int', '');
            $res = GroupactService::getInstance()->delAct($id);
            return $this->response->setJsonContent($res);
        }
    }

    /**
     * 取消活动
     * @return mixed
     */
    public function cancelAction(){
        if($this->request->isPost() || $this->request->isAjax()) {
            $id = $this->request->getPost('id', 'int', '');
            $res = GroupactService::getInstance()->cancelAct($id);
            return $this->response->setJsonContent($res);
        }
    }

    /**
     * 搜索商品
     * @return mixed
     */
    public function searchAction(){
        if($this->request->isAjax()){
            $goods = $this->request->getPost('goods','trim');
            $result = GroupactService::getInstance()->searchGoods($goods);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 上传图片
     * @return 返回json
     */
    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/group/');
            return $this->response->setJsonContent($res);
        }
    }

}