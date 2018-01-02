<?php

namespace Shop\Models;

class BaiyangMomApply extends BaseModel
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
     * @Column(type="integer", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $user_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $image;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $birth_time;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $idcard;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=false)
     */
    public $udid;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $reviewer;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $reviewer_time;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $remark;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $act_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $mobile_channel;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $download_channel;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_mom_apply';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomApply[]|BaiyangMomApply
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomApply
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
