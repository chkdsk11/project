<?php
/**
 *
 *
 *
 */
namespace Shop\Admin\Controllers;
use Shop\Admin\Controllers\ControllerBase;
use Phalcon\Mvc\Application\Exception;
use Phalcon\Mvc\Controller;
use Shop\Datas\BaiyangRoleData;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangSku;
use Shop\Services\AdminService;
use Shop\Services\CategoryService;
use Shop\Services\StatisticsService;

class IndexController extends ControllerBase
{

    /**
     * [initialize 相当于构造方法,如需要用，必须先调用父级的]
     * @return [type] [description]
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 主页
     */
    public function indexAction()
    {
        if($this->request->isPost()) {
            $post_type = $this->request->getPost('type');
            if($post_type == 'clearCatch'){
                $this->cache->delete('statistics');
                exit(json_encode(array('error'=>0,'msg'=>'清除缓存成功')));
            }else {
                $catch_array = $this->cache->getValue('statistics');
                if ($catch_array) {
                    $array = $catch_array;
                } else {
                    $StatisticsService = StatisticsService::getInstance();
                    $array = $StatisticsService->getThisWeekCount();
                    $this->cache->setValue('statistics', $array, 3600);
                }
                exit(json_encode($array));
            }
        }else {
            $this->view->pick('index/statistics');
        }
    }

    public function siteBanAction(){

    }

    public function roleBanAction(){

    }

    public function userBanAction(){

    }
}

