<?php

/**
 * Created by PhpStorm.
 * @author yanbo
 * @date: 2017-03-24
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\O2OSkuService;
use Shop\Home\Services\HproseService;

class O2oskuController extends ControllerBase {

    /**
     *  测试hprose
     */
    public function indexAction() {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(O2OSkuService::getInstance());
        $hprose->start();
    }

    public function categoryListAction() {
        $this->view->disable();
        $param = array(
            'categoryId' => 4350, //4350
            'platform' => 'app',
            'type' => 'sort',
            'typeStatus' => 1,
            'userId' => 0,
            'isTemp' => 1, //0时判断是否收藏
            'channel_subid' => 89,
            'udid' => 1,
            'pageStart' => 1,
            'pageSize' => 10
        );
        $result = O2OSkuService::getInstance()->getCategoryList($param);
        var_dump($result);
    }

    public function searchAction() {
        $this->view->disable();
        $param = array(
            'searchName' => '乐而雅超安心F系列',
            'platform' => 'app',
            'searchAttr' => '',
            'type' => '',
            'udid' => 1,
            'typeStatus' => '',
            'userId' => 0,
            'isTemp' => 1, //0时判断是否收藏
            'channel_subid' => 91,
            'pageStart' => 1,
            'pageSize' => 10
        );
        $result = O2OSkuService::getInstance()->getKeywordList($param);
        var_dump($result);
    }

    public function getSkuAction() {
        $this->view->disable();
        $param = array(
            'sku_id' => 8013282,
            'platform' => 'app',
            'user_id' => 0,
            'is_temp' => 1,
            'channel_subid' => 89,
            'udid' => 1
        );
        $result = O2OSkuService::getInstance()->getSku($param);
        print_r($result);
    }

    //分类数据
    public function firstCategoryAction() {
        $this->view->disable();
        $param = array(
            'pid' => 0,
            'platform' => 'app',
            'udid' => 1,
            'channel_subid' => 90
        );
        $list = O2OSkuService::getInstance()->getFirstCategory($param);
        print_r($list);
    }

    public function childCategoryAction() {
        $this->view->disable();
        $category_id = 1042;
        $param = array(
            'categoryId' => $category_id,
            'platform' => 'app',
            'udid' => 1,
            'channel_subid' => 90
        );
        $list = O2OSkuService::getInstance()->getMainCategory($param);
        print_r($list);
    }

}
