<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSkuInfo extends BaseModel
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
    public $sku_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=false)
     */
    public $virtual_stock_default;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=false)
     */
    public $virtual_stock_pc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=false)
     */
    public $virtual_stock_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=false)
     */
    public $virtual_stock_wap;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_price_pc;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $market_price_pc;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_price_app;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $market_price_app;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_price_wap;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $market_price_wap;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $ad_id_pc;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $ad_id_mobile;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $sku_detail_pc;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $sku_detail_mobile;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $whether_is_gift;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $gift_pc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $gift_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $gift_wap;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=false)
     */
    public $virtual_stock_wechat;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_price_wechat;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $market_price_wechat;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $gift_wechat;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_sku_info';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuInfo[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuInfo
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
