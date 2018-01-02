<?php

namespace Shop\Models;

use Phalcon\Mvc\Model\Validator\Email as Email;

/**
 * BaiyangSmsAlarmNotify
 * 
 * @package Shop\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2016-12-19, 08:48:07
 */
class BaiyangSmsAlarmNotify extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $notify_user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $user_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $phone;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $email;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $openid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $user_state;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $remark;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $create_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $modify_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $modify_at;

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        return true;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_sms_alarm_notify';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsAlarmNotify[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsAlarmNotify
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
