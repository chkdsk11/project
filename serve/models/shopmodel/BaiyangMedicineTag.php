<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangMedicineTag extends BaseModel
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
     * @Column(type="string", length=50, nullable=false)
     */
    public $tag_name;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    public $describe;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $url;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $medicine_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $platform;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_medicine_tag';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMedicineTag[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMedicineTag
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
