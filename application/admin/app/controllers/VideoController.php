<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/8/31
 * Time: 16:34
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\VideoService;

class VideoController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    /**
     * @remark 视频列表
     * @return array()数据
     * @author 杨永坚
     */
    public function listAction()
    {
        $seaData['video_name'] = $this->getParam('video_name', 'trim', '');
        $seaData['status'] = (int)$this->getParam('status', 'trim', 0);
        $param = array(
            'page' => (int)$this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/video/list',
            'seaData' => $seaData
        );
        $result = VideoService::getInstance()->getVideoList($param);
        if($result['res'] == 'success'){
            $this->view->setVars(array(
                'video' => $result,
                'seaData' => $seaData,
                'status' => \Shop\Models\BaiyangVideoEnum::VIDEO_STATUS
            ));
        }else{
            return $this->response->setContent('error');
        }
    }

    /**
     * @remark 添加视频
     * @return json
     * @author 杨永坚
     */
    public function addAction()
    {
        if(!empty($this->getParam('file_size', 'trim'))){
            $result = VideoService::getInstance()->addCheckVideo($this->request->get());
            return $this->response->setContent($result);
        }
        if($this->request->isAjax()){
            $result = VideoService::getInstance()->addVideo($this->postParam($this->request->getPost(), 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 编辑视频
     * @return json
     * @author 杨永坚
     */
    public function editAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = VideoService::getInstance()->editVideo($param);
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', 0);
        $result = VideoService::getInstance()->getVideoInfo($id);
        $this->view->setVar('info', $result['data'][0]);
    }

    /**
     * @remark 更新视频脚本
     * @return json
     * @author 杨永坚
     */
    public function videoCrontabAction(){
        $result = VideoService::getInstance()->videoCrontab();
        return $this->response->setJsonContent($result);
    }

    /**
     * @remark 删除视频
     * @return json
     * @author 杨永坚
     */
    public function delAction()
    {
        if($this->request->isAjax()){
            $result = VideoService::getInstance()->delVideo((int)$this->request->getPost('video_id', 'trim', ''));
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark ajax获取视频列表
     * @return array()数据
     * @author 梁伟
     */
    public function getallvideoAction()
    {
        $page = $this->request->get('page','trim',1);
        $this->view->disable();
        $result = VideoService::getInstance()->getVideoAll($page);
        return $this->response->setJsonContent($result);
    }

}