<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/20 0020
 * Time: 13:51
 */

namespace Shop\Home\Services;


use Shop\Home\Datas\BaiyangSmsData;
use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Listens\SmsAlarmListener;

use Shop\Home\Listens\SmsSendListener;
use Shop\Libs\Func;
use Shop\Libs\Sms\Providers\SmsProviderFactory;
use Shop\Libs\Sms\Providers\SmsProviderInterface;
use Shop\Models\BaiyangSmsAlarm;
use Shop\Models\BaiyangSmsLog;
use Shop\Models\BaiyangSmsProvider;
use Shop\Models\BaiyangSmsProviderPasswords;
use Shop\Rules\Sms\SmsRuleDispatcher;

class SmsService extends BaseService
{

    /**
     * 必须声明此静态属性，单例模式下防止内存地址覆盖
     * @var SmsService
     */
    protected static $instance = null;

    /**
     * @var SmsRuleDispatcher
     */
    protected $dispatcher;


    /**
     * @return SmsService
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance = new SmsService();
        }
        //实例化事件管理器
        $eventsManager = new EventsManager();

        //开启事件结果回收
        $eventsManager->collectResponses(true);

        $eventsManager->attach('sms_send',new SmsSendListener());
        $eventsManager->attach('sms_alarm',new SmsAlarmListener());

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);

        //初始化规则
        $dispatcher = new SmsRuleDispatcher();

        $dispatcher->initializer();

        static::$instance->dispatcher = $dispatcher;

        return static::$instance;
    }

    /**
     * 发送短信
     * @param string|array $phone 需要发送的手机号
     * @param string $templateCode 模板编号
     * @param string $client_code 客户端编号
     * @param array|null $environment 客户端环境变量
     * @param array|null $params 模板中的参数替换
     * @return array|bool
     */
    public function send($phone,$templateCode,$client_code,array $environment, array $params = null)
    {
        if(empty($phone)){
            return ['errcode' => 50001, 'errmsg' => '手机号不能为空'];
        }
        if(empty($templateCode)){
            return ['errcode' => 50002, 'errmsg' => '模板Code不能为空'];
        }
        if(empty($client_code)){
            return ['errcode' => 50003, 'errmsg' => 'client_code 不能为空'];
        }
        $environment['client_ip_address'] = $this->request->getClientAddress();

        $ruleParameter = ['phone' => $phone, 'client_code' => $client_code,'template_code' => $templateCode];
        $ruleParameter = array_merge($ruleParameter,$environment);

        $ruleResult = $this->dispatcher->handle($ruleParameter);
        // var_dump($ruleResult);exit;
        //执行短信发送规则
        if($ruleResult !== true && (!isset($ruleResult['action']) or $ruleResult['action'] != 'right')){
            return $ruleResult;
        }
        //var_dump($ruleResult);exit;

        //获取一个可用的短信服务商
        $providerData = BaiyangSmsData::getInstance()->getSmsProvider();
        if(empty($providerData)){
            return ['errcode' => 50004, 'errmsg' => '未获取到可用的短信服务商'];
        }
        $provider = SmsProviderFactory::create($providerData);

        if($provider === null){
            return ['errcode' => 50005, 'errmsg' => '没有已实现的短信提供商渠道' . $providerData['provider_code']];
        }

        $template = BaiyangSmsData::getInstance()->getSmsTemplate($templateCode);

        if(empty($template) || empty($template->content)){
            return ['errcode' => 50003, 'errmsg' => '短信模板不存在' . $templateCode];
        }

        $templateContent = $template->content;
        if(empty($params) === false){
            foreach ($params as $key=>$value){
                $templateContent = str_replace('#'.$key.'#',$value,$templateContent);
            }
        }

        if(empty($template->signature) === false){
            $templateContent = '【'. $template->signature .'】' . $templateContent ;
        }

        $this->_eventsManager->fire('sms_send:beforeSend',$this,$template);

        $parameters = [
            'phone'             => $phone,
            'template_content'  => $templateContent,
            'template_type'     => $template->template_type,
            'provider_code'     => $providerData['provider_code'] ,
            'client_code'       => $client_code,
            'remark'            => null,
            'template_code'     => $templateCode
        ];
        $parameters = array_merge($parameters,$providerData);

        $smsResult = $this->sendSms($provider,$parameters,$environment);


        //如果发送失败,使用补发渠道重发
        if($smsResult['errcode'] != 0){
            $remark = $providerData['provider_name'] . ' 通道发送失败，重发。' . json_encode($smsResult);

            $providers = BaiyangSmsData::getInstance()->getReserveProvider($providerData['provider_code']);

            if(empty($providers)){
                $logParams['description'] = "通过短信服务商 {$providerData['provider_code']} 发送短信失败，且没有备用短信服务商 ，"  . json_encode($smsResult,JSON_UNESCAPED_UNICODE);
                $logParams['original_data'] = json_encode($providerData);
                $logParams['log_type'] = 0;
                $logParams = array_merge($parameters,$logParams);
                BaiyangSmsData::getInstance()->addLog($logParams);

                return ['errcode' => 500 ,'errmsg' => '短信发送失败，未找到备用短信提供商'];
            }

            //循环使用剩余服务商补发
            foreach ($providers as $index=>$data){
                $provider = SmsProviderFactory::create($data);
                $parameters = array_merge($parameters,$data);
                $parameters['remark'] = $remark;

                $smsResult = $this->sendSms($provider,$parameters,$environment);
                if($smsResult['errcode'] == 0){
                    $logParams['description'] = "通过短信服务商 {$providerData['provider_name']} 发送短信失败，自动切换到 {$data['provider_name']} 重发, " . json_encode($smsResult,JSON_UNESCAPED_UNICODE);
                    $logParams['original_data'] = json_encode($data);
                    $logParams['log_type'] = 0;
                    $logParams = array_merge($parameters,$logParams);
                    BaiyangSmsData::getInstance()->addLog($logParams);

                    return $smsResult;
                }
            }
            $alarmParams = [
                'notify_id' => '1004',
                'params' => [
                    'phone' => $phone
                ]
            ];
            //发送失败时的警报
            $this->_eventsManager->fire('sms_alarm:handle',$this,$alarmParams);

            $logParams['description'] = "所有短信通道均发送失败";
            $logParams['log_type'] = 0;
            $providers[] = $providerData;
            array_unshift($providers,$providerData);
            $logParams['original_data'] = json_encode($providers);

            BaiyangSmsData::getInstance()->addLog($logParams);

            return ['errcode' => 500 ,'errmsg' => '发送失败'];
        }
        return [$smsResult];
    }

