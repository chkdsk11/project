<?php

namespace Shop\Models;

class BaiyangO2oFreightTemplate extends \Shop\Models\BaseModel
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
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $province;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $city;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $county;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $free_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $col_0;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $col_1;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $col_2;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $col_3;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $col_4;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_default;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_o2o_freight_template';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangO2oFreightTemplate[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangO2oFreightTemplate
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
