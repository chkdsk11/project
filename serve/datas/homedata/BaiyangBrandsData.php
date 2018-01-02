<?php
/**
 * Created by PhpStorm.
 * User: 林晓聪
 * Date: 2016/10/19 0004
 * Time: 下午 5:02
 */
namespace Shop\Home\Datas;


class BaiyangBrandsData extends BaseData
{
    protected static $instance=null;

    /**
     * 获取符合条件的品牌总数
     * @param $platform 站点标识 app、wap、pc
     * @return int
     */
    public function getBrandsCount($platform)
    {
        $count = $this->countData(
            [
                'table'=>"\\Shop\\Models\\BaiyangBrands as b",
                'join'=>"left join \\Shop\\Models\\BaiyangBrandsExtend as be on be.brand_id=b.id",
                'where'=>"where be.status=:status: and be.type=:type:",
                'bind'=>array('status'=>1,'type'=>$this->platform($platform))
            ]
        );
        return $count;
    }

    /**
     * 获取符合条件的品牌列表信息
     * @param $params['limitStart'] 分页开始条数
     * @param $params['limitSize'] 分页大小
     * @param $platform 站点标识 app、wap、pc
     * @return array
     */
    public function getBrandsData($params,$platform)
    {
        $data = $this->getData(
            [
                'column'=>'b.brand_name,b.brand_desc,b.add_time,be.brand_id,be.brand_logo,be.brand_describe,be.mon_title,be.sort,be.list_image,be.status,be.is_hot,be.type',
                'table'=>"\\Shop\\Models\\BaiyangBrands as b",
                'join'=>"left join \\Shop\\Models\\BaiyangBrandsExtend as be on be.brand_id=b.id",
                'where'=>"where be.status=:status: and be.type=:type:",
                'order'=>'order by be.sort asc',
                'limit'=>"limit {$params['limitStart']},{$params['limitSize']}",
                'bind'=>array('status'=>1,'type'=>$this->platform($platform))
            ]
        );
        return $data;
    }

    /**
     * 根据品牌ID获取品牌商品总数
     * @param $param['brand_id'] 品牌ID
     * @param $platform 站点标识 app、wap、pc
     * @return int
     */
    public function getBrandsGoodsCount($params,$platform)
    {
        if($platform == 'app')
        {
            $where="WHERE b.id=:brand_id: AND sku.status=:status: AND product_type=:product_type: AND sale_timing_app=:sale_timing_app:";
            $bind=['brand_id'=>$params['brand_id'],'status'=>1,'product_type'=>0,'sale_timing_app'=>1];
        }elseif($platform == 'wap') {
            $where="WHERE b.id=:brand_id: AND sku.status=:status: AND product_type=:product_type: AND sale_timing_wap=:sale_timing_wap:";
            $bind=['brand_id'=>$params['brand_id'],'status'=>1,'product_type'=>0,'sale_timing_wap'=>1];
        }else{
            $where="WHERE b.id=:brand_id: AND sku.status=:status: AND product_type=:product_type: AND is_on_sale=:is_on_sale:";
            $bind=['brand_id'=>$params['brand_id'],'status'=>1,'product_type'=>0,'is_on_sale'=>1];
        }
        $count = $this->countData(
            [
                'table'=>"\\Shop\\Models\\BaiyangBrands AS b",
                'join'=>"LEFT JOIN \\Shop\\Models\\BaiyangSpu AS spu ON spu.brand_id=b.id
                      LEFT JOIN \\Shop\\Models\\BaiyangGoods AS sku ON sku.spu_id=spu.spu_id",
                'where'=>$where,
                'bind'=>$bind
            ]
        );
        return $count;
    }

    /**
     * 根据品牌ID获取品牌商品列表信息
     * @param $param['brand_id'] 品牌ID
     * @param $params['limitStart'] 分页开始条数
     * @param $params['limitSize'] 分页大小
     * @param $platform 站点标识 app、wap、pc
     * @return array
     */
    public function getBrandsGoodsData($params=[],$platform)
    {
        if($platform == 'app')
        {
            $where="WHERE b.id=:brand_id: AND sku.status=:status: AND product_type=:product_type: AND sale_timing_app=:sale_timing_app:";
            $bind=['brand_id'=>$params['brand_id'],'status'=>1,'product_type'=>0,'sale_timing_app'=>1];
        }elseif($platform == 'wap') {
            $where="WHERE b.id=:brand_id: AND sku.status=:status: AND product_type=:product_type: AND sale_timing_wap=:sale_timing_wap:";
            $bind=['brand_id'=>$params['brand_id'],'status'=>1,'product_type'=>0,'sale_timing_wap'=>1];
        }else{
            $where="WHERE b.id=:brand_id: AND sku.status=:status: AND product_type=:product_type: AND is_on_sale=:is_on_sale:";
            $bind=['brand_id'=>$params['brand_id'],'status'=>1,'product_type'=>0,'is_on_sale'=>1];
        }
        $data =$this->getData(
            [
                'column'=>'sku.id',
                'table'=>"\\Shop\\Models\\BaiyangBrands AS b",
                'join'=>"LEFT JOIN \\Shop\\Models\\BaiyangSpu AS spu ON spu.brand_id=b.id
                      LEFT JOIN \\Shop\\Models\\BaiyangGoods AS sku ON sku.spu_id=spu.spu_id",
                'where'=>$where,
                'order'=>'',
                'limit'=>"LIMIT {$params['limitStart']},{$params['limitSize']}",
                'bind'=>$bind
            ]
        );

        $array = [];
        //返回一维数组
        if($data)
        {
            $array = array_column($data,'id');
        }
        return $array;
    }

    /**
     * 根据品牌ID获取一条品牌信息
     * @param $brand_id 品牌ID
     * @param $platform 站点标识 app、wap、pc
     * @return array
     */
    public function getBrandsOneInfo($brand_id,$platform)
    {
        $data = $this->getData(
            [
                'column' => 'b.id,b.brand_name,b.brand_desc,b.add_time,be.brand_logo,be.brand_describe,be.mon_title,be.sort,be.list_image,be.status,be.is_hot,be.type',
                'table' => "\\Shop\\Models\\BaiyangBrands as b",
                'join' => "LEFT JOIN \\Shop\\Models\\BaiyangBrandsExtend AS be ON be.brand_id=b.id",
                'where' => "WHERE b.id=:id: AND be.status=:status: AND be.type=:type:",
                'order' => '',
                'limit' => "",
                'bind' => ['id' => $brand_id,'status'=>1,'type'=>$this->platform($platform)]
            ],
            true
        );
        return $data;
    }

    /**
     * @param $platform 站点标识 app、wap、pc（默认pc）
     * @return int
     */
    public function platform($platform)
    {
        switch($platform){
            case 'app':
                $type = 1;
                break;
            case 'wap':
                $type = 1;
                break;
            case 'pc':
                $type = 0;
                break;
            default:
                $type = 0;
        }
        return $type;
    }

    /**获取一条品牌记录
     * @param $brand_id
     * @return array|bool
     */
    public function getBrandRow($brand_id){

        return  $this->getData(
            [
                'column' => 'id,brand_name,brand_desc,add_time',
                'table' => "\\Shop\\Models\\BaiyangBrands",
                'where' => "WHERE id=:id: ",
                'order' => '',
                'limit' => "",
                'bind' => ['id' => $brand_id]
            ],
            true
        );
    }
}