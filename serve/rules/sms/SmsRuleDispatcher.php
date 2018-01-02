<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/21 0021
 * Time: 17:36
 */

namespace Shop\Rules\Sms;

use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Listens\SmsAlarmListener;
use Shop\Home\Listens\SmsBlackWhiteListListener;

/**
 * 短信规则调度器
 * Class SmsRuleDispatcher
 * @package Shop\Rules\Sms
 */
class SmsRuleDispatcher
{
    private $rules = [];

    /**
     * 注册需要执行的规则
     * @param string $name 标识名称
     * @param SmsRuleInterface $rule 实现的规则
     */
    public function registerDispatcher($name,SmsRuleInterface $rule)
    {
        $this->rules[$name] = $rule;
    }

    /**
     * 初始化内置的规则
     */
    public function initializer()
    {
        $blackEventManager = new EventsManager();

        $blackEventManager->attach('sms_list',new SmsBlackWhiteListListener());
        $blackEventManager->attach('sms_alarm',new SmsAlarmListener());
        //全局过滤
        $this->registerDispatcher('global',new GlobalRule());
        //白名单过滤
        $whiteList = new WhitelistRule();
        $whiteList->setEventsManager($blackEventManager);
        $this->registerDispatcher('whitelist',$whiteList);
        //黑名单过滤
        $blackList = new BlacklistRule();
        $blackList->setEventsManager($blackEventManager);
        $this->registerDispatcher('blacklist',$blackList);
        //IP地址过滤
        $this->registerDispatcher('ip_address',new IpAddressRule());
        //每分钟限制过滤
        $minuteRule = new MinuteRule();

        $this->registerDispatcher('minute',$minuteRule);
        $this->registerDispatcher('session',new SessionRule());
        //全局频率过滤
        $rateRule = new RateRule();
        $rateRule->setEventsManager($blackEventManager);


        $this->registerDispatcher('rate',$rateRule);
    }

    /**
     * 循环执行规则
     * @param array|null $parameters
     * @return bool
     */
    public function handle(array $parameters = null)
    {
        if(empty($this->rules) ){
            return true;
        }
        foreach ($this->rules as $name => $rule){
            $result = $rule->handle($parameters);
            if($result !== true){
                return $result;
            }
        }
        return true;
    }
}