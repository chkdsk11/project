<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\AppAdvertisementsData;

class AdwapService extends BaseService
{
    public $new_data;
    public $adTable;
    public function __construct()
    {
        $this->data =AppAdvertisementsData::getInstance();
        $this->adTable = '\Shop\Models\AppWapAdvertisements';
        $this->aptable='\Shop\Models\AppWapAdPosition';
        $this->goods_table ='\Shop\Models\BaiyangGoods';
        $this->goods_timeoffer ='\Shop\Models\AppLimitTimeOffer';
    }

    //获取所有广告
    public function getAllad($param)
    {

        $where = 'WHERE 1=1 ';
        if($param['param']['ad_name']){
            $where .= " AND ad.advertisement like '%{$param['param']['ad_name']}%'";
        }
        if($param['param']['ad_position']>0){
            $where .= ' AND ad.adp_id = '.$param['param']['ad_position'];
        }
        $cur_time = strtotime('now');
        //未开始
        if($param['param']['ad_status'] == 'start'){
            $where .= " AND ad.start_time > $cur_time";
        } //已结束
        else if($param['param']['ad_status'] == 'end'){
            $where .= " AND ad.end_time < $cur_time";
        } //进行中
        else if($param['param']['ad_status'] == 'middle'){
            $where .= " AND $cur_time BETWEEN ad.start_time AND ad.end_time";
        }

        if($param['param']['ad_type']>0){
            $where .= ' AND ad.advertisement_type = '.$param['param']['ad_type'];
        }

        if($param['param']['ad_channel']>0){
            $where .= ' AND channel = '.$param['param']['ad_channel'];
        }
        $counts = $this->data->countData(array(
            'where'=>$where,
            'table' =>$this->adTable.' as ad' ,
        ));
        if (empty($counts)) {
            return array('res' => 'success', 'list' => 0);
        }
        //分页
        $pages['page'] = (int)isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $field = "ad.advertisement_id,ad.order,ad.advertisement,ad.adp_id,ad.start_time,ad.end_time,ad.advertisement_type,p.adpositionid_desc as adpositionid_name";

        $where .= ' ORDER BY ad.advertisement_id DESC limit ' . $page['record'] . ',' . $page['psize'];
//        $join = "left join {$this->aptable} as p ON ad.adp_id = p.id ";
//        $result = $this->data->getData(array(
//            'column'=>$field,
//            'where'=>$where,
//            'table' =>$this->adTable.' as ad' ,
//            'join' =>$join
//        ));

        $sql = "select {$field} from wap_advertisements as ad left join wap_ad_position as p ON ad.adp_id = p.id {$where}";
        $stmt = $this->dbWriteApp->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchall(\PDO::FETCH_ASSOC);

        $return = [
            'res' => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }


    public function delAdvertisement($id){
        $data['advertisement_id'] = $id;
        $where = 'advertisement_id=:advertisement_id:';
        $res = $this->data->delete( $this->adTable, $data, $where);
        $this->clearCache();//清除缓存
        return $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    public function add_ad_position($data,$url){
        if (empty($data))
        {
            return false;
        }
        $insert['advertisement_id'] = NULL;	// int类型
        $insert['advertisement']	= $data['action_name'];
        $insert['adp_id'] = $data['adp_id'];
        $insert['start_time'] = strtotime($data['start_time']);
        $insert['end_time'] = strtotime($data['end_time']);
        $insert['advertisement_type'] =(int) $data['advertisement_type'];
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
            //品牌街目标地址
//            if($data['adp_id']==22){
//                $mes = preg_match('/App:\/\//', $data['location_image']) ? false : true;
//                var_dump($mes);exit;
//                if($mes){
//                    return $this->arrayData('目标地址不正确，例：App://type=2&&&value=155', '', '', 'error');
//                }
//            }
            //wap菜单，42,43
            if($data['adp_id']== 42 || $data['adp_id']== 43){
                if($data['change_image']){
                    $insert['change_img_url'] = $data['change_image'];
                }else{
                    $insert['change_img_url'] = $data['changet_second_image'];
                }
            }
//            if($data['adp_id']==22){
//                $mes = preg_match('/App:\/\//', $data['location_image']) ? false : true;
//                if($mes){
//                    return $this->arrayData('目标地址不正确，例：App://type=2&&&value=155', '', '', 'error');
//                }
//            }

            $insert['slogan'] = $data['slogan_image'];
            $insert['location'] = trim($data['location_image'],' ');
            $insert['order'] = (int)$data['order_image'];
            if($data['first_image']){
                $insert['image_url'] = $data['first_image'];
            }else{
                $insert['image_url'] = $data['second_image'];
            }
        } elseif ($insert['advertisement_type'] == 2) {
            $insert['products']= $this->packaging_products($data['product_id'], $data['order_product'], $data['product_image']);
        } elseif (in_array($insert['advertisement_type'], array(3,4,5,6,7,8))) {
            $insert['slogan'] = $data['slogan_text'];
            $insert['location'] = $data['location_text'];
            $insert['order'] = (int)$data['order_text'];
        }
        // 开启事务
        $this->dbWrite->begin();
        $advertisement_Id =  $this->data->insert($this->adTable,$insert,true);
        if(empty($advertisement_Id)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }else{
            $this->dbWrite->commit();
            $this->clearCache();//清除缓存
            return $this->arrayData('添加成功！', $url);
        }
    }

    //编辑广告
    public function editAdvertisements($data){
        $columStr = "advertisement=:advertisement:,advertisement_desc=:advertisement_desc:,products=:products:,author=:author:,update_time=:update_time:,start_time=:start_time:,end_time=:end_time:";

        $conditions['advertisement']	= $data['action_name'];
        $conditions['advertisement_desc'] = $data['action_description'];
        $conditions['products'] = NULL;
        $conditions['author'] = $this->session->get('username');
        $conditions['update_time'] = strtotime('now');
        $conditions['start_time'] = strtotime($data['start_time']);
        $conditions['end_time'] = strtotime($data['end_time']);
        $where='advertisement_id = '.$data['id'];

        if ($data['advertisement_type'] == 1) {

            $columStr .=",slogan=:slogan:,location=:location:,backgroud=:backgroud:,image_url=:image_url:";
            $conditions['slogan'] = $data['slogan_image'];
            $conditions['location'] = trim($data['location_image'],' ');
            $conditions['backgroud'] = empty($data['background']) ? NULL : $data['background'];
            $conditions['order'] = (int)$data['order_image'];

            //品牌街目标地址
            /*if($data['adp_id']==22){
                $mes = preg_match('/App:\/\//', $data['location_image']) ? false : true;
                if($mes){
                    return $this->arrayData('目标地址不正确，例：App://type=2&&&value=155', '', '', 'error');
                }
            }*/
            //wap菜单，42,43
            if($data['adp_id']== 42 || $data['adp_id']== 43){
                $columStr .=",change_img_url=:change_img_url:";
                if($data['change_image']){
                    $conditions['change_img_url'] = $data['change_image'];
                }else{
                    $conditions['change_img_url'] = $data['changet_second_image'];
                }
            }

            if($data['first_image']){
                $conditions['image_url'] = $data['first_image'];
            }else{
                $conditions['image_url'] = $data['second_image'];
            }

        } elseif ( $data['advertisement_type'] == 2) {

            $conditions['products']= $this->packaging_products($data['product_id'], $data['order_product'], $data['product_image']);
        } elseif (in_array($data['advertisement_type'], array(3,4,5,6,7,8))) {
            $columStr .=",slogan=:slogan:,location=:location:";
            $conditions['slogan'] = $data['slogan_text'];
            $conditions['location'] = $data['location_text'];
            $conditions['order'] = (int)$data['order_text'];
        }
        $feilds = $this->arrayTostr($conditions);
        $sql     = "UPDATE wap_advertisements SET {$feilds} WHERE {$where}";
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
    //获取广告信息
    public function getPosition($PositionId){
        $where = 'WHERE id ='.$PositionId;
        $field = 'id,adpositionid_desc as adpositionid_name';
        $data = [
            'column'=>$field,
            'table'=>$this->aptable,
            'where'=>$where,
        ];
        $data = $this->data->getData($data,true);
        return $data;

    }

    public function getPositions($parent_id=0){
        $field='id,adpositionid_desc as adpositionid_name,parent_id,adposition_type';
//        $where ='parent_id='.$parent_id;
        $where='1=1 AND status=1';
        $data = array();
        $result = $this->data->select($field,$this->aptable,$data,$where);
        $return = [
            'res' => 'success',
            'data' => $result,
        ];
        return $return;
    }

    //获取广告信息
    public function getAdvertisementsInfo($id){
        $where = 'WHERE advertisement_id='.$id;
        $field = '*';
        $data = [
            'column'=>$field,
            'table'=>$this->adTable,
            'where'=>$where,
        ];
        $result =$this->data->getData($data,true);
        //广告位置
        $ad_position = $this->getPosition($result['adp_id']);
        //序列化商品
        if($result['advertisement_type']==2){
            $result['products'] = unserialize($result['products']);

        }

        return !empty($result) ? array('status'=>'success', 'data'=>$result,'ad_position'=>$ad_position) : array('status'=>'error');
    }

    public function searchGoods($param){
        if($param['is_global'] == 1){
            $statusWhere = ' status=1 ';
        }else{
            $statusWhere = ' sale_timing_wap=1 ';
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

    //取消操作
    public function cancel($id){
        $columStr='end_time=:end_time:,update_time=:update_time:,author=:author:';
        $conditions['author'] = $this->session->get('username');
        $conditions['end_time'] = strtotime('now');
        $conditions['update_time'] = strtotime('now');
        $where = 'advertisement_id='.$id;
        $res = $this->data->update($columStr, $this->adTable,$conditions, $where);
        $this->clearCache(); //清除缓存
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
                'where'=>"WHERE id IN({$ids})  AND product_type = 0  and ((sale_timing_wap = 1 and is_global = 0) or (status = 1 and  is_global = 1))",
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
                    $result[$key]['default_image'] = $upload_images[$val['product_id']] ? $upload_images[$val['product_id']] :$result[$key]['small_path'];
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
    /**
     * 获取广告位分类树
     */
    public function get_position_all_tree(){
        $all_position = $this->ad_position_all();
        //print_r($all_position);die;
        return $this->position_tree($all_position['list']);
    }
    public function position_tree($data , $parent_id = '0', $lev = 1){
        foreach ($data as $v) {
            //print_r($v);die;
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
        $this->data->update('display_status=:display_status:', $this->aptable,['display_status'=>0], $where);
        if($data){
            $data = implode(',',$data);
            $where = "id in($data)";
            $result = $this->data->update('display_status=:display_status:', $this->aptable,['display_status'=>1], $where);
        }
        return $result ? $this->arrayData('修改成功！', '/ad/home_show', '') : $this->arrayData('修改失败！', '', '', 'error');

    }

    //获取所有的父级
    public function getAllParent(){
        $field='id,adpositionid_desc as adpositionid_name';
        $where ='parent_id = 0';
        $data = array();
        $result = $this->data->select($field,$this->aptable,$data,$where);
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
        $table = $this->aptable;
        $where = ' parent_id=:id:';
        $data['id'] = (int)$id;
        $field = 'id,adpositionid_desc as adpositionid_name';
        $result = Ad_positionData::getInstance()->select($field,$table,$data,$where);
        return $this->arrayData('','',$result,'success');
    }

    public function clearCache(){
        $this->AppRedisCache->delete('brand_street');// 删除品牌街缓存
        $this->AppRedisCache->delete('advertisements_list_all');// 所有广告活动缓存
        $this->AppRedisCache->delete('categories_list_data');// 所有分类数据缓存
        $this->AppRedisCache->delete('wap_advertisement_all');//删除分类页获取的所有广告数据

    }
}