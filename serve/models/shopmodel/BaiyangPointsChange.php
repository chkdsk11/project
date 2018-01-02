<?php
namespace Shop\Models;

class BaiyangPointsChange extends BaseModel
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
     * @var decimal
     * @Column(type="string", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $points;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sent_type;


    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $inning_num;



    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_points_change';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPrescription[]|BaiyangPrescription
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPrescription
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
