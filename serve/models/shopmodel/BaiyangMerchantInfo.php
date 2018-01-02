<?php

namespace Shop\Models;

class BaiyangMerchantInfo extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $merchant_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=true)
     */
    public $merchant_type;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $merchant_address;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_merchant_info';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMerchantInfo[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMerchantInfo
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
