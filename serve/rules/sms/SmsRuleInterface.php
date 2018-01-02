<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/21 0021
 * Time: 17:22
 */

namespace Shop\Rules\Sms;

/**
 * 定义短信防爆规则接口
 * Interface SmsRuleInterface
 * @package Shop\Libs\Sms\Rules
 */
interface SmsRuleInterface
{

    public function handle(array $parameter = null);
}