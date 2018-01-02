<?php

namespace Shop\Models;

class BaiyangOrderOperationLog extends BaseModel
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
    public $belong_sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $belong_type;

    /**
     *
     * @var string
     * @Column(type="string", length=250, nullable=false)
     */
    public $content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $operation_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $operator_id;

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
        return 'baiyang_order_operation_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderOperationLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderOperationLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
