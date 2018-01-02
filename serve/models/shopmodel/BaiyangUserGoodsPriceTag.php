<?php

namespace Shop\Models;

class BaiyangUserGoodsPriceTag extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=8, nullable=false)
     */
    public $tag_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $remark;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_user_goods_price_tag';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserGoodsPriceTag[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangUserGoodsPriceTag
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
