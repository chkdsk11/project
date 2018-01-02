<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/21 0021
 * Time: 上午 10:32
 */

namespace Shop\Admin\Controllers;

require APP_PATH."/vendor/autoload.php";
use Hprose\Http\Client;
use Hprose\Future;
use Shop\Libs\FastDfsClient;
use Shop\Models\HttpStatus;

class MainController extends ControllerBase
{
    public function indexAction()
    {
//        $fdfs=new \FastDFS();
//        $class=new \ReflectionClass($fdfs);
//        echo '<pre>';
//        var_dump($class->getMethods());
        $aa= "SUCCESS";
        $class='Shop\Models\HttpStatus';
        echo $class::SUCCESS;
    }

    /**
     *
     */
    public function testAction()
    {

    }

    /**
     *
     */
    public function uploadAction()
    {
        $this->view->disable();
//        $dir='/data/Chrysanthemum.jpg';
//        $uploadInfo=$this->FastDfs->uploadByFilename($dir,2,'G1');
//        echo $uploadInfo;
        $ret=$this->FastDfs->deleteFile('M00/00/01/rBCA-1g0DnGAV2FYAA1rIuRd3Es932.jpg','G1');
        var_dump($ret);

    }
}