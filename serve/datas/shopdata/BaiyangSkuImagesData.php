<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangGoodsImages;

class BaiyangSkuImagesData extends BaseData
{
    protected static $instance=null;

    public function selectImg($conditions,$whereStr)
    {
        $table = '\Shop\Models\BaiyangGoodsImages';
        $selections = 'id,goods_id,goods_image sku_image,goods_middle_image sku_middle_image,goods_big_image sku_big_image,is_default,sort,spu_id';
        $phql ="select {$selections} from {$table} where {$whereStr}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            foreach($result as $k=>$v){
                $result[$k]['sku_image'] = $v['sku_image'];
                $result[$k]['sku_middle_image'] = $v['sku_middle_image'];
                $result[$k]['sku_big_image'] = $v['sku_big_image'];
            }
            return $result;
        }
        return false;
    }
}