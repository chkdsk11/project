<?php

namespace Shop\Models;

use Shop\Models\BaseModel;

class BaiyangUserConsignee extends BaseModel
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
     * @Column(type="string", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $consignee;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $province;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
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
     * @Column(type="string", length=20, nullable=false)
     */
    public $telphone;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $fix_line;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $email;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $zipcode;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $tag_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $default_addr;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sale_addr;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $addr_group;

    /**
     *
     * @var string
     * @Column(type="string", length=22, nullable=true)
     */
    public $consignee_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $identity_confirmed;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_user_consignee';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserConsignee[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserConsignee
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
