<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangConfig extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $config_sign;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $config_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $config_value;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $explain;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sort;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_config';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangConfig[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangConfig
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
