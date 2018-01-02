<?php

namespace Shop\Models;

class BaiyangAppHomeAdImg extends BaseModel
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
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $ad_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $image_url;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $location;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $height;
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $width;
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sort;
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $creator;
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $create_time;
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_app_home_ad_img';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Advertisements[]|Advertisements
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Advertisements
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
