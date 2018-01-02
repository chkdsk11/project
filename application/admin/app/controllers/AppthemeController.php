<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\BaseService;
use Shop\Services\AppthemeService;


class AppthemeController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();
        $this->service =  AppthemeService::getInstance();
    }

    public function appthemeAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $data['channel']='89,90';
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = $this->service->getLists($param);
        $this->view->setVar('list',$list);
        $this->view->setVar('search',$data);

    }

    public function wapthemeAction(){
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $param['config_sign'] = 'displayAPPaccesories';
            $result =$this->service->editData($param);
            return $this->response->setJsonContent($result);
        }else{
            $param =[];
            foreach($this->request->get() as $k=>$v){
                $data[$k]  =   $this->getParam($k,'trim');
            }
            $data['channel']='91';
            $param['param'] = $data;
            $param['page']  =   $this->getParam('page','int',1);
            $param['url'] = $this->automaticGetUrl();
            $list = $this->service->getLists($param);
            $this->view->setVar('list',$list);
            $this->view->setVar('search',$data);
        }
    }

    public function wapeditAction(){
        $id = (int)$this->getParam('id', 'trim', '');
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $param['channel']=91;
            if($id>0){
                $param['theme_id']=$id;
                $result =$this->service->editData($param);
            }else{
                $result =$this->service->addData($param);
            }
            return $this->response->setJsonContent($result);
        }else{
            if($id>0){
                $result = $this->service->getData($id);
                $this->view->setVars(array(
                    'info' => $result
                ));
            }
        }
    }
    public function appeditAction(){
        $id = (int)$this->getParam('id', 'trim', '');
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            if($id>0){
                $param['theme_id']=$id;
                $result =$this->service->editData($param);
            }else{
                $result =$this->service->addData($param);
            }
            return $this->response->setJsonContent($result);
        }else{
            if($id>0){
                $result = $this->service->getData($id);
                $this->view->setVars(array(
                    'info' => $result
                ));
            }
        }
    }

    public function delAction(){
        if($this->request->isAjax()){
            $result =$this->service->delData((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

    public function uploadAction()
    {
        $channel = $this->request->getPost('channel');
        if( $channel !=89 ||  $channel !=90){
            $res = array('status' => 'error', 'info'=>'上传失败！');
        }
        $filename="theme_{$channel}_2";
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->themeUpload($this->request,$filename, 'apptheme/package/','','zip');
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }

        }
        return $this->response->setJsonContent($res);
    }

}