<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/29
 * Time: 16:59
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\GoodsTreatmentService;
use Shop\Services\BaseService;
use Shop\Models\BaiyangPromotionEnum;

class GoodstreatmentController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','promotion');
    }

    /**
     * @remark 疗程列表
     * @return array
     * @author 杨永坚
     */
    public function listAction()
    {
        $seaData['goods_name'] = $this->getParam('goods_name', 'trim', '');
        $seaData['status'] = (int)$this->getParam('status', 'trim', '');
        $seaData['platform'] = $this->getParam('platform', 'trim', '');
        $param = array(
            'page' => (int)$this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/goodstreatment/list',
            'seaData' => $seaData
        );
        $result = GoodsTreatmentService::getInstance()->getGoodsTreatmentList($param);
        if($result['res'] == 'success'){
            $this->view->setVars(array(
                'data'=>$result,
                'seaData' => $seaData
            ));
        }else{
            return $this->response->setContent('error');
        }
    }

    /**
     * @remark 添加疗程
     * @return json
     * @author 杨永坚
     */
    public function addAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'create_time');
            $result = GoodsTreatmentService::getInstance()->addGoodsTreatment($param);
            return $this->response->setJsonContent($result);
        }
        $this->view->setVar('promotionEnum', BaiyangPromotionEnum::$MutexPromotion);
    }

    /**
     * @remark 修改疗程
     * @return json data
     * @author 杨永坚
     */
    public function editAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = GoodsTreatmentService::getInstance()->editGoodsTreatment($param);
            return $this->response->setJsonContent($result);
        }
        $goods_id = (int)$this->getParam('goods_id', 'trim', '');
        $result = GoodsTreatmentService::getInstance()->getGoodsTreatmentInfo($goods_id);
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        $compiler = $volt->getCompiler();
        $compiler->addFunction('in_array', 'in_array');
        $this->view->setVars(array(
            'data' => $result,
            'promotionEnum' => BaiyangPromotionEnum::$MutexPromotion
        ));
    }

    /**
     * @remark 更新疗程status状态  相当于删除
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     * @author 杨永坚
     */
    public function updateAction()
    {
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = !empty($param['goods_id']) ? GoodsTreatmentService::getInstance()->updateGoodsTreatment($param) : GoodsTreatmentService::getInstance()->delGoodsTreatment($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @remark 查询对应商品id的疗程
     * @return json
     * @author 杨永坚
     */
    public function searchAction()
    {
        if($this->request->isAjax()){
            $goods_id = (int)$this->request->getPost('goods_id', 'trim', '');
            $result = GoodsTreatmentService::getInstance()->getGoodsTreatmentInfo($goods_id);
            return $this->response->setJsonContent($result);
        }
    }

}