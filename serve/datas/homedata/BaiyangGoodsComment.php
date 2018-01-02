<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2017/05/22 1504
 */
namespace Shop\Home\Datas;

class BaiyangGoodsComment extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取商品评论信息
     * @param array $param
     *       -int user_id 用户id
     *       -string order_sn 订单编号
     *       -string goods_id 商品id (多个以逗号隔开)
     * @param bool $returnOne 是否返回单条数据(false:多条 true:单条)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getGoodsComment($param, $returnOne = false)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsComment',
            'column' => 'id,goods_id',
            'where' => "where order_sn = :order_sn: and goods_id in ({$param['goods_id']})",
            'bind' => [
                'order_sn' => $param['order_sn'],
            ],
        ];
        return $this->getData($condition, $returnOne);
    }

    /**
     * @desc 获取已上传图片的商品数
     * @param array $param
     *       -string comment_id 评论id (多个以逗号隔开)
     * @return array []  结果信息
     * @author 吴俊华
     */
    public function getGoodsCommentImageNumber($param)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsCommentImage',
            'column' => 'comment_id',
            'where' => "where comment_id in ({$param['comment_id']}) group by comment_id",
        ];
        return $this->getData($condition);
    }

}