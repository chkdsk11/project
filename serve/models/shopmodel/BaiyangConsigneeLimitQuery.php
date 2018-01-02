<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangConsigneeLimitQuery extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $spare_chance;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $flag;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $query_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_consignee_limit_query';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangConsigneeLimitQuery[]|BaiyangConsigneeLimitQuery
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangConsigneeLimitQuery
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