    protected function sendSms(SmsProviderInterface $provider, $params,array  $environment)
    {
        if(empty($provider)){
            return ['errcode' => 500,'errmsg' => '发送失败'];
        }

        $smsResult = $provider->send($params['phone'], $params['template_content'], $params['template_type']);

        $parameters = $smsResult;
        $parameters['provider_code'] = $params['provider_code'];
        $parameters['phone'] = $params['phone'];
        $parameters['content'] = $params['template_content'];
        $parameters['client_code'] = $params['client_code'];
        $parameters['send_type'] = 0;
        $parameters['template_code'] = $params['template_code'];
        $parameters['remark'] = $params['remark'];
        //如果存在缓存配置
        if($this->cache){
            $key = 'sms_balance.count'. strtolower($parameters['provider_code']);

            $this->cache->selectDb(8);

            $balance = $this->cache->getValue($key);

            if(!$balance){
                try {
                    $parameters['balance'] = $provider->getBalance();
                    $this->cache->setValue($key, 'true', 60);
                }catch (\Exception $ex){
                    $parameters['description'] = $ex->getMessage();
                    $this->_eventsManager->fire('sms_send:afterSend',$this,$parameters);
                }
            }
        }else{
            $parameters['balance'] = null;
        }

        //如果更新了余额
        if(isset($parameters['balance'])){
            try {
                $parameters['balance'] = $provider->getBalance();

                $alarmBalance = BaiyangSmsAlarm::findFirst([
                    'alarm_code = :code:',
                    'bind' => ['code' => 'balance_' . strtolower($params['provider_code']) . '_number']
                ]);

                if ($alarmBalance && $parameters['balance'] <= intval($alarmBalance->alarm_value)) {
                    $alarmParams = [
                        'notify_id' => '1003',
                        'params' => [
                            'provider_name' => $params['provider_name'],
                            'number' => $parameters['balance']
                        ]
                    ];
                    $this->_eventsManager->fire('sms_alarm:handle', $this, $alarmParams);
                }
            }catch (\Exception $ex){
                $parameters['description'] = $ex->getMessage();
                $this->_eventsManager->fire('sms_send:afterSend',$this,$parameters);
            }
        }


        $parameters = array_merge($parameters,$environment);

        $this->_eventsManager->fire('sms_send:afterSend',$this,$parameters);
        //自动触发密码修改
        // $this->autoChangePassword($params['provider_code']);

        return $smsResult;
    }

