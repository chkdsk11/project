<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAdminSite extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $site_id;

    /**
     *
     * @var string
     * @Column(type="string", length=512, nullable=false)
     */
    public $site_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $is_enable;

    /**
     *
     * @var string
     * @Column(type="string", length=2048, nullable=false)
     */
    public $site_menus;

    /**
     *
     * @var string
     * @Column(type="string", length=2048, nullable=false)
     */
    public $site_controllers;

    /**
     *
     * @var string
     * @Column(type="string", length=2048, nullable=false)
     */
    public $site_module;

    /**
     *  初始方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->setup([
            'notNullValidations'=>false
        ]);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_admin_site';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminSite[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminSite
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
