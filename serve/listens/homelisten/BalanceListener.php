<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/1/25
 * Time: 14:03
 */

namespace Shop\Home\Listens;

use Shop\Home\Datas\BaseData;
use Shop\Models\HttpStatus;

class BalanceListener extends BaseListen {

    private $domainArr = [
        'dev'   =>  '172.16.40.250:9012',//172.16.40.250:8012 开发环境
        'stg'   =>  'stgapp.baiyjk.com',
        'pro'   =>  'mallapp.baiyjk.com',
    ];

    private $checkSignKey = '4rfv$TGB';

    /**
     * @desc  余额支付订单
     * @param $param array
     *    - phone          int        用户手机号
     *    - amount         float      支付金额
     *    - pay_password   float      支付密码MD5
     *    - order_sn       float      订单号
     * @return array
     *      - expend_sn       支出流水号
     *      - expend_amount   支出金额
     *      - left_amount     用户剩余金额
     * @author 柯琼远
     */
    public function add_user_expend($event, $class, $param) {
        $domain = $this->config->app_url[$this->config->environment];
        $apiUrl = "{$domain}/user_balance/add_user_expend";
        $postData = array(
            'user_id'       =>  $param['phone'],
            'pay_way'       =>  'balance',
            'nonce_str'     =>  $this->func->getRandChar(),
            'amount'        =>  $param['amount'],
            'pay_password'  =>  strtoupper($param['pay_password']),
            'channel'       =>  $this->config->channel_subid,
            'order_id'      =>  $param['order_sn'],
            'remark'        =>  "余额支付" . $param['amount'],
        );
        $postData['sign'] = $this->func->checkSign($postData, $this->checkSignKey);
        $result_str  = $this->curl->sendPost($apiUrl, http_build_query($postData));

        $result = json_decode($result_str, true);
        if(
            empty($result)
            or (empty($result_str) === false and empty($result))
        ){
            $this->log->error("余额支付结果：" . $result . '；支付参数：'. print_r($postData, 1) );
        }
        return $class->responseResult($result['code'], $result['message'], isset($result['data']) ? $result['data'] : []);
    }

    /**
     * @desc  订单余额退款
     * @param $param array
     *    - order_sn          int        用户手机号
     *    - refund_money      float      支付金额
     * @return array
     * @author 柯琼远
     */
    public function external_refund_order($event, $class, $param) {
        $domain = $this->config->app_url[$this->config->environment];
        $apiUrl = "{$domain}/user_balance/external_refund_order";
        $postData = array(
            'order_sn'      =>  $param['order_sn'],
            'refund_money'  =>  $param['refund_money'],
            'nonce_str'     =>  $this->func->getRandChar(),
        );
        $postData['sign'] = $this->func->checkSign($postData, $this->checkSignKey);
        $result = json_decode($this->curl->sendPost($apiUrl, http_build_query($postData)), true);
        return [
            'status' => $result['code'],
            'explain' => $result['message'],
            'data' => isset($result['data']) ? $result['data'] : []
        ];
    }


    //充值余额
    public function add_balance($event, $class, $param){
        $domain = $this->config->wap_url[$this->config->environment];
        $apiUrl = "{$domain}/wap/user_balance/refund_money_add_balance";
        $postData = array(
            'service_sn'      =>  $param['service_sn'],
            'money'  =>  $param['refund_money'],
            'nonce_str'     =>  $this->func->getRandChar(),
        );
        $postData['sign'] = $this->func->make_rsa_sign($postData);
        $result = json_decode($this->curl->sendPost($apiUrl, http_build_query($postData)), true);
        return [
            'status' => $result['code'],
            'explain' => $result['message'],
            'data' => isset($result['data']) ? $result['data'] : []
        ];

    }
}