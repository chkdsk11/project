<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSkuDefault extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $info_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_alias_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_pc_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_mobile_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_pc_subheading;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_mobile_subheading;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $sku_batch_num;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $barcode;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $period;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $manufacturer;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $sku_weight;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $sku_bulk;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $attribute_value_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $sku_video;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $bind_gift;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_description;

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
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_usage;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $specifications;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $sku_label;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $prod_name_common;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_sku_default';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuDefault[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuDefault
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
