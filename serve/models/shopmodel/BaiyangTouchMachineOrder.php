<?php

namespace Shop\Models;

class BaiyangTouchMachineOrder extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $log_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_global;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $machine_sn;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_touch_machine_order';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangTouchMachineOrder[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangTouchMachineOrder
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
