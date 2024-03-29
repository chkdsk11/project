<?php

namespace Shop\Models;

/**
 * BaiyangSmsRelationship
 * 
 * @package Shop\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2016-12-19, 09:16:52
 */
class BaiyangSmsRelationship extends BaseModel
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
     * @Column(type="integer", length=11, nullable=false)
     */
    public $template_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $client_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $is_enable_captcha;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $is_enable_client;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_sms_relationship';
    }
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
    public function getSequenceName()
    {
        return 'id';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsRelationship[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsRelationship
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
