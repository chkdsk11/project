<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/16 0016
 * Time: 下午 2:14
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\O2OConsigneeService;
use Shop\Home\Services\O2OrderService;
use Shop\Home\Services\OrderService;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\OrderDetailService;
use Shop\Home\Services\SkuService;
use Shop\Models\BaiyangOrder;


class O2OrderController extends ControllerBase
{
    /**
     * 发布orderservice类
     */
    public function indexAction()
    {

        $this->view->disable();
        $hprose = new HproseService();
        //$hprose->debug = true;
        $hprose->addInstanceMethods(O2OrderService::getInstance());
        $hprose->start();
    }

    /**
     *  @desc 发布收货地址模块接口
     *  author 吴俊华
     */
    public function consigneeAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->debug = true;
        $hprose->addInstanceMethods(O2OConsigneeService::getInstance());
        $hprose->start();
    }

    public function confirm2Action()
    {
        $this->view->disable();
        $service = OrderService::getInstance();
        $ret=$service->confirmOrder(
            [
                'user_id'=>412692,//412692,
                'address_id'=>11736,
                'coupon_sn'=>'',
                'payment_id'=>0,
                'express_type'=>3,
                'o2o_time'=>'',
                'shop_id'=>'',
                'is_balance'=>0,
                'is_first'=>0,
                'platform'=>'app',
                'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
                'udid'=>13969620308 //手机唯一id   (app必填参数)
            ]);
        echo "<pre>";
        print_r($ret);
    }

    public function confirmAction()
    {
        $this->view->disable();
        $service = O2OrderService::getInstance();
        $ret=$service->confirmOrder(
            [
                'user_id'=>isset($_GET['user_id']) ? $_GET['user_id'] :539400,//412692,
                'address_id'=>isset($_GET['address_id']) ? $_GET['address_id'] : 11735,//11736,
                'coupon_sn'=>isset($_GET['coupon_sn']) ? $_GET['coupon_sn'] : '',
                'payment_id'=>isset($_GET['payment_id']) ? $_GET['payment_id'] : 3,
                'express_type'=>isset($_GET['express_type']) ? $_GET['express_type'] : 3,
                'o2o_time'=>isset($_GET['o2o_time']) ? $_GET['o2o_time'] : '',
                'shop_id'=>'',
                'is_balance'=>isset($_GET['is_balance']) ? $_GET['is_balance'] : 0,
                'is_first'=>isset($_GET['is_first']) ? $_GET['is_first'] : 1 ,
                'platform'=>'app',
                'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
                'udid'=>isset($_GET['udid']) ? $_GET['udid'] : 13969620308 //手机唯一id   (app必填参数)
            ]);
        echo "<pre>";
        print_r($ret);
    }

    public function commitAction()
    {
        $this->view->disable();
        $orderService=O2OrderService::getInstance();
        $param = [
            'user_id'=>isset($_GET['user_id']) ? $_GET['user_id']:412692,
            'address_id'=>isset($_GET['address_id']) ? $_GET['address_id'] :143,
            'coupon_sn'=>isset($_GET['coupon_sn']) ? $_GET['coupon_sn'] :'33499',
            'payment_id'=>isset($_GET['payment_id']) ? $_GET['payment_id'] :3,
            'express_type'=>isset($_GET['express_type']) ? $_GET['express_type'] : 3,
            'o2o_time'=>isset($_GET['o2o_time']) ? $_GET['o2o_time'] :'',
            'shop_id'=>'',
            'is_balance'=>isset($_GET['is_balance']) ? $_GET['is_balance'] : 0,
            'pay_password'=>isset($_GET['pay_password']) ? $_GET['pay_password'] : '',
            'invoice_type'=>isset($_GET['invoice_type']) ? $_GET['invoice_type'] : '',
            'invoice_header'=>isset($_GET['invoice_header']) ? $_GET['invoice_header'] : '',
            'invoice_content_type'=>isset($_GET['invoice_content_type']) ? $_GET['invoice_content_type'] : '',
            'is_first'=>isset($_GET['is_first']) ? $_GET['is_first']:0,
            'platform'=>'app',
            'o2o_time'=>isset($_GET['o2o_time']) ? $_GET['o2o_time'] : 1491408000,
            'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
            'udid'=>isset($_GET['udid']) ? $_GET['udid'] : 13969620308 //手机唯一id   (app必填参数)
        ];
        $ret=$orderService->commitOrder($param);

        echo "<pre>";print_r($param);
        print_r($ret);
    }

    public function getConsigneeInfoAction()
    {
        $this->view->disable();
        $service = O2OConsigneeService::getInstance();
        $ret=$service->getConsigneeInfo(
            [
                'user_id'=> $_GET['user_id'] ? :539400,//412692,
                'consignee_id'=> $_GET['consignee_id'] ? :72560,
                'platform'=>'app',
                'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
                'udid'=>13969620308 //手机唯一id   (app必填参数)
            ]);
        echo "<pre>";
        print_r($ret);
    }

    public function getConsigneeListAction()
    {
        $this->view->disable();
        $service = O2OConsigneeService::getInstance();
        $ret=$service->getConsigneeList    (
            [
                'user_id'=> $_GET['user_id'] ? :539400,//412692,
                'consignee_id'=> isset($_GET['consignee_id']) ? $_GET['consignee_id'] :72560,
                'platform'=>'app',
                'channel_subid'=>90, // 89-iOS客户端, 90-Andriod客户端,
                'udid'=>13969620308 //手机唯一id   (app必填参数)
            ]);
        echo "<pre>";
        print_r($ret);
    }

    public function addOrEditConsigneeAction(){
        $action =               $_POST['action']?:'add';
        $consignee_id =         $_POST['consignee_id']? :null;
        $consignee = $_POST['consignee']? :null;
        $province = $_POST['province']? :null;
        $city = $_POST['city']? :null;
        $county = $_POST['county']? :null;
        $address = $_POST['address']? :null;
        $telphone = $_POST['telphone']? :null;
        $fix_line = isset($_POST['fix_line'])? :null;
        $email = isset($_POST['email'])? $_POST['email'] :null;
        $zipcode = isset($_POST['zipcode']) ? $_POST['zipcode'] :null;
        $default_addr = $_POST['default_addr']? :0;
        $tag_id = isset($_POST['tag_id'])? $_POST['tag_id'] :0;
        $udid = $_POST['udid']? :$telphone;
        $user_id = $_POST['user_id'] ? :539400;//412692,
        $param = array(
            'user_id'=>$user_id,
            'action'=>$action,
            'consignee_id'=>$consignee_id,
            'consignee'=>$consignee,
            'province'=>$province,
            'city'=>$city,
            'county'=>$county,
            'address'=>$address,
            'telphone'=>$telphone,
            'fix_line'=>$fix_line,
            'email'=>$email,
            'zipcode'=>$zipcode,
            'tag_id'=>$tag_id,
            'default_addr'=>$default_addr,
            'platform'=>'app',
            'channel_subid'=>90,
            'udid' =>$udid
        );
        $this->view->disable();
        $service = O2OConsigneeService::getInstance();
        $ret=$service->getConsigneeList($param);

        print_r($ret);
    }

    public function getAction()
    {
       $this->view->disable();
        $cookieFile=APP_PATH.'/static/cookie/cookie.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.epaylinks.cn/paycenter/v2.0/getoi.do?base64_memo=sNnR8y22qbWlseC6xTpHOTUxNzAxMDMxODU0NTgxNTgyMzM%3D&certId=335902764374016746163578510918738695929360058949&currency_type=RMB&notify_url=http%3A%2F%2F127.0.0.1%2FShop%2FEpaylinks%2Fnotify%2Fpay_id%2FwxNative.html&out_trade_no=G95170103185458158233&partner=EC130422C0003&pay_id=wxNative&return_url=http%3A%2F%2F127.0.0.1%2FShop%2FPay%2FglobalDonePay%2Forder_sn%2FG95170103185458158233.html&sign_type=SHA256withRSA&time_limit=2880&time_out=20170105190118&total_fee=0.01&version=4.0&sign=AIEwpepItFYnuCfPLBEHm3GtXswniqba92V7hp%2BWgYYAy7Uyxi1ShCDQ0RU7Psp%2FjC2kdjJxAYJU3DoP%2Fw0TrsSY8m04%2BoQbJS2ey8utKIE6PdVN8s74VwfGfntIKU20BmLC9AMIw0boSwGBvI3Yjsu7gd5Zo9bGMLbWvxwcQI7MRUZZW0qalECUkkSgJoAYVv%2BRNAgQZ7VVJYIUCREZwg4%2B4jufnUeL1TX2CvbtpBAdJK4d90SoKKdJwPQCnnKpJmt16D4dR9q4B2UZ%2BoxKviO3jG0kMPpD7ut4yuNdIHqsch0MWAq%2BQkPDHnuyq8wbAdV8ftLPwIF9wMacmf%2F98Q%3D%3D');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        $headerSize = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
        $header=substr($data,0,$headerSize);
        $ret=explode("\r\n",$header);
        $ret=explode(":",$ret[4]);
        $ret=explode(";",$ret[1]);
        $ret=ltrim($ret[0]);
        curl_close($ch);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.epaylinks.cn/paycenter/v2.0/payType.do?selPayType=wechat&rBankId=wxNative');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieFile);
        curl_setopt($ch,CURLOPT_COOKIEFILE,$cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        $ret=simplexml_load_string($data);
        var_dump($ret);
    }

    public function chkOtoDeliveryAreaAction(){
        $this->view->disable();
        $service=O2OrderService::getInstance();
        $ret=$service->chkOtoDeliveryArea([
            'county'=>'李沧区',
            'city'=>'青岛市',
            'province'=>'山东省'
        ]);
        print_r($ret);
    }

    public function getAddressAction(){
        $this->view->disable();
        $service=O2OrderService::getInstance();
        $ret=$service->getOtoAddress([
            'user_id'=>412692
        ]);
        print_r($ret);
    }

    public function clearAction(){
        $this->view->disable();
        $sku_id = $_GET['id'];
        if($sku_id){
            SkuService::getInstance()->updateGoodsRedis(['sku_id'=>$sku_id]);
        }else{
            echo '失败';
        }


    }

}