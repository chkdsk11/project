<?php

namespace Shop\Models;

class BaiyangO2oTime extends \Shop\Models\BaseModel
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
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $begin_time;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $end_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_o2o_time';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangO2oTime[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangO2oTime
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
