<?php

namespace Shop\Models;

/**
 * BaiyangCpsBackActivity
 * 
 * @package Shop\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2017-05-15, 20:33:38
 */
class BaiyangCpsBackActivity extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $act_id;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $act_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $act_desc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $channel_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $expire_day;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $start_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", length=3000, nullable=false)
     */
    public $no_include;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $creator;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $updater;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $is_cancel;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $for_users;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $act_logo;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $act_image;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $act_share_link;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    public $act_share_title;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $act_share_content;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sort;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_cps_back_activity';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsBackActivity[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsBackActivity
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return array(
            'act_id' => 'act_id',
            'act_name' => 'act_name',
            'act_desc' => 'act_desc',
            'channel_id' => 'channel_id',
            'type_id' => 'type_id',
            'expire_day' => 'expire_day',
            'start_time' => 'start_time',
            'end_time' => 'end_time',
            'no_include' => 'no_include',
            'creator' => 'creator',
            'add_time' => 'add_time',
            'updater' => 'updater',
            'update_time' => 'update_time',
            'is_cancel' => 'is_cancel',
            'for_users' => 'for_users',
            'act_logo' => 'act_logo',
            'act_image' => 'act_image',
            'act_share_link' => 'act_share_link',
            'act_share_title' => 'act_share_title',
            'act_share_content' => 'act_share_content',
            'sort' => 'sort'
        );
    }

}