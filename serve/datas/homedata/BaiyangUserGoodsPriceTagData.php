<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/26 1504
 */
namespace Shop\Home\Datas;

class BaiyangUserGoodsPriceTagData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取用户的商品标签价 (排除辣妈标签的)
     * @param array $param
     *       -int|string goods_id 商品id(多个以逗号隔开)
     *       -string platform 平台【pc、app、wap】
     *       -int user_id 用户id
     *       -int is_temp 是否临时用户
     *       -bool tag_sign 用户是否绑定标签 (可填)
     * @param bool $returnOne 是否返回单条数据，true为返回单条，false为返回多条
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getUserGoodsPriceTag($param,$returnOne = false)
    {
        // 临时用户不查标签价
        if($param['is_temp']) return [];
        // 判断用户有没绑定标签
        if(isset($param['tag_sign'])){
            if(!$param['tag_sign']) return [];
        }else{
            if(!$this->isUserPriceTag($param)) return [];
        }
        $momTagId = $this->config->mom->tag_id; // 辣妈标签
        $sql = "SELECT b.tag_id as id,a.goods_id,a.type,a.price,a.rebate,a.limit_number,b.tag_name,a.mutex FROM baiyang_goods_price as a LEFT JOIN baiyang_goods_price_tag as b on a.tag_id = b.tag_id left join baiyang_user_goods_price_tag as c on a.tag_id = c.tag_id where a.platform_".$param['platform']." = 1 and c.user_id = {$param['user_id']} and b.`status` = 1 and c.`status` = 1 and a.goods_id in({$param['goods_id']}) and b.tag_id != {$momTagId}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        if($returnOne){
            $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
        }else{
            $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        }
        return $ret;
    }

    /**
     * @desc 获取商品标签名
     * @param string $platform 平台【pc、app、wap】
     * @param int $tagId 标签id
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getPriceTagName($platform, $tagId)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsPriceTag a',
            'join' => 'left join \Shop\Models\BaiyangGoodsPrice b on a.tag_id = b.tag_id',
            'column' => 'tag_name',
            'where' => "where b.platform_".$platform." = 1 and a.tag_id = :tag_id:",
            'bind' => [
                'tag_id' => $tagId,
            ],
        ];
        return $this->getData($condition,true);
    }

    /**
     * @desc 判断用户是否有会员标签
     * @param array $param
     *       -int user_id 用户id
     *       -int is_temp 是否临时用户
     * @return bool true|false true:有 false:无
     * @author 吴俊华
     */
    public function isUserPriceTag($param)
    {
        $counts = 0;
        if(isset($param['is_temp']) && $param['is_temp'] == 0){
            $counts = $this->countData([
                'table' => '\Shop\Models\BaiyangUserGoodsPriceTag',
                'column' => 'user_id',
                'where' => "where user_id = :user_id: and status = 1",
                'bind' => [
                    'user_id' => $param['user_id'],
                ],
            ]);
        }
        return $counts ? true : false;
    }

}