<?php
namespace Shop\Home\Datas;
use Shop\Home\Datas\BaseData;
/**
 * Class BaiyangShoppingCartData
 * @package Shop\Home\Datas
 * @desc 购物车Data类
 * @author 柯琼远
 */
class BaiyangShoppingCartData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 查询购物车
     * @param array $param
     *       -ids string  多个购物车ID，逗号隔开
     *       -goods_id  一个或者多个商品ID，逗号隔开
     *       -selected  是否选中
     *       -user_id int 用户ID
     *       -group_id int 套餐ID
     *       -is_temp int 是否临时用户
     *       -is_global int 是否海外购
     *       -increase_buy int 加价购活动ID
     * @param bool $returnOne  是否返回一条数据，默认：false
     * @return array
     * @author 柯琼远
     */
    public function getShoppingCart($param, $returnOne = false) {
        $where = array();
        if (isset($param['ids'])) {
            $where[] = "id in (" . $param['ids'] . ")";
        }
        if (isset($param['goods_id'])) {
            $where[] = "goods_id = " . (int)$param['goods_id'];
        }
        if (isset($param['selected'])) {
            $where[] = "selected = " . (int)$param['selected'];
        }
        if (isset($param['user_id'])) {
            $where[] = "user_id = " . (int)$param['user_id'];
        }
        if (isset($param['group_id'])) {
            $where[] = "group_id = " . (int)$param['group_id'];
        }
        if (isset($param['is_temp'])) {
            $where[] = "is_temp = " . (int)$param['is_temp'];
        }
        if (isset($param['is_global'])) {
            $where[] = "is_global = " . (int)$param['is_global'];
        }
        if (isset($param['increase_buy'])) {
            $where[] = "increase_buy = " . (int)$param['increase_buy'];
        }
        if (empty($where)) {
            return array();
        }
        $whereParam['column'] = "id,goods_id,group_id,goods_number,is_global,selected,selected_promotion,increase_buy";
        $whereParam['table'] = "\\Shop\\Models\\BaiyangGoodsShoppingCart";
        $whereParam['where'] = "where " . implode(' and ', $where);
        $whereParam['order'] = "order by add_time desc";
        if ($returnOne) $whereParam['limit'] = "limit 1";
        return $this->getData($whereParam, $returnOne);
    }

    /**
     * @desc 删除购物车
     * @param array $param
     *       -ids string  多个购物车ID，逗号隔开
     *       -goods_id  一个或者多个商品ID，逗号隔开
     *       -user_id int 用户ID
     *       -group_id int 套餐ID
     *       -is_temp int 是否临时用户
     *       -is_global int 是否海外购
     *       -increase_buy int 加价购活动ID
     * @return array
     * @author 柯琼远
     */
    public function deleteShoppingCart($param) {
        $where = array();
        if (isset($param['ids'])) {
            $where[] = "id in (" . $param['ids'] . ")";
        }
        if (isset($param['goods_id'])) {
            $where[] = "goods_id = " . (int)$param['goods_id'];
        }
        if (isset($param['user_id'])) {
            $where[] = "user_id = " . (int)$param['user_id'];
        }
        if (isset($param['group_id'])) {
            $where[] = "group_id = " . (int)$param['group_id'];
        }
        if (isset($param['is_temp'])) {
            $where[] = "is_temp = " . (int)$param['is_temp'];
        }
        if (isset($param['is_global'])) {
            $where[] = "is_global = " . (int)$param['is_global'];
        }
        if (isset($param['increase_buy'])) {
            $where[] = "increase_buy = " . (int)$param['increase_buy'];
        }
        if (empty($where)) {
            return false;
        }
        $whereParam = array(
            'table' => "\\Shop\\Models\\BaiyangGoodsShoppingCart",
            'where' => "where " . implode(' and ', $where)
        );
        return $this->deleteData($whereParam);
    }

    /**
     * @desc 更新购物车
     * @param array $param
     *       -ids string  多个购物车ID，逗号隔开
     *       -goods_id  一个或者多个商品ID，逗号隔开
     *       -user_id int 用户ID
     *       -group_id int 套餐ID
     *       -is_temp int 是否临时用户
     *       -is_global int 是否海外购
     *       -increase_buy int 加价购活动ID
     * @param array $updateData
     * @return array
     * @author 柯琼远
     */
    public function updateShoppingCart($param, $updateData) {
        $where = array();
        if (isset($param['ids'])) {
            $where[] = "id in (" . $param['ids'] . ")";
        }
        if (isset($param['goods_id'])) {
            $where[] = "goods_id = " . (int)$param['goods_id'];
        }
        if (isset($param['user_id'])) {
            $where[] = "user_id = " . (int)$param['user_id'];
        }
        if (isset($param['group_id'])) {
            $where[] = "group_id = " . (int)$param['group_id'];
        }
        if (isset($param['is_temp'])) {
            $where[] = "is_temp = " . (int)$param['is_temp'];
        }
        if (isset($param['is_global'])) {
            $where[] = "is_global = " . (int)$param['is_global'];
        }
        if (isset($param['increase_buy'])) {
            $where[] = "increase_buy = " . (int)$param['increase_buy'];
        }
        if (empty($where) || empty($updateData)) {
            return false;
        }
        $column = [];
        foreach ($updateData as $key => $value) {
            $column[] = $key . "='" . $value . "'";
        }
        $whereParam = array(
            'column' => implode(',', $column),
            'table' => "\\Shop\\Models\\BaiyangGoodsShoppingCart",
            'where' => "where " . implode(' and ', $where)
        );
        return $this->updateData($whereParam);
    }

    /**
     * @desc 批量删除购物车商品或套餐
     * @param array $param
     *       -user_id int 用户ID
     *       -is_temp int 是否临时用户
     *       -goods_ids  string 多个商品id，逗号隔开
     *       -group_ids  string 多个套餐id，逗号隔开
     *       -increase_buys  string 多个加价购，逗号隔开
     * @return array
     * @author 柯琼远
     */
    public function batchDeleteShoppingCart($param) {
        $whereOr = array();
        $whereAnd = array();
        if (isset($param['goods_ids']) && !empty($param['goods_ids'])) {
            $whereOr[] = "(goods_id in (" . $param['goods_ids'] . ") and group_id = 0 and increase_buy = 0)";
        }
        if (isset($param['group_ids']) && !empty($param['group_ids'])) {
            $whereOr[] = "(group_id in (" . $param['group_ids'] . ") and group_id > 0 and increase_buy = 0)";
        }
        if (isset($param['increase_buys']) && !empty($param['increase_buys'])) {
            $whereOr[] = "increase_buy in (" . $param['increase_buys'] . ")";
        }
        if (empty($whereOr)) {
            return false;
        }
        $whereOr = "(".implode(' or ', $whereOr).")";
        if (isset($param['user_id'])) {
            $whereAnd[] = "user_id = " . (int)$param['user_id'];
        }
        if (isset($param['is_temp'])) {
            $whereAnd[] = "is_temp = " . (int)$param['is_temp'];
        }
        if (empty($whereAnd)) {
            return false;
        }
        $whereAnd = "(".implode(' and ', $whereAnd).")";
        $whereParam = array(
            'table' => "\\Shop\\Models\\BaiyangGoodsShoppingCart",
            'where' => "where " . $whereAnd . " and " . $whereOr,
        );
        return $this->deleteData($whereParam);
    }

    /**
     * @desc 提交订单后清空购物车
     * @param array $param
     * @return bool
     * @author 柯琼远
     */
    public function deleteShoppingCartAftercommitOrder($param) {
        $goodsIds = array();
        $groupIds = array();
        $increaseBuys = array();
        foreach ($param['goodsList'] as $key => $value) {
            if (isset($value['increase_buy']) && $value['increase_buy'] > 0) {
                $increaseBuys[] = $value['increase_buy'];
            } else {
                if ($value['group_id'] == 0) {
                    $goodsIds[] = $value['goods_id'];
                } else {
                    $groupIds[] = $value['group_id'];
                }
            }
        }
        // 删除商品或套餐
        return $this->batchDeleteShoppingCart([
            'user_id'  => $param['userId'],
            'is_temp'  => 0,
            'goods_ids'=> implode(',', $goodsIds),
            'group_ids'=> implode(',', $groupIds),
            'increase_buys'=> implode(',', $increaseBuys),
        ]);
    }
}