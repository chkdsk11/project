<?php
/**
 * @author 邓永军
 * @desc 优惠券控制器
 */
namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Datas\BaiyangUserData;
use Shop\Datas\CouponData;
use Shop\Services\CouponService;
use Shop\Services\CategoryService;
use Shop\Services\PromotionService;
use Shop\Services\GoodsPriceTagService;

class CouponController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->setTitle('优惠券管理');
        $this->view->setVar('management','promotion');
    }

    /**
     * @author 邓永军
     * @desc 优惠券列表管理
     */
    public function listAction()
    {
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/cpoupon/list',
        ];
        $result=CouponService::getInstance()->getCouponList($param);
        $this->view->setVar('couponList',$result);
        $this->view->setVar('couponEnum',CouponService::getInstance()->getCouponEnum());
    }

    /**
     * @author 邓永军
     * @desc 上传批量电话号码
     */
    public function importTelListAction()
    {
        if ($this->request->hasFiles() == true) {
            $path= APP_PATH."/static/assets/csv/";
            foreach ($this->request->getUploadedFiles() as $file) {
                $getName=$file->getName();
                $path.=uniqid(md5(time())).".".strtolower(pathinfo($getName)['extension']);
                if($file->moveTo($path)){
                    $info = function() use($path){
                        $tmp = explode("\r\n",file_get_contents($path));
                        array_pop($tmp);
                        return $tmp;
                    };
                    $FetchRepeatMemberInArray =function($array) {
                        $unique_arr = array_unique ( $array );
                        $repeat_arr = array_diff_assoc ( $array, $unique_arr );
                        return $repeat_arr;
                    };
                    $trace_result = 1;
                    $errorPhoneList = [];
                    $successPhoneList = [];
                    foreach ($info() as $phone){
                        $phone = trim(str_replace('"','',$phone));
                        $user_id = BaiyangUserData::getInstance()->findUserIdByPhone($phone);
                        if($user_id == false){
                            $errorPhoneList[] = $phone;
                            $trace_result = 0;
                        }else{
                            $successPhoneList[] = $phone;
                        }
                    }
                    if(count($info()) != count(array_unique($info()))){
                        $trace_result = 2;
                    }
                    if($trace_result == 1){
                        echo json_encode(["data"=>$successPhoneList,'error'=>0 ]);
                    }elseif ($trace_result == 2){
                        $repeatString = implode(',',$FetchRepeatMemberInArray($info()));
                        echo json_encode(["data"=>'用户:'.$repeatString.'已经重复,不能添加重复用户','error'=>1,'info' => array_merge(array_diff($info(),$FetchRepeatMemberInArray($info())),$FetchRepeatMemberInArray($info())) ]);
                    }else{
                        echo json_encode(["data"=>implode(',',$errorPhoneList).'对应的用户不存在,请修改数据文件后重新导入','error'=>1,'info'=> array_diff($info(),$errorPhoneList) ]);
                    }
                }
            }
        }
    }

    /**
     * @desc 添加优惠券
     * @author 邓永军
     */
    public function addAction()
    {
        if($this->request->isAjax()&&$this->request->isPost()){
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->PostAddCoupon($this->request->getPost()));
        }else{
            $this->view->setVar('GoodsTagList',GoodsPriceTagService::getInstance()->getGoodsPriceTag()['data']);
            $this->view->setVar("CpsChannelList",CouponService::getInstance()->getCpsChannelList());
            $this->view->setVar('promotionEnum',CouponService::getInstance()->getCouponEnum());
            $this->view->setVar("category",CategoryService::getInstance()->getCategory()["data"]);
        }
    }

    /**
     * @desc 编辑优惠券
     * @author 邓永军
     */
    public function editAction()
    {
        if($this->request->isAjax()&&$this->request->isPost()){
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->PostUpdateCoupon($this->request->getPost()));
        }else{
           $id=(int)$this->getParam('id','int');
            $this->view->setVar('GoodsTagList',GoodsPriceTagService::getInstance()->getGoodsPriceTag()['data']);
           $this->view->setVar("CpsChannelList",CouponService::getInstance()->getCpsChannelList());
           $this->view->setVar('promotionEnum',CouponService::getInstance()->getCouponEnum());
           $this->view->setVar("getEditCouponByParam",CouponService::getInstance()->getEditCouponByParam($id,true));
            $this->view->setVar("category",CategoryService::getInstance()->getCategory()["data"]);
           if(CouponService::getInstance()->getEditCouponByParam($id,true)["group_set"]==3){
               $tels_arr=explode(",",CouponService::getInstance()->getEditCouponByParam($id,true)["tels"]);
               $this->view->setVar("tels_arr",$tels_arr);
           }
            if(CouponService::getInstance()->getEditCouponByParam($id,true)["use_range"]=="category"){
                $category_id=CouponService::getInstance()->getEditCouponByParam($id,true)["category_ids"];
                $this->view->setVar("category_arr",CouponService::getInstance()->getReverseCategory($category_id));
            }
            if(CouponService::getInstance()->getEditCouponByParam($id,true)["use_range"]=="brand"){
                $this->view->setVar("brand_ids",CouponService::getInstance()->getEditCouponByParam($id,true)["brand_ids"]);
            }
            if(CouponService::getInstance()->getEditCouponByParam($id,true)["use_range"]!="single"){
                $this->view->setVar("ban_join_rule",CouponService::getInstance()->getEditCouponByParam($id,true)["ban_join_rule"]);
            }
            $this->view->setVar("isshow",(int)$this->getParam('isshow','int'));
            $this->view->setVar("sid",$id);
            $this->view->setVar("codenum",CouponService::getInstance()->getCodeCount($id));
        }
    }

    /**
     * @author 邓永军
     * @desc 通过id或者名称查询品牌信息
     */
    public function getBrandSearchComponentsAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $input=$this->request->getPost("input");
            if(!isset($input)||empty($input)){
                $input="";
            }
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->getBrandList($input));
        }
    }

    /**
     * @author 邓永军
     * @desc 验证是否有品牌id存在
     */
    public function getBrandValidIssetIdAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $input=$this->request->getPost("ids");
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->BrandValidIds($input));
        }
    }

    /**
     * @author 邓永军
     * @desc 验证类型ids是否存在
     */
    public function checkExistIdsAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $type=$this->request->getPost("type");
            $ids=$this->request->getPost("ids");
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->checkExistIds($type,$ids));
        }
    }

    /**
     * @author 邓永军
     * @desc 通过ids获取商品数据列表
     */
    public function getGoodsListsAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $ids=$this->request->getPost("ids");
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->getGoodsLists($ids));
        }

    }

    /**
     * @author 邓永军
     * @desc 更改注册送状态
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function postRegisterBonusAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $mid=$this->request->getPost("mid");
            $this->view->disable();
            return $this->response->setJsonContent(CouponService::getInstance()->postRegisterBonus($mid));
        }
    }

    /**
     * @author 邓永军
     * @desc 取消
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function delAction()
    {
        $mid = $this->request->getPost('mid','trim');
        $request = (string)$this->request->getPost('request','trim','');
        $result = CouponService::getInstance()->postCancel($mid,$request);
        return $this->response->setJsonContent($result);
    }


    /**
     * @desc 根据id获取详情页面
     * @author 邓永军
     * @param  $id
     */
    public function detailAction(int $id)
    {
        $result = CouponService::getInstance()->getEditCouponByParam($id,true);
        if(isset($result) && !empty($result)){
            $param = [
                'id'=>$id,
                'param' => $this->postParam($this->request->getPost(), 'trim'),
                'page' => $this->getParam('page','int',1),
                'url' => $this->automaticGetUrl(),
                'url_back' => '',
                'home_page' => '/cpoupon/detail/'.$id,
            ];
            $this->view->setVar('couponName',$result['coupon_name']);
            $result=CouponService::getInstance()->getCouponDetailList($param);
            $this->view->setVar('couponDetailList',$result);
            $this->view->setVar('couponEnum',CouponService::getInstance()->getCouponEnum());
        }
    }

    /**
     * @author 邓永军
     * @desc 赠送优惠券
     * @param $type
     */
    public function deliverAction($type)
    {
        switch($type){
            case "add":
                //赠卷列表
                $this->view->pick("coupon/deliver_add");
                $this->view->setVar('couponEnum',CouponService::getInstance()->getCouponEnum());

                break;
            case "list":
                //已赠券列表
                $this->view->pick("coupon/deliver_list");
                $param = [
                    'param' => $this->request->get(),
                    'page' => $this->getParam('page','int',1),
                    'url' => $this->automaticGetUrl(),
                    'url_back' => '',
                    'home_page' => '/cpoupon/deliver/list',
                ];
                $this->view->setVar('couponEnum',CouponService::getInstance()->getCouponEnum());
                $this->view->setVar('DeliverList', CouponService::getInstance()->getDeliverList($param));


                break;
        }
    }

    /**
     * @author 邓永军
     * @desc 赠送优惠券_根据活动属性获取活动列表
     */
    public function getScopeActivitiesAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $sid=$this->request->getPost("sid");
            $coupon_name=$this->request->getPost("coupon_name");
            $coupon_scope=$this->request->getPost("coupon_scope");
            return $this->response->setJsonContent(CouponService::getInstance()->getScopeActivities($sid,$coupon_name,$coupon_scope));
        }
    }

    /**
     * @author 邓永军
     * @desc 赠送优惠券_处理提交上来的数据_控制器接口
     */
    public function treatedCouponDataAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            //用户id列表
            $user_id = htmlspecialchars($this->request->getPost("user_id"));
            //优惠券信息（包括优惠券编号和赠送数量）
            $coupon_info=htmlspecialchars($this->request->getPost("coupon_info"));

            return $this->response->setJsonContent(CouponService::getInstance()->treatedCouponData($user_id,$coupon_info));
        }
    }

    /**
     * @param  $id
     * @author 邓永军
     * @desc 获取激活码详细页面
     */
    public function actcodeAction(int $id)
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $current_id = $this->request->getPost('current_id');
            $info=CouponService::getInstance()->getCodeInfo($id,$current_id);
            return $this->response->setJsonContent($info['code_sn_list']);
        }else{
            $info=CouponService::getInstance()->getCodeInfo($id);
            $this->view->setVar('sid',$id);
            $this->view->setVar("info",$info);
        }
    }

    /**
     * @author 邓永军
     * @desc 添加激活码
     */
    public function addactcodeAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $id=$this->request->getPost("id");
            $num=$this->request->getPost("num");
            return $this->response->setJsonContent(CouponService::getInstance()->addactcode($id,$num));
        }
    }
    /**
     * @author 邓永军
     * @desc 赠送优惠券_获取用户信息
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function getUserByInfoAction()
    {
        if($this->request->isPost()&&$this->request->isAjax()){
            $tels["input"]=$this->request->getPost("tels");
            return $this->response->setJsonContent(CouponService::getInstance()->getUserByInfo($tels));
        }
    }

    /**
     * @author 邓永军
     * @desc 根据订单_获取用户信息
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function getUserByOrderIdAction()
    {
        if($this->request->isPost() && $this->request->isAjax())
        {
            $order_ids=$this->request->getPost("order_ids");
            return $this->response->setJsonContent(CouponService::getInstance()->getUserByOrderId($order_ids));
        }
    }
    /**
     * @author 邓永军
     * @desc 导出激活码csv
     */
    public function exportactcodeAction()
    {
        $couponSn = $this->request->getPost("coupon_sn",'string');
        $this->view->disable();
        CouponService::getInstance()->exportData($couponSn);
    }

    public function testUniqueCouponAction()
    {
        $this->view->disable();
        print_r(CouponService::getInstance()->makeCouponSn());
    }
}