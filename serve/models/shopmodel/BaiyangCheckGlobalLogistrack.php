<?php

namespace Shop\Models;

use Shop\Models\BaseModel;

class BaiyangCheckGlobalLogistrack extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $express_sn;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $logistics;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $update_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $show_logistics;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_check_global_logistrack';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCheckGlobalLogistrack[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCheckGlobalLogistrack
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
