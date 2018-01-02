<?php
/**
 * Created by PhpStorm.
 * User: 梁伟
 * Date: 2016/8/25
 * Time: 12:04
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\CategoryService;
use Shop\Services\CategoryProductRuleService;


class SkuRuleController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    /**
     * @desc 多品规列表
     * @param
     * User: lw
     * Date: 2016/8/16
     * Time: 15:50
     */
    public function listAction()
    {
        $CategoryService = CategoryService::getInstance();
        $category = $CategoryService->getCategory();
        $category2 = $CategoryService->getCategory($category['data'][0]['id']);
        $this->view->setVar('category',$category['data']);
        $this->view->setVar('category2',$category2['data']);
        $tmp = $CategoryService->getCategory($category2['data'][0]['id']);
        $list = array();
        foreach($tmp['data'] as $k => $v){
            $tmps = $CategoryService->getCategory($v['id'],'id');
            $list[$k] = $tmps['data'];
        }
        $this->view->setVar("list",$list);
    }

    /**
     * 添加商品规格
     */
    public function addAction(){
        $param = $this->postParam($this->request->getPost(), 'trim', '');
        $res = CategoryProductRuleService::getInstance()->addCategoryProductRule($param);
        return $this->response->setJsonContent($res);
    }


}