<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/11 0011
 * Time: 上午 10:32
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;

class ErrorsController extends ControllerBase
{
    public function show404Action()
    {
        $this->view->disable();
        echo '404 not found';
    }
}