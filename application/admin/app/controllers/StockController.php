<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\StockService;


class StockController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function listAction()
    {
        $param  = $this->request->get();
        $param['url'] = $this->automaticGetUrl();
        $StockService = StockService::getInstance();
        $res = $StockService->getList($param);
        $this->view->setVar('list',$res['list']);
        $this->view->setVar('sku_id',$param['sku_id']);
        $this->view->setVar('spu_id',$param['spu_id']);
        $this->view->setVar('name',$param['name']);
        $this->view->setVar('page',$res['page']);
    }

    public function setStockAction()
    {
        $param  = $this->postParam($this->request->getPost(), 'trim', '');
        $res = StockService::getInstance()->setStock($param);
        return $this->response->setJsonContent($res);
    }
}