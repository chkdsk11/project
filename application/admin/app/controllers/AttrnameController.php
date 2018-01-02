<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/5
 * Time: 9:45
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\CategoryService;
use Shop\Services\AttrNameService;
use Shop\Services\BaseService;

class AttrnameController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    /**
     * @remark 商品属性列表
     * @return json
     * @author 杨永坚
     */
    public function listAction()
    {
        if($this->request->isAjax()){
            $result = AttrNameService::getInstance()->getCategoryAttr((int)$this->request->getPost('category_id', 'trim', 0));
            return $this->response->setJsonContent($result);
        }
        if(isset($_GET['category_id']) and !empty($this->getParam('category_id', 'trim', ''))){
            $category_id = (int)$this->getParam('category_id', 'trim', '');
            $categoryData = AttrNameService::getInstance()->getCategory($category_id);
            $attrData = AttrNameService::getInstance()->getCategoryAttr($category_id);
            $this->view->setVars(array(
                'categoryData' => $categoryData['data'],
                'attrData' => $attrData['data'],
                'category_id' => $category_id
            ));


            //注入函数到模版
            $volt = $this->di->get("volt", [$this->view, $this->di]);
            $compiler = $volt->getCompiler();
            $compiler->addFunction('in_array', 'in_array');
        }
        $category = CategoryService::getInstance()->categoryLists();
        $this->view->setVar('category',$category);
    }

    /**
     * @remark 添加商品属性
     * @return json
     * @author 杨永坚
     */
    public function addAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $param['attrValueJson'] = $this->request->getPost('attrValueJson', 'trim');
            $result = AttrNameService::getInstance()->addAttrName($param);
            return $this->response->setJsonContent($result);
        }
        $this->view->setVar('category_id', (int)$this->getParam('category_id', 'trim', ''));
    }

    /**
     * @remark 编辑、更新商品属性
     * @return json
     * @author 杨永坚
     */
    public function editAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $param['attrValueJson'] = $this->request->getPost('attrValueJson', 'trim');
            $result = AttrNameService::getInstance()->editAttrName($param);
            return $this->response->setJsonContent($result);
        }
        $param['id'] = (int)$this->getParam('id', 'trim', 0);
        $param['category_id'] = (int)$this->getParam('category_id', 'trim', 0);
        $result = AttrNameService::getInstance()->getAttrInfo($param);
        $this->view->setVars(array(
            'info' => $result['data'][0],
            'valueData' => $result['data']['valueData'],
            'category_id' => $param['category_id']
        ));
    }

    /**
     * @remark 删除商品属性
     * @return json
     * @author 杨永坚
     */
    public function delAction()
    {
        if($this->request->isAjax()){
            $result = AttrNameService::getInstance()->delAttrName((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 更新商品属性字段
     * @return json
     * @author 杨永坚
     */
    public function updateAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = AttrNameService::getInstance()->updateAttrString($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 导入商品属性
     * @return json
     * @author 杨永坚
     */
    public function importAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->filesUpload($this->request, '', '', 'csv');
            if($res['status'] == 'success'){
                $result = AttrNameService::getInstance()->importAttr($res['data'][0]['filePath']. $res['data'][0]['fileName']);
                return $this->response->setJsonContent($result);
            }
            return $this->response->setJsonContent($res);
        }
    }
}