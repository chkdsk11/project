<?php
/**
 * @desc 关联商品组
 * @author 邓永军
 * @date 2016-10-12
 */
namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;

use Shop\Services\CouponService;
use Shop\Services\GoodsetsService;

class GoodsetsController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','promotion');
    }

    /**
     * @desc 商品组列表Lite
     * @author 邓永军
     */
    public function listAction()
    {
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
            if($k=="goods_info"){
                $this->view->setVar('goods_info',$v);
            }
            if($k=="platform_status"){
                $this->view->setVar('platform',$v);
            }
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/goodsets/list'
        ];
        $this->view->setVar('GoodSetsList',GoodsetsService::getInstance()->getList($param));

    }

    /**
     * @desc 添加产品关联组
     * @author 邓永军
     * @date 2016/10/31
     */
    public function addAction()
    {
        if($this->request->isPost()) {
            return $this->response->setJsonContent(GoodsetsService::getInstance()->addGoodsSetsNGoods($this->request->getPost()));
        }
    }
    /**
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     * @desc 添加商品组名称
     * @author 邓永军
     */
    public function addgoodssetsAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $names=$this->request->getPost("names",'trim',1);
            return $this->response->setJsonContent(GoodsetsService::getInstance()->addGoodsSets($names));
        }
    }

    /**
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     * @desc 修改拖动顺序
     * @author 邓永军
     */
    public function modifysortAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            return $this->response->setJsonContent(GoodsetsService::getInstance()->modifySort($this->request->getPost()));
        }
    }

    /**
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     * @desc 修改拖动顺序
     * @author 邓永军
     */
    public function modifyeditsortAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            return $this->response->setJsonContent(GoodsetsService::getInstance()->modifyEditSort($this->request->getPost()));
        }
    }

    /**
     * @desc 修改商品组_添加商品_商品已经添加列表
     * @author 邓永军
     */
    public function editgoodsAction()
    {
       if($this->request->isPost()){
           return $this->response->setJsonContent(GoodsetsService::getInstance()->doEditALL($this->request->getPost()));
       }else{
           $sid=$this->request->get('id','int');
           if(!isset($sid) || empty($sid)){
               $this->response->redirect('/goodsets/list');
           }
           $this->view->setVar("edit_info",GoodsetsService::getInstance()->getEditInfo($this->request->get('id','int')));
           $this->view->setVar("mid",$this->request->get('id','int'));
       }
    }

    public function getskuinfobyidAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $skuId=$this->request->getPost("id",'int');
            return $this->response->setJsonContent(GoodsetsService::getInstance()->getSkuInfoById($skuId));
        }
    }

    public function savegoodsetsAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $param=$this->request->getPost();
            return $this->response->setJsonContent(GoodsetsService::getInstance()->saveGoodSets($param));
        }
    }

    public function deleditAction()
    {
        $edit_id = $this->request->getPost('id','trim');
        $result = GoodsetsService::getInstance()->delEdit($edit_id);
        return $this->response->setJsonContent($result);
    }
    
    public function delAction()
    {
        $edit_id = $this->request->getPost('id','trim');
        $request = (string)$this->request->getPost('request','trim','');
        $result = GoodsetsService::getInstance()->delList($edit_id, $request);
        return $this->response->setJsonContent($result);
    }
}