<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\AdwapService;
use Shop\Services\BaseService;



class AdwapController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();
        $this->service = AdwapService::getInstance();
    }

    public function adlistAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        //获取所有广告
        $list =$this->service->getAllad($param);
        $ad_position = $this->service->getPositions();
        $this->view->setVars(array(
                'ad_position'=>$ad_position['data'],
                'search'=>$data,
                'list'=>$list
            )
        );
        $this->view->pick('adwap/adlist');
    }

    public function addAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $result =$this->service->add_ad_position($param,'/adwap/adlist');
            return $this->response->setJsonContent($result);

        }else{
            $ad_position =$this->service->getPositions();
            $this->view->setVar('ad_position',$ad_position['data']);
            $this->view->pick('adwap/add');
        }
    }

    public function editAction(){
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $param['id'] = $this->request->get('id');
            $result =$this->service->editAdvertisements($param);
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', '');
        $result =$this->service->getAdvertisementsInfo($id);
        $this->view->setVars(array(
            'info' => $result['data'],
            'ad_position' => $result['ad_position']
        ));
        $this->view->pick('adwap/edit');
    }

    // 三级联动
    public function getPositionAction()
    {
        $http = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
        $enable = strstr($http,'/spu/add');
        $this->view->disable();
        $pid = $this->request->getPost('pid','int',1);
        $category = Ad_positionService::getInstance()->getAllChild($pid,'pid',false,(bool)$enable);
        return $this->response->setJsonContent($category);
    }
    //搜索商品
    public function searchgoodsAction(){
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim');
            $return =$this->service->searchGoods($param);
            return $this->response->setJsonContent($return);
        }else{
            return $this->response->setJsonContent(array('code '=>1));
        }
    }
    //删除活动
    public function delAction(){
        if($this->request->isAjax()){
            $result = $this->service->delAdvertisement((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }
    //取消活动
    public function cancelAction(){
        if($this->request->isAjax()){
            $result =$this->service->cancel((int)$this->request->getPost('id', 'trim'));
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
            $res = BaseService::getInstance()->uploadFile($this->request);
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }

    //验证广告位
    public function ajax_check_is_groupAction($ad_position = 'ajax'){
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim');
            if ($ad_position == 'ajax')
            {
                $post_array = $param['ad_position'];
            }
            else
            {
                $post_array = $ad_position;
            }
        }
        $data['code'] = 200;
        $data['advertisement_type'] = 0;
        $data['adp_id'] = NULL;
        if (isset($post_array) && !empty($post_array) && is_array($post_array))
        {
            $end_id = end($post_array);
            $position_all =$return = AdService::getInstance()->ad_position_all();
            foreach ($position_all['list'] as $key => $value)
            {
                if ($value['id'] == $end_id)
                {
                    if ($value['is_group'] == 1)
                    {
                        $data['code'] = 300;
                    }
                    elseif($value['is_group'] == 0)
                    {
                        $data['advertisement_type'] = $value['adposition_type'];
                        $data['image_size'] = $value['image_size'];
                        $data['adp_id'] = $end_id;
                    }
                }
            }
        }
        else
        {
            $data['code'] = 300;
        }
        if ($ad_position == 'ajax')
        {
            return $this->response->setJsonContent($data);
        }else
        {
            return $data;
        }
    }
    //查询广告于商品条件
    public function ajax_check_product_numAction($result = array(), $type = 'ajax'){
        if ($type == 'ajax')
        {
            $post_array = $this->postParam($this->request->getPost(), 'trim');
        }
        else
        {
            $post_array = $result;
        }
        $ad_num = array(
            '1424' 	=> 3,
            '1427'	=> 4,
            '1429'	=> 4,
            '1431'	=> 4,
            '1433'	=> 4,
            '1435'	=> 4,
            '1437'	=> 4
        );
        if (is_array($post_array['ad_position']))
        {
            $ad_position = end($post_array['ad_position']);
        }
        else
        {
            $ad_position = $post_array['ad_position'];
        }
        $product_num = $post_array['product_num'];
        $data['code'] = 200;
        if ($product_num < $ad_num[$ad_position])
        {
            $data['code'] = 300;
            $data['num'] = $ad_num[$ad_position];
        }
        if ($type == 'ajax')
        {
            return $this->response->setJsonContent($data);
        }
        else
        {
            return $data;
        }
    }
}