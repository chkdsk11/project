<?php

namespace Shop\Models;

class BaiyangGoodsWarehouse extends BaseModel
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
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $bid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $shop_id;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $warehouse_attri;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $warehouse_type;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $warehouse_name;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $province;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $county;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $address;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $sort;

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
        return 'baiyang_goods_warehouse';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsWarehouse[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsWarehouse
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
