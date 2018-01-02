<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAdminAuthRule extends BaseModel
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
     * @Column(type="string", length=80, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $title;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $direct_jump;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $sort;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $condition;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $controller;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $action;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $parent_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_admin_auth_rule';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminAuthRule[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminAuthRule
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
