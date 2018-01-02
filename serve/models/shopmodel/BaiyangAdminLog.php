<?php
/**
 * Created by PhpStorm.
 * User: 罗毅庭
 * Date: 2017/4/24
 * Time: 14:20
 */
namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAdminLog extends BaseModel
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
     * @Column(type="string", length=128, nullable=false)
     */
    public $admin_account;

    /**
     *
     * @var string
     * @Column(type="string", length=2048, nullable=false)
     */
    public $url;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $get_param;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=1, nullable=false)
     */
    public $post_param;

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=1024, nullable=false)
     */
    public $controller_id;

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=1024, nullable=false)
     */
    public $ip;

    public $add_time;

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
        return 'baiyang_admin_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminRole[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminRole
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}