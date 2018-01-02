<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\SearchService;


class SearchController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function hotListAction()
    {
        $param  = $this->request->get();
        $param['url']    = $this->automaticGetUrl();
        unset($param['_url']);
        unset($param['endDateAt']);
        $SearchService = SearchService::getInstance();
        $result = $SearchService->getHotLinst($param);
        $this->view->setVar('list',$result['list']);
        $this->view->setVar('pcurl',$this->config->wap_home_url[$this->config->environment]."/product-list.html");
        $this->view->setVar('act',isset($param['act'])?$param['act']:'0');
        $this->view->setVar('dataAt',isset($param['dateAt']) ? $param['dateAt'] : date('Y-m-d',strtotime("-1 day")).'~'.date('Y-m-d',strtotime("-1 day")));
        $this->view->setVar('keywords',isset($param['keywords'])?$param['keywords']:'');
        $this->view->setVar('startCount',isset($param['startCount'])?$param['startCount']:'');
        $this->view->setVar('endCount',isset($param['endCount'])?$param['endCount']:'');
        $this->view->setVar('dateText',isset($param['dateText'])&&$param['dateText']?(int)$param['dateText']:1);
        $this->view->setVar('platformId',isset($param['platformId'])?$param['platformId']:'');
        $this->view->setVar('page',$result['page']);
        $this->view->setVar('psize', isset($param['psize'])?$param['psize'] : '15');
        $this->view->pick('search/hotList');
    }



    /**
     * 添加到词库中
     */
    public function addWordAction()
    {
        $param  = $this->request->get();
        $res = SearchService::getInstance()->addWord($param);
        return $this->response->setJsonContent($res);
    }

    /**
     * 从词库中去除
     */
    public function removeWordAction()
    {
        $param  = $this->request->get();
        $res = SearchService::getInstance()->removeWord($param);
        return $this->response->setJsonContent($res);
    }

    /**
     * 添加到黑名单
     */
    public function appendToBlacklistAction()
    {
        $param  = $this->request->get();
        $res = SearchService::getInstance()->appendToBlacklist($param);
        return $this->response->setJsonContent($res);
    }
}