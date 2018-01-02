<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：SkuAd_id_5     一个skuad信息
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class SupplierService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 获取店铺列表
     */
    public function getList($param)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangSkuSupplier';
        $where = ' 1';
        $data   =   array();
        //组织where语句
        if(isset($param['name']) && $param['name'] != ''){
            $where .= " AND name LIKE :name:";
            $data['name']   =   '%'.$param['name'].'%';
        }
        if(isset($param['id']) && $param['id'] != ''){
            $where .= " AND id = :id:";
	        $data['id']   =   $param['id'];
        }
        if(isset($param['user_name']) && $param['user_name'] != ''){
            $where .= " AND user_name LIKE :user_name:";
            $data['user_name']   =   $param['user_name'];
        }
        if(isset($param['address']) && $param['address'] != ''){
            $where .= " AND address LIKE :address:";
            $data['address']   =   '%'.$param['address'].'%';
        }
        if(isset($param['phone']) && $param['phone'] != ''){
            $where .= " AND phone LIKE :phone:";
            $data['phone']   =   $param['phone'];
        }
        if(isset($param['code']) && $param['code'] != ''){
            $where .= " AND code LIKE :code:";
            $data['code']   =   $param['code'];
        }
        $BaiyangSpuData = BaseData::getInstance();
        //总记录数
        $counts = $BaiyangSpuData->count($table,$data,$where);
        if(empty($counts)){
            return array('res' => 'success','list' => 0,'page'=>'');
        }
        //分页
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['psize'] = (int)isset($param['psize'])?$param['psize']:12;
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $selections = '*';
        $where .= ' order by updatetime desc limit '.$page['record'].','.$page['psize'];
        $result = $BaiyangSpuData->select($selections,$table,$data,$where);
        $return = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    public function setSupplier($param)
    {
        if(!(int)$param['id']){
            return $this->arrayData('参数错误','','','error');
        }
        if(!isset($param['user_name']) || empty($param['user_name'])){
            return $this->arrayData('请填写收货人名称','','','error');
        }
        if(!isset($param['address']) || empty($param['address'])){
            return $this->arrayData('请填写收货人详细地址','','','error');
        }
        if(!isset($param['code']) || empty($param['code'])){
            return $this->arrayData('请填写邮编','','','error');
        }
        if(!isset($param['phone']) || empty($param['phone'])){
            return $this->arrayData('请填写手机号','','','error');
        }
        if(!isset($param['telephone']) || empty($param['telephone'])){
            return $this->arrayData('请填写固定电话','','','error');
        }

        $BaiyangSpuData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangSkuSupplier';
        $where = "id=:id:";
        $data['id'] = (int)$param['id'];
        $columStr = "
            user_name=:user_name:,
            address=:address:,
            code=:code:,
            phone=:phone:,
            telephone=:telephone:,
            updatetime=:updatetime:";
        $data['user_name'] = trim($param['user_name']);
        $data['address'] = $param['address'];
        $data['code'] = $param['code'];
        $data['telephone'] = $param['telephone'];
        $data['phone'] = $param['phone'];
        $data['updatetime'] = time();
        $res = $BaiyangSpuData->update($columStr,$table,$data,$where);
        if($res){
            //更新缓存
//            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
//            $UpdateCacheSkuData->updateSpu((int)$param['id']);
            return $this->arrayData('修改成功','',$res,'success');
        }else{
            return $this->arrayData('修改失败','',$res,'error');
        }
    }
    
    public function addSupplier($param)
    {
	    if(!isset($param['name']) || empty($param['name'])){
		    return $this->arrayData('请填写收货人名称','','','error');
	    }
        if(!isset($param['user_name']) || empty($param['user_name'])){
            return $this->arrayData('请填写收货人名称','','','error');
        }
        if(!isset($param['address']) || empty($param['address'])){
            return $this->arrayData('请填写收货人详细地址','','','error');
        }
        if(!isset($param['code']) || empty($param['code'])){
            return $this->arrayData('请填写邮编','','','error');
        }
        if(!isset($param['phone']) || empty($param['phone'])){
            return $this->arrayData('请填写手机号','','','error');
        }

        $BaiyangSpuData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangSkuSupplier';
	    $maxId = $BaiyangSpuData->getData(['column'=>'max(id)','table'=>$table]);
	    $maxId = json_decode(json_encode($maxId),true);
        if (strlen($maxId[0][0])<6)
        {
        	$id = 100001;
        	$data['id'] = $id;
        }
        $data['name'] = trim($param['name']);
        $data['user_name'] = trim($param['user_name']);
        $data['address'] = $param['address'];
        $data['code'] = $param['code'];
        $data['telephone'] = $param['telephone'];
        $data['phone'] = $param['phone'];
        $data['updatetime'] = time();
        $res = $BaiyangSpuData->insert($table,$data, true);
        if($res){
            //更新shop_id
	        $where = "id=:id:";
	        $shop['id'] = (int)$res;
	        $shop['shop_id'] = (int)$res;
	        $columStr = "shop_id=:shop_id:";
	        $BaiyangSpuData->update($columStr,$table,$shop,$where);
            return $this->arrayData('添加成功','',$res,'success');
        }else{
            return $this->arrayData('添加失败','',$res,'error');
        }
    }

    public function getSupplier($param)
    {
        if(!(int)$param['id']){
            return false;
        }
        $table = '\Shop\Models\BaiyangSkuSupplier';
        $result = BaseData::getInstance()->select('*',$table,['id'=>(int)$param['id']],'id=:id:');
        if($result){
            return $result[0];
        }else{
            return false;
        }
    }
	
	public function getAllSupplier()
	{
		$table = '\Shop\Models\BaiyangSkuSupplier';
		$result = BaseData::getInstance()->select('*',$table);
		if($result){
			return $result;
		}else{
			return false;
		}
	}
}
