<?php

/**
 *
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Datas\BaiyangRoleData;
use Shop\Datas\BaseData;
use Shop\Datas\SiteData;
use Shop\Services\AdminService;
use Shop\Services\AdminRoleService;
use Shop\Services\BaseService;
use Shop\Models\CacheKey;
use Shop\Libs\Func;

/**
 * Class ControllerBase
 * @package Shop\Admin\Controllers
 * 很坑爹
 */
class ControllerBase extends Controller
{

    /**
     * [$code description]
     * @var array
     */
    public $code = array('code'=>201,'msg'=>'失败','data'=>'');

    /**
     * [$error 错误提示]
     * @var string
     */
    public $error = '';

    /**
     *  相当于构造方法
     */
    public function initialize()
    {
        // 给模板引擎添加自定义函数
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        /** @var \Phalcon\Mvc\View\Engine\Volt\Compiler $compiler */
        $compiler = $volt->getCompiler();
        $compiler->addFunction('count', 'count');
        $compiler->addFunction('is_array', 'is_array');
        // 是否登录
        $this->checkIsLogin();
        $admin_role_id = $this->session->get('role_id');
        $admin_is_super = $this->is_super($admin_role_id);
        // 检查权限
        $res = $this->filterAccess($admin_role_id,$admin_is_super);
        if($res){
            $this->assignMenu($admin_role_id,$admin_is_super);
        }
        AdminService::getInstance()->addLog();
    }

    /**
     * [is_super description]
     * @param  integer $role_id [description]
     * @return boolean          [description]
     */
    public function is_super($role_id = 0)
    {
        if(empty($role_id)){
            return 0;
        }
        $role_id_info = AdminRoleService::getInstance()->getOneCahce($role_id);
        return $role_id_info['is_super'];
    }

    /**
     * [filterAccess 检查权限]
     * @param  integer $admin_role_id  [description]
     * @param  integer $admin_is_super [description]
     * @return [type]                  [description]
     */
    public function filterAccess($admin_role_id = 0,$admin_is_super = 0)
    {
        // 此处做权限判断
        $AdminRoleService = AdminRoleService::getInstance();
        $res = $AdminRoleService->filterAccess($admin_role_id,$admin_is_super);
        if(empty($res)){
            $res = false;
            $this->error = $AdminRoleService->error;
        }
        if($res === -1){
            // 退出
            AdminService::getInstance()->logout();
            $res = false;
            $this->error = $AdminRoleService->error;
        }
        if(empty($res)){
            $jump_url = getenv("HTTP_REFERER");
            $has_login = stripos($jump_url,'login');
            if($has_login !== false){
                $jump_url = '';
            }
            if(empty($jump_url)){
                $jump_url = AdminService::getInstance()->getDefaultUrl($admin_role_id,$admin_is_super);
                if(empty($jump_url)){
                    $jump_url = CacheKey::ADMIN_LOGOUT_KEY;
                }
            }
            if($this->request->isAjax()){
                // 退出
                $this->code['msg'] = $this->error;
                $this->code['data'] = $jump_url;
                $this->view->disable();
                echo json_encode($this->code);exit;
            }else{
                // 此处的return没用？？initialize()下面的程序还会执行
                $this->success($this->error,$jump_url,'error',3);
                // 用这个方法，initialize()的下面的还会执行
            }
            return false;
        }
        return true;
    }

    /**
     * [assignMenu 菜单显示]
     * @param  integer $admin_role_id  [description]
     * @param  integer $admin_is_super [description]
     * @return [type]                  [description]
     */
    public function assignMenu($admin_role_id = 0,$admin_is_super = 0)
    {
        if(!$this->request->isAjax()){
            $AdminRoleService = AdminRoleService::getInstance();
            $data = $AdminRoleService->assignMenu($admin_role_id,$admin_is_super);
            if($data){
                $this->view->setVar('main_menu',$data['main_menu']);
                $this->view->setVar('current_menu',$data['current_menu']);
                $this->view->setVar('bread_crumb',$data['bread_crumb']);
            }else{
                $this->error = $AdminRoleService->error;
                // 此处的return没用？？initialize()下面的程序还会执行
                $jump_url = getenv("HTTP_REFERER");
                $has_login = stripos($jump_url,'login');
                if($has_login !== false){
                    $jump_url = '';
                }
                if(empty($jump_url)){
                    $jump_url = AdminService::getInstance()->getDefaultUrl($admin_role_id,$admin_is_super);
                    if(empty($jump_url)){
                        $jump_url = CacheKey::ADMIN_LOGOUT_KEY;
                    }
                }
                $this->success($this->error,$jump_url,'error',3);
                // 用这个方法，initialize()的下面的还会执行
            }
        }
    }