    /**
     * 使用自定义服务商发送自定义短信
     * @param string $providerCode 服务商编号
     * @param string $phone 手机号
     * @param string $content 短信内容
     * @param string $clientCode 客户端编号
     * @return array|mixed
     */
    public function sendByProviderCode($providerCode,$phone,$content,$clientCode)
    {
        //获取一个可用的短信服务商
        $providerData = BaiyangSmsData::getInstance()->getSmsProvider($providerCode);
        if(empty($providerData)){
            return ['errcode' => 50004, 'errmsg' => '未获取到可用的短信服务商'];
        }
        $provider = SmsProviderFactory::create($providerData);

        $parameters = [
            'phone'             => $phone,
            'template_content'  => $content,
            'template_type'     => null,
            'provider_code'     => $providerData['provider_code'] ,
            'client_code'       => $clientCode,
            'remark'            => null,
            'template_code'     => null
        ];
        $parameters = array_merge($parameters,$providerData);

        return $this->sendSms($provider,null,$parameters);
    }

    /**
     * 解析参数并补发短信
     * @param string $name 短信服务商编号
     * @return array
     */
    public function resolveReportAndSend($name)
    {
        $providerData = BaiyangSmsData::getInstance()->getSmsProvider($name);
        if(empty($providerData)){
            return ['errcode' => 500, 'errmsg' => '参数错误'];
        }
        $provider = SmsProviderFactory::create($providerData);

        if($provider === null){
            return ['errcode' => 500, 'errmsg' => '参数错误'];
        }
        $report = $provider->resolveReport();

        if($report !== false){

            //如果发送成功
            if($report['errcode'] === 0){
                BaiyangSmsData::getInstance()->updateRecordByMsgId($report);
                return ['errcode' => 200, 'errmsg'=>'ok'];
            }else{
                $result = SmsService::getInstance()->repeatSend($report['msg_id'],$report['phone']);

                return  $result;
            }
        }else{
            $this->response->setContent('fail');
            $this->response->send();

            return ['errcode' => 500, 'errmsg' => '解析参数失败'];
        }

    }

    /**
     * 短信补发
     * @param string  $msgId
     * @param string $phone
     * @return array
     */
    public function repeatSend($msgId, $phone)
    {
        //获取短信发送记录
        $record = BaiyangSmsData::getInstance()->getSmsRecord($msgId,$phone);
        if(empty($record)){
            return ['errcode' => 500 , 'errmsg' => '短信记录查询不存在'];
        }
        if(time() - strtotime($record->create_time) > 60){
            return ['errcode' => 500 , 'errmsg' => '超过一分钟的不再补发'];
        }


        //获取短信服务商信息
        $providerData = BaiyangSmsData::getInstance()->getRepeatSmsProvider($msgId,$phone);


        if(empty($providerData) === false && empty($record) === false && empty($record->content) === false && empty($record->template_code) === false){


            $provider = SmsProviderFactory::create($providerData);
            if($provider === null){
                return null;
            }
            $template = BaiyangSmsData::getInstance()->getSmsTemplate($record->template_code);

            $smsResult = $provider->send($phone, $record->content, $template->template_type);

            $parameters = $smsResult;
            $parameters['provider_code'] = $providerData['provider_code'];
            $parameters['phone'] = $phone;
            $parameters['content'] = $record->content;
            $parameters['client_code'] = $record->client_code;
            $parameters['send_type'] = 1;
            $parameters['ip_address'] = $record->ip_address;
            $parameters['session_id'] = $record->session_id;
            $parameters['user_agent'] = $record->user_agent;
            $parameters['template_code'] = $template->template_code;
            $parameters['remark'] = '由消息ID: ' . $msgId .'; 手机号: ' . $phone .' 补发';


            $this->_eventsManager->fire('sms_send:afterSend',$this,$parameters);

            //var_dump($rre);exit;

            return $smsResult;
        }
        return ['errcode' => 404,'errmsg' => '消息ID或手机号无效'];
    }

