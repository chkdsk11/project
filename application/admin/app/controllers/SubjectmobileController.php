<?php
/**
 * Created by PhpStorm.
 * User: 陈河源
 * Date: 2017/6/5
 * Time: 15:14
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\SubjectService;
use Shop\Services\BaseService;

class SubjectmobileController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * @desc 移动端专题活动列表
     * @author 陈河源
     */
    public function listAction()
    {
        $array = array('channel' => 91);
        $arr = [
            '未发布' => 0 ,
            '已发布' => 1,
            '已停用' => 2,
        ];
        $this->view->setVar('arr',$arr);
        if($this->request->isPost()){
            $data = $this->postParam($this->request->getPost(), 'trim');
            if(isset($data['title']) && !empty($data['title'])){
                $this->view->setVar('title',$data['title']);
                $array['title'] = $data['title'];
            }
            if(isset($data['status']) && !empty($data['status'])){
                $this->view->setVar('status',$data['status']);
                $array['status'] = $arr[$data['status']];
            }
            if(isset($data['start']) && !empty($data['start'])){
                $this->view->setVar('start',$data['start']);
                $array['start'] = $data['start'];
            }
            if(isset($data['end']) && !empty($data['end'])){
                $this->view->setVar('end',$data['end']);
                $array['end'] = $data['end'];
            }
        }

        $param = [
            'param' => $array,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/subjectmobile/list',
        ];
        $result = SubjectService::getInstance()->getSubjectList($param);
        $this->view->setVar('subjectList',$result);
    }

    /**
     * @desc 添加移动端专题活动
     * @author 陈河源
     */
	public function addAction()
    {
        if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
			if(!empty($param['id'])){
				//编辑
				$result = SubjectService::getInstance()->updateSubject($param);
				return $this->response->setJsonContent($result);
			}else{
				//添加
				$result = SubjectService::getInstance()->addSubject($param);
				return $this->response->setJsonContent($result);
			}
            
        }
		 /***** 加载编辑组件活动页面 *****/
        $data['id'] = (int)$this->getParam('id','int',0);
        If($data['id'] > 0){
            $subject = SubjectService::getInstance()->getSubjectInfo($data);
        }
        if(!empty($subject)){
            foreach($subject as $val){
				$this->view->setVar('param',$val);
				$this->view->setVar('img',$val['share_img']);
			}
        }
    }

    /**
     * @desc 编辑专题活动
     * @author 陈河源
     */
	 
    public function editAction()
    {
		if($this->request->isPost() && $this->request->isAjax()){
            $data = $this->postParam($this->request->getPost(), 'trim');
			$data['channel'] =  91;
			//生成静态文件
			if(isset($data['html'])&&!empty($data['html'])){
				$result = SubjectService::getInstance()->editSubject($data);
				return $this->response->setJsonContent($result);
			}
			//页面信息
			$result = SubjectService::getInstance()->getSubject($data);
			return $this->response->setJsonContent($result);
			
        }
		/***** 加载编辑页面 *****/
        $subjectId = (int)$this->getParam('id','int',0);
        $this->view->setVar('id',$subjectId);
    }

    /**
     * @desc 移动端专题预览
     * @author 陈河源
     */
    public function reviewAction()
    {
		$param['id'] = (int)$this->getParam('id','int',0);
        if(!empty($param['id'])){
			$subject = SubjectService::getInstance()->getSubjectInfo($param);
			//print_r($subject);exit;
			if(!empty($subject)){
				foreach($subject as $v){
					if(!empty($v['link'])){
						$this->view->setVar('link',$v['link']);
					}
				}
			}
		}
    }

    /**
     * @desc 移动端专题组件列表
     * @author 陈河源
     */
    public function listwidgetAction()
    {
        $array = array('channel' => 91);
        $arr = [
            '禁用' => 0 ,
            '启用' => 1,
        ];
        $this->view->setVar('arr',$arr);
        if($this->request->isPost()){
            $data = $this->postParam($this->request->getPost(), 'trim');
            if(isset($data['component_name']) && !empty($data['component_name'])){
                $this->view->setVar('component_name',$data['component_name']);
                $array['component_name'] = $data['component_name'];
            }
            if(isset($data['status']) && !empty($data['status'])){
                $this->view->setVar('status',$data['status']);
                $array['status'] = $arr[$data['status']];
            }
        }

        $param = [
            'param' => $array,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/subjectmobile/listWidget',
        ];
        $result = SubjectService::getInstance()->getWidgetList($param);
        $this->view->setVar('widgetList',$result);
    }

    /**
     * @desc 移动端专题组件编辑
     * @author 陈河源
     */
    public function editwidgetAction()
    {
        if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = SubjectService::getInstance()->editWidget($param);
            return $this->response->setJsonContent($result);
        }
        /***** 加载编辑组件活动页面 *****/
        $componentId = (int)$this->getParam('id','int',0);
        If($componentId > 0){
            $componentDetail = SubjectService::getInstance()->getComponentById($componentId);
        }
        $this->view->setVar('id',$componentId);
        if(isset($componentDetail['component'])){
            $this->view->setVar('component',$componentDetail['component']);
        }
        if(isset($componentDetail['field'])){
            $this->view->setVar('field',$componentDetail['field']);
        }
        if(isset($componentDetail['field_ids'])){
            $this->view->setVar('field_ids',$componentDetail['field_ids']);
        }

    }

    /**
     * @desc 移动端更新组件状态
     * @author 陈河源
     */
    public function updatewidgetstatusAction()
    {
        $url = $_SERVER['REQUEST_URI'];
        $this->view->pick($url);
        if($this->request->isPost() && $this->request->isAjax()){
            $component_id=$this->request->getPost("component_id");
            $this->view->disable();
			$result = SubjectService::getInstance()->updateComponentStatus($component_id);
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
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/subjectmobile/');
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }
	
	/**
     * @remark 移动端模版
     * @return 返回json
     * @author 陈河源
     */
    public function templateAction()
    {
		//模版文件
		$fileurl =  APP_PATH.'/static/assets/subjecttpl/mobile.html' ;
		if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
			$param['file'] = $fileurl;
            $result = SubjectService::getInstance()->updateTemplate($param);
            return $this->response->setJsonContent($result);
        }
		//模版源码
		$html = file_get_contents($fileurl);
		$this->view->setVar('contents',$html);
    }
	
	/**
     * @remark 停用专题
     * @return 返回json
     * @author 陈河源
     */
    public function stopsubjectAction()
    {
		if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
			$param['channel'] = 91;
			$param['page'] = $this->getParam('page','int',1);
            $result = SubjectService::getInstance()->updateSubjectStatusToStop($param);
            return $this->response->setJsonContent($result);
        }
    }
	
	/**
     * @remark 复制专题
     * @return 返回json
     * @author 陈河源
     */
    public function copysubjectAction()
    {
		if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
			$param['channel'] = 91;
			$param['page'] = $this->getParam('page','int',1);
            $result = SubjectService::getInstance()->copySubject($param);
            return $this->response->setJsonContent($result);
        }
    }
	
	/**
     * @desc 移动端LOG列表
     * @author 陈河源
     */
    public function loglistAction()
    {
        $array = array('channel' => 91);
        if($this->request->isPost()){
            $data = $this->postParam($this->request->getPost(), 'trim');
            if(isset($data['start']) && !empty($data['start'])){
                $this->view->setVar('start',$data['start']);
                $array['start'] = $data['start'];
            }
            if(isset($data['end']) && !empty($data['end'])){
                $this->view->setVar('end',$data['end']);
                $array['end'] = $data['end'];
            }
        }
        $param = [
            'param' => $array,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/subjectmobile/loglist',
        ];
        $result = SubjectService::getInstance()->getLogList($param);
        $this->view->setVar('logList',$result);
    }
	
	/**
     * @desc 删除组件
     * @author 陈河源
     */
	public function delewidgetAction(){
        
		if($this->request->isPost()){
            $this->view->disable();
			$param = $this->postParam($this->request->getPost(), 'trim');
            $result = SubjectService::getInstance()->deleWidget($param);
            return $this->response->setJsonContent($result);
        }
    }
}