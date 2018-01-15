<?php
/**
 * Class BaiyangUserSinceShopData
 * @package Shop\Home\Datas
 * @desc 门店
 */
namespace Shop\Home\Datas;

class BaiyangUserSinceShopData extends BaseData
{
    protected static $instance=null;

    public static function getInstance(){
        return parent::getInstance();
    }

    /**
     * @desc 门店列表
     * @param array $param
     * @return array  []   结果信息
     * @author 柯琼远
     */
    public function getSinceShopList($param = []) {
        $data = [
            'column'=>'*',
            'table'=>'\Shop\Models\BaiyangUserSinceShop'
        ];
        if ($param) {
            if (isset($param['where'])) {
                $data['where'] = $param['where'];
            }
            if (isset($param['limit'])) {
                $data['limit'] = $param['limit'];
            }
        }
        return $this->getData($data);
    }

    /**
     * @desc 门店信息
     * @param $shop_id int
     * @return array  []   结果信息
     * @author 柯琼远
     */
    public function getSinceShopInfo($shop_id) {
        return $this->getData([
            'column'=>'*',
            'table'=>'\Shop\Models\BaiyangUserSinceShop',
            'where' => "where id = :id:",
            'bind' => [
                'id' => $shop_id
            ]
        ], true);
    }
}