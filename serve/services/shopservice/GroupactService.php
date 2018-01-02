<?php
/**
 * Created by PhpStorm.
 * User: yanbo
 * Date: 2017/5/19
 * Time: 11:28
 */
namespace Shop\Services;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangGroupFightActivity;
use Shop\Models\BaiyangSkuInfo;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class GroupactService extends BaseService {
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * 活动列表
     * @param $param 筛选内容
     * @return array
     */
    public function getList($param){
        $where = " 1=1 ";
        $map = [];
        if(!empty($param['seaData'])) {
            $seaData = $param['seaData'];
            if ($seaData['gfastate'] != '') {
                $where .= " AND gfa_state = :gfa_state:";
                $map['gfa_state'] = 0;
                if ($seaData['gfastate'] == 's0') { // 未开始
                    $where .= " AND gfa_starttime > :gfa_starttime:";
                    $map['gfa_starttime'] = time();
                } else if ($seaData['gfastate'] == 1) { //进行中
                    $where .= " AND (gfa_starttime <= :gfa_starttime: AND gfa_endtime > :gfa_endtime:)";
                    $map['gfa_starttime'] = time();
                    $map['gfa_endtime'] = time();
                } else if ($seaData['gfastate'] == 2) {  //已结束
                    $where .= " AND gfa_endtime < :gfa_endtime:";
                    $map['gfa_endtime'] = time();
                } else if ($seaData['gfastate'] == 3) { //取消
                    $map['gfa_state'] = 3;
                }
            }
            if ($seaData['gfanum'] != '') {
                $where .= " AND gfa_user_count = :gfa_user_count:";
                $map['gfa_user_count'] = $seaData['gfanum'];
            }
            if ($seaData['goods'] != '') {
                if (is_numeric($seaData['goods'])) {
                    $where .= " AND goods_id = :goods_id:";
                    $map['goods_id'] = $seaData['goods'];
                } else {
                    $where .= " AND goods_name LIKE :goods_name:";
                    $map['goods_name'] = "%{$seaData['goods']}%";
                }
            }
            if ($seaData['gfa_user_type'] != '') {
                $where .= " AND gfa_user_type = :gfa_user_type:";
                if ($seaData['gfa_user_type'] == 's0') {
                    $map['gfa_user_type'] = 0;
                } else {
                    $map['gfa_user_type'] = $seaData['gfa_user_type'];
                }
            }
        }
        //数量
        $count = BaseData::getInstance()->count('\Shop\Models\BaiyangGroupFightActivity', $map, $where);
        if($count <= 0){
            return array(
                'status' => 'success',
                'list' => [],
                'page' => ''
            );
        }
        //分页
        $pages['page'] = $param['page']; //当前页
        $pages['counts'] = $count;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $page = $this->page->pageDetail($pages);
        //获取列表
        $params = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangGroupFightActivity',
            'where' => "WHERE " . $where,
            'bind' => $map,
            'order' => 'ORDER BY gfa_id desc',
            'limit' => "LIMIT {$page['record']},{$page['psize']}"
        );
        $result = BaseData::getInstance()->getData($params);
        return array(
            'status' => 'success',
            'list' => empty($result) ? [] : $result,
            'page' => $page['page']
        );
    }

    /**
     * 添加活动
     * @param $data
     * @return array
     */
    public function addAct($param){
        $data = $this->checkPost($param);
        if(isset($data['status']) && $data['status'] == 'error'){
            return $data;
        }
        //验证商品是否重复
        $checkGoods = $this->checkGoods(0, $data['goods_id'], $data['gfa_starttime'], $data['gfa_endtime']);
        if(isset($checkGoods['status']) && $checkGoods['status'] == 'error'){
            return $checkGoods;
        }
        $data['add_time'] = time();
        if(BaseData::getInstance()->insert('\Shop\Models\BaiyangGroupFightActivity',$data)){
            return $this->arrayData('添加成功');
        }else{
            return $this->arrayData('添加失败','','','error');
        }
    }

    /**
     * 修改活动
     * @param $data
     * @return array
     */
    public function editAct($param){
        //获取活动，判断活动情况
        $row = $this->getAct($param['gfa_id']);
        if(empty($row)){
            return $this->arrayData('活动不存在','','','error');
        }
        if($row['gfa_state'] == 3){
            return $this->arrayData('取消的活动不能编辑','','','error');
        }
        if ($row['gfa_starttime'] <= time() && $row['gfa_endtime'] > time()) {
            return $this->arrayData('不能编辑,活动已经开始！','','','error');
        }
        if ($row['gfa_endtime'] <= time()) {
            return $this->arrayData('不能编辑,活动已经结束！','','','error');
        }
        //获取活动，判断活动情况end
        //初始化数据
        $data = $this->checkPost($param);
        if(isset($data['status']) && $data['status'] == 'error'){
            return $data;
        }
        $data['gfa_id'] = intval($param['gfa_id']);
        //验证商品是否重复
        $checkGoods = $this->checkGoods($data['gfa_id'], $data['goods_id'], $data['gfa_starttime'], $data['gfa_endtime']);
        if(isset($checkGoods['status']) && $checkGoods['status'] == 'error'){
            return $checkGoods;
        }
        $data['edit_time'] = time();
        $where = " gfa_id = :gfa_id:";
        $columStr = $this->jointString($data, array('gfa_id'));
        if(BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangGroupFightActivity', $data, $where)){
            return $this->arrayData('更新成功');
        }else{
            return $this->arrayData('更新失败','','','error');
        }
    }

    /**
     * 验证表单提交数据
     * @param $param
     * @return array
     */
    private function checkPost($param){
        $data = [];
        $data['gfa_user_type'] = $param['gfa_user_type'];
        if(empty($param['gfa_name'])){
            return $this->arrayData('活动名称不能为空','','','error');
        }
        $data['gfa_name'] = $param['gfa_name'];
        if(empty($param['gfa_starttime'])){
            return $this->arrayData('开始时间不可为空','','','error');
        }
        $data['gfa_starttime'] = strtotime(str_replace('/','-',$param['gfa_starttime']));
        if(empty($param['gfa_endtime'])){
            return $this->arrayData('结束时间不可为空','','','error');
        }
        $data['gfa_endtime'] = strtotime(str_replace('/','-',$param['gfa_endtime']));
        if($param['gfa_starttime'] >= $param['gfa_endtime']){
            return $this->arrayData('结束时间必须大于开始时间','','','error');
        }
        if(empty($param['gfa_user_count']) || !$this->checkInt($param['gfa_user_count']) || $param['gfa_user_count'] <= 1 || $param['gfa_user_count'] > 10){
            return $this->arrayData('成团人数必须在2~10之间','','','error');
        }
        $data['gfa_user_count'] = intval($param['gfa_user_count']);
        if(empty($param['gfa_cycle']) || !$this->checkInt($param['gfa_cycle'])){
            return $this->arrayData('组团周期不可为空','','','error');
        }
        $data['gfa_cycle'] = intval($param['gfa_cycle']);
        if(!$this->checkInt($param['gfa_allow_num'])){
            return $this->arrayData('用户参团次数为>=0整数','','','error');
        }
        $data['gfa_allow_num'] = intval($param['gfa_allow_num']);
        if ($param['gfa_type'] == 1) {
            $data['gfa_way'] = intval($param['gfa_way']);
            if ($data['gfa_way'] == 1) {
                $draw_scale = $param['draw_scale'];
                if (empty($draw_scale) || $draw_scale > 100) {
                    return $this->arrayData('中奖率不能为大于100的整数','','','error');
                }
                $data['gfa_draw_num'] = $draw_scale / 100;
            } else if ($data['gfa_way'] == 2) {
                $draw_num = intval($param['draw_num']);
                if (empty($draw_num)) {
                    return $this->arrayData('请填写中奖个数','','','error');
                }
                $data['gfa_draw_num'] = $draw_num;
            }
        }
        $data['is_show_hot'] = intval($param['is_show_hot']);
        $data['gfa_type'] = $param['gfa_type'];
        if(empty($param['goods_id']) || !$this->checkInt($param['goods_id'])){
            return $this->arrayData('商品不可为空','','','error');
        }
        $data['goods_id'] = intval($param['goods_id']);
        if(empty($param['goods_name'])){
            return $this->arrayData('商品标题不能为空','','','error');
        }
        $data['goods_name'] = $param['goods_name'];
        if(empty($param['goods_introduction'])){
            return $this->arrayData('商品卖点不能为空','','','error');
        }
        $data['goods_introduction'] = $param['goods_introduction'];
        if(empty($param['gfa_sort']) || !$this->checkInt($param['gfa_sort'])){
            return $this->arrayData('权重不可为空','','','error');
        }
        $data['gfa_sort'] = intval($param['gfa_sort']);
        if(empty($param['gfa_price']) || $param['gfa_price'] <= 0 || !$this->checkFloat($param['gfa_price'])){
            return $this->arrayData('拼团价格格式不正确','','','error');
        }
        $data['gfa_price'] = $param['gfa_price'];
        if(!$this->checkInt($param['gfa_num_init'])){
            return $this->arrayData('初始化已参团人数为>=0整数','','','error');
        }
        $data['gfa_num_init'] = intval($param['gfa_num_init']);
        if(empty($param['share_title'])){
            return $this->arrayData('分享标题不能为空','','','error');
        }
        $data['share_title'] = $param['share_title'];
        if(empty($param['share_content'])){
            return $this->arrayData('分享内容不能为空','','','error');
        }
        $data['share_content'] = $param['share_content'];
        //商品列表图不能为空
        if(empty($param['goods_image'])){
            return $this->arrayData('商品列表图不可为空','','','error');
        }
        $data['goods_image'] = $param['goods_image'];
        //轮播图
        if(empty($param['goods_slide_images']) || !is_array($param['goods_slide_images'])){
            return $this->arrayData('商品图至少上传一张','','','error');
        }
        $data['goods_slide_images'] = json_encode($param['goods_slide_images']);
        //分享图不能为空
        if (empty($param['share_image'])) {
            return $this->arrayData('分享图片不能为空','','','error');
        }
        $data['share_image'] = $param['share_image'];
        return $data;
    }

    /**
     * 检验是否正整数,0/正整数
     * @param $num
     * @return bool
     */
    private function checkInt($num){
        if(preg_match('/^[0-9]+$/',$num)){
            return true;
        }
        return false;
    }

    /**
     * 验证整数或两位小数
     * @param $num
     * @return bool
     */
    private function checkFloat($num){
        if (preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $num)) {
            return true;
        }
        return false;
    }

    /**
     * 获取单个活动详情
     * @param $id
     * @return mixed
     */
    public function getAct($id){
        $where = "gfa_id = :gfa_id:";
        $map['gfa_id'] = intval($id);
        $param = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangGroupFightActivity',
            'where' => "WHERE " . $where,
            'bind' => $map
        );
        $result = BaseData::getInstance()->getData($param,true);
        if($result){ //获取商品原名
            $goods = $this->getGoods($result['goods_id']);
            $result['sku_mobile_name'] = $goods['sku_mobile_name'];
        }
        return $result;
    }

    /**
     * 根据商品ID获取商品信息
     * @param $goods_id
     * @return bool
     */
    public function getGoods($goods_id){
        $map['id'] = intval($goods_id);
        $params = array(
            'column' => 'id,sku_mobile_name',
            'table' => '\Shop\Models\BaiyangGoods',
            'where' => 'where id = :id:',
            'bind' => $map
        );
        $result = BaseData::getInstance()->getData($params,true);
        return $result;
    }

    /**
     * 搜索商品
     * @param $param
     * @return array
     */
    public function searchGoods($param){
        if(empty($param)){
            return $this->arrayData('搜索内容为空','',[],'error');
        }
        $map['goods_id'] = $param;
        $map['goods_name'] = '%' .$param . '%';
        $where = 'WHERE g.id = :goods_id: OR g.sku_mobile_name LIKE :goods_name:';
        //获取列表
        $params = array(
            'column' => 'g.id,g.sku_mobile_name goods_name',
            'table' => '\Shop\Models\BaiyangGoods AS g',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangSkuInfo AS k ON g.id = k.sku_id',
            'where' => $where,
            'bind' => $map
        );
        $result = BaseData::getInstance()->getData($params);
        return $this->arrayData('搜索成功','',$result);
    }

    /**
     * 验证活动商品是否重合，是否赠品，是否在活动期间上架
     * @param type $gfa_id  修改时验证
     * @param type $goods_id
     * @param type $gfa_starttime
     * @param type $gfa_endtime
     */
    public function checkGoods($gfa_id = 0, $goods_id, $gfa_starttime, $gfa_endtime) {
        if(empty($goods_id)){
            return $this->arrayData('请选择商品','','','error');
        }
        //判断是否活动重复
        $actmap['gfa_id'] = $gfa_id;
        $actmap['goods_id'] = $goods_id;
        $actmap['gfa_starttime'] = $gfa_starttime;
        $actmap['gfa_endtime'] = $gfa_endtime;
        $actwhere = "WHERE gfa_id != :gfa_id: AND goods_id = :goods_id: AND gfa_state !=3 AND ((gfa_starttime >= :gfa_starttime: AND gfa_starttime < :gfa_endtime:) OR (gfa_endtime > :gfa_starttime: AND gfa_endtime <= :gfa_endtime:) OR (gfa_starttime < :gfa_starttime: AND gfa_endtime > :gfa_endtime:)) ";
        $actparam = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangGroupFightActivity',
            'where' => $actwhere,
            'bind' => $actmap
        );
        $act = BaseData::getInstance()->getData($actparam);
        if(empty($act) == false){
            return $this->arrayData('选择商品与已添加活动的商品时间有重合','','','error');
        }
        $goodsmap['goods_id'] = $goods_id;
        $goodswhere = "WHERE g.id = :goods_id:";
        $goodscolumn = "g.id,g.sku_mobile_name,g.sale_timing_wap,g.sale_timing_wechat,g.product_type,k.whether_is_gift,k.gift_wap,k.gift_wechat";
        //获取商品列表
        $goodsparam = array(
            'column' => $goodscolumn,
            'table' => '\Shop\Models\BaiyangGoods AS g',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangSkuInfo AS k ON g.id = k.sku_id',
            'where' => $goodswhere,
            'bind' => $goodsmap
        );
        $goods = BaseData::getInstance()->getData($goodsparam);
        if(empty($goods)){
            return $this->arrayData('所选商品不存在','','','error');
        }
        //上下架
        if($goods[0]['sale_timing_wap'] == 0 || $goods[0]['sale_timing_wechat'] == 0){
            return $this->arrayData("设置商品“{$goods[0]['sku_mobile_name']}”在WAP或微信已下架，已下架的商品不可以参加拼团活动！请重新设置！",'','','error');
        }
        //是否赠品
        if($goods[0]['whether_is_gift'] == 0 && $goods[0]['product_type'] == 1){
            return $this->arrayData("设置商品“{$goods[0]['sku_mobile_name']}”在WAP或微信已下架，已下架的商品不可以参加拼团活动！请重新设置！",'','','error');
        }
        if($goods[0]['whether_is_gift'] == 1 && ($goods[0]['gift_wap'] == 1 || $goods[0]['gift_wechat'] == 1)){
            return $this->arrayData("设置商品“{$goods[0]['sku_mobile_name']}”在WAP或微商城为赠品，赠品不可以参加拼团活动！请重新设置！",'','','error');
        }
    }

    /**
     * 删除活动
     * @param $id
     * @return array
     */
    public function delAct($id){
        if(empty($id)){
            return $this->arrayData('参数丢失','','','error');
        }
        //获取活动
        $map['gfa_id'] = $id;
        $act = BaiyangGroupFightActivity::findFirst([
                'gfa_id = :gfa_id:',
                'bind' =>$map
            ]
        );
        if(empty($act)){
            return $this->arrayData('活动不存在','','','error');
        }
        if ($act->gfa_starttime < time()) {
            return $this->arrayData('只有未开始的活动可以删除','','','error');
        }
        if($act->delete()){
            return $this->arrayData('删除成功');
        }else{
            return $this->arrayData('删除失败','','','error');
        }
    }

    /**
     * 取消活动
     * @param $id
     * @return array
     */
    public function cancelAct($id){
        if(empty($id)){
            return $this->arrayData('参数丢失','','','error');
        }
        //获取活动
        $map['gfa_id'] = $id;
        $act = BaiyangGroupFightActivity::findFirst([
                'gfa_id = :gfa_id:',
                'bind' =>$map
            ]
        );
        if(empty($act)){
            return $this->arrayData('活动不存在','','','error');
        }
        if ($act->gfa_starttime <= time() && $act->gfa_endtime > time() && $act->gfa_state != 3) { //正在开始的非取消的活动
            $act->gfa_state = 3;
            if ($act->save()) {
                return $this->arrayData('取消成功');
            } else {
                return $this->arrayData('取消失败','','','error');
            }
        }else{
            return $this->arrayData('该活动不能取消','','','error');
        }
    }
}