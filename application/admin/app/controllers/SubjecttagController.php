<?php
/**
 * Created by PhpStorm.
 * User: 陈河源
 * Date: 2017/5/27
 * Time: 15:14
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\SubjectService;
use Shop\Services\BaseService;

class SubjecttagController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();

    }

    /**
     * @desc 专题商品标签活动列表
     * @author 陈河源
     */
    public function goodlistAction()
    {
        $data['type'] = 1;
        $arr = [
            '禁用' => 0 ,
            '启用' => 1,
        ];
        $this->view->setVar('arr',$arr);
		//过滤
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
			if(!empty($v)){
                $this->view->setVar($k,$v);
            }
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl()
        ];
        $result = SubjectService::getInstance()->listGoodTag($param);
        $this->view->setVar('tagList',$result);
    }

    /**
     * @desc 专题价格标签活动列表
     * @author 陈河源
     */
    public function pricelistAction()
    {
        $data['type'] = 2;
        $arr = [
            '禁用' => 0 ,
            '启用' => 1,
        ];
        $this->view->setVar('arr',$arr);
        //过滤
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
			if(!empty($v)){
                $this->view->setVar($k,$v);
            }
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl()
        ];
        $result = SubjectService::getInstance()->listGoodTag($param);
        $this->view->setVar('tagList',$result);
    }

    /**
     * @desc 所有专题列表
     * @author 陈河源
     */
    public function allsubjectlistAction()
    {
        if($this->request->isPost() && $this->request->isAjax()) {
            $input = $this->request->getPost("input");
            if (!isset($input) || empty($input)) {
                $input = "";
            }
            $this->view->disable();
            $list = SubjectService::getInstance()->allsubjectList($input);
            return $this->response->setJsonContent($list);
        }
    }
	
	/**
     * @desc 所有专题列表
     * @author 陈河源
     */
    public function allsubjectlist2Action()
    {
        if($this->request->isPost() && $this->request->isAjax()) {
            $data = $this->postParam($this->request->getPost(), 'trim');
            $this->view->disable();
			$param = [
				'param' => $data,
				'page' => $this->getParam('page','int',1),
				'url' => $this->automaticGetUrl(),
				'url_back' => '',
				'home_page' => '/subjectmobile/list',
			];
			$result = SubjectService::getInstance()->getallsubjectList($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @desc 添加/编辑专题商品标签
     * @author 陈河源
     */
	public function editgoodAction()
    {
        $tagId = (int)$this->getParam('id','int',0);
        if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            If($param['id'] > 0){
                $result = SubjectService::getInstance()->editGoodTag($param);
            }else{
                $result = SubjectService::getInstance()->addGoodTag($param);
            }
            return $this->response->setJsonContent($result);
        }
        /***** 加载编辑页面 *****/
        If($tagId > 0){
            $tagDetail = SubjectService::getInstance()->getTagById($tagId);
			$this->view->setVar('tag',$tagDetail);
        }
        $this->view->setVar('id',$tagId);
    }

    /**
     * @desc 添加/编辑专题价格标签
     * @author 陈河源
     */
    public function editpriceAction()
    {
        $tagId = (int)$this->getParam('id','int',0);
        if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            If($param['id'] > 0){
                $result = SubjectService::getInstance()->editPriceTag($param);
            }else{
                $result = SubjectService::getInstance()->addPriceTag($param);
            }
            return $this->response->setJsonContent($result);
        }
        /***** 加载编辑页面 *****/
        If($tagId > 0){
            $tagDetail = SubjectService::getInstance()->getTagById($tagId);
			$this->view->setVar('tag',$tagDetail);
        }
        $this->view->setVar('id',$tagId);
    }
	
	/**
     * @desc 更新标签状态
     * @author 陈河源
     */
    public function updatetagstatusAction()
    {
        $url = $_SERVER['REQUEST_URI'];
        $this->view->pick($url);
        if($this->request->isPost() && $this->request->isAjax()){
            $tag_id=$this->request->getPost("tag_id");
            $this->view->disable();
			$result = SubjectService::getInstance()->updateTagStatus($tag_id);
            return $this->response->setJsonContent($result);
        }
    }
	
	/**
     * @remark 上传图片
     * @return 返回json
     * @author 陈河源
     */
    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/subjecttag/');
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }
	
	/**
     * @desc 导入商品id
     * @author 陈河源
     */
	public function importtagAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->filesUpload($this->request, $this->config['application']['uploadDir'].'csv/', '', 'csv');
            if($res['status'] == 'success'){
                $result = SubjectService::getInstance()->importTag(array(
                    'filename' => $res['data'][0]['filePath']. $res['data'][0]['fileName']
                ));
                return $this->response->setJsonContent($result);
            }
            return $this->response->setJsonContent($res);
        }
    }
	
	/**
     * @desc 查看含有相同商品的专题
     * @author 陈河源
     */
	public function viewgoodtagAction()
    {
        $tagId = (int)$this->getParam('id','int',0);
        if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
			$result = SubjectService::getInstance()->viewgoodtag($param['product_ids']);
            return $this->response->setJsonContent($result);
        }
    }

}