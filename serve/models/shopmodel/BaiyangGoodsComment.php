<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsComment extends BaseModel
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
     * @Column(type="string", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $nickname;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $headimgurl;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $level;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $agent_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $title;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $contain;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $star;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $click_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_anonymous;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $goods_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $message_reply;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $serv_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $serv_nickname;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $com_ty;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $created_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $updated_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_dummy;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $serv_created_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_global;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $remark;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $admin_account;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_check_image;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $check_image_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_goods_comment';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsComment[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsComment
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
