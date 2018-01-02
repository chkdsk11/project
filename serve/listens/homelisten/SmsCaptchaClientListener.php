<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/28 0028
 * Time: 17:37
 */

namespace Shop\Home\Listens;

use Shop\Home\Datas\BaiyangSmsData;
use Shop\Models\BaiyangSmsClient;
use Shop\Models\BaiyangSmsTemplate;

/**
 * 验证码和客户端启用禁用事件
 * Class SmsCaptchaClientListener
 * @package Shop\Home\Listens
 */
class SmsCaptchaClientListener extends BaseListen
{

    public function handle($event,$class, $relationship)
    {
        if(is_array($relationship)){
            $relationship = (object)$relationship;
        }
        $template = BaiyangSmsTemplate::findFirst($relationship->template_id);
        if(empty($template)){
            return true;
        }
        $client = BaiyangSmsClient::findFirst($relationship->client_id);
        if(empty($client)){
            return true;
        }
        return BaiyangSmsData::getInstance()->isEnableCaptcha($client->client_code,$template->template_code,true);

    }
}