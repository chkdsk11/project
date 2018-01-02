<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：SkuAd_id_5     一个skuad信息
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Models\CacheGoodsKey;

class StockService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 获取店铺列表
     */
    public function getList($param)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangGoods';
        $join = ' a ';
	    #$join = ' a left join \Shop\Models\BaiyangSkuInfo b on a.id=b.sku_id';
        $where = 'where is_global=0';
        $data   =   array();
        //组织where语句
        if(isset($param['sku_id']) && (int)$param['sku_id']){
            $where .= " AND a.id=:id:";
            $data['id']   =   (int)$param['sku_id'];
        }
        if(isset($param['spu_id']) && (int)$param['spu_id']){
            $where .= " AND spu_id=:spu_id:";
            $data['spu_id']   =   (int)$param['spu_id'];
        }
        if(isset($param['name']) && $param['name'] != ''){
            $where .= " AND goods_name LIKE :name:";
            $data['name']   =   '%'.$param['name'].'%';
        }
        $BaiyangSpuData = BaseData::getInstance();
        //总记录数
        $counts = $BaiyangSpuData->countData([ 'table'=>$table,'bind'=>$data,'join'=>$join, 'where'=>$where ]);
        if(empty($counts)){
            return array('res' => 'success','list' => 0,'page'=>'');
        }
        $table .= ' a left join \Shop\Models\BaiyangSkuInfo b on a.id=b.sku_id';
        //分页
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['psize'] = (int)isset($param['psize'])?$param['psize']:12;
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $selections = 'a.id,a.goods_name,a.spu_id, a.product_code,a.v_stock,a.is_use_stock,b.virtual_stock_default,b.virtual_stock_pc,b.virtual_stock_app,b.virtual_stock_wap,b.virtual_stock_wechat';
        $where = substr($where, 5);
        $where .= ' order by update_time desc limit '.$page['record'].','.$page['psize'];
        $result = $BaiyangSpuData->select($selections,$table,$data,$where);
        $return = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    public function setStock($param)
    {
        if(!is_array($param['id'])) return $this->arrayData('参数错误','','','error');
        $BaiyangSpuData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangGoods';
        $tableInfo = '\Shop\Models\BaiyangSkuInfo';
        $key = CacheGoodsKey::SKU_INFO;
        //事务处理
        $this->dbWrite->begin();
        foreach($param['id'] as $k=>$v){
            $where = "id=:id:";
            $data['id'] = (int)$v;
            $data['is_use_stock'] = $param['type'][$k];
            $columStr = "is_use_stock=:is_use_stock:";
            $res = $BaiyangSpuData->update($columStr,$table,$data,$where);
            if(!$res){
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','',$res,'error');
            }
            $datas = array();
            if($param['type'][$k] == 1){
                continue;
            }else if($param['type'][$k] == 2){
                $where = "sku_id=:id:";
                $datas['id'] = (int)$v;
                $datas['virtual_stock_default'] = (int)$param['public'][$k];
                $columStr = "virtual_stock_default=:virtual_stock_default:";
            }else if($param['type'][$k] == 3){
                $where = "sku_id=:id:";
                $datas['id'] = (int)$v;
                $datas['virtual_stock_pc'] = (int)$param['pc'][$k];
                $datas['virtual_stock_app'] = (int)$param['app'][$k];
                $datas['virtual_stock_wap'] = (int)$param['wap'][$k];
                $datas['virtual_stock_wechat'] = (int)$param['wei'][$k];
                $columStr = "virtual_stock_pc=:virtual_stock_pc:,virtual_stock_app=:virtual_stock_app:,virtual_stock_wap=:virtual_stock_wap:,virtual_stock_wechat=:virtual_stock_wechat:";
            }else{
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','',$res,'error');
            }
            $res = $BaiyangSpuData->update($columStr,$tableInfo,$datas,$where);
            if(!$res){
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','',$res,'error');
            }
            $this->RedisCache->delete($key.(int)$v);
        }
        //事务提交
        $this->dbWrite->commit();
        return $this->arrayData('修改成功','',$res,'success');
    }
}
