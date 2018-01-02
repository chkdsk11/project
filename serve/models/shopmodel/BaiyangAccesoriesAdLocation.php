<?php

namespace Shop\Models;

class BaiyangAccesoriesAdLocation extends BaseModel
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $ad_id;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $product_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $location;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $channel;

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
        return 'baiyang_accesories_ad_location';
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
