<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/27 0027
 * Time: 10:15
 */

namespace Shop\Home\Services;


use Shop\Libs\UserAgent;

class UserAgentService extends BaseService
{
    public function isMobile($userAgent)
    {
        return UserAgent::isMobile($userAgent);
    }

    public function isAndroid($userAgent)
    {
        return UserAgent::isAndroid($userAgent);
    }
}