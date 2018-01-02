<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\SkuAdService;
use Shop\Services\BaseService;


class SkuAdController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    //广告列表
    public function listAction()
    {
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = SkuAdService::getInstance()->getAllSkuAd($param);
        $this->view->setVar('list',$list);
        //组织搜索条件,显示在前端页面
        $this->view->setVar('ad_name',isset($param['ad_name'])?$param['ad_name']:'');
    }

    //广告添加
    public function addAction()
    {
        //判断是否post提交
        if($this->request->isPost() || $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SkuAdService::getInstance()->addSkuAd($param);;
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            $this->view->pick('skuad/edit');
        }
    }
    //广告修改
    public function editAction()
    {
        if($this->request->isPost() || $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SkuAdService::getInstance()->updateSkuAd($param);;
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            $id = $this->getParam('id','trim');
            $res = SkuAdService::getInstance()->getOneSkuAd($id);
            $this->view->setVar('ad',$res['data'][0]);
            $this->view->setVar('act',1);
            $this->view->pick('skuad/edit');
        }
    }
    //广告删除
    public function delAction()
    {
        $id = $this->getParam('id','trim');
        $res = SkuAdService::getInstance()->delSkuAd($id);
        return $this->response->setJsonContent($res);
    }

    //获取广告信息
    public function getadallAction()
    {
        $id = $this->request->get('id','trim');
        $res = SkuAdService::getInstance()->getAdAll();
        return $this->response->setJsonContent($res);
    }

    //kindeditor上传图片
    public function uploadAction(){
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request, 'skuad/');
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }

    /**
     * @remark 根据sku商品id或商品名称搜索
     * @return json
     * @author 杨永坚
     */
    public function searchAction()
    {
        if($this->request->isAjax()){
            $goods_name = $this->request->getPost('goods_name', 'trim', '');
            $result = SkuService::getInstance()->searchSku($goods_name);
            return $this->response->setJsonContent($result);
        }
    }
}