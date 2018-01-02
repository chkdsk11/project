<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/10/8
 * Time: 13:59
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\GoodsPriceTagService;
use Shop\Services\BaseService;


class GoodspricetagController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','promotion');
    }

    /**
     * @remark 会员标签列表
     * @return array
     * @author 杨永坚
     */
    public function listAction()
    {
        $seaData['tag_name'] = $this->getParam('tag_name', 'trim', '');
        $seaData['status'] = $this->getParam('status', 'trim', '-1');
        $param = array(
            'page' => (int)$this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/goodspricetag/list',
            'seaData' => $seaData
        );
        $result = GoodsPriceTagService::getInstance()->getTagList($param);
        if($result['res'] == 'success'){
            $this->view->setVars(array(
                'data'=>$result,
                'seaData' => $seaData
            ));
        }else{
            return $this->response->setContent('error');
        }
    }

    public function addAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $result = GoodsPriceTagService::getInstance()->addGoodsPriceTag($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 修改会员标签
     * @return json array
     * @author 杨永坚
     */
    public function editAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $result = GoodsPriceTagService::getInstance()->editGoodsPriceTag($param);
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', 0);
        $result = GoodsPriceTagService::getInstance()->getGoodsPriceTagInfo($id);
        $this->view->setVar('info', $result['data'][0]);
    }

    /**
     * @remark 修改单个会员标签
     * @return json array
     * @author 罗毅庭
     */
    public function edituserAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $result = GoodsPriceTagService::getInstance()->editUserGoodsPriceTag($param);
            return $this->response->setJsonContent($result);
        }
    }

    public function delAction()
    {
        if($this->request->isAjax()){
            $param['tag_id'] = (int)$this->request->getPost('id', 'trim');
            $param['request'] = (string)$this->request->getPost('request', 'trim','');
            $result = GoodsPriceTagService::getInstance()->delGoodsPriceTag($param);
            return $this->response->setJsonContent($result);
        }
    }

    public function bindmemberlistAction()
    {
        $seaData['keyword'] = $this->getParam('keyword', 'trim', '');
        $seaData['searchTag'] = $this->getParam('searchTag', 'trim', 0);
        $param = array(
            'page' => (int)$this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/goodspricetag/bindMemberList',
            'seaData' => $seaData
        );
        $tagServcie = GoodsPriceTagService::getInstance();
        $result = $tagServcie->getBindMemberList($param);
        $tagList = $tagServcie->getMemberTagList();
        if($result['res'] == 'success'){
            $this->view->setVars(array(
                'data'=>$result,
                'seaData' => $seaData,
                'tagList' => $tagList
            ));
        }else{
            return $this->response->setContent('error');
        }
    }

    public function addtagAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $result = GoodsPriceTagService::getInstance()->addTag($param);
            return $this->response->setJsonContent($result);
        }
    }

    public function edittagAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = GoodsPriceTagService::getInstance()->editTag($param);
            return $this->response->setJsonContent($result);
        }
    }

    public function deltagAction()
    {
        if($this->request->isAjax()){
            $param['tag_id'] = (int)$this->request->getPost('tag_id', 'trim');
            $param['user_id'] = (int)$this->request->getPost('user_id', 'trim');
            $param['request'] = (string)$this->request->getPost('request', 'trim');
            $result = GoodsPriceTagService::getInstance()->delTag($param);
            return $this->response->setJsonContent($result);
        }
    }
    /*
     * 批量删除
     */
    public function betchdelAction(){
        if($this->request->isAjax()){
            $param = $this->request->getPost('data');
            $result = GoodsPriceTagService::getInstance()->betchDelTag($param);
            return $this->response->setJsonContent($result);
        }
    }

    public function importtagAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->filesUpload($this->request, '', '', 'csv');
            if($res['status'] == 'success'){
                $result = GoodsPriceTagService::getInstance()->importTag(array(
                    'filename' => $res['data'][0]['filePath']. $res['data'][0]['fileName'],
                    'tag_id' => (int)$this->request->getPost('tag_id', 'trim')
                ));
                return $this->response->setJsonContent($result);
            }
            return $this->response->setJsonContent($res);
        }
    }

    public function downtagtplAction()
    {
        $this->view->disable();
        $file = APP_PATH.'/static/assets/csvtpl/tagTpl.csv';
        if (!file_exists($file))
        {
            echo '会员标签模版不存在！';die;
        }
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=".basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        readfile($file);
    }
}