<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\AppthemeData;

class AppthemeService extends BaseService
{
    public $data;
    public $table;

    public function __construct()
    {
        $this->data =AppthemeData::getInstance();
        $this->table = '\Shop\Models\Apptheme';
    }

    //获取列表信息
    public function getLists($param){
        $where = 'WHERE 1=1 ';

        if($param['param']['channel']){
            $where .= " AND channel IN ({$param['param']['channel']})";
        }
        $startTime = $endTime = 0;
        if($param['param']['start_time']){
            $startTime = strtotime($param['param']['start_time']);
        }
        if($param['param']['end_time']){
            $endTime = strtotime($param['param']['end_time']);
        }
        if ($startTime && $endTime) {
            $where .= " AND (start_time BETWEEN $startTime AND $endTime or end_time BETWEEN $startTime AND $endTime)";
        }  elseif ($startTime && !$endTime) {
            $where .= " AND start_time >= $startTime";
        } elseif (!$startTime && $endTime) {
            $where .= " AND end_time <= $endTime)";
        }
        $counts = $this->data->countData(array(
            'where'=>$where,
            'table' =>$this->table,
        ));

        if (empty($counts)) {
            return array('res' => 'success', 'list' => 0);
        }
        //分页
        $pages['page'] = (int)isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $field = "*";
        $where .= ' ORDER BY create_time DESC limit ' . $page['record'] . ',' . $page['psize'];
        $result = $this->data->getData(array(
            'column'=>$field,
            'where'=>$where,
            'table' =>$this->table ,
        ));

        $return = [
            'res' => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }
    public function getData($id){
        $where = 'WHERE theme_id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->table,
            'where'=>$where,
        ];
        return $this->data->getData($data,true);
    }
    public function addData($data){
        if (empty($data))
        {
            return $this->arrayData('添加失败！', '', '', 'error');
        }

        $insert['channel'] = $data['channel'];
        $insert['start_time'] = strtotime($data['start_time']);

        $insert['end_time'] = strtotime($data['end_time']);
        $insert['creater'] = $this->session->get('username');
        $insert['create_time'] = strtotime('now');
        $insert['update_time'] = strtotime('now');
        if ($insert['channel'] == 89|| $insert['channel']==90) {
            $insert['is_show_local'] = $data['is_show_local'];
            $insert['local_url'] = $data['local_url'];
            $insert['path'] = $data['theme_zip'];
            $insert['scale'] = 2;
            $return_url = '/apptheme/apptheme';
            //删除旧的主题包数据
            $action = $this->delTheme($data['channel'],$insert['scale']);
            if(!$action){
                return $this->arrayData('添加失败！', '', '', 'error');
            }
        } elseif ($insert['channel'] == 91) {
            if(!$this->checkTime($insert['start_time'], $insert['end_time'])){
                return $this->arrayData('所选时间已在其他主题内，请重新选择！', '', '', 'error');
            }
            $insert['path'] = $data['wap_path'];
            $return_url = '/apptheme/waptheme';
        } else{

        }
        // 开启事务
        $this->dbWrite->begin();
        $advertisement_Id =  $this->data->insert($this->table,$insert,true);
        if(empty($advertisement_Id)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }else{
            $this->dbWrite->commit();

            return $this->arrayData('添加成功！', $return_url);
        }
    }
    public function editData($data){
        $columStr = "update_time=:update_time:,start_time=:start_time:,end_time=:end_time:";
        $conditions['start_time'] = strtotime($data['start_time']);

        $conditions['end_time'] = strtotime($data['end_time']);
        $conditions['update_time'] = strtotime('now');
        if ($data['channel'] == 89|| $data['channel']==90) {
            $columStr .=",path=:path:,is_show_local=:is_show_local:,local_url=:local_url:";
            $conditions['is_show_local'] = $data['is_show_local'];
            $conditions['local_url'] = $data['local_url'];
            $conditions['path'] = $data['theme_zip'];
            $return_url = '/apptheme/apptheme';
        } elseif ($data['channel'] == 91) {
            if(!$this->checkTime($conditions['start_time'],$conditions['end_time'],$data['theme_id'])){
                return $this->arrayData('所选时间已在其他主题内，请重新选择！', '', '', 'error');
            }
            $columStr .=",path=:path:";
            $conditions['path'] = $data['wap_path'];
            $return_url = '/apptheme/waptheme';
        } else{

        }
        $where="theme_id = '{$data['theme_id']}'";
        if($this->data->update($columStr, $this->table,$conditions, $where)){
            return $this->arrayData('编辑成功！', $return_url);
        }
        return $this->arrayData('编辑失败！', '', '', 'error');
    }

    //检查时间
    public function checkTime($startTime,$endTime,$id=0){
        $where = "WHERE end_time BETWEEN {$startTime} AND {$endTime} AND channel=91";
        if($id>0){
            $where .= " AND theme_id !=".$id;
        }

        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->table,
            'where'=>$where,
        ];
        if($this->data->getData($data,true)){
            return false;
        }
        return true;
    }

    public function delTheme($channel,$scale){
        $data['channel'] = $channel;
        $data['scale'] = $scale;
        $where = 'channel=:channel: AND scale=:scale:';
        $res = $this->data->delete($this->table, $data, $where);
        return $res ? $res:false;
    }
    public function delData($id){
        $data['theme_id'] = $id;
        $where = 'theme_id=:theme_id:';
        $res = $this->data->delete($this->table, $data, $where);
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

}