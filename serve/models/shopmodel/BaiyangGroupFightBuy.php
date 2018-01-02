<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 8:49
 */

namespace Shop\Models;

/**
 * 拼团活动 用户表
 * @package Shop\Models
 */
class BaiyangGroupFightBuy extends BaseModel
{
    public $gfb_id;
    public $gf_id;
    public $gfa_id;
    public $user_id;
    public $nickname;
    public $phone;
    public $order_sn;
    public $is_head;
    public $add_time;
    public $edit_time;
    public $gfu_state;
    public $is_overtime;
    public $sync_erp;
    public $gf_start_time;
    public $gf_end_time;
    public $gfa_type;
    public $gfa_user_type;
    public $is_win;


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_group_fight_buy';
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