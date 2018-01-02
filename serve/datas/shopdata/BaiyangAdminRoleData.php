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
use Shop\Models\BaiyangAdminRole;

class BaiyangAdminRoleData extends BaseData
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
    public $table = "\\Shop\\Models\\BaiyangAdminRole";

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
     */
    public function add($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdminRole = new BaiyangAdminRole();
        $res = $BaiyangAdminRole->icreate($data);
        if(empty($res)){
            $messages = $BaiyangAdminRole->getMessages();
            foreach ($messages as $key => $message) {
                $this->error['message'] = $message->getMessage();
                $this->error['field'] = $message->getField();
                break;
            }
            return false;
        }
        $this->cache();
        return true;
    }

    /**
     * [edit description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function edit($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdminRole = new BaiyangAdminRole();
        $res = $BaiyangAdminRole->iupdate($data);
        if(empty($res)){
            $messages = $BaiyangAdminRole->getMessages();
            foreach ($messages as $key => $message) {
                $this->error['message'] = $message->getMessage();
                $this->error['field'] = $message->getField();
                break;
            }
            return false;
        }
        $this->cache();
        return true;
    }

    /**
     * [del description]
     * @param  integer $role_id [description]
     * @return [type]      [description]
     */
    public function del($role_id = 0)
    {
        if(empty($role_id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdminRole = new BaiyangAdminRole();
        $res = $BaiyangAdminRole->findFirst($role_id);
        if($res !== false){
            if($res->delete() === false){
                $messages = $res->getMessages();
                foreach ($messages as $key => $message) {
                    $this->error = $message->getMessage();
                    break;
                }
                return false;
            }else{
                $this->cache();
                return true;
            }
        }else{
            return false;
        }
    }


    /**
     * [cache description]
     * @return [type] [description]
     */
    public function cache()
    {
        $this->cache->selectDb(1);
        $data = BaiyangAdminRole::find();
        if(count($data)){
            $data = $data->toArray();
            $this->cache->setValue(CacheKey::ADMIN_ROLE_KEY,$data);
            return $data;
        }else{
            $this->cache->delete(CacheKey::ADMIN_ROLE_KEY);
        }
    }

    /**
     * [getById description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function getByRoleId($id = 0)
    {
        if(empty($id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdminRole = new BaiyangAdminRole();
        $resource = $BaiyangAdminRole::findFirstByRoleId($id);
        if($resource){
            return $resource->toArray();
        }else{
            $this->error = "角色不存在！";
            return false;
        }
    }

    /**
     * [getAll 取所有]
     * @return [type] [description]
     */
    public function getAll()
    {
        $res = $this->cache();
        if($res){
            $data = array();
            foreach ($res as $key => $value) {
                $data[$value['role_id']] = $value;
            }
            return $data;
        }else{
            $this->error = "没有数据！";
            return false;
        }
    }

    /**
     * [getOneCahce 找单个角色缓存]
     * @param  integer $role_id [description]
     * @return [type]           [description]
     */
    public function getOneCahce($role_id = 0)
    {
        $data = array();
        $list = $this->getAll();
        if(empty($list)){
            $this->error = '角色不存在！';
            return false;
        }
        foreach ($list as $key => $value) {
            if($key == $role_id){
                $data = $value;
            }
        }
        if(empty($data)){
            $this->error = '角色不存在！';
            return false;
        }
        return $data;
    }

}