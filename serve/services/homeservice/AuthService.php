<?php
namespace Shop\Home\Services;

use Shop\Home\Listens\AuthListener;
use Shop\Home\Services\BaseService;
use Shop\Libs\Curl;
use Shop\Libs\Func;
use Shop\Models\HttpStatus;
use Shop\Home\Datas\BaiyangConsigneeLimitData;

class AuthService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 身份证验证
     *
     * @param $param
     * @return \array[]
     */
    public function idCardVerify($param)
    {
        $this->eventsManager->attach('authListener', new AuthListener());
        $data = $this->eventsManager->fire('authListener:idCardVerify', $this, $param);
        return $this->uniteReturnResult($data['code']);
    }
}