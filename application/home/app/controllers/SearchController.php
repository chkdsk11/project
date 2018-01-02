<?php
/**
 * Created by PhpStorm.
 * User: zhuyk
 * Date: 2017/05/26
 */
 
namespace Shop\Home\Controllers;

use Shop\Home\Services\HproseService;
use Shop\Home\Services\OfflineService;
use Shop\Home\Services\SearchService;
use Shop\Home\Services\EventsManager;

class SearchController extends ControllerBase
{
    /**
     *  加载监听器
     */
    public function indexAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(OfflineService::getInstance());
        $hprose->start();
    }

    /**
     *  pc智能推荐及结果数量
     */
    public function pc_getWordsAction()
    {
        $this->view->disable();
        $data = [
            'searchName' => $this->request->get('searchName')?:NULL,
        ];
        echo json_encode(SearchService::getInstance()->pc_getWords($data));
    }

    /**
     *  app智能推荐及结果数量
     */
    public function app_getWordsAction()
    {
        $this->view->disable();
        $data = [
            'searchName' => $this->request->get('searchName')?:NULL,
        ];
        echo json_encode(SearchService::getInstance()->app_getWords($data));
    }

    /**
     *  wap智能推荐及结果数量
     */
    public function wap_getWordsAction()
    {
        $this->view->disable();
        $data = [
            'searchName' => $this->request->get('searchName')?:NULL,
        ];
        echo json_encode(SearchService::getInstance()->wap_getWords($data));
    }


}