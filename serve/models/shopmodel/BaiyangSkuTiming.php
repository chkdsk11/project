<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSkuTiming extends BaseModel
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
     * @Column(type="integer", length=11, nullable=false)
     */
    public $time_start;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $time_end;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $is_enable;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_on_sale;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sale_timing_wap;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sale_timing_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_sku_timing';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuTiming[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuTiming
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
