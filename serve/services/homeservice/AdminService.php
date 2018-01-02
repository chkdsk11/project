<?php
/**
 * 管理员Service
 * Class AdminService
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */
namespace Shop\Home\Services;
use Phalcon\Mvc\User\Component;
use Shop\Models\CacheKey;
use Shop\Home\Datas\BaiyangAdminResourceData;

class AdminService extends Component
{
	//必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * 实例化对象
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    /**
     * [isLogin 判断管理员是否在线]
     * @param  integer $id [description]
     * @return boolean     [description]
     */
    public function isLogin($id = 0)
    {
        $return = false;
    	if($id){
    		$this->cache->selectDb(1);
        	$data = $this->cache->getValue(CacheKey::ADMIN_IDS);
        	if(!empty($data)){
                foreach ($data as $key => $value) {
                    $tmp_data = explode("//",$value);
                    if(isset($tmp_data[1]) && $tmp_data[1]){
                        $return = true;
                        break;
                    }
                }
            }
    	}
    	return $return;
    }

    /**
     * [getAllAdminResource 取所有资源]
     * @return [type] [description]
     */
    public function getAllAdminResource()
    {
    	$this->cache->selectDb(1);
        $data = $this->cache->getValue(CacheKey::ADMIN_RESOURCE_KEY);
        if(empty($data)){
            $BaiyangAdminResourceData = BaiyangAdminResourceData::getInstance();
            $data = $BaiyangAdminResourceData->cache();
        }
        return $data;
    }

    /**
     * [getAllAdminRole 取所有权限]
     * @return [type] [description]
     */
    public function getAllAdminRole()
    {
    	$this->cache->selectDb(1);
        $data = $this->cache->getValue(CacheKey::ADMIN_ROLE_KEY);
        return $data;
    }

}