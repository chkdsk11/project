<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\BaiyangIndexHandyEntryData;

class HandyEntryService extends BaseService
{
    public function __construct()
    {
        $this->data = BaiyangIndexHandyEntryData::getInstance();
        $this->table = '\Shop\Models\BaiyangIndexHandyEntry';
    }
    //获取wap首页入口列表
    public function EntryList($param)
    {
        $data = array();
        $where = '';
        $where .= $where ? '' : '1=1';
        if($param['param']['name']){
            $where .= ' AND name like :name:';
            $data['name'] = '%'.$param['param']['name'].'%';
        }
        if($param['param']['id']){
            $where .= ' AND id = :id:';
            $data['id'] = $param['param']['id'];
        }
        if($param['param']['status']!= ''){
            $where .= ' AND status = :status:';
            $data['status'] = $param['param']['status'];
        }

        if($param['param']['channel_name']){
            $where .= ' AND channel_name = :channel_name:';
            $data['channel_name'] = $param['param']['channel_name'];
        }

        $counts = $this->data->count($this->table, $data, $where);
        if (empty($counts)) {
            return array('res' => 'success', 'list' => 0);
        }
        //分页
        $pages['page'] = (int)isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $field = "*";
        $where .= ' ORDER BY sort,id DESC  limit ' . $page['record'] . ',' . $page['psize'];
        $result = $this->data->select($field, $this->table, $data, $where);
        $return = [
            'res' => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    //添加入口
    public function addEntry($data,$url){
        if (empty($data))
        {
            return false;
        }
        $insert['id'] = NULL;	// int类型
        $insert['name']	= trim($data['name']);
        $insert['link'] = trim($data['link']);
        $insert['icon_img'] =$data['icon_img'];
        $insert['status'] = $data['status'];
        $insert['remark'] =trim($data['remark']);
        $insert['start_time'] = strtotime($data['start_time']);
        $insert['end_time'] = strtotime($data['end_time']);
        $insert['start_version'] = $data['start_version'];
        $insert['end_version'] = $data['end_version'];
        $insert['channel_name'] =$data['channel_name'];
        $insert['add_time'] = strtotime('now');
        $insert['up_time'] = strtotime('now');
        // 开启事务
        $this->dbWrite->begin();
        $advertisement_Id =   $this->data->insert($this->table,$insert,true);
        if(empty($advertisement_Id)){
            $this->dbWrite->rollback();
            return  $this->arrayData('创建失败！', '', '', 'error');
        }else{
            $this->dbWrite->commit();
            return $this->arrayData('创建成功！',$url);
        }
    }

    public function editEntry($data,$url){
        $columStr = "name=:name:,start_version=:start_version:,end_version=:end_version:,link=:link:,status=:status:,remark=:remark:,icon_img=:icon_img:,start_time=:start_time:,end_time=:end_time:";
        $where='id = '.$data['id'];
        $conditions = [
            'name'=>trim($data['name']),
            'start_version'=>$data['start_version'],
            'end_version'=>$data['end_version'],
            'start_time' => strtotime($data['start_time']),
            'end_time' => strtotime($data['end_time']),
            'link'=>trim($data['link']),
            'icon_img'=> $data['icon_img'],
            'status'=>$data['status'],
            'remark'=>trim($data['remark'])
        ];
        $res = $this->data->update($columStr, $this->table,$conditions, $where);
        return $res ? $this->arrayData('修改成功！',$url) : $this->arrayData('修改失败！', '', '', 'error');
    }

    public function delData($id){
        $data['id'] = $id;
        $where = 'id=:id:';
        $res = $this->data->delete($this->table, $data, $where);
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    //修改显示状态
    public function update_status($id,$action='show'){
        $columStr="status=:status:";
        if($action =='show'){
            $data['status'] = 0;
        }else{
            $data['status'] = 1;
        }
        $where = 'id ='.$id;
        $res = $this->data->update($columStr,$this->table, $data, $where);
        return $res ? $this->arrayData('修改成功！') : $this->arrayData('修改失败！', '', '', 'error');
    }
    //修改排序
    public function update_sort($param){
        $columStr="sort=:sort:";
        $data['sort'] = $param['sort'];
        $where = 'id ='.$param['id'];
        $res = $this->data->update($columStr,$this->table, $data, $where);
        return $res ? $this->arrayData('修改成功！') : $this->arrayData('修改失败！', '', '', 'error');
    }


    //获取app所有的版本
    public function get_versions(){
        $where = ' 1=1';
        $field = 'id,version,version_num';
        $data = [];
        $result =  $this->data->select($field, '\Shop\Models\BaiyangAppVersions', $data, $where);
        return $result;
    }

    public function getIndexEntryInfo($id){
        $where = 'WHERE id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->table,
            'where'=>$where,
        ];
        $result =  $this->data->getData($data,true);

        return !empty($result) ? array('status'=>'success', 'data'=>$result, 'app_versions'=>$this->get_versions()) : array('status'=>'error');
    }

}