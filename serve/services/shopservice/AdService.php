<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\AdvertisementsData;
use Shop\Datas\Ad_positionData;

class AdService extends BaseService
{
    public $new_data;
    public $data;
    public $table;
    public $ap_table;
    public function __construct()
    {
        $this->data =AdvertisementsData::getInstance();
        $this->table = '\Shop\Models\AppAdvertisements';
        $this->ap_table = '\Shop\Models\AppAdPosition';
        $this->goods_table ='\Shop\Models\BaiyangGoods';
        $this->goods_timeoffer ='\Shop\Models\AppLimitTimeOffer';
    }

    //获取所有广告
    public function getAllad($param)
    {
        $where = 'WHERE 1=1 ';
        if($param['param']['ad_name']){
            $where .= " AND ad.advertisement like '%{$param['param']['ad_name']}%'";
            $data['name'] = '%'.$param['param']['name'].'%';
        }
        if($param['param']['ad_position']>0){
            //获取位置分类id
            $ids = $this->getChlidids($param['param']['ad_position']);
            if(is_array($ids)){
                $ids =  implode(',',$ids);
                $where .= " AND ad.adp_id IN($ids)";
            }
        }
        $cur_time = strtotime('now');
        //未开始
        if($param['param']['ad_status'] == 'start'){
            $where .= " AND ad.start_time > $cur_time";
        } //已结束
        else if($param['param']['ad_status'] == 'end'){
            $where .= " AND ad.start_time < $cur_time";
        } //进行中
        else if($param['param']['ad_status'] == 'middle'){
            $where .= " AND $cur_time BETWEEN ad.start_time AND ad.end_time";
            $data['nowtime'] = $cur_time;
        }else if($param['param']['ad_status'] == 'cancel'){
            $where .= " AND ad.status = 0";
        }

        if($param['param']['ad_type']>0){
            $where .= ' AND ad.advertisement_type = '.$param['param']['ad_type'];
        }

        if($param['param']['ad_channel']>0){
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
        $field = "ad.advertisement_id,ad.order,ad.advertisement,ad.adp_id,ad.start_time,ad.end_time,ad.advertisement_type,p.adpositionid_name";
        $where .= ' ORDER BY ad.advertisement_id DESC limit ' . $page['record'] . ',' . $page['psize'];
        /**


        $join = "left join {$this->ap_table} as p ON ad.adp_id = p.id ";
        $result = $this->data->getData(array(
            'column'=>$field,
            'where'=>$where,
            'table' =>$this->table.' as ad' ,
            'join' =>$join
        ));
         **/
        $sql = "select {$field} from advertisements as ad left join ad_position as p ON ad.adp_id = p.id {$where} ";
        $stmt = $this->dbWriteApp->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if($result){
            foreach ($result as $k =>$v){
                $ad_position = $this->getpositiontree($v['adp_id']);
                ksort($ad_position);//对数组排序
                unset($ad_position[$v['adp_id']]);
                $result[$k]['position_name'] =implode(' >> ',$ad_position);
            }

        }
        $return = [
            'res' => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    //获取广告位列表
    public function getPositions($parent_id=0){
        $field='id,adpositionid_name,parent_id,versions,channel';
        $where ='WHERE parent_id='.$parent_id;
        $result = $this->data->getData(array(
            'column'=>$field,
            'where'=>$where,
            'table' =>$this->ap_table
        ));
        $return = [
            'res' => 'success',
            'data' => $result,
        ];
        return $return;
    }
    public function delAdvertisement($id){
        $data['advertisement_id'] = $id;
        $where = 'advertisement_id=:advertisement_id:';
        $res = $this->data->delete($this->table, $data, $where);
        $this->clearCache();
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    //添加广告活动
    public function add_ad_position($data,$url){
        if (empty($data))
        {
            return false;
        }
        $insert['advertisement_id'] = NULL;	// int类型
        $insert['advertisement']	= $data['action_name'];
        $insert['adp_id'] = array_pop($data['ad_position']);
        if($insert['adp_id']==0){
            $insert['adp_id'] = array_pop($data['ad_position']);
        }
        $positionData = $this->check_ad_position_group($insert['adp_id']);
        if(!$positionData ){
            return $this->arrayData('找不到广告位！', '', '', 'error');
        }
        if($positionData['is_group']==1){
            return $this->arrayData('不能选择广告组，请选择广告位！', '', '', 'error');
        }
        $insert['start_time'] = strtotime($data['start_time']);
        $insert['end_time'] = strtotime($data['end_time']);
        $insert['advertisement_type'] =(int) $data['advertisement_type'];
        $insert['is_default'] = $data['is_default'];
        $insert['image_url'] = NULL;
        $insert['backgroud'] = empty($data['background']) ? NULL : $data['background'];//数据库字段为backgroud
        $insert['slogan'] = NULL;
        $insert['location'] = NULL;
        $insert['order'] = NULL;// int 类型
        $insert['advertisement_desc'] = $data['action_description'];
        $insert['products'] = NULL;
        $insert['author'] = $this->session->get('username');
        $insert['create_time'] = strtotime('now');
        $insert['update_time'] = strtotime('now');
        if ($insert['advertisement_type'] == 1) {
            if($positionData['adposition_type'] != 1){
                return $this->arrayData('该广告位不允许添加图片广告！', '', '', 'error');
            }
            $insert['slogan'] = $data['slogan_image'];
            $insert['location'] = trim($data['location_image'],' ');
            $insert['order'] = (int)$data['order_image'];
            if(isset($data['first_image']) && $data['first_image'] != ''){
                $insert['image_url'] = $data['first_image'];
            }else{
                $insert['image_url'] = $data['second_image'];
            }
        } elseif ($insert['advertisement_type'] == 2) {
            if($positionData['adposition_type']!=2){
                return $this->arrayData('该广告位不允许添加商品推荐！', '', '', 'error');
            }
            $insert['products']= $this->packaging_products($data['product_id'], $data['order_product'], $data['product_image']);
        } elseif (in_array($insert['advertisement_type'], array(3,4,5,6,7,8))) {

            if(!in_array($positionData['adposition_type'], array(3,4,5,6,7,8))){
                return $this->arrayData('该广告位不允许添加文字广告！', '', '', 'error');
            }
            $insert['slogan'] = $data['slogan_text'];
            $insert['location'] = $data['location_text'];
            $insert['order'] = (int)$data['order_text'];
        }
        // 开启事务
        $this->dbWrite->begin();
        $advertisement_Id =  $this->data->insert($this->table,$insert,true);
        if(empty($advertisement_Id)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }else{
            $this->dbWrite->commit();
            $this->clearCache();
            return $this->arrayData('添加成功！', $url);
        }
    }

    //编辑广告活动
    public function editAdvertisements($data){
        $conditions['advertisement']	= $data['action_name'];
        $conditions['is_default'] = $data['is_default'];
        $conditions['advertisement_desc'] = $data['action_description'];
        $conditions['products'] = NULL;
        $conditions['author'] = $this->session->get('username');
        $conditions['update_time'] = strtotime('now');
        $conditions['start_time'] = strtotime($data['start_time']);
        $conditions['end_time'] = strtotime($data['end_time']);
        $where='advertisement_id = '.$data['id'];
        if ($data['advertisement_type'] == 1) {
            $conditions['slogan'] = $data['slogan_image'];
            $conditions['location'] = trim($data['location_image'],' ');
            $conditions['backgroud'] = empty($data['background']) ? NULL : $data['background'];
            $conditions['order'] = $data['order_image'];
            if(isset($data['first_image']) && $data['first_image'] != ''){
                $conditions['image_url'] = $data['first_image'];
            }else{
                $conditions['image_url'] = $data['second_image'];
            }
        } elseif ( $data['advertisement_type'] == 2) {
            $conditions['products']= $this->packaging_products($data['product_id'], $data['order_product'], $data['product_image']);
        } elseif (in_array($data['advertisement_type'], array(3,4,5,6,7,8))) {
            $conditions['slogan'] = $data['slogan_text'];
            $conditions['location'] = $data['location_text'];
            $conditions['order'] = (int)$data['order_text'];
        }
        $feilds = $this->arrayTostr($conditions);
        $sql     = "UPDATE advertisements SET {$feilds} WHERE {$where}";
        $this->log->error($sql);
        if($this->dbWriteApp->execute($sql)){
            $this->clearCache();
            return $this->arrayData('修改成功！');
        }
            return $this->arrayData('添加失败！', '', '', 'error');
    }
    //拼接sql语句字段
    public function arrayTostr($arr){

        if(is_array($arr)){
            $newarr = array();
            foreach ($arr as $k => $v){
                $newarr[] = "`{$k}` = '{$v}'";
            }
            return implode(',',$newarr);
        }else{
            return $arr;
        }
    }

    //获取广告位
    public function getPosition($PositionId){
        $where = 'WHERE id ='.$PositionId;
        $field = 'id,adpositionid_name';
        $data = [
            'column'=>$field,
            'table'=>$this->ap_table,
            'where'=>$where,
        ];
        $data = $this->data->getData($data,true);
        return $data;
    }

    public function getpositiontree($pid,$arr= array()){
        $where = 'WHERE id ='.$pid;
        $field = 'id,parent_id,adpositionid_name';
        $data = [
            'column'=>$field,
            'table'=>$this->ap_table,
            'where'=>$where,
        ];
        $data = $this->data->getData($data,true);
        if($data){
            $arr[$data['parent_id']] = $data['adpositionid_name'];
            if($data['parent_id']>0){
                return $this->getpositiontree($data['parent_id'],$arr);
            }
        }
        return $arr;
    }

    public function search_position_tree($pid,$arr= array()){
        $where = 'WHERE id ='.$pid;
        $field = 'id,parent_id,adpositionid_name';
        $data = [
            'column'=>$field,
            'table'=>$this->ap_table,
            'where'=>$where,
        ];
        $data = $this->data->getData($data,true);
        if($data){
            $arr['tree'][] =$data['id'];
            $arr['position'][$data['id']] = $this->get_chlids_list($data['parent_id']);
            if($data['parent_id']>0){
                return $this->search_position_tree($data['parent_id'],$arr);
            }
        }
        return $arr;
    }
    //获取所有的子级
    public function get_chlids_list($pid)
    {
        $where = 'WHERE parent_id ='.$pid;
        $field = 'id,parent_id,adpositionid_name';
        $data = [
            'column'=>$field,
            'table'=>$this->ap_table,
            'where'=>$where,
        ];
        return $this->data->getData($data);
    }

    //获取广告信息
    public function getAdvertisementsInfo($id){
        $where = 'WHERE advertisement_id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->table,
            'where'=>$where,
        ];
        $result = $this->data->getData($data,true);
        //广告位置
        $ad_position = $this->getpositiontree($result['adp_id']);
        ksort($ad_position);//对数组排序
        //序列化商品
        if($result['advertisement_type']==2){
            $result['products'] = unserialize($result['products']);
        }
        return !empty($result) ? array('status'=>'success', 'data'=>$result,'ad_position'=>$ad_position) : array('status'=>'error');
    }
    //搜索商品信息
    public function searchGoods($param){
        if($param['is_global'] == 1){
            $statusWhere = ' status=1 ';
        }else{
            $statusWhere = ' sale_timing_app=1 ';
        }
        $where = "WHERE (id ='{$param['search']}' or goods_name like '%{$param['search']}%') AND product_type=0 AND is_global={$param['is_global']} AND {$statusWhere} ";
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
    //验证广告位
    public function check_ad_position_group($id){
        return $this->data->getData(array(
            'column'=>'*',
            'table'=>$this->ap_table,
            'where'=>'WHERE id = '.$id,
        ),true);
    }
    //验证广告位
    public function ad_position_all($limit=array(), $order=array(), $seach_data=array()){
        $data =array();
        $where = "1=1";
        if(isset($seach_data['adposition_type']) && !empty($seach_data['adposition_type'])){
            $where .= 'AND `adposition_type` = '.$seach_data['adposition_type'].' ';
        }
        if(isset($seach_data['position_page']) && !empty($seach_data['position_page'])){
            $where .= 'AND `id` IN('.$seach_data['position_page'].') ';
        }
        if(isset($seach_data['adpositionid_name']) && !empty($seach_data['adpositionid_name'])){
            $where .= 'AND (`id` LIKE "%'.$seach_data['adpositionid_name'].'%" OR `adpositionid_name` LIKE "%'.$seach_data['adpositionid_name'].'%") ';
        }
        if(!empty($order)){
            $where .= 'GROUP BY `id` ORDER BY ';
            foreach ($order as $key => $val) {
                $where .= '`'.$key.'` '.$val.',';
            }
            $where .= ' ';
        }
        if(!empty($limit) && isset($limit[0]) && isset($limit[1])){
            $where .= 'LIMIT '.$limit[0].','.$limit[1].' ';
        }
        $field = 'id,adpositionid_name,parent_id,is_group,adposition_type,image_size,versions,channel,display_status';
        $result = $this->data->select($field,$this->ap_table, $data, $where);

        $return = [
            'res' => 'success',
            'list' => $result
        ];
        return $return;
    }
    /**
     * 获取广告位分类树
     */
    public function get_position_all_tree(){
        $all_position = $this->ad_position_all();
        return $this->position_tree($all_position['list']);
    }

    public function position_tree($data , $parent_id = '0', $lev = 1){
        foreach ($data as $v) {
            if ($v['parent_id'] == $parent_id) {
                $v['lev'] = $lev;
                $v['checked'] = $v['display_status'] ? 'checked' : '';
                $this->new_data[] = $v;
                $this->position_tree($data, $v['id'], $lev + 1);
            }
        }
        return $this->new_data;
    }

    /**
     * 设置首页广告位是否显示
     */
    public function up_position_show($data)
    {
        if ($data && !is_array($data)) {
            return false;
        }
        $where = '1=1';
        $this->data->update('display_status=:display_status:', $this->ap_table,['display_status'=>0], $where);
        if($data){
            $data = implode(',',$data);
            $where = "id in($data)";
            $result = $this->data->update('display_status=:display_status:', $this->ap_table,['display_status'=>1], $where);
        }
        //清除缓存
        $this->AppRedisCache->delete('all_position_list');
        $this->AppRedisCache->delete('advertisements_list_all');
        return $result ? $this->arrayData('修改成功！', '', '') : $this->arrayData('修改失败！', '', '', 'error');

    }

    //获取所有的父级
    public function getAllParent(){
        $field='id,adpositionid_name';
        $where ='parent_id = 0';
        $data = array();
        $result = Ad_positionData::getInstance()->select($field,$this->ap_table,$data,$where);
        $return = [
            'res' => 'success',
            'data' => $result,
        ];
        return $return;
    }

    //获取所有的子级
    public function getAllChild($id = 0,$type = 'pid',$act = true,$enable = false)
    {
        //查询条件
        $table = $this->ap_table;
        $where = ' parent_id=:id:';
        $data['id'] = (int)$id;
        $field = 'id,adpositionid_name,versions,channel';
        $result = Ad_positionData::getInstance()->select($field,$table,$data,$where);
        return $this->arrayData('','',$result,'success');
    }
    //取消操作
    public function cancel($id){
        $columStr='end_time=:end_time:,update_time=:update_time:,author=:author:';
        $conditions['author'] = $this->session->get('username');
        $conditions['end_time'] = strtotime('now');
        $conditions['update_time'] = strtotime('now');
        $where = 'advertisement_id='.$id;
        $res = $this->data->update($columStr, $this->table,$conditions, $where);
        $this->clearCache();
        return $res ? $this->arrayData('取消成功！') : $this->arrayData('取消失败！', '', '', 'error');
    }

    /**
     * 组装商品推荐广告的products字段，返回id，名字，包装语，排序，价格，促销，图片路径
     * @param $product_id_list => 商品id列表
     * @param $order_product => 商品排序
     * @param $upload_images => 上传图片
     * @return arary => 返回products字段
     */
    public function packaging_products($product_id_list, $order_product, $upload_images)
    {
        if (is_array($product_id_list) && is_array($order_product) && is_array($upload_images))
        {
            $ids = implode(',', $product_id_list);
            //查询商品信息
            $result =  $this->data->getData(array(
                'column'=>" id as product_id,goods_name as product_name,packing,market_price,goods_price as price,goods_image as small_path",
                'table'=>$this->goods_table,
                'where'=>"WHERE id IN({$ids})  AND product_type = 0 AND ((sale_timing_app = 1 and is_global = 0) or (status = 1 and  is_global = 1))",
            ));
            //查询优惠活动
            $now = strtotime('now');
            $result_limit =  $this->data->getData(array(
                'column'=>" limit_condition",
                'table'=>$this->goods_timeoffer,
                'where'=>" WHERE {$now} BETWEEN start_time AND end_time AND limit_type = 4 AND is_cancel = 0",
            ));
            // 如果有上传图片则default_image为上传字段
            if (is_array($upload_images) && !empty($result))
            {
                foreach($result as $key=>$val){
                    //1、判断是否有商品图片，有则处理
                    $result[$key]['default_image'] = isset($upload_images[$val['product_id']]) ? $upload_images[$val['product_id']] :$result[$key]['small_path'];
                    //2、处理商品排序
                    $result[$key]['sort'] = $order_product[$val['product_id']] ? $order_product[$val['product_id']] : 0;
                    //3、处理优惠活动
                    if($result_limit){
                        foreach ($result_limit as $k => $v)
                        {
                            $condition = json_decode($v['limit_condition'], TRUE);
                            if (array_key_exists($val['product_id'], $condition))
                            {
                                $result[$key]['price'] = $condition[$val['product_id']];
                            }
                        }
                    }
                    //4、促销信息，现在写死
                    $result[$key]['promotion'] = 'hot';
                    unset($result[$key]['small_path']);
                }

            }
            $result = $this->array_sort($result, 'sort', 'asc');//对sort字段进行排序
            return  serialize($result);
        }
        return NULL;
    }
    /**
     * -------------------------------------------------
     * 将二维数组按某字段进行排列
     * -------------------------------------------------
     * @author Sun
     * @param array $array       需要进行排序的数组
     * @param string $sort_key   排序的字段
     * @param string $sort_type   排序的类型
     */
    public function array_sort($array = array(), $sort_key, $sort_type = 'asc')
    {
        $sort_array = array();
        if (($sort_type != 'asc') && ($sort_type != 'desc'))
        {
            return $array;
        }
        if(!is_array($array)){
            return $array;
        }
        foreach ($array as $arr)
        {
            $sort_array[] = $arr[$sort_key];
        }
        switch ($sort_type)
        {
            case 'desc':
                array_multisort($sort_array, SORT_DESC, $array);
                break;
            case 'asc':
            default:
                array_multisort($sort_array, SORT_ASC, $array);
        }
        return $array;
    }

    //获取该分类下的所有子分类id
    public function getChlidids($id,$ids=array()){

        $data =  $this->data->getData(array(
            'column'=>'id',
            'table'=>$this->ap_table,
            'where'=>'WHERE parent_id = '.$id,
        ));
        $ids[$id] =$id;
        if($data){
            foreach ($data as $k){
                $ids[$k['id']] =$k['id'];
                $arr = $this->getChlidids($k['id'],$ids);
                if($arr){
                    foreach ($arr as $c => $v){
                        $ids[$c] = $v;
                    }
                }
            }
        }
        return $ids;
    }

    public function clearCache(){
        $this->AppRedisCache->delete('brand_street');// 删除品牌街缓存
        $this->AppRedisCache->delete('advertisements_list_all');// 所有广告活动缓存
        $this->AppRedisCache->delete('categories_list_data');// 所有分类数据缓存
        $this->AppRedisCache->delete('advertisement_all');//删除分类页获取的所有广告数据

    }
}