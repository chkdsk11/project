<?php
/**
 * Class BaiyangUserSinceShopData
 * @package Shop\Home\Datas
 * @desc 门店
 */
namespace Shop\Home\Datas;

class BaiyangMerchantGoodsWarehouseRelationData extends BaseData
{
    protected static $instance=null;

    public static function getInstance(){
        return parent::getInstance();
    }
	
	/**
	 * @desc 获取海外购所属仓库
	 * $goodsId  int
	 * @return array  []   结果信息
	 * @author sarcasme
	 */
	public function getGoodsWarehouseId($goodsId) {
		$result = $this->getData([
			'table'  => '\Shop\Models\BaiyangMerchantGoodsWarehouseRelation',
			'column' => 'warehouse_id',
			'where'  => 'where goods_id = '. (int)$goodsId
		], true);
		return !empty($result) ? $result['warehouse_id'] : 0;
	}
	
	/**
	 * @desc 获取海外购所属仓库
	 * $goodsId  int
	 * @return array  []   结果信息
	 * @author sarcasme
	 */
	public function getGoodsShopId($goodsId) {
		$result = $this->getData([
			'table'  => '\Shop\Models\BaiyangMerchantGoodsWarehouseRelation',
			'column' => 'merchant_id',
			'where'  => 'where goods_id = '. (int)$goodsId
		], true);
		return !empty($result) ? $result['merchant_id'] : 0;
	}


	/**
	 * @desc 获取商品在用的仓库IDs
	 * @param $goodsId
	 * @return array|bool|int
	 */
	public function getUsingGoodsWhIds($goodsId)
	{
		$result = $this->getData([
			'table'     =>      '\Shop\Models\BaiyangMerchantGoodsWarehouseRelation',
			'column'    =>      'warehouse_id',
			'where'     =>      'where goods_id = '.(int)$goodsId . ' and status = 1',
			'order'     =>      'order by sort asc'
		]);
		return !empty($result) ? $result : 0;
	}
}