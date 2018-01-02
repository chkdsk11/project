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
            $type = $this->request->getPost('type');
            $array =array();
            $StatisticsService = StatisticsService::getInstance();
            if($type == 'top') {
                $catch_array = $this->CacheRedis->getValue('statistics_catch_top');
                if($catch_array){
                    $array['info'] = $catch_array['info'];
                    $array['userCountList']	= $catch_array['userCountList'];
                    $array['goodsCountList'] = $catch_array['goodsCountList'];
                }else{
                    $userCount = $StatisticsService->totalUserCount();
                    $info['totalCount'] = array_sum($userCount);
                    $userCountList = $StatisticsService->userCountList();
                    $goodsCountList = $StatisticsService->goodsCountList();
                    $info['orderCount'] = $StatisticsService->orderCount();
                    $array['info'] = $info;
                    $array['userCountList']	= $userCountList;
                    $array['goodsCountList'] = $goodsCountList;
                    $this->CacheRedis->setValue('statistics_catch_top',$array,3600);
                }
            }else if($type == 'down') {
                $catch_array = $this->CacheRedis->getValue('statistics_catch_down');
                if($catch_array){
                    $array['orderCountList']	= $catch_array['orderCountList'];
                    $array['kjOrderCountList'] = $catch_array['kjOrderCountList'];
                }else{
                    $orderCountList = $StatisticsService->orderCountList();
                    $kjOrderCountList = $StatisticsService->kjOrderCountList();
                    $array['orderCountList']	= $orderCountList;
                    $array['kjOrderCountList'] = $kjOrderCountList;
                    $this->CacheRedis->setValue('statistics_catch_down',$array,3600);
                }
            }else{
                $this->CacheRedis->delete('statistics_catch_top');
                $this->CacheRedis->delete('statistics_catch_down');
                exit(json_encode(array('error'=> 0)));
            }
            exit(json_encode($array));
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

