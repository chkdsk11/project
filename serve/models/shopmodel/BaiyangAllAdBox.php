<?php

namespace Shop\Models;

class BaiyangAllAdBox extends BaseModel
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
     * @Column(type="integer", length=5, nullable=false)
     */
    public $site_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $group_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $show_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $count;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $sign;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $width;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $height;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_all_ad_box';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAllAdBox[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAllAdBox
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
