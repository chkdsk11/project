<?php
/**
 * Class BaiyangUserSinceShopData
 * @package Shop\Home\Datas
 * @desc 门店
 */
namespace Shop\Home\Datas;

class BaiyangGoodsStockBondedData extends BaseData
{
    protected static $instance=null;

    public static function getInstance(){
        return parent::getInstance();
    }

    /**
     * @desc 获取海外购真实库存
     * $goodsId  int
     * @return array  []   结果信息
     * @author 柯琼远
     */
    public function getGoodsRealStock($goodsId) {
        $result = $this->getData([
            'table'  => '\Shop\Models\BaiyangGoodsStockBonded',
            'column' => 'r_stock',
            'where'  => 'where goods_id = '. (int)$goodsId
        ], true);
        return !empty($result) ? $result['r_stock'] : 0;
    }
	
	/**
	 * @desc 获取海外购真实库存
	 * $goodsId  int
	 * @return array  []   结果信息
	 * @author 柯琼远
	 */
	public function getGoodsBondId($goodsId) {
		$result = $this->getData([
			'table'  => '\Shop\Models\BaiyangGoodsStockBonded',
			'column' => 'bonded_id',
			'where'  => 'where goods_id = '. (int)$goodsId
		], true);
		return !empty($result) ? $result['bonded_id'] : 0;
	}

	/**
	 * @desc 获取商品对应仓库的可售库存
	 * @param $goodsId
	 * @param $warehouse_id
	 * @return array|bool|int\
	 */
	public function getGoodsWhFinalStock($goodsId,$warehouse_id)
	{
		$result = $this->getData([
			'table'     =>      '\Shop\Models\BaiyangGoodsStockBonded',
			'column'    =>      'final_stock',
			'where'     =>      'where goods_id = '.(int)$goodsId.' and warehouse_id = '. (int)$warehouse_id,
		], true);
		return !empty($result) ? $result : 0;
	}
}