    /**
     * 获取指定渠道的余额
     * @param string|array $providerInfo 渠道编号或渠道数据数组
     * @return int|array
     */
    public function getBalance($providerInfo)
    {
        if (!is_array($providerInfo)) {
            $providerData = $providerInfo;
        } else {
            $providerData = BaiyangSmsData::getInstance()->getSmsProvider($providerInfo);
        }
        if (empty($providerData)) {
            return ['errcode' => 50004, 'errmsg' => '未获取到可用的短信服务商'];
        }
        $provider = SmsProviderFactory::create($providerData);

        if ($provider === null) {
            return ('没有已实现的短信提供商渠道:' . $providerData['provider_code']);
        }
        try {
            $balance = $provider->getBalance();

            return ['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => $balance]];
        } catch (\Exception $ex) {
            return ['errcode' => 50005,'errmsg' => $ex->getMessage()];
        }
    }

    /**
     * 修改密码
     * @param string $providerCode
     * @param string $password
     * @param bool $isAuto 是否是手动修改:0 自动修改/1 手动修改
     * @param string $phone 当手动修改时需要测试的手机号
     * @param int $create_at 修改人手机号
     * @return array
     */
    public function changePassword($providerCode,$password, $isAuto = false, $phone = null,$create_at = 0)
    {
        if (empty($providerCode)) {
            return ['errcode' => 50006, 'errmsg' => '服务商编码不能为空'];
        }
        if(strcasecmp($providerCode,'weiwang') !== 0){
            return ['errcode' => 0, 'errmsg' => 'ok'];
        }
        //获取一个可用的短信服务商
        $providerData = BaiyangSmsData::getInstance()->getSmsProvider($providerCode);
        if (empty($providerData)) {
            return ['errcode' => 50004, 'errmsg' => '未获取到可用的短信服务商'];
        }
        $provider = SmsProviderFactory::create($providerData);

        if ($provider === null) {
            return ['errcode' => 50005, 'errmsg' => '没有已实现的短信提供商渠道' . $providerData['provider_code']];
        }

        try {

            if(empty($phone)) {
                $result = $provider->changePassword($providerData['account'], $providerData['password'], $password);

                $logParams['description'] = '服务商密码'. (!$isAuto?'自动':'手动') .'修改，时间 ' . date('Y-m-d H:i:s') .' ；编号：' . $providerCode . '；修改后密码：'.$password;
                $logParams['original_data'] = json_encode($result);
                BaiyangSmsData::getInstance()->addLog($logParams);

            }else{
                $result = $provider->send($phone,'测试接口是否可以调用');
            }

            if(empty($result) === false && $result['errcode'] == 0){
                $provider_result = BaiyangSmsProvider::findFirst([
                    'provider_code = :code:',
                    'bind' => ['code' => $providerCode]
                ]);
                $provider_result->password = $password;
                $provider_result->next_change_password_time = date('Y-m-d H:i:s', time() + (intval($provider_result->frequency)) * 3600 * 24);

                if($provider_result->save()) {

                    $provider_record = new BaiyangSmsProviderPasswords();
                    $provider_record->create_at = 0;
                    $provider_record->provider_id = $provider_result->provider_id;
                    $provider_record->old_password = $providerData['password'];
                    $provider_record->new_password = $password;
                    $provider_record->modify_type = intval($isAuto);
                    $provider_record->create_time = date('Y-m-d H:i:s');
                    $provider_record->create_at = $create_at;
                    $provider_record->remark = json_encode($result);
                    $provider_record->save();
                    //TODO 正式环境需要开启
                }
            }else{
                $logParams['description'] = '服务商密码'. (!$isAuto?'自动':'手动') .'修改，时间 ' . date('Y-m-d H:i:s') .' ；编号：' . $providerCode . '；修改后密码：'.$password;
                $logParams['original_data'] = json_encode($result);
                BaiyangSmsData::getInstance()->addLog($logParams);

                return $result;
            }
            return ['errcode' => 0, 'errmsg' => 'ok'];
        }catch (\BadMethodCallException $ex){
            return ['errcode' => 50006,'errmsg'=> $ex->getMessage()];
        }catch (\Exception $ex) {
            return ['errcode' => 500,'errmsg'=>$ex->getMessage()];
        }

    }

    /**
     * 自动修改短信服务商密码
     * @param $providerCode
     * @return array
     */
    protected function autoChangePassword($providerCode)
    {
        $provider = BaiyangSmsProvider::findFirst([
            'provider_code = :code:',
            'bind' => [   'code' => $providerCode]
        ]);
        if($provider && ($changeTime = strtotime($provider->next_change_password_time))){
            if($changeTime <= time()){
                $password = Func::create_password(10);

                $result = $this->changePassword($providerCode,$password);
                return $result;
            }
        }
        return ['errcode' => 500, 'errmsg' => '短信服务商不存在'];
    }

    public function echoRecord($count = 20)
    {
        $builder = $this->modelsManager->createBuilder()
            ->columns('*')
            ->from(['log' =>'\Shop\Models\BaiyangSmsLog'])
            ->orderBy('log.log_id DESC')
            ->limit($count);

        $result = $builder->getQuery()->execute();
        return $result;
    }
}