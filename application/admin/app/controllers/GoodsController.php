<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\GoodsService;


class GoodsController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->setTitle('商品管理');
        $this->view->setVar('management','goods');
    }
    /**
     * User: lw
     * Date: 2016/8/16
     * Time: 15:50
     * @desc 商品列表
     * @param
     */
    public function goodsListsAction(){
        $page = $_GET['page'];
        $param = array(
            'page' => isset($page)?(int)$page:1,
            'url' => '/goods/goodsLists?page=',
            'url_back' => '',
            'home_page' => '/goods/goodsLists',
        );
        $goods = GoodsService::getInstance()->getAllGoods($param);
        if($goods['res'] == 'succcess'){
            $this->view->setVar('goods',$goods);
        }else{
            echo '错误';
        }
    }

    /**
     * @author 邓永军
     * @desc 通过id或者名称查询商品信息
     */
    public function getGoodsSearchComponentsAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $input=$this->request->getPost("input");
            $promotionType = $this->request->getPost("promotion_type",'int',0);
            if(!isset($input)||empty($input)){
                $input="";
            }
            $this->view->disable();
            return $this->response->setJsonContent(GoodsService::getInstance()->getGoodsList($input,$promotionType));
        }
    }

    /**
     * @author 邓永军
     * @desc 获取赠品（非海外购）
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function getGoodsForGiftAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $input = $this->request->getPost("input");
            $promotionType = $this->request->getPost("promotion_type",'int',0);
            if(!isset($input)||empty($input)){
                $input="";
            }
            $this->view->disable();
            return $this->response->setJsonContent(GoodsService::getInstance()->getGoodsForGift($input,$promotionType));
        }
    }

    /**
     * @author 邓永军
     * @desc 获取优惠券单品(非赠品)
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function getGoodsForCouponAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $input=$this->request->getPost("input");
            if(!isset($input)||empty($input)){
                $input="";
            }
            $this->view->disable();
            return $this->response->setJsonContent(GoodsService::getInstance()->getGoodsForCoupon($input));
        }
    }

    // 判断是否上下架
    public function isOnShelfAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $platform=$this->request->getPost('platform');
            $ids=$this->request->getPost('ids');
            return $this->response->setJsonContent(GoodsService::getInstance()->is_on_shelf($platform,$ids));
        }
    }

    // 判断是否赠品
    public function isGiftAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $platform = $this->request->getPost('platform');
            $ids = $this->request->getPost('ids');
            return $this->response->setJsonContent(GoodsService::getInstance()->isGift($platform,$ids));
        }
    }
}