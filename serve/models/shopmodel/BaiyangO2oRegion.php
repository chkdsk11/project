<?php

namespace Shop\Models;

class BaiyangO2oRegion extends \Shop\Models\BaseModel
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
        return 'baiyang_o2o_region';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangO2oRegion[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangO2oRegion
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
