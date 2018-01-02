<?php
/**
 * 管理员角色数据处理
 * Class BaiyangAdminRoleData
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */
namespace Shop\Datas;
use Shop\Models\CacheKey;
use Shop\Models\BaiyangAdmin;

class BaiyangAdminData extends BaseData
{

	/**
     * 必须声明此静态属性，单例模式下防止实例对象覆盖
     * @var null
     */
    protected static $instance = null;

    /**
     * [$table description]
     * @var string
     */
    public $table = "\\Shop\\Models\\BaiyangAdmin";

    /**
     * [$error description]
     * @var string
     */
    public $error = '';

    /**
     * [getPage description]
     * @param  string $where      [description]
     * @param  array  $conditions [description]
     * @param  array  $pageParam  [description]
     * @return [type]             [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function getPage($where = '',$conditions = array(),$pageParam = array())
    {
    	$selections = "*";
    	$table = $this->table;
    	// 总记录数
        $counts = count(BaseData::getInstance()->select($selections,$table,$conditions,$where));
       	// 分页
        $pages['page'] = isset($pageParam['page']) ? $pageParam['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($pageParam['psize'])? $pageParam['psize'] : 15;
        $pages['url'] = $pageParam['url'];
        $pages['url_back'] = isset($pageParam['url_back']) ? $pageParam['url_back'] : '';
        $pages['home_page'] = isset($pageParam['home_page']) ? $pageParam['home_page'] : '';
        $pages['size'] = isset($pageParam['size'])?$pageParam['size']:5;
        $page = $this->page->pageDetail($pages);
        $where .= ' LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$where);
         if(empty($result)){
         	$this->error = '没有数据';
            return false;
        }
        return array('list'=>$result,'page'=>$page['page']);
    }

    /**
     * [add description]
     * @param array $data [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function add($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdmin = new BaiyangAdmin();
        $res = $BaiyangAdmin->icreate($data);
        if(empty($res)){
            $messages = $BaiyangAdmin->getMessages();
            foreach ($messages as $key => $message) {
                $this->error['message'] = $message->getMessage();
                $this->error['field'] = $message->getField();
                break;
            }
            return false;
        }
        return true;
    }

    /**
     * [edit description]
     * @param  array  $data [description]
     * @return [type]       [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function edit($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdmin = new BaiyangAdmin();
        $res = $BaiyangAdmin->iupdate($data);
        if(empty($res)){
            $messages = $BaiyangAdmin->getMessages();
            foreach ($messages as $key => $message) {
                $this->error['message'] = $message->getMessage();
                $this->error['field'] = $message->getField();
                break;
            }
            return false;
        }
        return true;
    }

    /**
     * [getById description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function getById($id = 0)
    {
        if(empty($id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdmin = new BaiyangAdmin();
        $res = $BaiyangAdmin::findFirstById($id);
        if($res){
            return $res->toArray();
        }else{
            $this->error = "管理员不存在！";
            return false;
        }
    }

    /**
     * [getByAdminAccount description]
     * @param  string $id [description]
     * @return [type]      [description]
     */
    public function getByAdminAccount($admin_account = '')
    {
        if(empty($admin_account)){
            $this->error = '管理员用户名不能为空！';
            return false;
        }
        $BaiyangAdmin = new BaiyangAdmin();
        $res = $BaiyangAdmin::findFirstByAdminAccount($admin_account);
        if($res){
            return $res->toArray();
        }else{
            $this->error = "管理员不存在！";
            return false;
        }
    }



 }