<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangPromotion extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $promotion_code;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $promotion_number;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $promotion_title;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $promotion_content;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $promotion_copywriter;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $promotion_type;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $promotion_mutex;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $promotion_member_level;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $promotion_status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_start_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_end_time;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $promotion_scope;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_create_user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $promotion_create_username;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_edit_user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $promotion_edit_username;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $promotion_site;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $promotion_for_users;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $promotion_is_real_pay;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $promotion_platform_pc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $promotion_platform_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $promotion_platform_wap;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $promotion_platform_wechat;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_promotion';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotion[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotion
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
