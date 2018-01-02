<?php

namespace Shop\Models;

use Phalcon\Mvc\Model\Validator\Email as Email;
use Shop\Models\BaseModel;

class BaiyangOrderLog extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    public $logid;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $log_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $log_content;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $pcname;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $ipname;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $log_title;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_order_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
