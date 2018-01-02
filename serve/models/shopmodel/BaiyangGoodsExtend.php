<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsExtend extends BaseModel
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
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $code_ts;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $hs_code;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $goods_custom_name;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $item_record_no;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_unit;

    /**
     *
     * @var string
     * @Column(type="string", length=6, nullable=false)
     */
    public $gross_weight;

    /**
     *
     * @var string
     * @Column(type="string", length=6, nullable=false)
     */
    public $net_weight;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_item_no;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $first_unit;

    /**
     *
     * @var string
     * @Column(type="string", length=3, nullable=false)
     */
    public $first_count;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $country;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $flag;

    /**
     *
     * @var string
     * @Column(type="string", length=3, nullable=false)
     */
    public $tax_rate;

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
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_goods_extend';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsExtend[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsExtend
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
