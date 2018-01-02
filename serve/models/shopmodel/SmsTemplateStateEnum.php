<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/29 0029
 * Time: 10:05
 */

namespace Shop\Models;

/**
 * 短信场景启用状态
 */
class SmsTemplateStateEnum
{
    /**
     * 手动启用
     */
    const MANUAL_ENABLE = 0;
    /**
     * 手动禁用
     */
    const MANUAL_DISABLED = 1;
    /**
     * 自动启用
     */
    const AUTO_ENABLE = 2;
    /**
     * 自动禁用
     */
    const AUTO_DISABLED = 3;
}