    /**
     * ajax请求head
     */
    protected function AjaxHead()
    {
        $this->view->disable();
        $this->response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setContentType('application/json', 'UTF-8');
    }

    /**
     * 统一get请求，不相信get参数
     *  @param $param=string 参数名字
     * @param   $type=string 数据类型 example:int string
     * @param $default 默认值
     * @return mixed
     */
    protected function getParam($param,$type,$default=null)
    {
        if(!empty($default)){
            return htmlspecialchars($this->request->get($param,$type,$default));
        }else{
            return htmlspecialchars($this->request->get($param,$type));
        }
    }

    /**
     * @remark  收集、处理所有post参数
     * @param $param=array 所有post参数
     * @param $type=string 处理数据类型
     * @param null $default 默认值
     * @param null $timeString=string 时间字段
     * @return mixed
     * @author 杨永坚
     * @modify 吴俊华 增加过滤函数 2016-08-26
     */
    protected function postParam($param, $type, $default = null, $timeString = null)
    {
        if(!empty($default))
        {
            foreach ($param as $k=>$v){
                $param[$k] = !is_array($this->request->getPost($k, $type, $default)) ? htmlspecialchars($this->request->getPost($k, $type, $default)) : $this->request->getPost($k, $type, $default);
            }
        }else{
            foreach ($param as $k=>$v){
                $param[$k] = !is_array($this->request->getPost($k, $type)) ? htmlspecialchars($this->request->getPost($k, $type)) : $this->request->getPost($k, $type);
            }
        }
        if($timeString)
        {
            $param[$timeString] = time();
        }
        return $param;
    }

    /**
     * 设置title
     */
    protected function setTitle($title='诚仁堂商城')
    {
        $this->view->setVar('title',$title);
    }

    /**
     * 自动组成url,用于搜索列表页
     * @return  staring   组织的url
     * User: 梁伟
     * Date: 2016/8/31
     * Time: 17:28
     */
    public function automaticGetUrl()
    {
        $url = '';
        if($this->request->isGet()){
            //$arr = $this->request->get();
			$arr = $_GET;
            foreach($arr as $k => $v){
                if($k == '_url'){
                    $url .= htmlspecialchars($this->request->get($k,'trim')) . '?';
                }else if(is_array($v)){
                    foreach($v as $k1=>$v1){
                        $url .= $k . "[]=" . htmlspecialchars($v1) . "&";
                    }
                }else if($k != 'page'){
                    $url .= $k . "=" . htmlspecialchars($this->request->get($k,'trim')) . "&";
                }
            }
        }else{
            $url .= "/" . $this->dispatcher->getControllerName() . "/" . $this->dispatcher->getActionName();
        };
        return $url . "page=";
    }
    /**
     * 跳转提示页
     * @param   array(
            'info'      =>  string,//提示信息，默认为空
     *      'url'       =>  string,//跳转url，默认首页
     *      'status'    =>  string,//成功|错误提示，默认成功(success|error)
     *      'time'      =>  int,//等待时间，默认3秒
     * )
     * User: 梁伟
     * Date: 2016/9/5
     * Time: 14:40
     */
    public function success($info = '',$url = '/',$status = 'success',$time = 3,$qx = 0)
    {
        if($qx == 1){
            $info = '获取访问权限失败';
        }
        $this->view->setVar('infoI',$info);
        $this->view->setVar('url',$url);
        $this->view->setVar('time',$time);
        $this->view->setVar('status',$status);
        $this->view->setTemplateAfter('success');
    }

    /**
     * 警告提示语
     * string $msg 提示语
     * string $url 跳转地址
     * User: 梁育权
     * Date: 2017/03/10
     * Time: 14:40
     */
    public function msgRedirect($msg = '',$url = '/')
    {
        $this->view->disable();
        echo "<script type='text/javascript'>alert('".$msg."');location.href='".$url."';</script>";
    }

    /**
     * [checkIsLogin description]
     * @return [type] [description]
     */
    public function checkIsLogin()
    {
        $res = AdminService::getInstance()->checkIsLogin();
        if(empty($res)){
           if($this->request->isAjax()){
                $this->code['msg'] = "请先登录后再操作";
                $this->view->disable();
                echo json_encode($this->code);exit;
                // 此处的return没用？？
                // return $this->response->setJsonContent($list);
            }else{
                $login_url = CacheKey::ADMIN_LOGOUT_KEY;
                header("Location:{$login_url}");
                // 此处的response没用？？initialize()下面的程序还会执行
                // $this->response->redirect(CacheKey::ADMIN_LOGOUT_KEY);
            }
        }
    }
}
