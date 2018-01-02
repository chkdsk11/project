<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangIndexHandyEntry extends BaseModel
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
     * @Column(type="string", length=30, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $link;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $icon_img;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $sort;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $is_del;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $remark;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $up_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $start_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $start_version;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $end_version;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $channel_name;


    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_index_handy_entry';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCoupon[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCoupon
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
