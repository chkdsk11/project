<?php 
/**
 * 百洋钱包对接
 * 注意: 这里不做数据库查询，直接组装数据
 * User: qiuqiuyuan
 * Date: 2017/06/25 
 * Time: 15:00
 */
namespace Shop\Libs;
use Shop\Libs\LibraryBase;
use Shop\Datas\BaiyangBestyoopayData;

class BestYooPay extends LibraryBase
{
	/**
     * 百洋钱包appid
     * @var int
     */
    public $_appid;

    /**
     * 百洋钱包wap_appid
     * @var int
     */
    public $_wap_appid;

    /**
     * 百洋钱包private_key
     * @var string
     */
    public $_private_key;

    /**
     * 百洋钱包wap_private_key
     * @var string
     */
    public $_wap_private_key;

    /**
     * 百洋钱包接口
     * @var array
     */
    public $api_url_list;

    /**
     * 百洋钱包基础链接
     * @var string
     */
    public $_base_url;

    /**
     * 百洋2.0链接
     * @var [type]
     */
    public $_new_pay_url;
    
    /**
     * 百洋钱包银联支付标记
     * @var string
     */
    public $_upacpay = 'upacpay';//银联

    /**
     * 百洋钱包环境
     * @var string
     */
    public $_isTest = false;//是否测试

    /**
     * [$_logDir description]
     * @var string
     */
    public $_logDir = '/tmp/baiYangPay.log';

    /**
     * [$RefundFieldArr 退款必须字段]
     * @var array
     */
    public $RefundFieldArr = array(
    	'user_id',			// 订单所属用户
		'channel_subid',	// 渠道ID,95=pc,91=wap,90=Andriod,89=ios
		'appOrderId',		// 退款订单号
		'isPartialRefund',  // 是否部分退款：（false:全额退款，true:部分退款）
		'amount',			// 退款金额 数量为分
		'refundInfo',		// 退款说明信息
		'appRefundNo',		// 应用方退款单号
	);

    /**
     * [$_ssl_pwd ssl密码]
     * @var string
     */
	public $_ssl_pwd = '';

	/**
	 * [$_ssl_path ssl路径]
	 * @var string
	 */
	public $_ssl_path = '';

    /**
     * 返回格式
     * @var array
     */
    public $_code = array(
        'code' => 1,//失败
        'msg' => '',
        'data' => '',
    );

    /**
     * 初始化
     * Function __construct
     * User: edgeto
     * Date: 2016/08/10
     * Time: 11：00
     */
    public function __construct()
    {
       $this->config();
    }

    /**
     * 加载配置
     * Function config
     * User: edgeto
     * Date: 2017/06/26
     * Time: 11：00
     */
    public function config()
    {
    	if(!empty($this->config->UNIONPAY_CONFIG[$this->config->environment])){
    		$unionpay_config = $this->config->UNIONPAY_CONFIG[$this->config->environment];
    		$this->_appid = $unionpay_config['APPID'];
        	$this->_wap_appid = $unionpay_config['WAP_APPID'];
    		$this->_private_key = $unionpay_config['KEY'];
            $this->_wap_private_key = $unionpay_config['WAP_KEY'];
            $this->api_url_list = $unionpay_config['API'];
            if($this->config->environment != 'pro'){
            	// 测试环境
            	$this->_isTest = true;
            }
            $this->_ssl_pwd = $unionpay_config['SSL_PWD'];
            $this->_ssl_path = APP_PATH.$unionpay_config['SSL_PATH'];
    	}else{
    		$this->_code['msg'] = '百洋钱包配置出错';
    	}
    	// 日志目录
    	if(!empty($this->config->application->logDir)){
    		$this->_logDir = $this->config->application->logDir;
    	}
    }

	/**
	 * 退款
	 * Function refund
	 * User: edgeto
	 * Date: 2017/06/26
	 * Time: 15:00
	 * @param array $orderData
	 * @return array
	 */
    public function refund($orderData = array())
    {
        if(!empty($orderData)){
        	$check_res = $this->checkRefundField($orderData);
        	if($check_res){
        		// 先查订单是从哪个appid支付的
        		if($orderData['channel_subid'] == 95){
        			$order_info = $this->queryOrder($orderData,$this->_appid);
        			if($order_info['code'] == 0 && isset($order_info['data']['state']) && $order_info['data']['state'] == 1){
        				// pc这边下单支付的
        				$this->pcRefund($orderData);
        			}else{
        				// pc下单wap支付
        				$this->wapRefund($orderData);
        			}
        		}else{
        			$order_info = $this->queryOrder($orderData,$this->_wap_appid);
        			if($order_info['code'] == 0 && isset($order_info['data']['state']) && $order_info['data']['state'] == 1){
        				// wap这边下单支付的
        				$this->wapRefund($orderData);
        			}else{
        				// wap下单pc支付
        				$this->pcRefund($orderData);
        			}
        		}
        	}
        }else{
        	$this->_code['code'] = 1;
            $this->_code['msg'] = '订单数据不能为空';
        }
        return $this->_code;
    }

