<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Datas;
class BaiyangGoodSetData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取套餐列表
     * @param $param
     * @return array
     * @author 邓永军
     */
    public function getGoodSetList($param)
    {

        $where='';
        $where.=' AND b.start_time < :start_time: AND b.end_time > :end_time: ';
        $where.=' AND b.'.$param['platform'].'_platform = 1 ';

        $group_id_arr = $this->getData([
           'column' => 'b.id,b.group_name,b.group_introduction,b.start_time,b.end_time' ,
           'table' => '\Shop\Models\BaiyangGroupGoods as a' ,
            'where' => 'where a.goods_id = :goods_id:'.$where,
            'bind' => [
                'goods_id' => $param['goods_id'],
                'start_time' => time(),
                'end_time' =>time()
            ],
            'join'=>'LEFT JOIN \Shop\Models\BaiyangFavourableGroup as b on a.group_id = b.id'
        ]);
        if(is_array($group_id_arr)){
            foreach ($group_id_arr as $key => $info){
                $arr =[];
                $goods_id_arr = $this->getData([
                    'column' => 'goods_id',
                    'table' => '\Shop\Models\BaiyangGroupGoods' ,
                    'where' => 'where group_id = :group_id:',
                    'bind'=>[
                        'group_id' => $info['id']
                    ]
                ]);
                foreach ($goods_id_arr as $gd){
                    $arr[] = $gd['goods_id'];
                }
                $group_id_arr[$key]['g_ids'] = implode(',',$arr);
            }
        }
        return $group_id_arr;
    }

    /**
     * @desc 获取套餐对应商品信息
     * @param $group_id 套餐生成id
     * @param $sku_id   商品id
     * @return array
     * @author 邓永军
     */
    public function getFavourableInfo($group_id,$sku_id)
    {
        $favourable_info=$this->getData([
            'column'=>'favourable_price,goods_number',
            'table' => '\Shop\Models\BaiyangGroupGoods',
            'where' => 'where group_id = :group_id: AND goods_id = :goods_id:',
            'bind' =>[
                'group_id' => $group_id,
                'goods_id' => $sku_id
            ]
        ],1);
        return $favourable_info;
    }
}