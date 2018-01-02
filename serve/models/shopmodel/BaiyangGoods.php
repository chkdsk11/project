<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoods extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $supplier_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_ext_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $category_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $category_path;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $brand_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $app_brand_id;

    /**
     *
     * @var string
     * @Column(type="string", length=140, nullable=false)
     */
    public $barcode;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $prod_code;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $goods_name;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $product_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $mother_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $erp_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $goods_name_pinyin;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $prod_name_common;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $goods_image;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $big_path;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $small_path;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $name_desc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $price_range_id;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $introduction;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $gift_yes;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=true)
     */
    public $min_num_buy;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=true)
     */
    public $max_num_buy;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $hot_yes;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $recommend_yes;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $price;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $packing;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $virtual_stock;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $unit;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $cost_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $market_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $min_limit_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $guide_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $activ_price;

    /**
     *
     * @var double
     * @Column(type="double", length=11, nullable=true)
     */
    public $pricie_special;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $vip_price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $goods_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $v_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $is_use_stock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $shoppingcart_min_qty;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $shoppingcart_max_qty;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $attr_list;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $packaging_type;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=false)
     */
    public $weight;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=false)
     */
    public $color;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=false)
     */
    public $size;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $like_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $comment_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $sales_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $rate_of_praise;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_on_sale;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_hot;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_recommend;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_delete;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_has_largess;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $new_yes;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $otc_yes;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $net_ifsell;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $product_type;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $manufacturer;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $storage_method;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $additive;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $period;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $medicine_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $praise_ty;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $num_consults;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $num_collections;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $show_praise;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $drug_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $specifications;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_global;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $can_comment;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $can_consult;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $show_comments;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $show_consults;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $freight_temp_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $pc_freight_temp_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $video_id;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $summary;

    /**
     *
     * @var string
     * @Column(type="string", length=512, nullable=false)
     */
    public $goods_feature;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $usage;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $rule_value_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_unified_price;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $bind_gift;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_alias_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_mobile_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_pc_subheading;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_mobile_subheading;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $attribute_value_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sale_timing_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sale_timing_wap;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $sku_usage;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $sku_label;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $rule_value0;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $rule_value1;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $rule_value2;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sale_timing_wechat;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_lock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_rule_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_goods';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoods[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoods
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
