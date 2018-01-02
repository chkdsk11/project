<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSkuInstruction extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sku_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $cn_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $common_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $eng_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $component;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $indication;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $form;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $dosage;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $adverse_reactions;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $contraindications;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $precautions;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $use_in_pregLact;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $use_in_elderly;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $use_in_children;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $drug_interactions;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $mechanismAction;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $pharmacokinetics;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $storage;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $pack;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $period;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $approve_code;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $company_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $standard;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $overdosage;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $commodity_code;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $clinicalTrial;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    public $functionCategory;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_sku_instruction';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuInstruction[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuInstruction
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
