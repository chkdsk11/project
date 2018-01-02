<?php
namespace Shop\Home\Datas;

class BaiyangConsigneeLimitData extends BaseData
{

    protected static $instance=null;

    /**
     * 按身份证id获取海外购限购人名
     *
     * @param string $idCard 身份证id
     * @return array|bool
     */
    public function getConsigneeLimitBuyName($idCard)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangConsigneeLimitBuy',
            'column' => "consignee username",
            'where' => 'WHERE card_sn=:idCard:',
            'bind' => array(
                'idCard' => $idCard
            )
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 获取用户身份证查询数量
     *
     * @param int $userId 用户id
     * @return array|bool
     */
    public function getConsigneeLimitQueryNumber($userId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangConsigneeLimitQuery',
            'column' => "spare_chance,query_time, flag",
            'where' => 'WHERE user_id=:user_id:',
            'bind' => array(
                'user_id' => $userId
            )
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

}