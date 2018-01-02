<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/8 0008
 * Time: 上午 9:52
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;

class CodeController extends Controller
{
    /**
     * 生成验证码
     */
    public function indexAction()
    {
        $this->view->disable();
        $this->code->createCodeImage('code');
    }
}