<?php

namespace Shop\Models;

use Shop\Models\BaseModel;

class BaiyangFavourableGroup extends BaseModel
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
    public $group_name;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $group_introduction;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $mutex;

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
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $pc_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $wap_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $app_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $wechat_platform;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_favourable_group';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangFavourableGroup[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangFavourableGroup
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
