<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\BaseService;
use Shop\Services\AdaccesoriesService;

class AdaccesoriesController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();
        $this->service = AdaccesoriesService::getInstance();
    }

    public function adlistAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = $this->service->getAllad($param);
        $ad_position = $this->service->getPositions();
        $this->view->setVar('position',$ad_position['data']);
        $this->view->setVar('search',$data);
        $this->view->setVar('list',$list);
        $this->view->pick('adaccesories/index');
    }

    public function addAction(){

        if($this->request->isPost()){
            $param = $this->request->getPost();
            $result =  $this->service->add_ad($param);
            return $this->response->setJsonContent($result);
        }else{
            $ad_position = $this->service->getPositions();
            $this->view->setVar('ad_position',$ad_position['data']);
            $this->view->pick('adaccesories/add');
        }

    }

    public function editAction(){
        if($this->request->isAjax()){
            $param = $this->request->getPost();
            $param['id'] = $this->request->get('id');
            $result = $this->service->editData($param);
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', '');
        $result = $this->service->getDate($id);
        $this->view->setVars(array(
            'info' => $result['data'],
            'ad_position' => $result['ad_position']
        ));
        $this->view->pick('adaccesories/edit');
    }

    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request);
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }

    public function delAction(){
        if($this->request->isAjax()){
            $result =$this->service->delData((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

    public function cancelAction(){
        if($this->request->isAjax()){
            $result =$this->service->cancel((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }
    //搜索商品
    public function searchgoodsAction(){
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim');
            $return =$this->service->searchGoods($param);
            return $this->response->setJsonContent($return);
        }else{
            return $this->response->setJsonContent(array('code '=>1));
        }
    }
}