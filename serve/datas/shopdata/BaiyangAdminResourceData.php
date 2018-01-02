<?php
/**
 * 后台权限资源数据处理
 * Class BaiyangAdminResourceData
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */
namespace Shop\Datas;
use Shop\Models\CacheKey;
use Shop\Models\BaiyangAdminResource;

class BaiyangAdminResourceData extends BaseData
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
    public $table = "\\Shop\\Models\\BaiyangAdminResource";

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
        return array('list'=>$result,'page'=>$page);
    }

    /**
     * [getAll description]
     * @return [type] [description]
     */
    public function getAll()
    {
        $this->cache->selectDb(1);
        $data = $this->cache->getValue(CacheKey::ADMIN_RESOURCE_KEY);
        if($data){
            return $data;
        }else{
            $data = $this->cache();
            if(count($data)){
                if(is_object($data)){
                    $data = $data->toArray();
                }
                $this->cache->setValue(CacheKey::ADMIN_RESOURCE_KEY,$data);
                return $data;
            }
        }
    }

    public function getDefaultAll()
    {
        $data = array();
        $res = $this->getAll();
        if($res){
            foreach ($res as $key => $value) {
                if($value['site'] == 0){
                    $data[] = $value;
                }
            }
        }
        return $data;
    }

     /**
     * [getPcAll PC后台资源权限]
     * @return [type] [description]
     */
    public function getPcAll()
    {
        $data = array();
        $res = $this->getAll();
        if($res){
            foreach ($res as $key => $value) {
                if($value['site'] == 1){
                    $data[] = $value;
                }
            }
        }
        return $data;
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
        $BaiyangAdminResource = new BaiyangAdminResource();
        $resource = $BaiyangAdminResource::findFirstById($id);
        if($resource){
            return $resource->toArray();
        }else{
            $this->error = "资源不存在！";
            return false;
        }
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
        $BaiyangAdminResource = new BaiyangAdminResource();
        $res = $BaiyangAdminResource->icreate($data);
        if(empty($res)){
            $messages = $BaiyangAdminResource->getMessages();
            foreach ($messages as $key => $message) {
                $this->error['message'] = $message->getMessage();
                $this->error['field'] = $message->getField();
                break;
            }
            return false;
        }
        // 更新路径
        $this->updateRoutePath($data);
        $this->cache();
        return true;
    }

    /**
     * [updateRoutePath 更新route_path]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function updateRoutePath($data = array())
    {   
        $BaiyangAdminResource = new BaiyangAdminResource();
        $insertId = $BaiyangAdminResource->insertId();
        if(isset($data['level'])){
            // 一级
            if($data['level'] == 1){
                $data['route_path'] = $insertId;
            }else{
                $data['route_path'] .= "/".$insertId;
            }
            $data['id'] = $insertId;
            if($data){
                $res = $BaiyangAdminResource->iupdate($data);
            }
        }
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
        $BaiyangAdminResource = new BaiyangAdminResource();
        $res = $BaiyangAdminResource->iupdate($data);
        if(empty($res)){
            $messages = $BaiyangAdminResource->getMessages();
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
     * [cache description]
     * @return [type] [description]
     */
    public function cache()
    {
        $this->cache->selectDb(1);
        $data = BaiyangAdminResource::find(
            array(
                'order' => 'show_order desc,id asc',
            )
        );
        if(count($data)){
            $data = $data->toArray();
            $this->cache->setValue(CacheKey::ADMIN_RESOURCE_KEY,$data);
            return $data;
        }else{
            $this->cache->delete(CacheKey::ADMIN_RESOURCE_KEY);
        }
    }

    /**
     * [del description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function del($id = 0)
    {
        if(empty($id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangAdminResource = new BaiyangAdminResource();
        $res = $BaiyangAdminResource->findFirst($id);
        if($res !== false){
            // 看看有没有子模块
            $data = $res->toArray();
            $pid = $data['id'];
            $son_data = $BaiyangAdminResource->findFirst("pid = {$pid}");
            if($son_data !== false){
                $this->error = "请选删除子模块";
                return false;
            }
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

}