<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/8/25
 * Time: 14:34
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\BrandsService;
use Shop\Services\BaseService;

class BrandsController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    /**
     * @remark 品牌列表
     * @return array()数据
     * @author 杨永坚
     */
    public function listAction()
    {
        $brand_name = $this->getParam('brand_name', 'trim', '');
        $is_hot = $this->getParam('is_hot', 'trim', '-1');
        $param = array(
            'page' => (int)$this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/brands/list',
            'brand_name' => $brand_name,
            'is_hot' => $is_hot
        );
        $result = BrandsService::getInstance()->getBrandList($param);
        if($result['res'] == 'success'){
            $this->view->setVars(array(
                'brand'=>$result,
                'brand_name'=>$brand_name,
                'is_hot'=>$is_hot
            ));
        }else{
            return $this->response->setContent('error');
        }
    }

    /**
     * @remark 添加品牌
     * @author 杨永坚
     */
    public function addAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $result = BrandsService::getInstance()->addBrand($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 编辑品牌
     * @author 杨永坚
     */
    public function editAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = BrandsService::getInstance()->editBrand($param);
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', '');
        $result = BrandsService::getInstance()->getBrandInfo($id);
        $this->view->setVars(array(
            'info' => $result['data']['brandData'],
            'data' => $result['data']['extendData']
        ));
    }

    /**
     * @remark 删除品牌
     * @return 返回json
     * @author 杨永坚
     */
    public function delAction()
    {
        if($this->request->isAjax()){
            $result = BrandsService::getInstance()->delBrand((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark ajax请求获取、搜索品牌
     * @return json
     * @author 杨永坚
     */
    public function searchAction()
    {
        if($this->request->isAjax()){
            $result = BrandsService::getInstance()->searchBrand($this->request->getPost('brand_name', 'trim', ''));
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 上传图片
     * @return 返回json
     * @author 杨永坚
     */
    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/brands/');
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }

    /**
     * @remark 判断品牌名是否唯一
     * @return json
     * @author 杨永坚
     */
    public function uniqueAction()
    {
        if($this->request->isAjax()){
            $brand_name = $this->getParam('brand_name', 'trim', '');
            $result = BrandsService::getInstance()->getBrandUnique($brand_name);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 更新
     * @return json
     * @author 杨永坚
     */
    public function updateAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = BrandsService::getInstance()->updateBrands($param);
            return $this->response->setJsonContent($result);
        }
    }
}