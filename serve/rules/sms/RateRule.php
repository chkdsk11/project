<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/27 0027
 * Time: 13:28
 */

namespace Shop\Rules\Sms;

use Phalcon\Di;
use Phalcon\Events\ManagerInterface;
use Phalcon\Events\EventsAwareInterface;
use Shop\Home\Datas\BaiyangSmsData;
use Shop\Models\BaiyangSmsAlarm;
use Shop\Models\BaiyangSmsRecords;
use Shop\Models\SmsTemplateStateEnum;

class RateRule implements SmsRuleInterface,EventsAwareInterface
{
    /**
     * @var ManagerInterface
     */
    protected $_eventsManager;
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

    public function handle(array $parameter = null)
    {
        if(empty($this->cache)){
            return true;
        }

        $key = 'sms.limit.numbers';

        $count = $this->cache->incre($key,1) ;

        if($count === 1){
            $this->cache->expire($key,60);
        }

        $alarmResult = BaiyangSmsAlarm::findFirst([
            'alarm_code = :code:',
            'bind' => ['code' => 'minute_limit_captcha']
        ]);

        //检查当前请求条数是否超过150条
        if(empty($alarmResult) === false && $count > intval($alarmResult->alarm_value)){
            //查找一分钟内发送次数最高的场景，并加入缓存黑名单中。
            $record = BaiyangSmsData::getInstance()->getTemplateAndClientByMinute(time());

            //对指定场景进行验证码的启用和禁用
            if(empty($record) === false) {
                $template_code = $record['template_code'];
                $most_client_code = $record['client_code'];

                $state = BaiyangSmsData::getInstance()->isEnableCaptcha($most_client_code, $template_code);

                //如果当前是验证码时禁用状态，则启用
                if ($state == SmsTemplateStateEnum::AUTO_DISABLED) {
                    $template = BaiyangSmsData::getInstance()->getSmsTemplate($template_code);

                    $alarmParams = [
                        'notify_id' => '1001',
                        'params' => ['client_code' => $most_client_code,'template_name' => $template->template_name],
                        'callback' => function()use($alarmResult){
                            $alarmResult->alarm_number = intval($alarmResult->alarm_number) + 1;
                            $alarmResult->save();
                        }
                    ];
                    //触发警报
                    $this->_eventsManager->fire('sms_alarm:handle', $this,$alarmParams);

                    BaiyangSmsData::getInstance()->enableCaptchaForTemplate($most_client_code, $template_code, SmsTemplateStateEnum::AUTO_ENABLE);
                }

                //如果客户端没有标识启用验证码
                if (empty($parameter['captcha'])) {

                    $parameter['description'] = "一分钟内请求超过100次，因此对访问量最高的场景 {$template_code} 自动启用验证码，errcode：60007";
                    $parameter['original_data'] = json_encode($record);
                    $parameter['log_type'] = 1;
                    BaiyangSmsData::getInstance()->addLog($parameter);

                    return ['errcode' => 60007, 'errmsg' => '需要客户端启用图片验证码'];
                }
            }
        }

        $alarmResult = BaiyangSmsAlarm::findFirst([
            'alarm_code = :code:',
            'bind' => ['code' => 'minute_limit_client']
        ]);

        if(empty($alarmResult) === false && $count > intval($alarmResult->alarm_value)){

            //查找一分钟内发送次数最高的场景，并加入缓存黑名单中。
            $record = BaiyangSmsData::getInstance()->getTemplateAndClientByMinute(time());

            if(empty($record) === false) {
                $template_code = $record['template_code'];

                $most_client_code = $record['client_code'];

                $state = BaiyangSmsData::getInstance()->isEnableClient($most_client_code, $template_code);

                //如果当前启用了客户端则禁用
                if ($state == SmsTemplateStateEnum::AUTO_ENABLE || $state == SmsTemplateStateEnum::MANUAL_ENABLE) {
                    $template = BaiyangSmsData::getInstance()->getSmsTemplate($template_code);
                    $alarmParams = [
                        'notify_id' => '1002',
                        'params' => ['template_name' => $template->template_name],
                        'callback' => function () use ($alarmResult) {
                            $alarmResult->alarm_number = intval($alarmResult->alarm_number) + 1;
                            $alarmResult->save();
                        }
                    ];
                    $this->_eventsManager->fire('sms_alarm:handle', $this, $alarmParams);
                    BaiyangSmsData::getInstance()->enableClientForTemplate($most_client_code, $template_code, SmsTemplateStateEnum::AUTO_DISABLED);

                }

                $parameter['description'] = "客户端 {$most_client_code}, 一分钟内请求超过 {$alarmResult->alarm_value} 次，因此自动禁用该场景 {$template_code} 的短信发送，errcode：60008";
                $parameter['original_data'] = json_encode($record);
                $parameter['log_type'] = 1;
                BaiyangSmsData::getInstance()->addLog($parameter);
                //如果禁用的是当前场景则直接返回
                if (strcasecmp($parameter['template_code'], $template_code) === 0 && strcasecmp($parameter['client_code'],$most_client_code) === 0) {
                    return ['errcode' => 60008, 'errmsg' => '当前场景已被禁用'];
                }
            }
        }
        return true;
    }
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    public function getEventsManager()
    {
        return $this->_eventsManager;
    }
}