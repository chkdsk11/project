<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/21 0021
 * Time: 13:26
 */

namespace Shop\Libs\Sms\Providers;

use \InvalidArgumentException;
use PSX\OpenSsl\Exception;
use DateTime;

/***
 * 微网短信接口对接
 * Class WeiWangSmsProvider
 * @package Shop\libs\Sms
 */
class WeiWangSmsProvider implements SmsProviderInterface
{

    protected $config = array();
    protected $baseUrl = 'http://cf.51welink.com/submitdata/service.asmx';
    /**
     * @var array 产品映射
     */
    protected $productIds = ['1100'=>'1012808','1000'=>'1012818'];

    public function initializer(array $config = null)
    {
        if(empty($config)){
            throw new InvalidArgumentException('配置参数不能为空');
        }
        $this->config = $config;
    }

    protected function getCommonParamter($phone,$msg,$productId = null)
    {
        $params = [
            'sname'  => $this->config['account'],
            'spwd'  => $this->config['password'],
            'sprdid' => $productId === null || !isset($this->productIds[$productId]) ? '1012808' : $this->productIds[$productId],
            'scorpid'        => '',
            'smsg'          => $msg
        ];

        if(is_array($phone)){
            if(count($phone) > 100000){
                throw new InvalidArgumentException('同时发送手机号数量不能大于 100000 个');
            }
            $params['sdst'] = implode(',',$phone);
        }else{
            if(substr_count($phone,',') > 100000){
                throw new InvalidArgumentException('同时发送手机号数量不能大于 100000 个');
            }
            $params['sdst'] = $phone;
        }
        return $params;
    }

    protected function resolveResultToArray($xmlString)
    {
        $xml = simplexml_load_string($xmlString);

        if($xml === false){
            throw new Exception('解析短信发送结果时出错',50001);
        }
        return json_decode(json_encode($xml),true);
    }

    /**
     * 发送普通短信
     * @param string|array $phone 接受的手机号码
     * @param string $msg 短信内容
     * @param string $productId 产品id
     * @param DateTime $time 定时短信的发送时间
     * @param string $key 短信唯一自定义标识
     * @return mixed
     */
    public function send($phone, $msg, $productId = null,DateTime $time = null, $key = null)
    {
        $params = $this->getCommonParamter($phone,$msg,$productId);


        $url = $this->baseUrl . '/g_Submit';
        $parameter = http_build_query($params);

        //如果自定义标识
        if($time === null && $key !== null){
            $params['key'] = $key;
            $url = $this->baseUrl . '/g_SubmitWithKey';
        }elseif ($time !== null && $key === null){
            $params['sbegindate'] = $time->format('Y-m-d H:i:s');
            $url = $this->baseUrl . '/g_SchedulerSubmit';
        }elseif ($time !== null && $key !== null){
            $params['sbegindate'] = $time->format('Y-m-d H:i:s');
            $params['key'] = $key;
            $url = $this->baseUrl . '/g_SchedulerSubmitWithKey';
        }

        $result = $this->post($url,$parameter);


        $resultArray =  $this->resolveResultToArray($result);

        return [
            'errcode' => $resultArray['State'] == 0 ? 0 :  $resultArray['State'],
            'errmsg'  => $resultArray['MsgState'],
            'msg_id'    => $resultArray['State'] == 0 ? $resultArray['MsgID'] : null,
            'content'   => $msg,
            'raw'       => $result
        ];
    }

    /**
     * 修改密码
     * @param string $account 账号
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return array
     */
    public function changePassword($account, $oldPassword, $newPassword)
    {
        $params =[
            'account' => $account,
            'oldPassword'   => $oldPassword,
            'newPassword'   => $newPassword
        ];

        $url = $this->baseUrl . '/ChangePassword';
        $parameter = http_build_query($params);


        $result = $this->post($url,$parameter);

        $resultArray =  $this->resolveResultToArray($result);

        return [
            'errcode' => $resultArray[0],
            'errmsg'  => null,
            'raw'       => $result
        ];
    }

    /**
     * 获取余额
     * @param null|string $productId
     * @return int
     * @throws \Exception
     */
    public function getBalance($productId = null)
    {
        if($productId !== null){
            $result = $this->getRemain($productId);
            if(is_array($result)&& $result['State'] == 0){
                return intval($result['Remain']);
            }
            throw new \Exception('获取剩余额度时出错：' . $productId . ' Error:' . print_r($result,true));
        }
        $sum = 0;
        foreach ($this->productIds as $key=>$productId){
            $result = $this->getRemain($key);
            if(is_array($result) && $result['State'] == 0){
                $sum = $sum + intval($result['Remain']);
            }else{
                throw new \Exception('获取剩余额度时出错：' . $productId . ' Error:' . print_r($result,true));
            }
        }
        return $sum;
    }

    /**
     * 解析推送的内容
     * @return array
     */
    public function resolveReport()
    {
        $msgId = isset($_POST['MsgID']) ? $_POST['MsgID'] : null;
        $mobilePhone = isset($_POST['MobilePhone']) ? $_POST['MobilePhone'] : null;
        $sendResultInfo = isset($_POST['SendResultInfo']) ? $_POST['SendResultInfo'] : null;
        $sendState = isset($_POST['SendState']) ? $_POST['SendState'] : null;

        return [
            'errcode'   => intval(boolval($sendState)),
            'errmsg'    => $sendResultInfo,
            'phone'     => $mobilePhone,
            'msg_id'     => $msgId,
            'result'    => 'success',
            'raw'       => $_POST
        ];
    }

    protected function getRemain($productId)
    {
        if($productId === null || !isset($this->productIds[$productId]) ){
            return false;
        }

        $parameters = [
            'sname' => $this->config['account'],
            'spwd'  => $this->config['password'],
            'scorpid'   => '',
            'sprdid'    => $this->productIds[$productId]
        ];
        $parameter = http_build_query($parameters);

        $url = $this->baseUrl . '/Sm_GetRemain';
        $result = $this->post($url,$parameter);

        return $this->resolveResultToArray($result);
    }

    /**
     * 发起 POST 请求
     * @param array|string $data 请求的数据
     * @param string $url 请求路径
     * @param int $second 超时时间
     * @param null|array $sslcert 请求的证书数组
     * @param array|null $headers 请求头
     * @return mixed
     * @throws \Exception
     */
    protected function post($url, $data, $second = 300,$sslcert = null, $headers = null)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('缺少参数 $url');
        }
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if (empty($sslcert) === false) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, $sslcert['sslcert_type']);
            curl_setopt($ch, CURLOPT_SSLCERT, $sslcert['sslcert_path']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, $sslcert['sslkey_type']);
            curl_setopt($ch, CURLOPT_SSLKEY, $sslcert['sslkey_path']);
        }
        if($headers === null){
            $headers = ['Content-Type'=>'application/x-www-form-urlencoded'];
        }
        if (is_array($headers) && empty($headers) === false) {
            $header = array();
            foreach ($headers as $key => $value) {
                $header[] = $key . ':' . $value;
            }
            if (count($header) > 0) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
        }

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            $info = curl_getinfo($ch);

            curl_close($ch);
            throw new \Exception("curl出错: " . print_r($info, true), $error);
        }
    }
}