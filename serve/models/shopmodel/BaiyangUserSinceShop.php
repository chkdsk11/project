<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangUserSinceShop extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=5, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $province;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $county;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $address;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $trade_name;

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
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_user_since_shop';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserSinceShop[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserSinceShop
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
