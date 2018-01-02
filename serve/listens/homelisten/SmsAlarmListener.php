<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/26 0026
 * Time: 11:13
 */

namespace Shop\Home\Listens;

use Phalcon\Di;
use Shop\Home\Datas\BaiyangSmsData;
use Shop\Libs\Sms\Providers\SmsProviderFactory;
use Shop\Models\BaiyangConfig;
use Shop\Models\BaiyangSmsAlarm;
use Shop\Models\BaiyangSmsAlarmNotify;

/**
 * 警报处理事件
 * Class SmsAlarmListener
 * @package Shop\Home\Listens
 */
class SmsAlarmListener extends BaseListen
{
    protected $templates = [
        '1001'  => '{client_code}客户端的{template_name}场景出现异常请求，请及时监控并处理!',
        '1002'  => '{template_name}短信请求操作频繁，已停止向该短信场景提供短信服务!',
        '1003'  => '{provider_name}短信平台剩余短信数量为{number}条，请及时充值。',
        '1004'  => '给{phone}用户发送短信失败，请及时处理。'
    ];

    /**
     * @var \Shop\Libs\CacheRedis
     */
    protected $cache;

    public function __construct()
    {
        $this->cache = Di::getDefault()->getShared('cache');
        if(empty($this->cache) === false){
            $this->cache->selectDb(8);
        }else{
            $this->cache = false;
        }
    }

    public function handle($event,$class, $parameter)
    {
        $config = BaiyangConfig::findFirst([
           'config_sign = :name:',
            'bind' => ['name' => 'smsAlarm']
        ]);
        //如果没有开启预警通知
        if(empty($config) === false && $config->config_value){
            return false;
        }
        $notifyId = empty($parameter['notify_id']) ? null : $parameter['notify_id'];

        if(isset($this->templates[$notifyId])) {
            $content = $this->templates[$notifyId];
            foreach ($parameter['params'] as $key => $param) {
                $content = str_replace('{' . $key . '}', $param, $content);
            }
            //如果不存在未替换的变量则发送警报
            if (stripos($content, '{') === false) {
                $logParams['description'] = '警报触发:' . $notifyId;
                $logParams['content'] = $content;

                $notifyPhone = BaiyangSmsAlarmNotify::find([
                    'user_state = 0',
                ]);
                if (empty($notifyPhone) === false) {

                    //获取一个可用的短信服务商
                    $providerData = BaiyangSmsData::getInstance()->getSmsProvider();
                    if (empty($providerData)) {
                        return ['errcode' => 50004, 'errmsg' => '未获取到可用的短信服务商'];
                    }
                    $provider = SmsProviderFactory::create($providerData);
                    foreach ($notifyPhone as $index => $item) {
                        $logParams['original_data'] = $item->phone;
                        $result = $provider->send($item->phone, $content);
                        if($result['errcode'] != 0){
                            $result = $this->sendReserve($providerData['provider_code'],$item->phone,$content);
                            if($result){
                                BaiyangSmsData::getInstance()->addLog($logParams);
                                BaiyangSmsData::getInstance()->updateQuantity($providerData['provider_code'],-1,1);
                            }else{
                                $logParams['description'] .= ';发送失败: ' . json_encode($result);
                                BaiyangSmsData::getInstance()->addLog($logParams);
                            }
                            if($result && isset($parameter['callback'])&& is_callable($parameter['callback'])){
                                $parameter['callback']();
                            }
                        }else{
                            BaiyangSmsData::getInstance()->addLog($logParams);
                            BaiyangSmsData::getInstance()->updateQuantity($providerData['provider_code'],-1,1);
                            if(isset($parameter['callback']) && is_callable($parameter['callback'])){
                                $parameter['callback']();
                            }
                        }
                    }
                }
            }

        }
        return true;

    }

    protected function sendReserve($provider_code,$phone,$content)
    {
        $providers = BaiyangSmsData::getInstance()->getReserveProvider($provider_code);
        $result = false;
        if(empty($providers) === false){
            foreach ($providers as $i=>$providerData){
                $provider = SmsProviderFactory::create($providerData);
                if(empty($provider)){
                    $result = $provider->send($phone, $content);
                    if($result['errcode'] === 0){
                        return true;
                    }
                }
            }
            return $result;
        }
        return $result;
    }
}