<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSubjectLog extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $log_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $subject_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $field_name;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $old_value;

    /**
     *
     * @var string
     * @Column(type="string", length=500,nullable=false)
     */
    public $new_value;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $channel;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $add_time;

    

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_subject_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotion[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotion
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
