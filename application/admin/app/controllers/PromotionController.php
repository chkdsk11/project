<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/9/5
 * Time: 9:23
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\PromotionService;
use Shop\Services\CategoryService;
use Shop\Services\CouponService;
use Shop\Services\GoodsPriceTagService;

class PromotionController extends ControllerBase
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
        $http = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
        $enable = strstr($http,'/spu/add');
        $this->view->disable();
        $pid = $this->request->getPost('pid','int',1);
        $category = CategoryService::getInstance()->getCategory($pid,'pid',false,(bool)$enable);
        return $this->response->setJsonContent($category);
    }

    /**
     * @desc 促销活动列表
     * @author 吴俊华
     */
    public function listAction()
    {
        //过滤
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl()
        ];
        $result = PromotionService::getInstance()->getPromotionList($param);

        $this->view->setVar('promotionList',$result);
        $this->view->setVar('promotionEnum',PromotionService::getInstance()->getPromotionEnum());
    }

    /**
     * @desc 添加促销活动
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
        $this->view->setVar('promotionEnum',PromotionService::getInstance()->getPromotionEnum());
    }

    /**
     * @desc 编辑促销活动
     * @author 吴俊华
     */
    public function editAction()
    {
        /***** 编辑保存促销活动 *****/
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = PromotionService::getInstance()->editPromotion($param);
            return $this->response->setJsonContent($result);
        }

        /***** 加载编辑促销活动页面 *****/
        $category = CategoryService::getInstance()->getCategory();
        $memberTag = GoodsPriceTagService::getInstance()->getGoodsPriceTag();
        $sign = (int)$this->getParam('sign','int',0);
        $promotionId = (int)$this->getParam('promotion_id','int',0);
        $promotionDetail = PromotionService::getInstance()->getPromotionListById($promotionId);

        //处理促销活动详细信息
        if(!empty($promotionDetail)){
            $promotionDetail['promotion_mutex'] = explode(',',$promotionDetail['promotion_mutex']);
            $promotionDetail['condition_string'] = $promotionDetail['condition'];
            $promotionDetail['condition'] = explode(',',$promotionDetail['condition']);
            $promotionDetail['promotion_start_time'] = date('Y-m-d H:i:s',$promotionDetail['promotion_start_time']);
            $promotionDetail['promotion_end_time'] = date('Y-m-d H:i:s',$promotionDetail['promotion_end_time']);
            //遍历整合数据
            foreach($promotionDetail['condition'] as $key => $val){
                switch ($promotionDetail['promotion_scope']) {
                    case 'category':
                        $categoryDetail = CategoryService::getInstance()->getFatherCategory($promotionDetail['condition'][$key]);
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
                        $brandDetail[] = CouponService::getInstance()->checkExistIds('brand',$promotionDetail['condition'][$key]);
                        break;
                    case 'single':
                        $singleDetail[] = CouponService::getInstance()->checkExistIds('single',$promotionDetail['condition'][$key]);
                        break;
                }
            }
        }

        $rule_value_arr=json_decode($promotionDetail["rule_value"],true);
            foreach($rule_value_arr as &$rule_value){
                if(isset($rule_value["premiums_group"]) && !empty($rule_value["premiums_group"])){
                    foreach($rule_value["premiums_group"] as &$tmp){
                        $tmp["premiums_title"] = CouponService::getInstance()->checkExistIds("single",$tmp["premiums_id"])[0]["goods_name"];
                        $tmp["premiums_price"] = CouponService::getInstance()->checkExistIds("single",$tmp["premiums_id"])[0]["price"];
                    }
                }
                if(isset($rule_value["reduce_group"]) && !empty($rule_value["reduce_group"])){
                    foreach($rule_value["reduce_group"] as &$tmp){
                        $tmp["reduce_title"] = CouponService::getInstance()->checkExistIds("single",$tmp["product_id"])[0]["goods_name"];
                        $tmp["price"] = CouponService::getInstance()->checkExistIds("single",$tmp["product_id"])[0]["price"];
                    }
                }
            }

        $this->view->setVar("json_rule_premiums_value",json_encode($rule_value_arr,JSON_UNESCAPED_UNICODE));

        //给模板引擎添加自定义函数
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        $compiler = $volt->getCompiler();
        $compiler->addFunction('in_array', 'in_array');

        //模板赋值
        if($promotionDetail['promotion_scope']=="category")
        $this->view->setVar("category_arr",CouponService::getInstance()->getReverseCategory($promotionDetail['condition_string']));
        $this->view->setVar('sign',$sign);
        $this->view->setVar('promotionDetail',$promotionDetail);
        $this->view->setVar('promotionEnum',PromotionService::getInstance()->getPromotionEnum());
        if(isset($category['data'])){
            $this->view->setVar('category',$category['data']);
        }
        if(isset($memberTag['data'])){
            $this->view->setVar('memberTag',$memberTag['data']);
        }
        if(isset($catagory_is)){
            $this->view->setVar('category_is',$catagory_is);
        }
        if(isset($categoryID)){
            $this->view->setVar('categoryID',$categoryID);
        }
        if(isset($brandDetail)){
            $this->view->setVar('brandDetail',json_encode($brandDetail,JSON_UNESCAPED_UNICODE));
        }
        if(isset($singleDetail)){
            $this->view->setVar('singleDetail',json_encode($singleDetail,JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 
     * @desc 取消促销活动
     * @author 吴俊华
     */
    public function delAction()
    {
        $promotionId = (int)$this->request->getPost('promotion_id','trim');
        $promotionType = (int)$this->request->getPost('promotion_type','int',0);
        $request = (string)$this->request->getPost('request','trim','');
        $result = PromotionService::getInstance()->delPromotion($promotionId,$promotionType,$request);
        return $this->response->setJsonContent($result);
    }

    /**
     * @author 邓永军
     * @desc 通过商品ids获取商品信息(非海外购,非赠品)
     */
    public function getGoodListByIdsAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $input=$this->request->getPost('ids');
            $promotionType = $this->request->getPost("promotion_type",'int',0);
            $result = \Shop\Services\GoodsService::getInstance()->getGoodListByIds($input,$promotionType);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * @desc 通过事件检查改变促销活动的状态
     * @author 吴俊华
     */
    public function beforeExecuteRoute($dispatcher)
    {
        //在促销活动的增改查方法之前调用事件
        if ($dispatcher->getActionName() == 'list' || $dispatcher->getActionName() == 'add' || $dispatcher->getActionName() == 'edit') {
            $result = PromotionService::getInstance()->checkPromotionStatus();
        }
    }

    /**
     * @desc 验证在相同活动、时间里是否设置相同的使用范围【主要针对品牌、单品、多单品】
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