<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/10/8
 * Time: 13:59
 */

namespace Shop\Admin\Controllers;
use Hprose\Socket\Server;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model;
use Shop\Services\GoodsPriceService;
use Shop\Services\GoodsPriceTagService;
use Shop\Services\BaseService;
use Shop\Services\PromotionService;


class GoodspriceController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','promotion');
    }

    /**
     * @remark 会员商品列表
     * @return array
     * @author 杨永坚
     */
    public function listAction()
    {
        $seaData['goods_name'] = $this->getParam('goods_name', 'trim', '');
        $seaData['tag_id'] = (int)$this->getParam('tag_id', 'trim', '');
        $seaData['platform'] = $this->getParam('platform', 'trim', '');
        $param = array(
            'page' => (int)$this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/goodsprice/list',
            'seaData' => $seaData
        );
        $result = GoodsPriceService::getInstance()->getGoodsPriceList($param);
        if($result['res'] == 'success'){
            $tagData = GoodsPriceTagService::getInstance()->getGoodsPriceTag();
            $this->view->setVars(array(
                'data' => $result,
                'seaData' => $seaData,
                'tagData' => $tagData['data'],
                'tag_id' => \Shop\Models\BaiyangGoodsPriceEnum::TAG_ID
            ));
        }else{
            return $this->response->setContent('error');
        }
    }

    /**
     * @remark 添加会员商品
     * @return json array
     * @author 杨永坚
     */
    public function addAction()
    {
        if($this->request->isAjax()){
            $param = $this->request->getPost();
            $result = GoodsPriceService::getInstance()->addGoodsPrice($param);
            return $this->response->setJsonContent($result);
        }
        $this->view->setVar('goodsPriceEnum',PromotionService::getInstance()->getPromotionEnum());
        $tagData = GoodsPriceTagService::getInstance()->getGoodsPriceTag();
        $this->view->setVars(array(
            'tagData' => $tagData['data'],
            'tag_id' => \Shop\Models\BaiyangGoodsPriceEnum::TAG_ID
        ));
    }

    /**
     * @remark 修改会员商品
     * @return json array
     * @author 杨永坚
     */
    public function editAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $result = GoodsPriceService::getInstance()->editGoodsPrice($param);
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', 0);
        $result = GoodsPriceService::getInstance()->getGoodsPriceInfo($id);
        $this->view->setVar('goodsPriceEnum',PromotionService::getInstance()->getPromotionEnum());
        $tagData = GoodsPriceTagService::getInstance()->getGoodsPriceTag(true);
        $this->view->setVars(array(
            'info' => $result['data'][0],
            'tagData' => $tagData['data'],
            'tag_id' => \Shop\Models\BaiyangGoodsPriceEnum::TAG_ID
        ));
    }

    /**
     * @remark 删除会员商品
     * @return json
     * @author 杨永坚
     */
    public function delAction()
    {
        if($this->request->isAjax()){
            $param['tag_goods_id'] = (int)$this->request->getPost('tag_goods_id', 'trim');
            $param['request'] = (string)$this->request->getPost('request', 'trim','');
            $result = GoodsPriceService::getInstance()->delGoodsPrice($param);
            return $this->response->setJsonContent($result);
        }
    }
}