    /**
     * [pcRefund pc退款]
     * @param  array  $orderData [description]
     * @return [type]            [description]
     */
    public function pcRefund($orderData = array())
    {
    	if(!empty($orderData)){
    		$appid = $this->_appid;
			$param_array['appId'] = $appid;
            $param_array['appOrderId'] = $orderData['appOrderId'];
            $param_array['isPartialRefund'] = $orderData['isPartialRefund'];
            $param_array['amount'] = $orderData['amount'];
            $param_array['refundInfo'] = $orderData['refundInfo'];
            $param_array['appRefundNo'] = $orderData['appRefundNo'];
            $head_array = $this->makeHeadArray($param_array,$appid);
            // 这里记录下请求参数
            $logAppid['appid'] = $appid;
           	$logData['user_id'] = $orderData['user_id'];
           	$logData['order_sn'] = $orderData['appOrderId'];
           	$logData['type'] = 2; // 退款申请
           	$logData['content'] = serialize($param_array).serialize($head_array).serialize($logAppid);
           	$this->addLog($logData);
            $res = $this->post('refund',$param_array,$head_array);
            // 这里记录下返回的数据
            $logData['type'] = 3; // 退款回调
            $logData['content'] = serialize($res).serialize($logAppid);
            $this->addLog($logData);
            $res = json_decode($res,1);
            if($res && isset($res['success']) && $res['success'] && isset($res['data'])){
                $this->_code['code'] = 0;
            	$this->_code['msg'] = $res['data'];
            }else{
            	$this->_code['msg'] =  $res['msg'];
            }
    	}else{
    		$this->_code['code'] = 1;
            $this->_code['msg'] = '订单数据不能为空';
    	}
    }

    /**
     * [pcRefund wap退款]
     * @param  array  $orderData [description]
     * @return [type]            [description]
     */
    public function wapRefund($orderData = array())
    {
    	if(!empty($orderData)){
    		$appid = $this->_wap_appid;
			$param_array['appId'] = $appid;
            $param_array['appOrderId'] = $orderData['appOrderId'];
            $param_array['isPartialRefund'] = $orderData['isPartialRefund'];
            $param_array['amount'] = $orderData['amount'];
            $param_array['refundInfo'] = $orderData['refundInfo'];
            $param_array['appRefundNo'] = $orderData['appRefundNo'];
            $head_array = $this->makeHeadArray($param_array,$appid);
            // 这里记录下请求参数
            $logAppid['appid'] = $appid;
           	$logData['user_id'] = $orderData['user_id'];
           	$logData['order_sn'] = $orderData['appOrderId'];
           	$logData['type'] = 2; // 退款申请
           	$logData['content'] = serialize($param_array).serialize($head_array).serialize($logAppid);
           	$this->addLog($logData);
            $res = $this->post('refund',$param_array,$head_array);
            // 这里记录下返回的数据
            $logData['type'] = 3; // 退款回调
            $logData['content'] = serialize($res).serialize($logAppid);
            $this->addLog($logData);
            $res = json_decode($res,1);
            if($res && isset($res['success']) && $res['success'] && isset($res['data'])){
                $this->_code['code'] = 0;
            	$this->_code['msg'] = $res['data'];
            }else{
                $this->_code['msg'] = $res['msg'];
            }
    	}else{
    		$this->_code['code'] = 1;
            $this->_code['msg'] = '订单数据不能为空';
    	}
    }

    /**
     * [checkRefundField description]
     * @param  array  $orderData [description]
     * @return [type]            [description]
     */
    public function checkRefundField($orderData = array())
    {
    	$back = false;
    	if(!empty($orderData)){
    		foreach ($this->RefundFieldArr as $key => $value) {
    			if(!isset($orderData[$value])){
    				$this->_code['code'] = 1;
            		$this->_code['msg'] = "缺少{$value}参数";
            		$back = false;
            		break;
    			}
    			$back = true;
    		}
    	}else{
    		$this->_code['code'] = 1;
            $this->_code['msg'] = '订单数据不能为空';
    	}
        return $back;
    }

