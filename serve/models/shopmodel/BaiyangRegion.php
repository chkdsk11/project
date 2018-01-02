<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangRegion extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=5, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $pid;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=false)
     */
    public $region_name;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=false)
     */
    public $true_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $level;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
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
        return 'baiyang_region';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangRegion[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangRegion
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
