<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Home\Services;

use Shop\Datas\BaiyangGoodsData;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class GoodsService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    private $authKey='goods_';  //缓存使用的前辍


    /**
     * 根据分类获取商品信息
     * @param $param=['page'=>int]          当前页数(默认1页)
     * @param $param=['com'=>int]           每页显示条数(默认15)
     * @param $param=['url'=>string]        跳转链接前缀(必须)
     * @param $param=['url_back'=>string]   跳转链接后缀(必须)
     * @param $param=['home_page'=>string]  首页链接(必须)
     * @param $param=['size'=>int]          中间显示页数,默认为5(非必须)
     * @return array()
     */
    public function getAllGoods($param){

        //切换到redis 8库
        $this->cache->selectDb(8);

        //查询条件
        $table = '\Shop\Models\BaiyangGoods';
        $where = '1';

        $ret = $this->cache->getValue($this->authKey . $where);
        if($ret){
            return $ret;
        }else{
            //总记录数
            $counts = 1645;
            if($counts <= 0){
                return array(['res' => 'succcess'],['list' => 0]);
                exit;
            }
            //分页
            $pages['page'] = isset($param['page'])?$param['page']:1;//当前页
            $pages['counts'] = $counts;
            $pages['com'] = isset($param['com'])?$param['com']:15;
            $pages['url'] = $param['url'];
            $pages['url_back'] = $param['url_back'];
            $pages['home_page'] = $param['home_page'];
            $pages['size'] = isset($param['size'])?$param['size']:5;
            $page = $this->page->pageDetail($pages);

            $selections = 'id,product_code,goods_name,prod_code';
            $where .= ' limit '.$page['record'].','.$page['psize'];

            $data['id'] = '';
            $data['product_code'] = '';
            $data['goods_name'] = '';
            $data['prod_code'] = '';


            $result = BaseData::getInstance()->select($selections,$table,$data,$where);
            if(!empty($result)){
                $return = [
                    'res'  => 'succcess',
                    'list' => $result,
                    'page' => $page['page']
                ];
                return $return;
            }else{
                return ['res' => 'error'];
            }
        }
    }

    /**
     * @author 邓永军
     * @desc 通过id或者名称查询商品信息(没有海外购，非赠品)
     */
    public function getGoodsList($input="")
    {
        if($input==""){
            $info=BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["is_global"=>0,"product_type"=>0],"is_global = :is_global: AND product_type = :product_type: limit 20");
            if($info!==false){
                return $info;
            }else{
                return "0";
            }
        }
        $input=explode(",",$input);
        $info=[];
        foreach ($input as $tmp){
            $res=BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["id"=>$tmp,"goods_name"=>"%".$tmp."%","is_global"=>0,"product_type"=>0],"( id = :id: OR goods_name LIKE :goods_name: ) AND is_global = :is_global: AND product_type = :product_type:");
            if(count($res)==1){
                $info[]=[$res[0]];
            }else{
                foreach ($res as $tmp2){
                    $info[]=[$tmp2];
                }
            }
        }
        if(count($info)>0){
            return $info;
        }else{
            return "0";
        }
    }

    /**
     * @author 邓永军
     * @desc 通过商品ids获取商品详细信息(没有海外购，非赠品)
     */
    public function getGoodListByIds($input)
    {
        $input=explode(",",$input);
        $info=array_map(function($v){
            return BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["id"=>$v,"is_global"=>0,"product_type"=>0],"id = :id: AND is_global = :is_global: AND product_type = :product_type:")[0];
        },$input);
        if(count($info)>0){
            return $info;
        }else{
            return "0";
        }
    }

    /**
     * @desc 查询赠品详细(没有海外购)
     * @param string $input
     * @return string
     */
    public function getGoodsForGift($input="")
    {

        if($input==""){
            $info=BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["is_global"=>0,"product_type"=>1],"is_global = :is_global: AND product_type = :product_type: limit 20");
            if($info!==false){
                return $info;
            }else{
                return "0";
            }
        }
        $input=explode(",",$input);
        $info=[];
        foreach ($input as $tmp){
            $res=BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["id"=>$tmp,"goods_name"=>"%".$tmp."%","is_global"=>0,"product_type"=>1],"( id = :id: OR goods_name LIKE :goods_name: ) AND is_global = :is_global: AND product_type = :product_type:");
            if(count($res)==1){
                $info[]=[$res[0]];
            }else{
                foreach ($res as $tmp2){
                    $info[]=[$tmp2];
                }
            }
        }
        if(count($info)>0){
            return $info;
        }else{
            return "0";
        }

    }

    /**
     * @desc 优惠券单品列表
     * @param $input
     * @return string
     */
    public function getGoodsForCoupon($input="")
    {
        if($input==""){
            $info=BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["product_type"=>0],"product_type = :product_type: limit 20");
            if($info!==false){
                return $info;
            }else{
                return "0";
            }
        }
        $input=explode(",",$input);
        $info=[];
        foreach ($input as $tmp){
            $res=BaiyangGoodsData::getInstance()->select("id,goods_name,price","\\Shop\\Models\\BaiyangGoods",["id"=>$tmp,"goods_name"=>"%".$tmp."%","is_global"=>0,"product_type"=>0],"( id = :id: OR goods_name LIKE :goods_name: ) AND is_global = :is_global: AND product_type = :product_type:");
            if(count($res)==1){
                $info[]=[$res[0]];
            }else{
                foreach ($res as $tmp2){
                    $info[]=[$tmp2];
                }
            }
        }
        if(count($info)>0){
            return $info;
        }else{
            return "0";
        }
    }
}
