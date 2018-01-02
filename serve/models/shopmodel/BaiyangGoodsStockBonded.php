<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsStockBonded extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $merchant_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $warehouse_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $material_no;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $qualified_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $impe_stock;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $bonded_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $r_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $v_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $final_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $lock_stock;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_goods_stock_bonded';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsStockBonded[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsStockBonded
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
