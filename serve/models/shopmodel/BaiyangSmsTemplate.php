<?php

namespace Shop\Models;

/**
 * BaiyangSmsTemplate
 * 
 * @package Shop\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2016-12-19, 09:21:22
 */
class BaiyangSmsTemplate extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $template_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $template_name;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $template_code;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $signature;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $content;

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
    public $template_type;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_sms_template';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsTemplate[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsTemplate
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
