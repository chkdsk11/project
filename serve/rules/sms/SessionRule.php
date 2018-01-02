<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/27 0027
 * Time: 9:11
 */

namespace Shop\Rules\Sms;

use Phalcon\Di;

/**
 * Session 校验规则 ， 依赖于Redis接口。
 * Class SessionRule
 * @package Shop\Rules\Sms
 */
class SessionRule implements SmsRuleInterface
{
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
        if (empty($parameter) || (empty($parameter['session_id']) && empty($parameter['client_code']))) {
            return true;
        }
        $clientCode = strtolower($parameter['client_code']);
        $sessionId = $parameter['session_id'];

        if (in_array($clientCode, ['wap', 'pc'])) {

            if(empty($sessionId) && empty($parameter['captcha'])){

                return ['errcode' => 60009, 'errmsg' => '需要客户端启用图片验证码','captcha' => true];
            }

            $key = 'sms.limit.session.id.' . $sessionId;

            $count = $this->cache ? $this->cache->incre($key,1) : 0;

            if($count === 1){
                $this->cache->expire($key,60);
            }

            if($count > 5 && empty($parameter['captcha'])){

                return ['errcode' => 60009, 'errmsg' => '需要客户端启用图片验证码','captcha' => true];
            }
        }
        return true;
    }

}