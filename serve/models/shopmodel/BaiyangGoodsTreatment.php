<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsTreatment extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=8, nullable=false)
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
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $min_goods_number;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $unit_price;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $promotion_msg;

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
     * @Column(type="string", length=128, nullable=false)
     */
    public $promotion_mutex;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_goods_treatment';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsTreatment[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsTreatment
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
