<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangUserInvoice extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $invoice_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $title_type;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $title_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $content_type;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $content;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */
    public $taker_phone;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $taker_email;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $taker_name;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=true)
     */
    public $taker_province;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=true)
     */
    public $taker_city;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=true)
     */
    public $taker_county;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $taker_address;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $unit_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $taxpayer_number;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $register_address;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */
    public $register_phone;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $deposit_bank;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $bank_account;

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
        return 'baiyang_user_invoice';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserInvoice[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserInvoice
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
