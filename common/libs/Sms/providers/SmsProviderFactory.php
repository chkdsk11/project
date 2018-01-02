<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/23 0023
 * Time: 9:08
 */

namespace Shop\Libs\Sms\Providers;

/**
 * 短信发送渠道工厂模式
 * Class SmsProviderFactory
 * @package Shop\Libs\Sms\Providers
 */
class SmsProviderFactory
{
    public static function create(array $parameter)
    {
        if(isset($parameter['provider_code'])){
            $params['account'] = $parameter['account'];

            $params['password'] = $parameter['password'];

            $params['access_token'] = $parameter['access_token'];

            if (empty($providerName['customize']) === false && ($customize = json_decode($providerName['customize'])) !== null) {
                $params = array_merge($params, $customize);
            }
            //助通
            if(strcasecmp($parameter['provider_code'],'ZhuTong') === 0){

                $provider = new ZhuTongSmsProvider();
                $provider->initializer($params);
                return $provider;
            }
            //微网
            if(strcasecmp($parameter['provider_code'],'WeiWang') === 0){
                $provider = new WeiWangSmsProvider();
                $provider->initializer($params);
                return $provider;
            }
        }
        return null;
    }
}