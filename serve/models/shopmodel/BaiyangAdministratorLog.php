<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAdministratorLog extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=8, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $admin_account;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $title;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $url;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $get_param;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $post_param;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $ip;

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
        return 'baiyang_administrator_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdministratorLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdministratorLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
