<?php
/**
 * @author 邓永军
 */
namespace Shop\Services;
use Shop\Models\CacheKey;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangFavourableGroup;
use Shop\Models\BaiyangGroupGoods;
class GoodsetService extends BaseService
{
    protected static $instance=null;

    protected function getPlatform($param)
    {
        $platform=[];
        array_map(function($v) use(&$platform){
            switch($v){
                case "1":
                    $platform["pc"] = "1";
                    break;
                case "2":
                    $platform["app"] = "1";
                    break;
                case "3":
                    $platform["wap"] = "1";
                    break;
                case "4":
                    $platform['wechat'] = "1";
            }
        },$param["use_platform"]);
        return $platform;
    }

    public function getList($param)
    {
        $whereStr="";
        $binder=[];
        if(isset($param['param']["group_name"]) && !empty($param['param']["group_name"])){
            if($whereStr == ""){
                $whereStr.="group_name LIKE :group_name:";
            }else{
                $whereStr.="AND group_name LIKE :group_name:";
            }
            $binder["group_name"] = $param['param']["group_name"]."%";
            
        }
        if(isset($param['param']["group_status"]) && !empty($param['param']["group_status"])){
            switch ($param['param']["group_status"]){
                case "0":
                    break;
                case "1":
                    if($whereStr == ""){
                        $whereStr.="start_time > :now_time:";
                    }else{
                        $whereStr.="AND start_time > :now_time:";
                    }
                    $binder["now_time"]=time();
                    break;
                case "2":
                    if($whereStr == ""){
                        $whereStr.="start_time < :now_time: AND end_time > :now_time:";
                    }else{
                        $whereStr.="AND start_time < :now_time: AND end_time > :now_time:";
                    }
                    $binder["now_time"]=time();
                    break;
                case "3":
                    if($whereStr == ""){
                        $whereStr.="end_time < :now_time:";
                    }else{
                        $whereStr.="AND end_time < :now_time:";
                    }
                    $binder["now_time"]=time();
                    break;
            }
           
        }
        if(isset($param['param']['platform_status']) && !empty($param['param']['platform_status'])){
            switch ($param['param']["platform_status"]){
                case '0':
                    break;
                case '1':
                    if($whereStr == ""){
                        $whereStr.="pc_platform = :pc_platform:";
                    }else{
                        $whereStr.="AND pc_platform = :pc_platform:";
                    }
                    $binder["pc_platform"]=1;
                    break;
                case '2':
                    if($whereStr == ""){
                        $whereStr.="app_platform = :app_platform:";
                    }else{
                        $whereStr.="AND app_platform = :app_platform:";
                    }
                    $binder["app_platform"]=1;
                    break;
                case '3':
                    if($whereStr == ""){
                        $whereStr.="wap_platform = :wap_platform:";
                    }else{
                        $whereStr.="AND wap_platform = :wap_platform:";
                    }
                    $binder["wap_platform"]=1;
                    break;
                case '4':
                    if($whereStr == ""){
                        $whereStr.="wechat_platform = :wechat_platform:";
                    }else{
                        $whereStr.="AND wechat_platform = :wechat_platform:";
                    }
                    $binder["wechat_platform"]=1;
                    break;
            }
        }
        if(isset($param['param']["start_time"]) && !empty($param['param']["start_time"]) && isset($param['param']["end_time"]) && !empty($param['param']["end_time"]) ){
            $start_time=strtotime($param['param']["start_time"]);
            $end_time=strtotime($param['param']["end_time"]);
            if($whereStr == ""){
                $whereStr.="start_time > :start_time: AND end_time < :end_time:";
            }else{
                $whereStr.="AND start_time > :start_time: AND end_time < :end_time:";
            }
            $binder["start_time"]=$start_time;
            $binder["end_time"]=$end_time;
        }

        $count=BaseData::getInstance()->count('\Shop\Models\BaiyangFavourableGroup',$binder,$whereStr);
        if(empty($count)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $count;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        if($whereStr==""){
            $whereStr .= '1 ORDER BY add_time DESC LIMIT '.$page['record'].','.$page['psize'];
        }else{
            $whereStr .= ' ORDER BY add_time DESC LIMIT '.$page['record'].','.$page['psize'];
        }
        $list=BaseData::getInstance()->select(
            'id,group_name,group_introduction,start_time,end_time,pc_platform,wap_platform,app_platform,wechat_platform',
            '\Shop\Models\BaiyangFavourableGroup',
            $binder,
            $whereStr
        );
        foreach ($list as &$tmp){
            $tmp_arr=[];
            if($tmp['pc_platform'] == 1){
                $tmp_arr[]='PC';
            }
            if($tmp['wap_platform'] == 1){
                $tmp_arr[]='WAP';
            }
            if($tmp['app_platform'] == 1){
                $tmp_arr[]='APP';
            }
            if($tmp['wechat_platform'] == 1){
                $tmp_arr[]='微商城';
            }
            $tmp['platform_value']=implode('、',$tmp_arr);
        }
        if(empty($list)){
            return ['res' => 'error'];
        }
        return ['res'  => 'succcess', 'list' => $list, 'page' => $page['page'], 'voltValue' => $param['param']];
    }

    public function edit($mid)
    {
        $info=BaseData::getInstance()->select(
            'id,group_name,group_introduction,mutex,start_time,end_time,pc_platform,wap_platform,app_platform,wechat_platform',
            '\Shop\Models\BaiyangFavourableGroup',
            ["id"=>$mid],
            "id = :id:"
        )[0];
        $group_list=BaseData::getInstance()->select(
            'a.id,a.group_id,a.goods_id,a.favourable_price,a.goods_number,b.goods_name,b.goods_price as price,b.supplier_id,b.is_unified_price,c.goods_price_pc,goods_price_app,goods_price_wap,goods_price_wechat',
            '\Shop\Models\BaiyangGroupGoods as a',
            ['group_id'=>$mid],
            'group_id = :group_id:',
            'LEFT JOIN \Shop\Models\BaiyangGoods as b on a.goods_id = b.id LEFT JOIN \Shop\Models\BaiyangSkuInfo as c on c.sku_id=b.id'
        );
        $info["group_list"]=$group_list;
        if($info["start_time"]>time())$info["status"]=0;//未开始
        if($info["start_time"]<time() && $info["end_time"]>time())$info["status"]=1;//进行中
        if($info["end_time"]<time())$info["status"]=3;//已结束
        return $info;
    }

    public function do_edit($param)
    {
        $redis = $this->cache;
        $redis->selectDb(5);
        if(isset($param['good_set_mutex']) && !empty($param['good_set_mutex'])){
            $good_set_mutex = implode(',',$param['good_set_mutex']);
        }else{
            $good_set_mutex = '';
        }
        $platform=self::getPlatform($param);
        /*if($param['start_time'] < time()){
            return $this->arrayData('开始时间不能早于当前时间','','','error');
        }*/
        if(isset($platform["pc"])&&!empty($platform["pc"])){
            $data["pc_platform"]="1";
        }else{
            $data["pc_platform"]="0";
        }
        if(isset($platform["app"])&&!empty($platform["app"])){
            $data["app_platform"]="1";
        }else{
            $data["app_platform"]="0";
        }
        if(isset($platform["wap"])&&!empty($platform["wap"])){
            $data["wap_platform"]="1";
        }else{
            $data["wap_platform"]="0";
        }
        if(isset($platform["wechat"])&&!empty($platform["wechat"])){
            $data["wechat_platform"]="1";
        }else{
            $data["wechat_platform"]="0";
        }
        $data["wechat_platform"] = "0"; //暂时不支持微商城
        $id=$param["mid"];
        $param["single_list"]=json_decode($param["single_list"],JSON_UNESCAPED_UNICODE);
        $update_data=[
            "id"=>$id,
            "group_name"=>$param["group_name"],
            "group_introduction"=>$param["group_introduction"],
            "mutex" => $good_set_mutex,
            "start_time"=>strtotime($param["start_time"]),
            "end_time"=>strtotime($param["end_time"]),
            "pc_platform"=>$data["pc_platform"],
            "app_platform"=>$data["app_platform"],
            "wap_platform"=>$data["wap_platform"],
            "wechat_platform"=>$data["wechat_platform"]
        ];
        $this->dbWrite->begin();
        $is_update=BaseData::getInstance()->update(
            'group_name = :group_name: ,group_introduction = :group_introduction: ,start_time = :start_time: ,end_time = :end_time:,pc_platform = :pc_platform: ,app_platform= :app_platform: ,wap_platform = :wap_platform:,wechat_platform = :wechat_platform:,mutex = :mutex:',
            '\Shop\Models\BaiyangFavourableGroup',
            $update_data,
            'id = :id:'
        );
        if($is_update!==false){
            BaseData::getInstance()->delete(
                '\Shop\Models\BaiyangGroupGoods',
                ['group_id'=>$id],
                'group_id = :group_id:'
            );
            $_eFlag=1;
            foreach ($param["single_list"] as $item) {
                $cachekey = CacheKey::GOODS_SET.'g'.$item["goods_id"];
                $redis->delete($cachekey);
                $data=[
                    "group_id"=>$id,
                    "goods_id"=>$item["goods_id"],
                    "favourable_price"=>$item["favourable_price"],
                    "goods_number"=>$item["goods_number"]
                ];
                $save_id=BaseData::getInstance()->insert(
                    "\\Shop\\Models\\BaiyangGroupGoods",
                    $data,
                    true
                );
                if($save_id<1){
                    $_eFlag=0;
                }
            }
            if($_eFlag==1){
                $this->dbWrite->commit();
                return $this->arrayData('修改成功！', '/goodset/list');
            }else{
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','','','error');
            }
        }else{
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','','error');
        }
    }
    
    public function do_del($mid, $request = '')
    {
        if(empty($mid)){
            return $this->arrayData('操作有误！', '', '', 'error');
        }
        $is_delete=BaseData::getInstance()->delete(
            '\Shop\Models\BaiyangFavourableGroup',
            ["id"=>$mid],
            "id = :id:"
        );
        $url = $request ? '/goodset/list'.$request : '/goodset/list';
        if($is_delete){
            return $this->arrayData('删除成功！', $url);
        }else{
            return $this->arrayData('删除失败','','','error');
        }
    }
    /**
     * @desc 添加操作
     * @param $param
     * @return array
     */
    public function do_add($param)
    {
        $redis = $this->cache;
        $redis->selectDb(5);
        if(isset($param['good_set_mutex']) && !empty($param['good_set_mutex'])){
            $good_set_mutex = implode(',',$param['good_set_mutex']);
        }else{
            $good_set_mutex = '';
        }
        $param["single_list"]=json_decode($param["single_list"],JSON_UNESCAPED_UNICODE);
        $platform=self::getPlatform($param);
        if(isset($platform["pc"])&&!empty($platform["pc"])){
            $data["pc_platform"]="1";
        }else{
            $data["pc_platform"]="0";
        }
        if(isset($platform["app"])&&!empty($platform["app"])){
            $data["app_platform"]="1";
        }else{
            $data["app_platform"]="0";
        }
        if(isset($platform["wap"])&&!empty($platform["wap"])){
            $data["wap_platform"]="1";
        }else{
            $data["wap_platform"]="0";
        }
        if(isset($platform["wechat"])&&!empty($platform["wechat"])){
            $data["wechat_platform"]="1";
        }else{
            $data["wechat_platform"]="0";
        }
        $data["wechat_platform"] = "0"; //暂时不支持微商城
        /*if($param['start_time'] < time()){
            return $this->arrayData('开始时间不能早于当前时间','','','error');
        }*/
        $param['start_time'] = strtotime($param["start_time"]);
        $param['end_time'] = strtotime($param["end_time"]);
        if ($param['start_time']>= $param['end_time']) {
            return $this->arrayData('结束时间不能早于开始时间','','','error');
        }
        if (time()>= $param['end_time']) {
            return $this->arrayData('结束时间不能早于当前时间','','','error');
        }
        // 套餐名称不能相同
        if (BaseData::getInstance()->getData([
            'column' => 'id',
            'table'  => "\\Shop\\Models\\BaiyangFavourableGroup",
            'where' => "where group_name = :group_name:",
            'bind'   => ['group_name' => $param["group_name"]],
        ], true)) {
            return $this->arrayData('商品套餐名称不可重复！','','','error');
        }
        $this->dbWrite->begin();
        $insert_data=[
            "group_name"=>$param["group_name"],
            "group_introduction"=>$param["group_introduction"],
            'mutex'=>$good_set_mutex,
            "start_time"=>$param['start_time'],
            "end_time"=>$param['end_time'],
            "add_time"=>time(),
            "pc_platform"=>$data["pc_platform"],
            "app_platform"=>$data["app_platform"],
            "wap_platform"=>$data["wap_platform"],
            "wechat_platform"=>$data["wechat_platform"]
        ];
        $group_id=BaseData::getInstance()->insert(
            "\\Shop\\Models\\BaiyangFavourableGroup",
            $insert_data,
            true
        );

        if($group_id > 0){
            $_eFlag=1;
            foreach ($param["single_list"] as $item) {
                $cachekey = CacheKey::GOODS_SET.'g'.$item["goods_id"];
                $redis->delete($cachekey);
                $data=[
                  "group_id"=>$group_id,
                   "goods_id"=>$item["goods_id"],
                    "favourable_price"=>$item["favourable_price"],
                    "goods_number"=>$item["goods_number"]
                ];
                $save_id=BaseData::getInstance()->insert(
                    "\\Shop\\Models\\BaiyangGroupGoods",
                    $data,
                    true
                );
                if($save_id<1){
                    $_eFlag=0;
                }
           }
            if($_eFlag==1){
                $this->dbWrite->commit();
                return $this->arrayData('添加成功！', '/goodset/list');
            }else{
                $this->dbWrite->rollback();
                return $this->arrayData('添加失败','','','error');
            }
        }else{
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败','','','error');
        }
    }
}