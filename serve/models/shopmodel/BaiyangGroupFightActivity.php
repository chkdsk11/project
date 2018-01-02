<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 8:53
 */

namespace Shop\Models;

/**
 * 拼团活动表
 * @package Shop\Models
 */
class BaiyangGroupFightActivity extends BaseModel
{
    public $gfa_id;
    public $gfa_name;
    public $gfa_starttime;
    public $gfa_endtime;
    public $gfa_user_count;
    public $gfa_join_num;
    public $gfa_cycle;
    public $goods_id;
    public $goods_name;
    public $goods_introduction;
    public $goods_image;
    public $goods_slide_images;
    public $gfa_sort;
    public $gfa_price;
    public $gfa_num;
    public $gfa_num_init;
    public $share_title;
    public $share_content;
    public $share_image;
    public $add_time;
    public $edit_time;
    public $gfa_state;
    public $gfa_type;
    public $gfa_user_type;
    public $gfa_way;
    public $gfa_draw_num;
    public $gfa_allow_num;
    public $gfa_is_draw;
    public $is_show_hot;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_group_fight_act';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGroupGoods[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGroupGoods
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}