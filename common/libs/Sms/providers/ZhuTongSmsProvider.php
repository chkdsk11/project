<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/20 0020
 * Time: 10:33
 */

namespace Shop\Libs\Sms\Providers;

use InvalidArgumentException;
use DateTime;


/**
 * 上海助通信息科技有限公司 短信接口
 * 887362	优质通知专用5
 * 435227	商超会员营销4
 * 676767	优质验证码专用
 *
 * Class ZhuTongSMSProvider
 * @package Shop\Libs
 */
class ZhuTongSmsProvider implements SmsProviderInterface
{
    protected $config = array();
    protected $baseUrl = 'http://www.ztsms.cn/';

    protected $productIds = ['1100'=>'887362','1000'=>'676767'];

    public function initializer(array $config = null)
    {
        if(empty($config)){
            throw new InvalidArgumentException('配置参数不能为空');
        }
        $this->config = $config;
    }

    protected function getCommonParamter($phone,$msg,$productId = null)
    {

        $time = date('YmdHis');
        $params = [
            'username'  => $this->config['account'],
            'tkey'      => $time,
            'password'  => md5(md5($this->config['password']).$time),
            'productid' => $productId === null || !isset($this->productIds[$productId]) ? '676767' : $this->productIds[$productId],
            'xh'        => ''
        ];

        if(is_array($phone)){
            if(count($phone) > 2000){
                throw new InvalidArgumentException('同时发送手机号数量不能大于2000个');
            }
            $params['mobile'] = implode(',',$phone);
        }else{
            if(substr_count($phone,',') > 2000){
                throw new InvalidArgumentException('同时发送手机号数量不能大于2000个');
            }
            $params['mobile'] = $phone;
        }

        if(is_array($msg)){
            if(count($msg) > 200){
                throw new InvalidArgumentException('同时发送的短信内容最多200条');
            }
            $params['content'] = implode('※',$msg);
        }else {
            if (mb_strlen($msg) > 500) {
                throw new InvalidArgumentException('短信内容不能大于500字');
            }
            $params['content'] = $msg;
        }

        return $params;
    }

    /**
     * 解析运营商推送的短信发送状态
     * @return array|bool
     */
    public function resolveReport()
    {
        $msgid = isset($_GET['msgid']) ? $_GET['msgid'] : null;
        $mobile = isset($_GET['mobile']) ? $_GET['mobile'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        if(empty($msgid) || empty($mobile) || $status === null){

            return false;
        }

        return [
            'errcode'   => intval($status),
            'errmsg'    => '',
            'phone'     => $mobile,
            'msg_id'     => $msgid,
            'result'    => '0',
            'raw'       => ['msgid' => $msgid, 'mobile' => $mobile, 'status' => $status]
        ];

    }
    /**
     * 发送短信
     * @param array|string $phone
     * @param string $msg
     * @param null $productId
     * @param DateTime|null $time 助通不支持定时发送
     * @param string|null $key 主动不支持客户自定义标识
     * @return mixed
     */
    public function send($phone, $msg,$productId = null,DateTime $time = null, $key = null)
    {
        if(empty($phone) || empty($msg)){
            throw new \InvalidArgumentException('参数 phone 和 msg 不能为空');
        }

        $params = $this->getCommonParamter($phone,$msg,$productId);

        $url = $this->baseUrl . 'sendNSms.do?' . http_build_query($params);

        $result = $this->post($url,null);
        //$result = mt_rand(0,1) . ',20161226' . time();

        if(stripos($result,'0,') === 0){
            return [
              'errcode' => 500,
                'errmsg' => '发送失败',
                'raw'   => $result
            ];
        }
        if(stripos($result,'1,') === 0){
            list($code,$msg_id) = explode(',',$result);

            return [
                'errcode' => 0,
                'errmsg'    => '发送成功',
                'msg_id'    => $msg_id,
                'content'   => $msg,
                'raw'       => $result
            ];
        }
        return [
            'errcode' => $result,
            'errmsg'  => '',
            'raw'       => $result
        ];
    }

    public function changePassword($account, $oldPassword, $newPassword)
    {
        throw new \BadMethodCallException('方法未实现');
    }

    /**
     * 获取余额
     * @param null $productId
     * @return mixed
     */
    public function getBalance($productId = null)
    {
        $time = date('YmdHis');
        $params = [
            'username'  => $this->config['account'],
            'tkey'      => $time,
            'password'  => md5(md5($this->config['password']).$time),
        ];

        $url = $this->baseUrl . 'balanceN.do?' . http_build_query($params);
        $result = $this->post($url,null);

        return $result;
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
    protected function post($url, $data, $second = 30,$sslcert = null, $headers = null)
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