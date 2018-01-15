<?php
namespace Shop\Admin\Controllers;
use Shop\Services\AdminResourceService;
use Phalcon\Mvc\Application\Exception;
use Phalcon\Mvc\Controller;
use Shop\Services\AdminRoleService;
use Shop\Datas\BaiyangAdminRoleData;
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
                $admin_id = $this->session->get('admin_id');
                $this->cache->delete('statistics-'.$admin_id);
                exit(json_encode(array('error'=>0,'msg'=>'清除缓存成功')));
            }else {
                $catch_array = $this->cache->getValue('statistics-'.$admin_id);
                if ($catch_array) {
                    $return_list = $catch_array;
                } else {
                    $admin_role_id = $this->session->get('role_id');
                    $list = AdminResourceService::getInstance()->getDefaultAll();
                    $role_id_info = BaiyangAdminRoleData::getInstance()->getOneCahce($admin_role_id);
                    $statistics = StatisticsService::getInstance();
                    $temp = array();
                    $return_list = array();
                    foreach ($list[0]['son'] as $item) {
                        if($item['name'] == '仪表盘'){
                            $temp = $item['son'];
                            break;
                        }
                    }

                    if(!empty($temp)){
                        if($role_id_info['is_super'] == '1'){
                            foreach ($temp as $item) {
                                $action = $item['action'];
                                $return_list[$item['action']] = $statistics->$action();
                            }
                        }else {
                            $rules_array = explode(',', $role_id_info['rules']);
                            foreach ($temp as $item) {
                                if(in_array($item['id'],$rules_array)){
                                    $action = $item['action'];
                                    $return_list[$item['action']] = $statistics->$action();
                                }
                            }
                        }
                    }
                    $this->cache->setValue('statistics-'.$admin_id, $return_list, 3600);
                }
                exit(json_encode($return_list));
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

