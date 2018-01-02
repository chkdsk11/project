<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAd extends BaseModel
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
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $box_id;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=false)
     */
    public $ad_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $url;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $image;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $target;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

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
        return 'baiyang_ad';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAd[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAd
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
