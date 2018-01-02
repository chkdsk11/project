<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/5
 * Time: 9:52
 */

namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class CustomerconsultService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;
    private $table = '\Shop\Models\Feedback';
    private $BaseData = null;
    private $cltStatus= array(
	    0 => '未审核',
	    1 => '审核',
	    2 => '审核不通过'
    );
	
    
    public function __construct()
    {
        $this->BaseData = BaseData::getInstance();
    }
	
	/**
	 * 获取全部咨询
	 * @param array $param
	 * @return array
	 */
	public function  getAllClt(array $param):array
	{
		$where = ' 1 ';
		$data   =   array();
		//组织where语句
		if(isset($param['msg_content']) && $param['msg_content'] != ''){
			$where .= " AND msg_content like :msg_content:";
			$data['msg_content']   =  '%'.$param['msg_content'].'%';
		}
		if(isset($param['serv_nickname']) && $param['serv_nickname'] != ''){
			$where .= " AND serv_nickname = :serv_nickname:";
			$data['serv_nickname']   =   $param['serv_nickname'];
		}
		if(isset($param['msg_status']) && $param['msg_status'] != ''){
			$where .= " AND msg_status = :msg_status:";
			$data['msg_status']   =   $param['msg_status'];
		}
		//总记录数
		$counts = $this->BaseData->count($this->table,$data,$where);
		if(empty($counts)){
			return array('res' => 'success','list' => 0);
		}
		//分页
		$pages['page'] = isset($param['page'])?(int)$param['page']:1;//当前页
		$pages['counts'] = $counts;
		$pages['url'] = $param['url'];
		$page = $this->page->pageDetail($pages);
		$selections = '*';
		$where .= 'order by msg_id desc limit '.$page['record'].','.$page['psize'];
		$result = $this->BaseData->select($selections,$this->table,$data,$where);
		foreach ($result as &$item)
		{
			$item['msg_status'] = $this->cltStatus[$item['msg_status']];
		}
		$return = [
			'res'  => 'success',
			'list' => $result,
			'page' => $page['page']
		];
		return $return;
    }
	
	/**
	 * 批量关闭留言
	 * @param array $param
	 * @return array
	 */
	public function updateIdsClt (array $param)
	{
		if(!isset($param) || empty($param)){
			return $this->arrayData('参数丢失','','','error');
		}
		if (is_array($param['ids']))
		{
			$ids = implode(',', $param['ids']);
		}else{
			$ids = $param['ids'];
		}
		$username = $this->session->get('username');
		$uid = $this->session->get('user_id');
		$data['serv_id']   =   $uid;
		$data['serv_nickname'] = $username;
		$data['adult_time'] = time();
		$data['msg_status']   =   $param['msg_status'];
		$where = " msg_id in ({$ids})";
		$columStr = "msg_status=:msg_status:,serv_id = :serv_id:,serv_nickname = :serv_nickname:, adult_time=:adult_time:";
		$res = $this->BaseData->update($columStr,$this->table,$data,$where);
		return $this->arrayData('操作成功','','','success');
    }
	
	/**
	 * 获取全部留言
	 * @return array
	 */
	public function getAllMessage (array $param)
	{
		$where = ' 1 ';
		$data   =   array();
		//组织where语句
		if(isset($param['telephone']) && $param['telephone'] != ''){
			$where .= " AND telephone = :telephone:";
			$data['telephone']   =   $param['telephone'];
		}
		//总记录数
		$counts = $this->BaseData->count($this->table_cus,$data,$where);
		if(empty($counts)){
			return array('res' => 'success','list' => 0);
		}
		//分页
		$pages['page'] = isset($param['page'])?(int)$param['page']:1;//当前页
		$pages['counts'] = $counts;
		$pages['url'] = $param['url'];
		$page = $this->page->pageDetail($pages);
		$selections = '*';
		$where .= 'order by customer_id desc limit '.$page['record'].','.$page['psize'];
		$result = $this->BaseData->select($selections,$this->table_cus,$data,$where);
		foreach ($result as &$item)
		{
			$item['remark'] =  $item['remark'] ?? '无' ?? $item['remark'];
			$status = $item['adult_state'];
			$stat = array('stat'=>$status);
			$item['adult_state'] = $this->cusStatus[$item['adult_state']];
			$item = array_merge($item, $stat);
		}
		$return = [
			'res'  => 'success',
			'list' => $result,
			'page' => $page['page']
		];
		return $return;
	}
	
	/**
	 * 关闭留言
	 * @param array $param
	 * @return array
	 */
	public function updateMsg (array $param)
	{
		if(!isset($param) || empty($param)){
			return $this->arrayData('参数丢失','','','error');
		}
		$username = $this->session->get('username');
		$uid = $this->session->get('user_id');
		foreach ($param as $item)
		{
			$data['id']   =   $item;
			$data['serv_id']   =   $uid;
			$data['adult_state']   =   3;
			$data['serv_nickname'] = $username;
			$columStr = "adult_state=:adult_state:,serv_id = :serv_id:,serv_nickname = :serv_nickname:";
			$res = $this->BaseData->update($columStr,$this->table_cus,$data,"customer_id=:id:");
		}
		return $this->arrayData('关闭成功','','','success');
	}
	
	public function remarkMsg (array $param)
	{
		if(!isset($param) || empty($param)){
			return $this->arrayData('参数丢失','','','error');
		}
		$username = $this->session->get('username');
		$uid = $this->session->get('user_id');
		$data['id']   =   $param['customer_id'];
		$data['serv_id']   =   $uid;
		$data['adult_state']   =   $param['adult_state'];
		$data['serv_nickname'] = $username;
		$columStr = "adult_state=:adult_state:,serv_id = :serv_id:,serv_nickname = :serv_nickname:";
		$res = $this->BaseData->update($columStr,$this->table_cus,$data,"customer_id=:id:");
		return $this->arrayData('反馈成功','','','success');
	}
}