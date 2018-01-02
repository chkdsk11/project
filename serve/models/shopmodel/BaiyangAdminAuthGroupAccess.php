<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAdminAuthGroupAccess extends BaseModel
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
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $uid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $group_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_admin_auth_group_access';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminAuthGroupAccess[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminAuthGroupAccess
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
