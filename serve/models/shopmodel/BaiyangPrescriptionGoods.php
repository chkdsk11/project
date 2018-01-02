<?php

namespace Shop\Models;

class BaiyangPrescriptionGoods extends BaseModel
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
    public $prescription_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=20, nullable=false)
     */
    public $good_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $good_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $match_goods_number;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $good_spec;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $good_explain;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_prescription_goods';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPrescriptionGoods[]|BaiyangPrescriptionGoods
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPrescriptionGoods
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
