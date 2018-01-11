<?php
/**
 * @author 邓永军
 */
namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
class GoodsetsService extends BaseService
{
    protected static $instance=null;

    protected function getPlatform($param)
    {
        $platform=[];
        array_map(function($v) use(&$platform){
            switch($v){
                case "1":
                    $platform["pc"]="1";
                    break;
                case "2":
                    $platform["app"]="1";
                    break;
                case "3":
                    $platform["wap"]="1";
                    break;
                case "4":
                    $platform["wechat"]="1";
                    break;
            }
        },$param["use_platform"]);
        return $platform;
    }

    public function doEditALL($param)
    {
        $id=json_decode($param['info'],true)['mid'];
        $info=json_decode($param['info'],true)['info'];
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
        $group_name=$param['group_name'];
        $group_name_list = BaseData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangGoodsSets',
            'column' => 'id',
            'where' => 'where name = :name: ',
            'bind' => ['name' => $group_name]
        ));
        //$count = BaseData::getInstance()->count('\Shop\Models\BaiyangGoodsSets',['name' => $group_name],'name = :name: ');
        $count = count($group_name_list);
        if(empty($group_name_list) || ($count === 1 && $group_name_list[0]['id'] == $id)){
            $ret=BaseData::getInstance()->update(
                'name = :name:,pc_platform = :pc_platform: , wap_platform = :wap_platform:, app_platform = :app_platform:, wechat_platform = :wechat_platform:',
                '\Shop\Models\BaiyangGoodsSets',
                [
                    'name'=>$group_name,
                    "id"=>$id,
                    "pc_platform"=>$data["pc_platform"],
                    "app_platform"=>$data["app_platform"],
                    "wap_platform"=>$data["wap_platform"],
                    "wechat_platform"=>$data["wechat_platform"]
                ],
                'id = :id: '
            );
            if($ret !== false){
                foreach ($info as $v)
                {
                    $res=BaseData::getInstance()->select(
                        'id',
                        '\Shop\Models\BaiyangGoodsToSets',
                        [
                            "set_id"=>$id,
                            "goods_id"=>$v["sku_id"]
                        ],
                        "set_id = :set_id: AND goods_id= :goods_id: "
                    );
                    if($res){
                        //修改
                        BaseData::getInstance()->update(
                            'name = :name:',
                            '\Shop\Models\BaiyangGoodsToSets',
                            [
                                'name'=>$v["group_name"],
                                "set_id"=>$id,
                                "goods_id"=>$v["sku_id"]
                            ],
                            'set_id = :set_id: AND goods_id= :goods_id: '
                        );
                    }else{
                        //添加
                        $add_id=BaseData::getInstance()->insert(
                            '\Shop\Models\BaiyangGoodsToSets',
                            [
                                "set_id"=>$id,
                                "goods_id"=>$v["sku_id"],
                                "name"=>$v["group_name"],
                                "sort"=>0
                            ],
                            true
                        );
                        if($add_id>0){
                            BaseData::getInstance()->update(
                                'sort = :sort:',
                                '\Shop\Models\BaiyangGoodsToSets',
                                [
                                    'sort'=>$add_id,
                                    'id'=>$add_id
                                ],
                                'id = :id:'
                            );
                        }

                    }
                }
                return $this->arrayData('修改成功！', '', '', 'error');
            }else{
                return $this->arrayData('操作有误！', '', '', 'error');
            }
        }else{
            return $this->arrayData('关联组名字不允许重复！', '', '', 'error');
        }

    }

    public function getList($param){
        $bind=[];
        $where="";
        if(isset($param["param"]["goods_info"]) && !empty($param["param"]["goods_info"])){
            $bind["id"] = $param["param"]["goods_info"];
            $bind["name"] = '%'.$param["param"]["goods_info"].'%';
            $where.="(a.id = :id: OR a.name LIKE :name: ) ";
        }
        if(isset($param['param']['platform_status']) && !empty($param['param']['platform_status'])){
            switch ($param['param']["platform_status"]){
                case '0':
                    break;
                case 'pc':
                    if($where == ""){
                        $where.="a.pc_platform = :pc_platform:";
                    }else{
                        $where.="AND a.pc_platform = :pc_platform:";
                    }
                    $bind["pc_platform"]=1;
                    break;
                case 'app':
                    if($where == ""){
                        $where.="a.app_platform = :app_platform:";
                    }else{
                        $where.="AND a.app_platform = :app_platform:";
                    }
                    $bind["app_platform"]=1;
                    break;
                case 'wap':
                    if($where == ""){
                        $where.="a.wap_platform = :wap_platform:";
                    }else{
                        $where.="AND a.wap_platform = :wap_platform:";
                    }
                    $bind["wap_platform"]=1;
                    break;
                case 'wechat':
                    if($where == ""){
                        $where.="a.wechat_platform = :wechat_platform:";
                    }else{
                        $where.="AND a.wechat_platform = :wechat_platform:";
                    }
                    $bind["wechat_platform"]=1;
                    break;
            }
        }
        $count=count(BaseData::getInstance()->select(
            1,
          '\Shop\Models\BaiyangGoodsSets as a',
            $bind,
            $where
        ));
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
        if($where==""){
            $where .= '1 GROUP BY a.id ORDER BY a.sort DESC  LIMIT '.$page['record'].','.$page['psize'];
        }else{
            $where .= ' GROUP BY a.id ORDER BY a.sort DESC  LIMIT '.$page['record'].','.$page['psize'];
        }

        $list=BaseData::getInstance()->select(
          'a.id,a.name,a.pc_platform,a.wap_platform,a.app_platform,a.wechat_platform,COUNT(b.id) as count',
          '\Shop\Models\BaiyangGoodsSets as a',
          $bind,
          $where,
            'LEFT JOIN \Shop\Models\BaiyangGoodsToSets as b ON a.id = b.set_id'
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

    public function addGoodsSetsNGoods($param)
    {
        $info=json_decode($param['info'],true)['info'];
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
        $data['name']=$param['group_name'];
        $count = BaseData::getInstance()->count('\Shop\Models\BaiyangGoodsSets',['name' => $data['name']],'name = :name: ');
        if(!$count){
            $insert_id=BaseData::getInstance()->insert(
                '\Shop\Models\BaiyangGoodsSets',
                $data,
                true
            );
            if($insert_id > 0 ){
                $update_data=[
                    "id"=>$insert_id,
                    "sort"=>$insert_id
                ];
                $ret=BaseData::getInstance()->update(
                    'sort = :sort: ',
                    '\Shop\Models\BaiyangGoodsSets',
                    $update_data,
                    'id = :id:'
                );
                if($ret==false){
                    return  $this->arrayData('添加失败', '/goodsets/add', '','error');
                }else{
                    foreach ($info as $v){

                        $add_id=BaseData::getInstance()->insert(
                            '\Shop\Models\BaiyangGoodsToSets',
                            [
                                "set_id"=>$insert_id,
                                "goods_id"=>$v["sku_id"],
                                "name"=>$v["group_name"],
                                "sort"=>0
                            ],
                            true
                        );
                        if($add_id>0){
                            BaseData::getInstance()->update(
                                'sort = :sort:',
                                '\Shop\Models\BaiyangGoodsToSets',
                                [
                                    'sort'=>$add_id,
                                    'id'=>$add_id
                                ],
                                'id = :id:'
                            );
                        }
                    }
                    return  $this->arrayData('添加成功！', '/goodsets/add', '');
                }
            }else{
                return  $this->arrayData('添加失败', '/goodsets/add', '','error');
            }
        }else{
            return  $this->arrayData('关联组名字不允许重复！', '/goodsets/add', '','error');
        }

    }
    public function addGoodsSets($param)
    {
        $name_arr=explode(",",$param);
        $is_insert=1;
        $ids_arr=[];
        foreach ($name_arr as $tmp)
        {
            $data=[
                "name"=>$tmp
            ];
            $insert_id=BaseData::getInstance()->insert(
                '\Shop\Models\BaiyangGoodsSets',
                $data,
                true
            );
            if($insert_id>0){
                $ids_arr[]=$insert_id;
            }else{
                $is_insert=0;
            }
        }
        if($is_insert==1){
            $is_ok=1;
            foreach ($ids_arr as $tmp){
                $update_data=[
                    "id"=>$tmp,
                    "sort"=>$tmp
                ];
                $ret=BaseData::getInstance()->update(
                    'sort = :sort: ',
                    '\Shop\Models\BaiyangGoodsSets',
                    $update_data,
                    'id = :id:'
                );
                if($ret==false){
                    $is_ok=0;
                }
            }
            if($is_ok == 1){
                return ["code"=>200,"msg"=>"添加成功"];
            }else{

                return ["code"=>400,"msg"=>"添加失败"];
            }
        }else{

            return ["code"=>400,"msg"=>"添加失败"];
        }
    }
    
    public function modifySort($param)
    {
        foreach ($param as $tmp){
            $update_data=[
                "id"=>$tmp["id"],
                "sort"=>$tmp["sort"]
            ];
            BaseData::getInstance()->update(
                'sort = :sort: ',
                '\Shop\Models\BaiyangGoodsSets',
                $update_data,
                'id = :id:'
            );
        }
        return ["code"=>200,"msg"=>"修改成功"];
    }

    public function modifyEditSort($param)
    {
        foreach ($param as $tmp){
            $update_data=[
                "id"=>$tmp["id"],
                "sort"=>$tmp["sort"]
            ];
            BaseData::getInstance()->update(
                'sort = :sort: ',
                '\Shop\Models\BaiyangGoodsToSets',
                $update_data,
                'id = :id:'
            );
        }
        return ["code"=>200,"msg"=>"修改成功"];
    }

    public function getSkuInfoById($sku_id)
    {

        $info = BaseData::getInstance()->select(
            'id,goods_name',
            "\\Shop\\Models\\BaiyangGoods",
            [
                "is_on_sale"=>1,
                "id"=>$sku_id,
                "is_global"=>0,
                "product_type" => 0
            ],
            "is_on_sale = :is_on_sale: AND id = :id: AND is_global = :is_global: AND product_type = :product_type:"
        )[0];
        return $info;

    }

    public function getEditInfo($id){
        $info=BaseData::getInstance()->select(
            'id,name,pc_platform,wap_platform,app_platform,wechat_platform',
            '\Shop\Models\BaiyangGoodsSets',
            [
                'id'=>$id
            ],
            "id = :id:"
        )[0];
        $list=BaseData::getInstance()->select(
            'a.id,a.set_id,a.goods_id,a.name,b.goods_name,a.sort',
            '\Shop\Models\BaiyangGoodsToSets as a',
            [
                "set_id"=>$id
            ],
            "set_id = :set_id: ORDER BY a.sort DESC",
            "LEFT JOIN \\Shop\\Models\\BaiyangGoods as b on a.goods_id = b.id"
        );
        if(empty($list) && empty($info)){
            return ['res' => 'error'];
        }
        if(empty($list)){
            return ['res'  => 'succcess','info'=>$info];
        }
        return ['res'  => 'succcess', 'list' => $list,'info'=>$info];
    }

    public function saveGoodSets($param)
    {
        $id=$param["data"]["mid"];
        $info=$param["data"]["info"];
        foreach ($info as $v)
        {
            $res=BaseData::getInstance()->select(
                'id',
                '\Shop\Models\BaiyangGoodsToSets',
                [
                    "set_id"=>$id,
                    "goods_id"=>$v["sku_id"]
                ],
                "set_id = :set_id: AND goods_id= :goods_id: "
            );
            if($res){
                //修改
                BaseData::getInstance()->update(
                    'name = :name:',
                    '\Shop\Models\BaiyangGoodsToSets',
                    [
                        'name'=>$v["group_name"],
                        "set_id"=>$id,
                        "goods_id"=>$v["sku_id"]
                    ],
                    'set_id = :set_id: AND goods_id= :goods_id: '
                );
            }else{
                //添加
                $add_id=BaseData::getInstance()->insert(
                  '\Shop\Models\BaiyangGoodsToSets',
                    [
                        "set_id"=>$id,
                        "goods_id"=>$v["sku_id"],
                        "name"=>$v["group_name"],
                        "sort"=>0
                    ],
                    true
                );
                if($add_id>0){
                    BaseData::getInstance()->update(
                        'sort = :sort:',
                        '\Shop\Models\BaiyangGoodsToSets',
                        [
                            'sort'=>$add_id,
                            'id'=>$add_id
                        ],
                        'id = :id:'
                    );
                }

            }
        }
        return ["code"=>200,"msg"=>"处理完毕"];
    }

    public function delEdit($edit_id)
    {
        if(empty($edit_id)){
            return $this->arrayData('操作有误！', '', '', 'error');
        }
        $result=BaseData::getInstance()->delete(
            '\Shop\Models\BaiyangGoodsToSets',
            [
                "id"=>$edit_id
            ],
            "id = :id:"
        );
        return $result ? $this->arrayData('删除成功！', '') : $this->arrayData('删除失败！', '', '', 'error');
    }

    public function delList($id, $request = '')
    {
        if(empty($id)){
            return $this->arrayData('操作有误！', '', '', 'error');
        }
        $result=BaseData::getInstance()->delete(
            '\Shop\Models\BaiyangGoodsSets',
            [
                "id"=>$id
            ],
            "id = :id:"
        );
        BaseData::getInstance()->delete(
            '\Shop\Models\BaiyangGoodsToSets',
            [
                "id"=>$id
            ],
            "set_id = :id:"
        );
        $url = $request ? '/goodsets/list'.$request : '/goodsets/list';
        return $result ? $this->arrayData('删除成功！', $url) : $this->arrayData('删除失败！', '', '', 'error');
    }
}