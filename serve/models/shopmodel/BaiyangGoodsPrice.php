<?php

namespace Shop\Models;

class BaiyangGoodsPrice extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=20, nullable=false)
     */
    public $tag_goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $tag_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $price;

    /**
     *
     * @var double
     * @Column(type="double", length=2, nullable=true)
     */
    public $rebate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $platform_pc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $platform_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $platform_wap;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $platform_wechat;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $mutex;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $limit_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_goods_price';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsPrice[]|BaiyangGoodsPrice
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsPrice
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
