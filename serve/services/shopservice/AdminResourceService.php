<?php
/**
 * 后台权限资源业务处理
 * Class AdminResourceService
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */

namespace Shop\Services;
use Shop\Datas\BaiyangAdminResourceData;

class AdminResourceService extends BaseService
{

    /**
     * 必须声明此静态属性，单例模式下防止实例对象覆盖
     * @var null
     */
    protected static $instance = null;

    /**
     * [$error 错误提示]
     * @var string
     */
    public $error = '';

    /**
     * [getPage 分页]
     * @param  array  $map       [description]
     * @param  array  $pageParam [description]
     * @return [type]            [description]
     */
    public function getPage($map = array(),$pageParam = array())
    {
    	$where = $this->makeMap($map);
    	$conditions = $this->makeConditions($map);
    	$res = BaiyangAdminResourceData::getInstance()->getPage($where,$conditions,$pageParam);
    	if(empty($res)){
    		$this->error = BaiyangAdminResourceData::getInstance()->error;
    		return false;
    	}
    	return $res;
    }

    /**
     * [makeMap 条件集合]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function makeMap($map = array())
    {
    	$where = ' 1 = 1 ';
    	if($map){
    		foreach ($map as $key => $value) {
    			if(!is_array($value)){
    				$where .= $key ." = :{$key}: AND ";
    			}
    		}
    		$where = rtrim($where,"AND ");
    	}
    	return $where;
    }

    /**
     * [makeConditions description]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function makeConditions($map = array())
    {
    	$conditions = array();
    	if($map){
    		foreach ($map as $key => $value) {
    			if(!is_array($value)){
    				$conditions[$key] = $value;
    			}
    		}
    	}
    	return $conditions;
    }

    /**
     * [getDefaultAll 取统一后台资源权限]
     * @return [type] [description]
     */
    public function getDefaultAll()
    {
        $data = array();
        $res = BaiyangAdminResourceData::getInstance()->getDefaultAll();
        if($res){
            $data = $this->tree->structureTree($res,'id','pid');
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
        $res = BaiyangAdminResourceData::getInstance()->getPcAll();
        if($res){
            $data = $this->tree->structureTree($res,'id','pid');
        }
        return $data;
    }

    /**
     * [getAll 取所有]
     * @return [type] [description]
     */
    public function getAll()
    {
        $data = BaiyangAdminResourceData::getInstance()->getAll();
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
        $res = BaiyangAdminResourceData::getInstance()->getById($id);
        if(empty($res)){
            $this->error = BaiyangAdminResourceData::getInstance()->error;
            return false;
        }
        return $res;
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
        $res = BaiyangAdminResourceData::getInstance()->add($data);
        if(empty($res)){
            $this->error = BaiyangAdminResourceData::getInstance()->error;
            return false;
        }
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
        $res = BaiyangAdminResourceData::getInstance()->edit($data);
        if(empty($res)){
            $this->error = BaiyangAdminResourceData::getInstance()->error;
            return false;
        }
        return true;
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
        $res = BaiyangAdminResourceData::getInstance()->del($id);
        if(empty($res)){
            $this->error = BaiyangAdminResourceData::getInstance()->error;
            return false;
        }
        return true;
    }

}
