<?php
/**
 * 登录控制器
 * Class LoginController
 * Author: edgeto/qiuqiuyuan
 * Date: 2017/5/9
 * Time: 15:52
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\AdminService;
use Shop\Services\AdminRoleService;
use Shop\Models\CacheKey;
use Shop\Libs\Func;

class LoginController extends Controller
{

	/**
     * [$code description]
     * @var array
     */
    public $code = array('code'=>201,'msg'=>'失败','data'=>'');

    /**
     *  初始方法,可以当__construct方法使用
     */
    public function initialize()
    {
        
    }

	/**
	 * [loginAction description]
	 * @return [type] [description]
	 * @author: edgeto/qiuqiuyuan
	 */
    public function loginAction()
    {
        // 登录
        if($this->request->isPost()){
            $jump_url = '';
            $adminService = AdminService::getInstance();
            $post = $this->request->getPost();
            $res = $adminService->loginAuth($post);
            if(empty($res)){
                $this->code['msg'] = $adminService->error;
                $this->code['data']['field'] = $adminService->field;
            }else{
                // 找默认链接
                $role_id = $this->session->get('role_id');
                $jump_url = $adminService->getDefaultUrl($role_id);
                if($jump_url){
                    $this->code['code'] = 200;
                    $this->code['msg'] = '登录成功';
                    $this->code['data'] = $jump_url;
                }else{
                    $this->code['msg'] = $adminService->error;
                    $this->code['data']['field'] = $adminService->field;
                }
            }
        	return $this->response->setJsonContent($this->code);
        }else{
            // 如果已经登录，跳转到菜单页
            if($this->session->get('is_login')){
                $adminService = AdminService::getInstance();
                $role_id = $this->session->get('role_id');
                $jump_url = $adminService->getDefaultUrl($role_id);
                $this->response->redirect($jump_url);
            }
        }
    }

    /**
     * 退出登录
     */
    public function logoutAction()
    {
        $adminService = AdminService::getInstance();
        $res = $adminService->logout();
        $this->response->redirect(CacheKey::ADMIN_LOGIN_KEY);
    }

    /**
     * [accessAction PC后台登录授权]
     * @return [type] [description]
     */
    public function accessAction()
    {
        $from = $this->request->get('from');
        if(empty($from)){
            $this->response->redirect(CacheKey::ADMIN_LOGIN_KEY);
        }else{
            $admin_id = $this->session->get('admin_id');
            if(empty($admin_id)){
                $this->response->redirect(CacheKey::ADMIN_LOGIN_KEY);
            }else{
                $Func = new Func();
                $admin_to_admin_key = CacheKey::ADMIN_TO_ADMIN;
                $time = time();
                $str = $admin_id . $time . $from;
                $sign = $Func->Md5Sha1($str,$admin_to_admin_key);
                $url = 'id=' . $admin_id . '&time=' . $time . '&sign=' . $sign . '&self_url=' . urlencode($from);
                $has_wenhao = stripos($from,'?');
                if($has_wenhao !== false){
                    $from .= '&' . $url;
                }else{
                    $from .= '?' . $url;
                }
                header("Location:{$from}");
            }
        }
    }

}