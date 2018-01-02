<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/20 0020
 * Time: 15:31
 */

namespace Shop\Home\Controllers;

use Shop\Home\Datas\BaiyangSmsData;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\SmsService;
use Shop\Libs\Sms\Providers\SmsProviderFactory;

/**
 * 短信服务
 * Class SmsController
 * @package Shop\Home\Controllers
 */
class SmsController extends ControllerBase
{
    /**
     * 发送短信服务
     */
    public function sendAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(SmsService::getInstance());
        $hprose->start();
    }

    public function notificationAction($name)
    {
        $this->view->disable();

        $result = SmsService::getInstance()->resolveReportAndSend($name);

        print_r($result);
    }

    /**
     * 短信测试接口
     */
    public function smsAction()
    {
        $this->view->disable();

        $templateId = $this->request->get('template_code');

        $phone = $this->request->get('phone');
        $param = $this->request->get('param');
        $client_code = $this->request->get('client_code');

        $params = [];

        if(empty($param) === false){

            $temp = explode('|',$param);
            foreach ($temp as $item){
                $value = explode(',',$item);
                $params[$value[0]] = $value[1];
            }
        }
        // print_r($_SERVER);exit;
        $environment = [
            'ip' => $this->request->getClientAddress(true),
            'session_id' => $this->request->get('session_id'),
            'user_agent' => $this->request->getUserAgent(),
            'captcha'=> $this->request->get('captcha')
        ];

        $result =  SmsService::getInstance()->send($phone,$templateId,$client_code,$environment,$params);

        $this->response->setJsonContent($result)->send();
    }

    public function logAction()
    {
        $this->view->disable();
        $count = $this->request->get('count');

        $result = SmsService::getInstance()->echoRecord($count);
        echo '<html><head><link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"></head><body>';
        echo '<table class="table table-bordered" style="margin: 0px auto;font-size: 12px;">
                <thead>
                    <tr>
                        <th width="5%">log_id</th>
                        <th width="5%">log_type</th>
                        <th width="20%">description</th>
                        <th width="15%">original_data</th>
                        <th width="5%">phone</th>
                        <th width="15%">sms_content</th>
                        <th width="5%">client_code</th>
                        <th width="5%">ip_address</th>
                        <th width="5%">client_ip_address</th>
                        <th width="5%">session_id</th>
                        <th width="5%">user_agent</th>
                        <th width="5%">create_time</th>
                        <th width="5%">template_code</th>
                     </tr>
                </thead>';

        foreach ($result as $item){
            echo "<tr>
                    <td>{$item->log_id}</td>
                    <td>{$item->log_type}</td>
                    <td>{$item->description}</td>
                    <td>{$item->original_data}</td>
                    <td>{$item->phone}</td>
                    <td>{$item->sms_content}</td>
                    <td>{$item->client_code}</td>
                    <td>{$item->ip_address}</td>
                    <td>{$item->client_ip_address}</td>
                    <td>{$item->session_id}</td>
                    <td>{$item->user_agent}</td>
                    <td>{$item->create_time}</td>
                     <td>{$item->template_code}</td>
                 </tr>";
        }
        echo "</table></body></html>";
        //$this->response->setJsonContent($result)->send();

    }
}