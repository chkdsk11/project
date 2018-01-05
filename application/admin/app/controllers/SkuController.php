<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\CategoryService;
use Shop\Services\SpuService;
use Shop\Services\SkuService;
use Shop\Services\SkuAdService;
use Shop\Services\BaseService;
use Shop\Models\CacheGoodsKey;


class SkuController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }
    /**
     * sku商品列表
     * User: lw
     * Date: 2016/8/16
     * Time: 15:50
     */
    public function listAction()
    {
        $category = CategoryService::getInstance()->getCategory();
        $this->view->setVar('category',$category['data']);
        //过滤
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $categoryID = $this->request->get('shop_category');
        if(!empty($categoryID)){
            $categoryTmp = array();
            foreach($categoryID as $v){
                if($v>0) $categoryTmp[] = $v;
            }
            if(!empty($categoryTmp)){
                $cate = $categoryTmp[count($categoryTmp)-1];
                $act = count($categoryTmp);
                $categoryAll = CategoryService::getInstance()->categoryLists();
                $param['category'] = '';
                foreach($categoryAll as $v){
                    if($act == 1){
                        if($v['id']==$cate){
                            foreach($v['son'] as $v1){
                                foreach($v1['son'] as $v2){
                                    $param['category'] .= $v2['id'].',';
                                }
                            }
                            break;
                        }
                    }else if($act == 2){
                        foreach($v['son'] as $v1){
                            if($v1['id'] == $cate){
                                foreach($v1['son'] as $v2){
                                    $param['category'] .= $v2['id'].',';
                                }
                                break;
                            }
                        }
                    }else{
                        $param['category'] = $cate;
                        break;
                    }
                }
                $param['category'] = trim($param['category'],',');
            }
        }
        $list = SkuService::getInstance()->getAllSku($param);
        $array = array();
        $category_sup = array();
        $SpuService = SpuService::getInstance();
        $CategoryService = CategoryService::getInstance();
        if($list['list']){
            //处理分类和药物类型信息
            foreach($list['list'] as $k=>$v){
                if($v['spu_id'] > 0) {
                    if (!in_array($v['spu_id'], $array)) {
                        $array[] = $v['spu_id'];
                        $spu = $SpuService->getSpuOne($v['spu_id']);
                        if (isset($spu['data']) && $spu['data'][0]['category_id'] > 0) {
                            $aa = $CategoryService->getCategory($spu['data'][0]['category_id'], 'id');
                            $list['list'][$k]['category_name'] = $aa['data'][0]['category_name'];
                        }
                        switch ($spu['data'][0]['drug_type']) {
                            case 1:
                                $list['list'][$k]['drug_type'] = '处方药';
                                break;
                            case 2:
                                $list['list'][$k]['drug_type'] = '红色非处方药';
                                break;
                            case 3:
                                $list['list'][$k]['drug_type'] = '绿色非处方药';
                                break;
                            case 4:
                                $list['list'][$k]['drug_type'] = '非药物';
                                break;
                            case 5:
                                $list['list'][$k]['drug_type'] = '虚拟商品';
                                break;
                        }
                        $category_sup[$v['spu_id']]['drug_type'] = $list['list'][$k]['drug_type'];
                        $category_sup[$v['spu_id']]['category_name'] = $list['list'][$k]['category_name'];
                    } else {
                        $list['list'][$k]['category_name'] = $category_sup[$v['spu_id']]['category_name'];
                        $list['list'][$k]['drug_type'] = $category_sup[$v['spu_id']]['drug_type'];
                    }
                }else{
                    $list['list'][$k]['category_name'] = '';
                    $list['list'][$k]['drug_type'] = '';
                }
            }
        }
        $this->view->setVar('list',$list);
        //组织搜索条件,显示在前端页面
        $this->view->setVar('name',isset($param['name'])?$param['name']:'');
        $this->view->setVar('spu_name',isset($param['spu_name'])?$param['spu_name']:'');
        $this->view->setVar('drug_type',($param['drug_type'] > 0)?$param['drug_type']:'');
        $this->view->setVar('brand',isset($param['brand'])?$param['brand']:'');
        $this->view->setVar('is_hot',isset($param['is_hot'])?$param['is_hot']:'-1');
        $this->view->setVar('is_recommend',isset($param['is_recommend'])?$param['is_recommend']:'-1');
        $this->view->setVar('is_on_sale',isset($param['is_on_sale'])?$param['is_on_sale']:'-1');
        if(is_array($categoryID)){
            $catagory_is = array();
            for($i=0;$i<count($categoryID)-1;$i++){
                if($categoryID[$i] > 0){
                    $tmp = CategoryService::getInstance()->getCategory($categoryID[$i]);
                    $catagory_is[] = $tmp['data'];
                }
            }
        }else{
            $categoryID[] = 0;
        }
        $this->view->setVar('category_is',$catagory_is);
        $this->view->setVar('categoryID',$categoryID);
    }
    //修改sku数据
    public function editAction()
    {
        //判断是否post提交
        if($this->request->isPost()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SkuService::getInstance()->addSku($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{

        }
    }
    //
    public function addAction()
    {
        //判断是否post提交
        if($this->request->isPost()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SkuService::getInstance()->addSku($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }
    }

    /**
     * 获取sku信息
     * @param int spu_id spu ID
     * @param int id  商品id
     * @param int force 是否强制转换
     * @return array
     */
    public function getSkuInfoAction()
    {
        $spu_id = (int)$this->request->get('spu_id','trim');
        $id = (int)$this->request->get('id','trim');
        $shop_id = (int)$this->request->get('shop_id','trim');
        $erp_id = $this->request->get('erp_id','trim');
        $brand_id = $this->request->get('brand_id','trim');
        if(!$brand_id){
            $brand_id = 0;
        }
        $force = $this->request->get('force','trim',false);
        if( $erp_id && $spu_id > 0 ){
            $SpuService = SpuService::getInstance();
            $CategoryService = CategoryService::getInstance();
            $SkuService= SkuService::getInstance();
            //获取sku信息,判断是否已有spu
            $sku = $SkuService->getSkuOneByErp($erp_id,$id);
            //获取spu的分类id和分类路径
            $spu = $SkuService->getSpuOne($spu_id);
            //查到多个商品的，终止掉
            if(count($sku)>1){
                return $this->response->setJsonContent([
                    'res'  => 'error_info',
                    'info' => '商品id跟erp编码都已经存在'.print_r($sku,1)
                ]);
            }
            //商品不存在自增一条记录
            if(!$sku){
                $category_id = 0;
                $category_path = 0;
                if($spu){
                   $category_id =  isset($spu[0]['category_id'])?$spu[0]['category_id']:0;
                   $category_path =  isset($spu[0]['category_path'])?str_replace('/',',',$spu[0]['category_path']):0;
                }
                $goodsInfo = $SkuService->addOneGoods($spu_id,$erp_id, $shop_id,$brand_id,$category_id,$category_path,$id);
                $array = array();
                $array['info'] = $goodsInfo;
                return $this->response->setJsonContent($array);
            }
            if($sku[0]['spu_id'] > 0 && !$force){
                return $this->response->setJsonContent([
                    'res'  => 'error_info',
                    'info' => '该商品已有分类'
                ]);
            }
            $param['id'] = $sku[0]['id'];
            $id = $sku[0]['id'];
            $param['spu_id'] = $spu_id;
//            $param['field'] = 'spu_id';
//            $res = $SkuService->setSkuOne($param,true);
            $res = $SkuService->setSkuSpu($param);
            if(!$res){
                return $this->response->setJsonContent($res);
            }
            //获取sku信息
            $sku = $SkuService->getSkuOne($id);
        }else{
            return $this->response->setJsonContent([
                'res'  => 'error',
                'info' => '参数错误'
            ]);
        }
        //获取sku其他信息
        $tmp = $SkuService->getSkuInfo($sku[0]['id']);
        $array = $sku[0];
        $array['info'] = $tmp[0];
        //获取品规名信息
        $spu = $SpuService->getSpuOne((int)$spu_id);
        $spu_category = $CategoryService->getFatherCategory($spu['data'][0]['category_id']);
        if( isset($spu_category[3]) && $spu_category[3] > 0 ){
            //三级分类信息
            $spuId3 = $spu_category[3]['id'];
        }
        $categoryrule = $CategoryService->getCategory($spuId3,'id');
        $array['productRule'] = isset($categoryrule['data'][0]['productRule'])?$categoryrule['data'][0]['productRule']:'';
        //获取图片信息
        $tmpImg = $SkuService->getSkuImg(array('goods_id'=>$sku[0]['id']));
        $array['img']   =   $tmpImg;
        return $this->response->setJsonContent($array);
    }
    //删除sku
    public function delAction()
    {
        $id = $this->request->get('id','trim');
        $this->view->disable();
        $res = SkuService::getInstance()->delSku($id);
        return $this->response->setJsonContent($res);
    }

    //编辑sku详情信息
    public function editInfoAction()
    {
        $param = $this->postParam($this->request->getPost(),'trim');
        $res = SkuService::getInstance()->editSkuInfo($param);
        return $this->response->setJsonContent($res);
    }

    //编辑sku详情
    public function editModelAction()
    {
        $param = $this->postParam($this->request->getPost(),'trim');
        $res = SkuService::getInstance()->editSkuModel($param);
        return $this->response->setJsonContent($res);
    }

    //编辑sku说明书
    public function editInstructionAction()
    {
        $param = $this->postParam($this->request->getPost(),'trim');
        $res = SkuService::getInstance()->editSkuInstruction($param);
        return $this->response->setJsonContent($res);
    }

    //获取sku说明书
    public function getInstructionAction()
    {
        $id = (int)$this->request->get('id','trim');
        $res = SkuService::getInstance()->getSkuInstruction($id);
        return $this->response->setJsonContent($res);
    }

    //修改商品排序信息
    public function setSkuSortAction()
    {
        $sort = (int)$this->request->get('sort','trim');
        $id = (int)$this->request->get('id','trim');
        $res = SkuService::getInstance()->setSkuOne(array(
            'id'=>$id,
            'field'=>'sort',
            'act'=>$sort,
        ));
        return $this->response->setJsonContent($res);
    }

    //获取sku库存信息
    public function getStockAction()
    {
        $id = (int)$this->request->get('id','trim');
        $SkuService = SkuService::getInstance();
        $res = $SkuService->getSkuInfo($id);
        $sku = $SkuService->getSku($id);
        $res[0]['v_stock'] = $sku[0]['v_stock'];
        $res[0]['is_use_stock'] = $sku[0]['is_use_stock'];
        $res[0]['goods_number'] = $sku[0]['goods_number'];
        return $this->response->setJsonContent($res[0]);
    }

    //编辑sku库存信息
    public function setStockAction()
    {
        $param = $this->postParam($this->request->getPost(),'trim');
        $res = SkuService::getInstance()->setStockSku($param);
        return $this->response->setJsonContent($res);
    }

    //获取赠品信息
    public function getBindGiftAction()
    {
        $param = $this->postParam($this->request->getPost(),'trim');
        $this->view->disable();
        $res = SkuService::getInstance()->getBindGift($param);
        return $this->response->setJsonContent($res);
    }

    //获取上下架信息
    public function getIsOnSaleAction()
    {
        $SkuService = SkuService::getInstance();
        $param = $this->postParam($this->request->getPost(),'trim');
        if( $param['type'] == 2 ){
            $res = $SkuService->getSkuSpuTiming($param['spu_id']);
            $res['sku'] = $SkuService->getOneSku($param['spu_id']);
        }else{
            $res = $SkuService->getOneSku($param['spu_id']);
        }
        $this->view->disable();
        return $this->response->setJsonContent($res);
    }

    //批量设置上下架
    public function setSalesAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $res = SkuService::getInstance()->setSkuSales($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }
    }

    //设置热销
    public function setHotAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(),'trim');
            $param['act'] = ((int)$param['act'] == 1)?0:1;
            $param['field'] = 'is_hot';
            $res = SkuService::getInstance()->setSkuOne($param);
            $this->view->disable();
            if($res){
                $return = [
                    'start'  => 'success',
                    'data' => $param['act'],
                ];
            }else{
                $return = [
                    'start'  => 'error',
                    'info'  => '修改失败',
                ];
            }
            return $this->response->setJsonContent($return);
        }
    }

    //设置推荐
    public function setRecommendAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(),'trim');
            $param['act'] = ((int)$param['act'] == 1)?0:1;
            $param['field'] = 'is_recommend';
            $res = SkuService::getInstance()->setSkuOne($param);
            $this->view->disable();
            if($res){
                $return = [
                    'start'  => 'success',
                    'data' => $param['act'],
                ];
            }else{
                $return = [
                    'start'  => 'error',
                    'info'  => '修改失败',
                ];
            }
            return $this->response->setJsonContent($return);
        }
    }

    //设置是否锁定
    public function setIsLockAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(),'trim');
            $param['act'] = ((int)$param['act'] == 1)?0:1;
            $param['field'] = 'is_lock';
            $res = SkuService::getInstance()->setSkuOne($param);
            $this->view->disable();
            if($res){
                $return = [
                    'start'  => 'success',
                    'data' => $param['act'],
                ];
            }else{
                $return = [
                    'start'  => 'error',
                    'info'  => '修改失败',
                ];
            }
            return $this->response->setJsonContent($return);
        }
    }

    //设置上下架，热门，推荐信息
    public function editTimingAction()
    {
        $param = $this->postParam($this->request->getPost(),'trim');
        $res = SkuService::getInstance()->setTiming($param);
        $this->view->disable();
        return $this->response->setJsonContent($res);
    }

    /***************************sku广告模型修改************************************/
    //广告列表
    public function AdlistAction()
    {
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = SkuAdService::getInstance()->getAllSkuAd($param);
        $this->view->setVar('list',$list);
        //组织搜索条件,显示在前端页面
        $this->view->setVar('ad_name',isset($param['ad_name'])?$param['ad_name']:'');
    }

    //广告添加
    public function adaddAction()
    {
        //判断是否post提交
        if($this->request->isPost() || $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SkuAdService::getInstance()->addSkuAd($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            $this->view->pick('sku/adedit');
        }
    }
    //广告修改
    public function AdEditAction()
    {
        if($this->request->isPost() || $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SkuAdService::getInstance()->updateSkuAd($param);;
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            $isLimit = $this->getParam('isLimit','trim','');
            $id = $this->getParam('id','trim');
            $res = SkuAdService::getInstance()->getOneSkuAd($id);
            $this->view->setVar('ad',$res['data'][0]);
            $this->view->setVar('act',1);
            $this->view->setVar('isLimit',$isLimit);
            $this->view->pick('sku/adedit');
        }
    }
    //广告删除
    public function addelAction()
    {
        if($this->request->isPost() || $this->request->isAjax()){
            $id = $this->getParam('id','trim');
            $res = SkuAdService::getInstance()->delSkuAd($id);
            return $this->response->setJsonContent($res);
        }
    }

    //广告启用|暂停切换
    public function isShowAdAction()
    {
        $id = $this->getParam('id','trim',0);
        $is_show = $this->getParam('is_show','trim',0);
        $res = SkuAdService::getInstance()->isShowAd($id,$is_show);
        return $this->response->setJsonContent($res);
    }

    //获取广告信息
    public function getAdAllAction()
    {
        $type = $this->getParam('type','trim');
        $res = SkuAdService::getInstance()->getAdAll($type);
        return $this->response->setJsonContent($res);
    }
    //kindeditor上传图片
    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/skuad/');

            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                if($res['status'] == 'success'){
                    $res = array('error' => 0, 'url' => $res['data'][0]['src']);
                }else{
//                    $res = array('error' => 1,'message'=>$res['info'][0]);
                    echo '<div style="width: 100%;"><span style="margin: 40%;">'.$res['info'][0].'</span></div>';die;
                }
            }
            var_dump(json_encode($res));die;
            return $this->response->setJsonContent($res);
        }
    }

    //sku上传图片
    public function uploadImgAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/sku/',array(array(800, 800), array(350, 350), array(160, 160)));

            //写入数据库
            $id = $this->request->get('id','trim');
            $spu_id = $this->request->get('spu_id','trim');
            $res['id']  =   $id;
            $res['spu_id']  =   $spu_id;
            if( $id <= 0 && $spu_id <= 0 ){
                return $this->response->setJsonContent(array('error' => 0, 'url' => ''));
            }
            $ret = SkuService::getInstance()->insertSkuImg($res);
            if($ret['status'] != 'error'){
                $res['imgId'] = $ret;
                return $this->response->setJsonContent($res);
            }else{
                return $this->response->setJsonContent(array('error' => 0, 'url' => ''));
            }
        }
    }
    //sku上传主图图片
    public function uploadMianImgAction()
    {
        if ($this->request->hasFiles())
        {
            $id = $this->request->get('id','trim');
            $spu_id = $this->request->get('spu_id','trim');
            $res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/sku/',array(array(800, 800), array(350, 350), array(160, 160)));

            //写入数据库
            $res['id']  =   $id;
            $res['spu_id']  =   $spu_id;
            $ret = SkuService::getInstance()->updateSkuImgMain($res);
            if($ret){
                return $this->response->setJsonContent($res);
            }else{
                return $this->response->setJsonContent(fales);
            }
        }
    }
    //sku图片排序
    public function setSkuImgSortAction()
    {
        $param = $this->request->getPost();
        $res = SkuService::getInstance()->setSkuImgSort($param);
        return $this->response->setJsonContent($res);
    }
    //sku修改默认图片
    public function updateskuimgAction()
    {
        $id = $this->request->get('id','trim');
        $this->view->disable();
        $res = SkuService::getInstance()->updateSkuImg($id);
        return $this->response->setJsonContent($res);
    }
    //sku删除图片
    public function delskuimgAction()
    {
        $id = $this->request->get('id','trim');
        $this->view->disable();
        $res = SkuService::getInstance()->delSkuImg($id);
        return $this->response->setJsonContent($res);
    }

    /**
     * @remark 根据sku商品id或商品名称搜索
     * @return json
     * @author 杨永坚
     */
    public function searchAction()
    {
        if($this->request->isAjax()){
            $goods_name = $this->request->getPost('goods_name', 'trim', '');
            $result = SkuService::getInstance()->searchSku($goods_name);
            return $this->response->setJsonContent($result);
        }
    }

}