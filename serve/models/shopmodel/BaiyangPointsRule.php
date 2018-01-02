<?php
namespace Shop\Models;

class BaiyangPointsRule extends BaseModel
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
    public $order_return_points;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $sign_return_points;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $sign_num;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $send_points_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $update_time;





    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_points_rule';
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
