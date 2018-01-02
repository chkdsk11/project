<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/28 0028
 * Time: 14:59
 */

namespace Shop\Rules\Sms;


use Shop\Home\Datas\BaiyangSmsData;
use Shop\Models\BaiyangSmsLog;
use Shop\Models\SmsTemplateStateEnum;

/***
 * 全局的过滤规则
 * Class GlobalRule
 * @package Shop\Rules\Sms
 */
class GlobalRule implements SmsRuleInterface
{
    public function handle(array $parameter = null)
    {
        if(empty($parameter) || empty($parameter['template_code']) || empty($parameter['client_code'])){
            return ['errcode' => 40404, 'errmsg' => '确实必须的参数'];
        }
        $template_code = $parameter['template_code'];
        $client_code = $parameter['client_code'];
        $relationship = BaiyangSmsData::getInstance()->getRelationship($client_code,$template_code);

        if(!$relationship){
            return ['errcode' => 60010,'errmsg' => '模板不存在或场景未开启'];
        }

        //如果客户端是手动禁用，则直接返回错误信息
        if($relationship->is_enable_client == SmsTemplateStateEnum::MANUAL_DISABLED){
            $parameter['description'] = '客户端 ' . $client_code . ' 处于手动禁用状态，返回客户端 errcode：60001';
            $parameter['original_data'] = json_encode($relationship);
            BaiyangSmsData::getInstance()->addLog($parameter);

            return ['errcode'=> 60001, 'errmsg' => '指定客户端和短信场景没有启用短信服务'];
        }
        //如果客户端是自动禁用，则判断禁用时间是否过期
        if($relationship->is_enable_client == SmsTemplateStateEnum::AUTO_DISABLED){
            if(time() - strtotime($relationship->modify_time) > 12*3600){
                $parameter['description'] = '客户端 ' . $client_code . ' 处于自动禁用状态，且已过12小时有效期，因此更改为自动启用状态。';
                $parameter['original_data'] = json_encode($relationship);
                $parameter['log_type'] = 1;
                BaiyangSmsData::getInstance()->addLog($parameter);

                BaiyangSmsData::getInstance()->enableClientForTemplate($client_code,$template_code,SmsTemplateStateEnum::AUTO_ENABLE);
            }else{
                $parameter['description'] = '客户端 ' . $client_code . ' 处于自动禁用状态，返回客户端编号 errcode：60001';
                $parameter['original_data'] = json_encode($relationship);
                $parameter['log_type'] = 1;
                BaiyangSmsData::getInstance()->addLog($parameter);

                return ['errcode'=> 60001, 'errmsg' => '指定客户端和短信场景没有启用短信服务'];
            }
        }

        //如果启用了验证码
        if($relationship->is_enable_captcha == SmsTemplateStateEnum::MANUAL_ENABLE && empty($parameter['captcha'])){
            $parameter['description'] = '客户端 ' . $client_code . ' 手动启用了验证码，但客户端未提供 captcha 标识，errcode：60002';
            $parameter['original_data'] = json_encode($relationship);
            BaiyangSmsData::getInstance()->addLog($parameter);

            return ['errcode' => 60002, 'errmsg' => '需要客户端启用图片验证码', 'captcha' => true];
        }
        //如果是自动启用的验证码，则判断时间是否过期
        if($relationship->is_enable_captcha == SmsTemplateStateEnum::AUTO_ENABLE || $relationship->is_enable_captcha == SmsTemplateStateEnum::MANUAL_ENABLE){
            if(time() - strtotime($relationship->modify_time) > 12*3600){
                $parameter['description'] = "客户端 $client_code ，场景 {$template_code} 处于启用验证码状态，且已过12小时有效期，因此自动禁用验证码。";
                $parameter['original_data'] = json_encode($relationship);
                BaiyangSmsData::getInstance()->addLog($parameter);

                BaiyangSmsData::getInstance()->enableCaptchaForTemplate($client_code,$template_code,SmsTemplateStateEnum::AUTO_DISABLED);
                return true;
            }elseif(empty($parameter['captcha'])){
                $parameter['description'] = "客户端 $client_code ，场景 {$template_code} 处于启用验证码状态，但客户端未提供 captcha 标识，errcode：60002";
                $parameter['original_data'] = json_encode($relationship);
                $parameter['log_type'] = 1;
                BaiyangSmsData::getInstance()->addLog($parameter);

                return ['errcode' => 60002, 'errmsg' => '需要客户端启用图片验证码', 'captcha' => true];
            }
        }
        return true;
    }

}