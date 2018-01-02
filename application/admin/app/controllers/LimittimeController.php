<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/9/9
 * Time: 9:23
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\GoodsService;
use Shop\Services\PromotionService;
use Shop\Services\CategoryService;
use Shop\Services\CouponService;
use Shop\Services\GoodsPriceTagService;

class LimitTimeController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','promotion');
    }

    /**
     * @desc 根据父id获取子分类信息【分类三级联动使用】
     * @author 吴俊华
     */
    public function getCategoryAction()
    {
        $this->view->disable();
        $pid = $this->request->getPost('pid','int',1);
        $category = CategoryService::getInstance()->getCategory($pid);
        return $this->response->setJsonContent($category);
    }

    /**
     * @desc 限时优惠列表
     * @author 吴俊华
     */
    public function listAction()
    {
        //过滤
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param = [
            'promotion_type' => $this->request->get('promotion_type','trim') ? (int)$this->request->get('promotion_type','trim'): 35,
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/limittime/list',
        ];
        $result = PromotionService::getInstance()->getPromotionList($param);
        $this->view->setVar('promotionList',$result);
        $this->view->setVar('limitEnum',PromotionService::getInstance()->getPromotionEnum());
    }

    /**
     * @desc 添加限时优惠
     * @author 吴俊华
     */
    public function addAction()
    {
        if($this->request->isPost()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = PromotionService::getInstance()->addPromotion($param);
            return $this->response->setJsonContent($result);
        }
        $category = CategoryService::getInstance()->getCategory();
        $memberTag = GoodsPriceTagService::getInstance()->getGoodsPriceTag();
        if(isset($category['data'])){
            $this->view->setVar('category',$category['data']);
        }
        if(isset($memberTag['data'])){
            $this->view->setVar('memberTag',$memberTag['data']);
        }
        $this->view->setVar('limitEnum',PromotionService::getInstance()->getPromotionEnum());
    }

    /**
     * @desc 编辑限时优惠
     * @author 吴俊华
     */
    public function editAction()
    {
        /***** 编辑保存限时优惠 *****/
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = PromotionService::getInstance()->editPromotion($param);
            return $this->response->setJsonContent($result);
        }

        /***** 加载编辑限时优惠页面 *****/
        $category = CategoryService::getInstance()->getCategory();
        $memberTag = GoodsPriceTagService::getInstance()->getGoodsPriceTag();
        $sign = (int)$this->getParam('sign','int',0);
        $limitPromotionId = (int)$this->getParam('promotion_id','int',0);
        $limitPromotionDetail = PromotionService::getInstance()->getPromotionListById($limitPromotionId);

        //处理限时优惠详细信息
        if(!empty($limitPromotionDetail)){
            $limitPromotionDetail['promotion_mutex'] = explode(',',$limitPromotionDetail['promotion_mutex']);
            $limitPromotionDetail['condition'] = explode(',',$limitPromotionDetail['condition']);
            $limitPromotionDetail['promotion_start_time'] = date('Y-m-d H:i:s',$limitPromotionDetail['promotion_start_time']);
            $limitPromotionDetail['promotion_end_time'] = date('Y-m-d H:i:s',$limitPromotionDetail['promotion_end_time']);
            //遍历整合数据
            foreach($limitPromotionDetail['condition'] as $key => $val){
                switch ($limitPromotionDetail['promotion_scope']) {
                    /*case 'category':
                        $categoryDetail = CategoryService::getInstance()->getFatherCategory($limitPromotionDetail['condition'][$key]);
                        if(is_array($categoryDetail)){
                            $catagory_is = array();
                            for($i = 1;$i <= count($categoryDetail)-1;$i++){
                                $tmp = CategoryService::getInstance()->getCategory($categoryDetail[$i]['id']);
                                $catagory_is[] = $tmp['data'];
                            }
                        }else{
                            $categoryID[] = 0;
                        }
                        foreach($categoryDetail as $v){
                            $categoryID[] = $v['id'];
                        }

                        break;
                    case 'brand':
                        $brandDetail[] = CouponService::getInstance()->checkExistIds('brand',$limitPromotionDetail['condition'][$key]);
                        break;*/
                    case 'single':
                        $singleDetail[] = CouponService::getInstance()->checkExistIds('single',$limitPromotionDetail['condition'][$key]);
                        break;
                }
            }

            //限时优惠规则
            $rule_value_arr = json_decode($limitPromotionDetail["rule_value"],true);
            foreach($singleDetail as $key => $value){
                foreach($value as $val){
                    $singleDetailArr[$key] = $val;
                    if($limitPromotionDetail['offer_type'] == 1){
                        $singleDetailArr[$key]['type'] = $rule_value_arr[$key]['type'];
                        $singleDetailArr[$key]['discount'] = $rule_value_arr[$key]['discount'];
                    }
                    if($limitPromotionDetail['offer_type'] == 2){
                        $singleDetailArr[$key]['type'] = $rule_value_arr[$key]['type'];
                        $singleDetailArr[$key]['offers'] = $rule_value_arr[$key]['offers'];
                    }
                }
            }

        }

        //给模板引擎添加自定义函数
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        $compiler = $volt->getCompiler();
        $compiler->addFunction('in_array', 'in_array');

        //模板赋值
        $this->view->setVar('category',$category['data']);
        if(isset($memberTag['data'])){
            $this->view->setVar('memberTag',$memberTag['data']);
        }
        /*$this->view->setVar('category_is',$catagory_is);
        $this->view->setVar('categoryID',$categoryID);*/
        $this->view->setVar('sign',$sign);
        $this->view->setVar('limitPromotionDetail',$limitPromotionDetail);
        $this->view->setVar('limitEnum',PromotionService::getInstance()->getPromotionEnum());
        if(isset($singleDetailArr)){
            $this->view->setVar('limitTimeGoods',json_encode($singleDetailArr,JSON_UNESCAPED_UNICODE));
        }

    }

    /**
     * @desc 取消限时优惠
     * @author 吴俊华
     */
    public function delAction()
    {
        $limitId = (int)$this->request->getPost('promotion_id','trim');
        $limitType = $this->request->getPost('promotion_type','trim');
        $request = (string)$this->request->getPost('request','trim','');
        $result = PromotionService::getInstance()->delPromotion($limitId,$limitType,$request);
        return $this->response->setJsonContent($result);
    }

    /**
     * @desc 获取非海外购 的商品列表
     */
    public function getGoodsListAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $input = $this->request->getPost("input");
            $isGift = $this->request->getPost("is_gift",'int',1);
            $notIn = $this->request->getPost('not_in');
            if(!isset($input) || empty($input)){
                $input="";
            }
            $this->view->disable();
            $list = GoodsService::getInstance()->getGoodsList($input,0,$isGift,$notIn);
            foreach ($list as &$item){
                if(isset($item[0]) && !empty($item[0])){
                    $item[0]['goods_name'] = preg_replace("/\\s+/","&nbsp",$item[0]['goods_name']);
                }
            }
            return $this->response->setJsonContent($list);
        }
    }

    /**
     * @desc 通过事件检查改变限制活动的状态
     * @author 吴俊华
     */
    public function beforeExecuteRoute($dispatcher)
    {
        //在限制活动的增改查方法之前调用事件
        if ($dispatcher->getActionName() == 'list' || $dispatcher->getActionName() == 'add' || $dispatcher->getActionName() == 'edit') {
            $result = PromotionService::getInstance()->checkPromotionStatus();
        }
    }

    /**
     * @desc 验证在相同活动、时间里是否设置相同的使用范围【主要针对品牌、单品】
     * @author 吴俊华
     */
    public function verifyTimeRangeAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = PromotionService::getInstance()->verifyTimeRange($param);
            return $this->response->setJsonContent($result);
        }
    }

}