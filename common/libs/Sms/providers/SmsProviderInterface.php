<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/20 0020
 * Time: 10:02
 */

namespace Shop\Libs\Sms\Providers;

/**
 * 统一短信接口
 * Interface SMSInterface
 * @package Shop\Libs
 */
interface SmsProviderInterface
{
    /**
     * 初始化配置信息
     * @param array|null $config
     * @return mixed
     */
    public function initializer(array $config = null);

    /**
     * 发送短信
     * @param string|array $phone 接受的手机号码
     * @param string $msg 短信内容
     * @param string $productId 产品id
     * @param string $time 定时短信的发送时间
     * @param string $key 短信唯一自定义标识
     * @return mixed
     */
    public function send($phone,$msg,$productId = null,\DateTime $time = null, $key = null);


    /**
     * 修改密码
     * @param string $account 账号
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return mixed
     */
    public function changePassword($account,$oldPassword,$newPassword);

    /**
     * 余额查询
     * @return int
     */
    public function getBalance($productId = null);

    /**
     * 解析短信提供商主动推送的消息
     * @return array|bool
     */
    public function resolveReport();

}