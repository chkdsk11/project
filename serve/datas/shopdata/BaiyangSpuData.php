<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/17 0017
 * Time: 下午 4:04
 */

namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangSpu;

class BaiyangSpuData extends BaseData
{
    protected static $instance=null;
	
	public function updateSkuShopOfSpu ($spu_id, $shop_id,$brand_id=0)
	{
//		$column = 'supplier_id=:shop_id:,brand_id=:brand_id';
            $column = 'supplier_id=:shop_id:,brand_id=:brand_id:';
		$table = '\Shop\Models\BaiyangGoods';
		$data['shop_id'] = $shop_id;
                $data['brand_id'] = $brand_id;
                
		$data['id'] = $spu_id;
		$where = ' spu_id = :id:';
		$res = BaseData::getInstance()->update($column, $table, $data, $where);
        //更新缓存
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        $ids = BaseData::getInstance()->getData([
            'column'    =>  'id',
            'table'     =>  $table,
            'where'     =>  'where spu_id = :spu_id:',
            'bind'      =>  ['spu_id'=> $spu_id]
        ]);
        foreach ($ids as $item)
        {
            $UpdateCacheSkuData->updateSkuInfo($item['id']);
        }
		return $res;
    }
    
}