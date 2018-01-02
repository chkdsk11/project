<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\BaiyangAccesoriesData;

class AdaccesoriesService extends BaseService
{
    public $new_data;
    public $data;
    public function __construct()
    {
        $this->data =BaiyangAccesoriesData::getInstance();
        $this->table= '\Shop\Models\BaiyangAccesoriesAd';
        $this->ap_table ='\Shop\Models\BaiyangAccesoriesAdPosition';
        $this->location_table ='\Shop\Models\BaiyangAccesoriesAdLocation';
        $this->goodsextend_table ='\Shop\Models\BaiyangGoodsExtend';
        $this->goods_table ='\Shop\Models\BaiyangGoods';
    }

    //获取所有广告
    public function getAllad($param)
    {
        $data = array();
        $where = 'WHERE 1=1 ';
        if($param['param']['name']){
            $where .= " AND ad.name like '%{$param['param']['name']}%'";
            $data['name'] = '%'.$param['param']['name'].'%';
        }
        if($param['param']['ad_position']>0){
            $where .= ' AND ad.position_id = '.$param['param']['ad_position'];
        }
        $cur_time = strtotime('now');
        //未开始
        if($param['param']['ad_status'] == 'start'){
            $where .= " AND ad.start_time > $cur_time";
        } //已结束
        else if($param['param']['ad_status'] == 'end'){
            $where .= " AND ad.end_time < $cur_time AND ad.status = 1 ";
        } //进行中
        else if($param['param']['ad_status'] == 'middle'){
            $where .= " AND $cur_time BETWEEN ad.start_time AND ad.end_time AND ad.status = 1";
            $data['nowtime'] = $cur_time;
        }else if($param['param']['ad_status'] == 'cancel'){
            $where .= " AND ad.status = 0";
        }else{
            $where .= ' AND ad.status < 2';
        }
        if($param['param']['ad_type']>0){
            $where .= ' AND ad.ad_type = '.$param['param']['ad_type'];
        }

        if(isset($param['param']['ad_channel'])){
            $where .= ' AND channel = '.$param['param']['ad_channel'];
        }
        $counts = $this->data->countData(array(
                'where'=>$where,
                'table' =>$this->table.' as ad' ,
        ));
        if (empty($counts)) {
            return array('res' => 'success', 'list' => 0);
        }
        //分页
        $pages['page'] = (int)isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $field = "ad.id,ad.name,ad.start_time,ad.end_time,ad.ad_type,ad.status,ad.channel,p.name as pname";
        $where .= ' ORDER BY ad.id DESC limit ' . $page['record'] . ',' . $page['psize'];
        $join = "left join {$this->ap_table} as p ON ad.position_id = p.id ";
        $result = $this->data->getData(array(
            'column'=>$field,
            'where'=>$where,
            'table' =>$this->table.' as ad' ,
            'join' =>$join
        ));
        $return = [
            'res' => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }
    //添加海外购
    public function add_ad($data){
        if (empty($data))
        {
            return false;
        }
        $insert['id'] = NULL;	// int类型
        $insert['name']	= $data['action_name'];
        $insert['position_id'] = $data['position'];
        $insert['start_time'] = strtotime($data['start_time']);
        $insert['end_time'] = strtotime($data['end_time']);
        $insert['ad_type'] =(int) $data['advertisement_type'];
        $insert['description'] =$data['action_description'];
        $insert['channel'] = $data['channel'];
        $insert['sort'] = $data['sort'];
        $insert['creator'] = $this->session->get('username');
        $insert['updater'] = $this->session->get('username');
        $insert['create_time'] = strtotime('now');
        $insert['update_time'] = strtotime('now');
        //类型(1：商品推荐 )
        if ($insert['ad_type'] == 1) {
            if ($data['product_id']) {
                $goodsData = array();
                foreach ($data['product_id'] as $k => $v) {
                    if ($v) {
                        if(!$data['order_product'][$k]){
                            $none[] =$v;
                        }elseif(!preg_match('/^[0-9]{1,}$/', $data['order_product'][$k])){
                            $err[] = $v;
                        }else{
                            $goodsData[$v]['img_path'] = $data['product_image'][$k];
                            $goodsData[$v]['sort'] = $data['order_product'][$k];
                        }
                    }
                }
                if ($none && $err) {
                    $text = '排序必填且必须是正整数，ID为'.implode(',', $none).'的商品对应排序为空；ID为'.implode(',', $err).'的商品对应排序格式错误。';
                    return $this->arrayData($text, '', '', 'error');
                } elseif ($none && !$err) {
                    $text = '排序必填，ID为'.implode(',', $none).'的商品对应排序为空';
                    return $this->arrayData($text, '', '', 'error');
                } elseif (!$none && $err) {
                    $text = '排序必须是正整数,ID为'.implode(',', $err).'的商品对应排序格式错误';
                    return $this->arrayData($text, '', '', 'error');
                }
                //获取商品信息
                $product_list = $this->get_product_info($data['product_id']);
                if ($product_list) {
                    foreach ($product_list as $key => $val) {
                        $product_list[$key]['img_path'] = $goodsData[$val['product_id']]['img_path'];
                        $product_list[$key]['sort'] = $goodsData[$val['product_id']]['sort'];
                        if ($data['channel'] == 1 || !$data['channel']) {
                            $location[$key]['product_id'] = $val['product_id'];
                            $location[$key]['location'] = 'App://type=1&&&value=' . $val['product_id'];
                            $location[$key]['channel'] = 1;
                            $location[$key]['create_time'] =strtotime('now');;
                        }
                    }
                    $product_list = $this->array_sort($product_list,'sort');
                    $insert['content'] = json_encode($product_list);
                } else {
                    return $this->arrayData('所添加是已下架或是赠品或不是海外的商品', '', '', 'error');
                }
            }
        }
        //类型(2：图片)
        if ($insert['ad_type'] == 2) {
            //处理平台数据
            switch ($data['channel']){
                case 0:
                    $app = array(
                        'location' =>$data['app_location'],
                        'product_id'=>0,
                        'channel'=>1,
                        'create_time' => strtotime('now')
                    );
                    $wap = array(
                        'location' =>$data['wap_location'],
                        'product_id'=>0,
                        'channel'=>2,
                        'create_time' =>strtotime('now')
                    );
                    break;
                case 1:
                    $app = array(
                        'location' =>$data['app_location'],
                        'product_id'=>0,
                        'channel'=>$data['channel'],
                        'create_time' => strtotime('now')
                    );
                    break;
                case 2:
                    $wap = array(
                        'location' =>$data['wap_location'],
                        'product_id'=>0,
                        'channel'=>$data['channel'],
                        'create_time' => strtotime('now')
                    );
                    break;
            }
            if(isset($app)){
                $location[] = $app;
            }
            if(isset($wap)){
                $location[] = $wap;
            }
            $content[]['img_path'] = trim($data['first_image']);
            $insert['content'] = json_encode($content);
            //处理图片广告数
        }
        // 开启事务
        $this->dbWrite->begin();
        $Id =  $this->data->insert($this->table,$insert,true);
        if(empty($Id)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }else{
            //插入app引用
            if(is_array($location)){
                foreach ($location as $k){
                    $k['ad_id'] =$Id;
                    $this->data->insert($this->location_table,$k);
                }
            }
            $this->dbWrite->commit();
            return $this->arrayData('添加成功！', '/adaccesories/adlist');
        }
    }
    //编辑
    public function editData($data,$url=''){
        $columStr = "name=:name:,start_time=:start_time:,end_time=:end_time:,description=:description:,content=:content:";
        $where='id = '.$data['id'];
        $conditions = [
            'name'=>$data['action_name'],
            'start_time' => strtotime($data['start_time']),
            'end_time' => strtotime($data['end_time']),
            'description'=>$data['action_description'],
            'sort'=>$data['sort']
        ];

        //类型(1：商品推荐 )
        if ($data['advertisement_type'] == 1) {
            if ($data['product_id']) {
                $goodsData = array();
                foreach ($data['product_id'] as $k => $v) {
                    if ($v) {
                        if(!$data['order_product'][$k]){
                            $none[] =$v;
                        }elseif(!preg_match('/^[0-9]{1,}$/', $data['order_product'][$k])){
                            $err[] = $v;
                        }else{
                            $goodsData[$v]['img_path'] = $data['product_image'][$k];
                            $goodsData[$v]['sort'] = $data['order_product'][$k];
                        }
                    }
                }
                if ($none && $err) {
                    $text = '排序必填且必须是正整数，ID为'.implode(',', $none).'的商品对应排序为空；ID为'.implode(',', $err).'的商品对应排序格式错误。';
                    return $this->arrayData($text, '', '', 'error');
                } elseif ($none && !$err) {
                    $text = '排序必填，ID为'.implode(',', $none).'的商品对应排序为空';
                    return $this->arrayData($text, '', '', 'error');
                } elseif (!$none && $err) {
                    $text = '排序必须是正整数,ID为'.implode(',', $err).'的商品对应排序格式错误';
                    return $this->arrayData($text, '', '', 'error');
                }
                //获取商品信息
                $product_list = $this->get_product_info($data['product_id']);
                if ($product_list) {
                    foreach ($product_list as $key => $val) {
                        $product_list[$key]['img_path'] = $goodsData[$val['product_id']]['img_path'];
                        $product_list[$key]['sort'] = $goodsData[$val['product_id']]['sort'];
                        if ($data['channel'] == 1 || !$data['channel']) {
                            $location[$key]['product_id'] = $val['product_id'];
                            $location[$key]['location'] = 'App://type=1&&&value=' . $val['product_id'];
                            $location[$key]['channel'] = 1;
                            $location[$key]['create_time'] = strtotime('now');;
                        }
                    }
                    $product_list = $this->array_sort($product_list,'sort');
                    $conditions['content'] = json_encode($product_list);
                } else {
                    return $this->arrayData('所添加是已下架或是赠品或不是海外的商品', '', '', 'error');
                }
            }
        }
        //类型(2：图片)
        if ($data['advertisement_type'] == 2) {
            //处理平台数据
            switch ($data['channel']){
                case 0:
                    $app = array(
                        'location' =>$data['app_location'],
                        'product_id'=>0,
                        'channel'=>1,
                        'create_time' => strtotime('now')
                    );
                    $wap = array(
                        'location' =>$data['wap_location'],
                        'product_id'=>0,
                        'channel'=>2,
                        'create_time' => strtotime('now')
                    );
                    break;
                case 1:
                    $app = array(
                        'location' =>$data['app_location'],
                        'product_id'=>0,
                        'channel'=>$data['channel'],
                        'create_time' => strtotime('now')
                    );
                    break;
                case 2:
                    $wap = array(
                        'location' =>$data['wap_location'],
                        'product_id'=>0,
                        'channel'=>$data['channel'],
                        'create_time' => strtotime('now')
                    );
                    break;
            }
            if(isset($app)){
                $location[] = $app;
            }
            if(isset($wap)){
                $location[] = $wap;
            }
            $content[]['img_path'] = trim($data['first_image']);
            $conditions['content'] = json_encode($content);
        }
        $res = $this->data->update($columStr, $this->table,$conditions, $where);
        //插入app引用
        if(is_array($location)){
            //删除Location的数据重新生成
            $this->delLocationData($data['id']);

            foreach ($location as $k){
                $k['ad_id'] =$data['id'];
                $this->data->insert($this->location_table,$k);
            }
        }
        return $res ? $this->arrayData('修改成功！',$url) : $this->arrayData('修改失败！', '', '', 'error');
    }
    //根据某个键值来排序
    public function array_sort($array,$keys,$type='asc'){
        //$array为要排序的数组,$keys为要用来排序的键名,$type默认为升序排序
        $keysvalue = $new_array = array();
        foreach ($array as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $array[$k];
        }
        return $new_array;
    }
    /**
     * 根据商品ID获取商品信息
     * @param $id string
     * @return array || bool
     */
    public function get_product_info($id)
    {
        $product_id = !is_array($id) ? is_string($id) ? $id : '' : implode(',', $id);
        if (!$product_id) {
            return false;
        }

        $where = "WHERE g.is_global = 1 AND g.product_type = 0 AND g.id IN ({$product_id}) ";
        $field = 'g.id as product_id ,g.goods_name as product_name,g.packing,g.market_price,g.goods_price as price,gf.flag';
        $join = "LEFT JOIN {$this->goodsextend_table} AS gf ON g.id = gf.goods_id";
        $data = [
            'column'=>$field,
            'join'=>$join,
            'table'=>"{$this->goods_table} AS g",
            'where'=>$where,
        ];
        return $this->data->getData($data);
    }

    //获取海外购信息
    public function getDate($id){
        $where = 'WHERE id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->table,
            'where'=>$where,
        ];
        $result = $this->data->getData($data,true);

        $ad_position = $this->getPositions($result['position_id']);
        if($result['ad_type']==2){
            $location = $this->getlocation($id);
            if(is_array($location)){
                foreach ($location as $k){
                    if($k['channel']==1){
                        $result['app_location'] =$k['location'];
                    }
                    if($k['channel']==2){
                        $result['wap_location'] =$k['location'];
                    }
                }
            }
            $result['img_path'] = json_decode($result['content'],true)[0]['img_path'];

        }elseif($result['ad_type']==1){
            $result['products'] = json_decode($result['content'],true);
        }
        return !empty($result) ? array('status'=>'success', 'data'=>$result,'ad_position'=>$ad_position['data']) : array('status'=>'error');
    }
    //获取海外购location
    public function getlocation($id){
        $where = 'WHERE ad_id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->location_table,
            'where'=>$where,
        ];
        return $this->data->getData($data);
    }

    //获取海外购广告位列表
    public function getPositions($id){
        $field='id,name,ad_type';
        $where ='1=1';
        if($id){
            $where .= " AND id =".$id;
        }
        $data = array();
        $result = $this->data->select($field,'\Shop\Models\BaiyangAccesoriesAdPosition',$data,$where);
        $return = [
            'res' => 'success',
            'data' => $result,
        ];
        return $return;
    }

    //获取单个海外购广告位
    public function getPositionData($id){
        $where = 'WHERE id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->ap_table,
            'where'=>$where,
        ];
        return $this->data->getData($data,true);
    }

    public function searchGoods($param){
        $where = "WHERE ( id ='{$param['search']}' or goods_name like '%{$param['search']}%' )AND product_type=0 AND is_global=1 AND status=1 ";
        $field = 'id,goods_name,goods_image';

        $result =  $this->data->getData(array(
            'column'=>$field,
            'table'=>$this->goods_table,
            'where'=>$where,
        ));
        $return = [
            'res' => 'success',
            'list' => $result
        ];
        return $return;
    }

    public function cancel($id){
        $columStr='status=:status:';
        $conditions['status'] = 0;
        $where = 'id='.$id;
        $res = $this->data->update($columStr, $this->table,$conditions, $where);
        return $res ? $this->arrayData('取消成功！') : $this->arrayData('取消失败！', '', '', 'error');
    }

    public function delData($id){
        $columStr='status=:status:';
        $conditions['status'] = 2;
        $where = 'id='.$id;
        $res = $this->data->update($columStr, $this->table,$conditions, $where);
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }
    public function delLocationData($id){
        $data['ad_id'] = $id;
        $where = 'ad_id=:ad_id:';
        $res = $this->data->delete($this->location_table, $data, $where);
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }
}