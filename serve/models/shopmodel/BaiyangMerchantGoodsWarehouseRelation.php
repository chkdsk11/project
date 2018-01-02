<?php

namespace Shop\Models;

class BaiyangMerchantGoodsWarehouseRelation extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $merchant_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=true)
     */
    public $warehouse_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_merchant_goods_warehouse_relation';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMerchantGoodsWarehouseRelation[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMerchantGoodsWarehouseRelation
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
