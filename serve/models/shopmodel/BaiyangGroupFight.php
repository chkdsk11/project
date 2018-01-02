<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 8:58
 */

namespace Shop\Models;

/**
 * 拼团活动 开团表
 * @package Shop\Models
 */
class BaiyangGroupFight extends BaseModel
{
    public $gf_id;
    public $gfa_id;
    public $gfa_name;
    public $add_time;
    public $gf_join_num;
    public $gf_state;
    public $gf_start_time;
    public $gf_end_time;
    public $gf_over_time;
    public $user_id;
    public $nickname;
    public $phone;
    public $gfa_user_count;
    public $gfa_cycle;
    public $goods_id;
    public $goods_name;
    public $goods_image;
    public $gfa_price;
    public $edit_time;
    public $send_notice;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_group_fight';
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