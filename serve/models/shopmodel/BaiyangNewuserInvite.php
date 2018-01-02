<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangNewuserInvite extends BaseModel
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
     * @Column(type="integer", length=30, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $inviter_id;

    /**
     *
     * @var varchar
     * @Column(type="varchar", length=125, nullable=false)
     */
    public $couponid;

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
        return 'baiyang_newuser_invite';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangNewuserInvite[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangNewuserInvite
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