    /**
     * [queryOrder 订单详情]
     * @param  array   $orderData [description]
     * @param  integer $appid     [description]
     * @return [type]             [description]
     */
    public function queryOrder($orderData = array(),$appid = 100010)
    {
    	$code = array('code'=>1,'msg'=>'','data'=>'');
    	if($orderData){
    		$param_array['appId'] = $appid;
            $param_array['appOrderId'] = $orderData['appOrderId'];
            $head_array = $this->makeHeadArray($param_array,$appid);
            // 这里记录下请求参数
           	$logAppid['appid'] = $appid;
           	$logData['user_id'] = $orderData['user_id'];
           	$logData['order_sn'] = $orderData['appOrderId'];
           	$logData['type'] = 4; // 查询详情
           	$logData['content'] = serialize($param_array).serialize($head_array).serialize($logAppid);
           	$this->addLog($logData);
            $res = $this->post('queryOrder',$param_array,$head_array);
            // 这里记录下返回的数据
            $logData['content'] = serialize($res).serialize($logAppid);
           	$this->addLog($logData);
            $res = json_decode($res,1);
            if($res && isset($res['success']) && $res['success'] && isset($res['data'])){
                $code['code'] = 0;
                $code['data'] = $res['data'];
            }else{
                $code['msg'] = $res['msg'];
            }
    	}else{
    		$code['code'] = 1;
            $code['msg'] = '查询订单数据不能为空!';
    	}
    	return $code;
    }

    /**
     * [makeHeadArray 请求头]
     * @param  array   $param_array [description]
     * @param  integer $appid       [description]
     * @return [type]               [description]
     */
    public function makeHeadArray($param_array = array(),$appid = 100010)
    {
        $head_array['pid'] = $appid;
    	$head_array['ts'] = date('YmdHis');
        $head_array['rd'] = $this->generate_rand_text(4);
        $private_key = $this->_private_key;
        if($appid == 100011){
            $private_key = $this->_wap_private_key;
        }
        $head_array['sn'] = $this->makeSign($param_array,$head_array,$private_key);
        $head_array['Content-Type'] ='application/json;charset=utf-8';
        return $head_array;
    }

    /**
     * 生成随机字符串
     * @param int $length 生成字符串长度
     * @param bool $symbol 是否包含符号生成字符串长度
     * @param bool $casesensitivity 是否区分大小写，默认区分
     * @return string
     */
    public function generate_rand_text($length = 8, $symbol=false, $casesensitivity=true)
    {
        // 字母和数字
        $chars = 'abcdefghjkmnprstuvwxyz2345678';
        if($casesensitivity) {
            $chars .= 'ABCDEFGHJKMNPRSTUVWXYZ';
        }
        if($symbol)
        {
            // 标点符号
            $chars .= '!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        }
        $text = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $text .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        if($casesensitivity==false){
            $text=strtolower($text);
        }
        return $text;
    }

    /**
     * [makeSign 生成签名]
     * @param  [type] $param_array [description]
     * @param  [type] $head_array  [description]
     * @param  [type] $key         [description]
     * @return [type]              [description]
     */
   	public function makeSign($param_array,$head_array,$key)
   	{
   		$json_str = json_encode($param_array);
        return md5($json_str.$head_array['pid'].$head_array['rd'].$head_array['ts'].'key='.$key);
   	}

   	/**
     *
     * Function post
     * User: edgeto
     * Date: 2017/06/26
     * Time: 11:00
     * @param $api_name
     * @param $param_array
     * @param $head_array
     * @return bool|mixed
     */
    public function post($api_name,$param_array,$head_array)
    {
        $url = isset($this->api_url_list[$api_name]) ? $this->api_url_list[$api_name] : '';
        if(!$url || !$param_array || !$head_array)
        {
            return false;
        }
        $headerArr = array();
        foreach( $head_array as $n => $v )
        {
            $headerArr[] = $n .':' . $v;
        }
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        // 证书 --start
        if($this->_isTest == false){
        	// 生产用的是https
	        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
	        // 证书路径
	        curl_setopt($ch, CURLOPT_SSLKEY,$this->_ssl_path );
	        // 证书key
	        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->_ssl_pwd);
        }
        // 证书 --end
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($param_array) );
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    /**
     * [addLog 添加日志]
     * @param array $data [description]
     */
    public function addLog($data = array())
    {
    	if($data){
    		$BaiyangBestyoopayData = BaiyangBestyoopayData::getInstance();
    		$BaiyangBestyoopayData->add($data);
    	}
    }

}