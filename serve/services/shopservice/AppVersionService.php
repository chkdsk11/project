<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\AppVersionData;

class AppVersionService extends BaseService
{
    public $data;
    public $table;

    public function __construct()
    {
        $this->data =AppVersionData::getInstance();
        $this->table = '\Shop\Models\BaiyangVersions';
        $this->clanneltable = '\Shop\Models\BaiyangVersionsDownChannel';
        $this->downloadtable = '\Shop\Models\BaiyangVersionsDownUrl';
    }

    //获取所有列表
    public function getList($param)
    {
        $where = '';
        $where .= $where ? '' : 'WHERE 1=1';

        $counts = $this->data->countData([
            'table'=>$this->table,
            'where'=>$where
        ]);
        if (empty($counts)) {
            return array('res' => 'success', 'list' => 0);
        }
        //分页
        $pages['page'] = (int)isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $field = "v.*";
        $order = ' ORDER BY v.versions_id  DESC limit ' . $page['record'] . ',' . $page['psize'];
//        $join = 'LEFT JOIN  \Shop\Models\BaiyangVersionsDownUrl as u ON u.versions_id = v.versions_id  ';
        $result = $this->data->getData([
            'column'=>$field,
            'table'=>'\Shop\Models\BaiyangVersions as v',
//            'join'=>$join,
            'where'=>$where,
            'order'=>$order
        ]);
        $return = [
            'res' => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    public function getdata($id){
        $result = $this->data->getData([
            'column'=>'*',
            'table'=>$this->table,
            'where'=>'WHERE versions_id='.$id,
        ],true);
        return $result;
    }
    //获取下载渠道url列表
    public function getDownUrl($versions_id){
        $field='u.id,u.url,u.down_channel,c.name';
        $where = 'WHERE u.versions_id='.$versions_id;
        $join = ' LEFT JOIN  \Shop\Models\BaiyangVersionsDownChannel as c ON u.down_channel = c.id  ';
        $result = $this->data->getData([
            'column'=>$field,
            'table'=>'\Shop\Models\BaiyangVersionsDownUrl as u',
            'join'=>$join,
            'where'=>$where,
        ]);
        return $result;
    }

    ///添加渠道
    public function addData($data,$url=''){
        if (empty($data))
        {
            return $this->arrayData('添加失败！', '', '', 'error');
        }

        $insert['versions_id'] = NULL;	// int类型
        $insert['versions']	= $data['versions'];
        $insert['status'] = $data['status'];
        $insert['channel'] = $data['channel'];
        $insert['is_compulsive'] =  $data['is_up'];
        $insert['versions_description'] = $data['description'];
        $insert['add_time'] = strtotime('now');
        $insert['versions_id'] =  $this->data->insert($this->table,$insert,true);
        if($insert['versions_id']>0){
            if ($insert['channel'] == 90) {
                $download['versions_id'] = $insert['versions_id'];
                $download['add_time'] = strtotime('now');
                foreach ($data['clannel_id'] as $k => $v){
                    $download['down_channel'] = $v;
                    $download['url'] = $data['clannel_url'][$k];
                    $this->data->insert($this->downloadtable,$download);
                }
            }
            return $this->arrayData('操作成功！', $url);
        }

        return $this->arrayData('操作失败！', '', '', 'error');
    }

    //修改
    public function editData($data,$url=''){
        if(!$data['versions_id']){
            return $this->arrayData('修改失败！', '', '', 'error');
        }
        $columStr = "versions=:versions:,versions_description=:versions_description:,
        is_compulsive=:is_compulsive:,status=:status:,channel=:channel:,edit_time=:edit_time:";

        $conditions['versions'] = $data['versions'];
        $conditions['status'] = $data['status'];
        $conditions['channel'] = $data['channel'];
        $conditions['is_compulsive'] =  $data['is_up'];
        $conditions['versions_description'] = $data['description'];
        $conditions['edit_time'] = strtotime('now');

        $where='versions_id = '.$data['versions_id'];
        if($this->data->update($columStr,$this->table,$conditions, $where)){

            if ($conditions['channel'] == 90) {
                $download['versions_id'] = $data['versions_id'];
                $download['add_time'] = strtotime('now');
                foreach ($data['clannel_id'] as $k => $v){
                    $download['down_channel'] = $v;
                    $download['url'] = $data['clannel_url'][$k];
                    $this->data->insert($this->downloadtable,$download);
                }
            }

            return $this->arrayData('修改成功！',$url);
        }
        return $this->arrayData('修改失败！', '', '', 'error');
    }

    //获取下载渠道
    public function getClannelList(){
        $where = 'WHERE 1=1';
        $result = $this->data->getData([
            'column'=>'*',
            'table'=>$this->clanneltable,
            'where'=>$where
        ]);
        return $result;
    }
    //删除
    public function delData($id){
        $data['versions_id'] = $id;
        $where = 'versions_id=:versions_id:';
        $res = $this->data->delete($this->table, $data, $where);
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }
    //删除下载url
    public function delDwonUrl($id,$versions=false){
        if($versions){
            $data['versions_id'] = $id;
            $where = 'versions_id=:versions_id:';
        }else{
            $data['id'] = $id;
            $where = 'id=:id:';
        }
        $res = $this->data->delete($this->downloadtable, $data, $where);
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    //编辑广告活动
    public function editAdvertisements($data){
        $columStr = "advertisement=:advertisement:,is_default=:is_default:,advertisement_desc=:advertisement_desc:,products=:products:,author=:author:,update_time=:update_time:";

        $conditions['advertisement']	= $data['action_name'];
        $conditions['is_default'] = $data['is_default'];
        $conditions['advertisement_desc'] = $data['action_description'];
        $conditions['products'] = NULL;
        $conditions['author'] = $this->session->get('username');
        $conditions['update_time'] = strtotime('now');

        $where='advertisement_id = '.$data['id'];

        if ($data['advertisement_type'] == 1) {
            $columStr .=",slogan=:slogan:,location=:location:,backgroud=:backgroud:,image_url=:image_url:";
            $conditions['slogan'] = $data['slogan_image'];
            $conditions['location'] = trim($data['location_image'],' ');
            $conditions['backgroud'] = empty($data['background']) ? NULL : $data['background'];
            if(isset($data['first_image'])){
                $conditions['image_url'] = $data['first_image'];
            }else{
                $conditions['image_url'] = $data['second_image'];
            }

        } elseif ( $data['advertisement_type'] == 2) {
            $columStr .=",products=:products:";
            $goods=[];
            foreach ($data['product_id'] as $k => $v){
                $goods[$k]['product_id']=$v;
                if($data['product_name']){
                    $goods[$k]['product_name']=$data['product_name'][$k];
                }
                if($data['order_product']){
                    $goods[$k]['sort']=$data['order_product'][$k];
                }
                if(isset($data['product_image'])){
                    $goods[$k]['product_image']=$data['product_image'][$k];
                }
            }
            $conditions['products']= serialize($goods);
        } elseif (in_array($data['advertisement_type'], array(3,4,5,6,7,8))) {
            $columStr .=",slogan=:slogan:,location=:location:,order=:order:";
            $conditions['slogan'] = $data['slogan_text'];
            $conditions['location'] = $data['location_text'];
            $conditions['order'] = (int)$data['order_text'];
        }
        if($this->data->update($columStr, '\Shop\Models\Advertisements',$conditions, $where)){
            return $this->arrayData('修改成功！');
        }
            return $this->arrayData('添加失败！', '', '', 'error');
    }

}