<?php

namespace Shop\Models;

use Shop\Home\Services\BaseService;
use Shop\Models\BaseModel;

class BaiyangKjOrderReturn extends BaseModel
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
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $oid_traderno;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $decl_status;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $oid_orderno;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $logi_ente_code;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=true)
     */
    public $logi_ente_name;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $retcode;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $retmsg;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $sign;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $business_no;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $business_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $chk_mark;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $notice_time;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $notice_content;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=true)
     */
    public $way_bills;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_kj_order_return';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangKjOrderReturn[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangKjOrderReturn
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
