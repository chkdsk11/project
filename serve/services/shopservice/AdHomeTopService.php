<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\BaiyangAppHomeAdData;

class AdHomeTopService extends BaseService
{
    public $new_data;
    public $adTable;
    public function __construct()
    {
        $this->data =BaiyangAppHomeAdData::getInstance();
        $this->adTable = '\Shop\Models\BaiyangAppHomeAd';
        $this->img_table = '\Shop\Models\BaiyangAppHomeAdImg';
    }
    //获取所有广告
    public function getAllad($param)
    {
        $data = array();
        $where = ' WHERE 1=1 ';
        if ($param['param']['name']) {
            $where .= "AND ad_name like '%{$param['param']['name']}%' ";
        }
        if ($param['param']['id'] && is_numeric($param['param']['id'])) {
            $where .= ' AND id = ' . $param['param']['id'];
        }
        $cur_time = strtotime('now');
        //未开始
        if ($param['param']['status'] == 'start') {
            $where .= " AND start_time > $cur_time";
        } //已结束
        else if ($param['param']['status'] == 'end') {
            $where .= " AND end_time < $cur_time";
        } //进行中
        else if ($param['param']['status'] == 'middle') {
            $where .= " AND $cur_time BETWEEN start_time AND end_time";
        }
        $data['nowtime'] = $cur_time;
        $counts = $this->data->countData(array(
            'where' => $where,
            'table' => $this->adTable,
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
        $where .= ' ORDER  BY id desc  limit ' . $page['record'] . ',' . $page['psize'];
        $result = $this->data->getData(array(
            'column' => $field,
            'where' => $where,
            'table' => $this->adTable,
        ));

        $newData = [];
        if ($result) {
            foreach ($result as $k) {
                $k['img_num'] = $this->data->count($this->img_table, [], 'ad_id=' . $k['id']);
                $newData[] = $k;
            }
        }
        $return = [
            'res' => 'success',
            'list' => $newData,
            'page' => $page['page']
        ];
        return $return;

    }


    //获取信息
    public function getAd($id){
        $where = 'WHERE id='.$id;
        $field = "id,ad_name,start_time,end_time";
        $data = [
            'column'=>$field,
            'table'=> $this->adTable,
            'where'=>$where,
        ];
        $adresult = $this->data->getData($data,true);
        $data = array();
        $field = 'id,image_url,location,sort';
        $adresult['goods'] =$this->data->select($field,$this->img_table, $data,'ad_id='.$id);
        return $adresult;
    }

    //添加
    public function addAd($data){
        if (empty($data))
        {
            return false;
        }

        $insert['id'] = NULL;	// int类型
        $insert['ad_name']	= $data['name'];
        $insert['updater'] =$this->session->get('username');
        $insert['start_time'] = strtotime($data['start_time']);
        $insert['end_time'] = strtotime($data['end_time']);
        $insert['creator'] = $this->session->get('username');
        $insert['create_time'] = strtotime('now');
        $insert['update_time'] = strtotime('now');
        $arrCont = count($data['location']);
        $dataArr = [];
        if($arrCont>0){
            for($i=0;$i<$arrCont; $i++){
                if(isset($data['location'])){
                    $dataArr[$i]['location'] = $data['location'][$i];
                }
                if(isset($data['sort'])){
                    $dataArr[$i]['sort'] = $data['sort'][$i];
                }
                if(isset($data['image_url'])){
                    $dataArr[$i]['image_url'] = $data['image_url'][$i];
                }
            }
        }
        $this->dbWrite->begin();
        $ad_id =  $this->data->insert($this->adTable,$insert,true);
        if($ad_id){
            if($dataArr){
                foreach ($dataArr as $k){
                    $imgDate['id'] = null;
                    $imgDate['ad_id'] = $ad_id;
                    $imgDate['image_url']	= $k['image_url'].'';
                    $imgDate['location'] = $k['location'];
                    $imgDate['height'] = 228;
                    $imgDate['width'] = 640;
                    $imgDate['sort'] = $k['sort'];
                    $imgDate['create_time'] = strtotime('now');
                    $imgDate['creator'] = $this->session->get('username');
                    $this->data->insert('\Shop\Models\BaiyangAppHomeAdImg',$imgDate,true);
                }
            }
            $this->dbWrite->commit();
        }else{
            $this->dbWrite->rollback();
        }
        return $ad_id ? $this->arrayData('添加成功！','/adhometop/adlist') : $this->arrayData('添加失败！', '', '', 'error');
    }

    public function editData($data){
        if($data['id']<=0){
            return $this->arrayData('修改失败！', '', '', 'error');
        }
       //print_r($data);die;
        $columStr='ad_name=:ad_name:,start_time=:start_time:,end_time=:end_time:';

        $conditions['start_time'] = strtotime($data['start_time']);
        $conditions['end_time'] = strtotime($data['end_time']);
        $conditions['ad_name']	= $data['name'];
        $conditions['update_time'] = strtotime('now');
        $conditions['start_time'] = strtotime($data['start_time']);
        $conditions['end_time'] = strtotime($data['end_time']);
        //更新广告图
        if(isset($data['old_location']) && isset($data['oldimage_url']) && isset($data['old_sort'])){
            foreach ($data['old_location'] as $k => $v){
                $imgDate['id'] = $k;
                $imgDate['image_url']	= $data['oldimage_url'][$k];
                $imgDate['location'] =$v;
                $imgDate['sort'] = $data['old_sort'][$k];
                $this->data->update('image_url=:image_url:,location=:location:,sort=:sort:', '\Shop\Models\BaiyangAppHomeAdImg',$imgDate, 'id=:id:');
            }
        }
        //插入广告图
        if(isset($data['location']) && isset($data['sort']) && isset($data['image_url'])){
            $arrCont = count($data['location']);
            $dataArr = [];
            if($arrCont>0){
                for($i=0;$i<$arrCont; $i++){
                    if(isset($data['location'])){
                        $dataArr[$i]['location'] = $data['location'][$i];
                    }
                    if(isset($data['sort'])){
                        $dataArr[$i]['sort'] = $data['sort'][$i];
                    }
                    if(isset($data['image_url'])){
                        $dataArr[$i]['image_url'] = $data['image_url'][$i];
                    }
                }
            }
            if($dataArr){
                foreach ($dataArr as $k){
                    $imgDate['id'] = null;
                    $imgDate['ad_id'] = $data['id'];
                    $imgDate['image_url']	= $k['image_url'].'';
                    $imgDate['location'] = $k['location'];
                    $imgDate['height'] = 228;
                    $imgDate['width'] = 640;
                    $imgDate['sort'] = $k['sort'];
                    $imgDate['create_time'] = strtotime('now');
                    $imgDate['creator'] = $this->session->get('username');
                    $this->data->insert('\Shop\Models\BaiyangAppHomeAdImg',$imgDate,true);
                }
            }
        }
        $where='  id = '.$data['id'];
        if($this->data->update($columStr, $this->adTable,$conditions, $where)){
            return $this->arrayData('修改成功！');
        }
        return $this->arrayData('修改失败！', '', '', 'error');
    }

    public function delAppHomeAdImg($id){
        $this->data->delete('\Shop\Models\BaiyangAppHomeAdImg', ['ad_id'=>$id], 'ad_id=:ad_id:');
        $res = $this->data->delete($this->adTable, ['id'=>$id], 'id=:id:');
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    public function delAdImg($id){
        $res = $this->data->delete('\Shop\Models\BaiyangAppHomeAdImg', ['id'=>$id], 'id=:id:');
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

}