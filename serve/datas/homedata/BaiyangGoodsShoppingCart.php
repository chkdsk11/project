<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/11/16 1504
 */
namespace Shop\Home\Datas;
use Shop\Models\BaiyangPromotionEnum;

class BaiyangGoodsShoppingCart extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 根据用户id获取购物车商品信息
     * @param array $param
     *       -int user_id 用户id   【必填】
     *       -int is_temp 是否为临时用户 (1为临时用户、0为真实用户) 【必填】
     *       -int group_id 套餐组合id 【可填】
     *       -int is_global 是否是海外购商品 【可填】
     *       -int goods_id 商品id 【可填】
     *       -int selected 是否选中(1:是 0:否) 【可填】
     * @return array|bool [] 符合条件的购物车商品信息|false
     * @author 吴俊华
     */
    public function getCartGoodsInfo($param)
    {
        //基础条件
        $bind = [
            'user_id' => $param['user_id'],
            'is_temp' => $param['is_temp'],
        ];
        $where = 'where user_id = :user_id: and is_temp = :is_temp:';
        //判断再拼接条件
        if(isset($param['group_id'])){
            $bind['group_id'] = $param['group_id'];
            $where .= ' and group_id = :group_id:';
        }
        if(isset($param['is_global'])){
            $bind['is_global'] = $param['is_global'];
            $where .= ' and is_global = :is_global:';
        }
        if(isset($param['goods_id'])){
            $bind['goods_id'] = $param['goods_id'];
            $where .= ' and goods_id = :goods_id:';
        }
        if(isset($param['selected'])){
            $bind['selected'] = $param['selected'];
            $where .= ' and selected = :selected:';
        }
        if(isset($param['is_global'])){
            $bind['is_global'] = $param['is_global'];
            $where .= ' and is_global = :is_global:';
        }
        //最终条件
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsShoppingCart',
            'column' => 'goods_id,group_id,goods_number,is_global',
            'bind' => $bind,
            'where' => $where,
        ];
        return $this->getData($condition);
    }

}