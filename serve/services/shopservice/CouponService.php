<?php
/**
 * Author: DinYJ
 * Date: 2016/8/26
 */
namespace Shop\Services;

use Shop\Datas\BaiyangCategoryData;
use Shop\Datas\BaiyangGoodsData;
use Shop\Datas\BaiyangOrderData;
use Shop\Datas\BaiyangUserData;
use Shop\Datas\BaseData;
use Shop\Datas\BrandData;
use Shop\Datas\CouponData;
use Shop\Datas\CpsChannelData;
use Shop\Datas\BaiyangCouponCodeData;
use Shop\Datas\BaiyangCouponRecordData;
use Shop\Libs\Csv;
use Shop\Models\BaiyangCoupon;
use Shop\Models\BaiyangBrand;
use Shop\Models\BaiyangCouponCode;
use Shop\Models\CacheKey;
use Shop\Services\BaseService;
use Shop\Models\BaiyangCouponEnum as Enum;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CouponService extends BaseService
{
    protected $revers_arr = [];

    /**
     * @desc 通过参数查询修改数据
     * @param $id
     * @param bool $is_first
     * @return mixed
     */
    public function getEditCouponByParam($id, $is_first = false)
    {
        $column = '*';
        $table = '\Shop\Models\BaiyangCoupon';
        $condition = ["id" => $id];
        $where = "id = :id:";
        return $is_first ? CouponData::getInstance()->select($column, $table, $condition, $where)[0] : CouponData::getInstance()->select($column, $table, $condition, $where);
    }

    /**
     * @author 邓永军
     * @desc 获取cps渠道
     */
    public function getCpsChannelList()
    {
        $LocateSite[0] = [
            'channel_id' => 0,
            'channel_name' => '本站'
        ];
        $reselt = CpsChannelData::getInstance()->select("channel_id,channel_name", "\\Shop\\Models\\BaiyangCpsChannel");
        return array_merge($LocateSite, $reselt);
    }

    public function getPlatform($param)
    {
        $platform = [];
        array_map(function ($v) use (&$platform) {
            switch ($v) {
                case "1":
                    $platform["pc"] = "1";
                    break;
                case "2":
                    $platform["app"] = "1";
                    break;
                case "3":
                    $platform["wap"] = "1";
                    break;
                case "4":
                    $platform["wechat"] = "1";
                    break;
            }
        }, $param["use_platform"]);
        return $platform;
    }

    /**
     * @author 邓永军
     * @desc 修改保存数据
     * @param $param
     * @return array
     */
    public function PostUpdateCoupon($param)
    {
        $platform = self::getPlatform($param);
        $data["id"] = $param["id"];
        if(!empty($param['hiddenUseRange'])){
            $UseRangeIdsArray = explode(',', $param['hiddenUseRange']);
            if(count($UseRangeIdsArray) != count(array_unique($UseRangeIdsArray))){
                return $this->arrayData('添加失败！不能添加相同数据', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
            }
        }
        $_columStr = '';
        $data["coupon_name"] = $param["coupon_name"];
        $data["start_provide_time"] = strtotime($param["start_provide_time"]);
        $data["end_provide_time"] = strtotime($param["end_provide_time"]);
        if ($data["end_provide_time"] < $data["start_provide_time"]) {
            return $this->arrayData('添加失败！结束时间不能超过开始时间',  '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
        }
        if ($this->BrandCheck($param["use_range"], $data["start_provide_time"], $data["end_provide_time"]) == 0) {
            return $this->arrayData('添加失败！同一时间段相同品牌不能添加',  '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
        }
        $data["validitytype"] = $param["validitytype"];
        $data["medicine_type"] = $param["medicine_type"];
        if(!isset($param["is_present"]) || empty($param["is_present"])){
            $param["is_present"] = 0;
        }
        $data["is_present"] = $param["is_present"];
        $_columStr .= "coupon_name = :coupon_name:,start_provide_time = :start_provide_time:,end_provide_time = :end_provide_time:,validitytype = :validitytype:,drug_type = :medicine_type:,is_present = :is_present:,";
        if ($param["validitytype"] == "1") {
            //绝对有效期
            $data["start_use_time"] = strtotime($param["start_use_time"]);
            $data["end_use_time"] = strtotime($param["end_use_time"]);
            if ($data["end_use_time"] < $data["start_use_time"]) {
                return $this->arrayData('添加失败！结束时间不能超过开始时间',  '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
            }
            $data["relative_validity"] = 0;
            $_columStr .= "start_use_time = :start_use_time:,end_use_time = :end_use_time:,";
        } else {
            //相对有效期
            $data["relative_validity"] = $param["relative_validity"];

        }
        $_columStr .= "relative_validity = :relative_validity:,";
        $platform_value = $this->PlatformSet($platform);
        $data["pc_platform"] = $platform_value['pc_platform'];
        $data["app_platform"] = $platform_value['app_platform'];
        $data["wap_platform"] = $platform_value['wap_platform'];
        $data["wechat_platform"] = $platform_value['wechat_platform'];
        $data["coupon_description"] = $param["coupon_description"];
        $data["provide_type"] = $param["provide_type"];
        $data["coupon_type"] = $param["coupon_type"];
        $data["channel_id"] = $param["channel_id"];
        $data["coupon_number"] = $param["coupon_number"];
        $data["limit_number"] = $param["limit_number"];
        if($data["limit_number"] > 100 ){
            return $this->arrayData('添加失败！每人限领张数不能大于100', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
        }
        if($data["limit_number"] < 1 ){
            return $this->arrayData('添加失败！每人限领张数不能小于1', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
        }
        $data["pc_url"] = $param["pc_url"];
        $data["app_url"] = $param["app_url"];
        $data["wap_url"] = $param["wap_url"];
        $data["wechat_url"] = $param["wechat_url"];
        if ($param["limit_number"] > $param["coupon_number"] && $param["coupon_number"] > 0) {
            return $this->arrayData('添加失败！每人限领不能超过总张数', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
        }
        $_columStr .= "pc_platform = :pc_platform:,app_platform = :app_platform:,wap_platform = :wap_platform:,wechat_platform = :wechat_platform:, coupon_description = :coupon_description:,provide_type = :provide_type:,coupon_type = :coupon_type:,channel_id = :channel_id:,coupon_number = :coupon_number:,limit_number = :limit_number:,pc_url = :pc_url:,app_url = :app_url:,wap_url = :wap_url:,wechat_url = :wechat_url:,";
        $couponProps = $this->CouponProps($param, 1);
        $data["coupon_value"] = $couponProps["coupon_value"];
        $data["min_cost"] = $couponProps["min_cost"];
        if ($param["coupon_type"] == 2) $data["discount_unit"] = $couponProps["discount_unit"];
        $_columStr .= $couponProps['columnStr'];
        $data["group_set"] = $param["group"];
        $_columStr .= "group_set = :group_set:,";
        switch ($param["group"]) {
            case "0":
                $data["register_bonus"] = $param["register_bonus"];
                $data["tels"] = '';
                $_columStr .= "register_bonus = :register_bonus:,tels = :tels:,";
                break;
            case "1":
                $data["register_bonus"] = $param["register_bonus"];
                $data["tels"] = '';
                $_columStr .= "register_bonus = :register_bonus:,tels = :tels:,";
                break;
            case "2":
                $data["register_bonus"] = $param["register_bonus"];
                $data["tels"] = '';
                $_columStr .= "register_bonus = :register_bonus:,tels = :tels:,";
                break;
            case "3":
                return $this->arrayData('指定用户优惠券不能再次发放', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
                break;
        }

        $data["use_range"] = $param["use_range"];
        $_columStr .= "use_range = :use_range:,";
        if ($param["provide_type"] == "2" || $param["provide_type"] == "3") {
            if (isset($param["create_min_num"]) && !empty($param["create_min_num"])) {
                $data["is_activecode"] = $param["is_activecode"];
                $_columStr .= "is_activecode = :is_activecode:,";
            }
        }
        $data["goods_tag_id"] = $param["goods_tag"];
        $_columStr .= "goods_tag_id = :goods_tag_id:,";
        switch ($data["use_range"]) {
            case "all":
                $info = [
                    "category" => $param["except_category_id"],
                    "brand" => $param["except_brand_id"],
                    "single" => $param["except_good_id"]
                ];
                $data["ban_join_rule"] = json_encode($info);
                $data["category_ids"] = '';
                $data["brand_ids"] = '';
                $data["product_ids"] = '';
                $_columStr .= "category_ids = :category_ids:,";
                $_columStr .= "brand_ids = :brand_ids:,";
                $_columStr .= "product_ids = :product_ids:,";
                $_columStr .= "ban_join_rule = :ban_join_rule:";
                break;
            case "category":
                if(!isset($param['shop_category'][2]) || empty($param['shop_category'][2]) || $param['shop_category'][2] == 0){
                    return $this->arrayData('品类设置不完整,请重新设置', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
                }
                $info = [
                    "brand" => $param["except_brand_id"],
                    "single" => $param["except_good_id"]
                ];
                $data["ban_join_rule"] = json_encode($info);
                $data["category_ids"] = $param["hiddenUseRange"];
                $_columStr .= "ban_join_rule = :ban_join_rule:,";
                $data["brand_ids"] = '';
                $data["product_ids"] = '';
                $_columStr .= "brand_ids = :brand_ids:,";
                $_columStr .= "product_ids = :product_ids:,";
                $_columStr .= "category_ids = :category_ids:";
                break;
            case "brand":
                $info = [
                    "single" => $param["except_good_id"]
                ];
                $data["ban_join_rule"] = json_encode($info);
                $data["brand_ids"] = $param["shop_brand"];
                $data["product_ids"] = '';
                $data["category_ids"] = '';
                $_columStr .= "product_ids = :product_ids:,";
                $_columStr .= "category_ids = :category_ids:,";
                $_columStr .= "ban_join_rule = :ban_join_rule:,";
                $_columStr .= "brand_ids = :brand_ids:";
                break;
            case "single":
                $data["ban_join_rule"] = '';
                $data["category_ids"] = '';
                $data["brand_ids"] = '';
                $_columStr .= "category_ids = :category_ids:,";
                $_columStr .= "brand_ids = :brand_ids:,";
                $_columStr .= "ban_join_rule = :ban_join_rule:,";
                $data["product_ids"] = $param["hiddenUseRange"];
                $_columStr .= "product_ids = :product_ids:";
                break;
        }

        $is_update_ok = CouponData::getInstance()->update($_columStr, "\\Shop\\Models\\BaiyangCoupon", $data, "id = :id:");
        if ($is_update_ok !== false) {
            if ($param["provide_type"] == "2" || $param["provide_type"] == "3") {
                $data["is_activecode"] = $param["is_activecode"];
            }
            $coupon_sn = CouponData::getInstance()->select("coupon_sn", "\\Shop\\Models\\BaiyangCoupon", ["id" => $param["id"]], "id = :id:")[0]["coupon_sn"];
            if (isset($data["is_activecode"]) && !empty($data["is_activecode"]) && isset($param["create_min_num"]) && !empty($param["create_min_num"])) {
                self::createCodeByQueue($param["create_min_num"], $data, $coupon_sn,1);
            }
            return $this->arrayData('修改成功！', '/coupon/list', '');
        } else {
            return $this->arrayData('修改失败！', '/coupon/edit?id=' . $data["id"] . '&isshow=1', '', 'error');
        }
    }

    /**
     * @desc 判断品牌是否在同一个时间段内存在相同品牌
     * @param $use_range
     * @param $start_provide_time
     * @param $end_provide_time
     * @return int
     */
    protected function BrandCheck($use_range, $start_provide_time, $end_provide_time)
    {
        if ($use_range == "brand") {
            $brand_arr = explode(",", $use_range);
            foreach ($brand_arr as $brand_id) {
                $brand_list = CouponData::getInstance()->select("start_provide_time,end_provide_time", "\\Shop\\Models\\BaiyangCoupon", ["use_range" => "brand", "brand_id" => $brand_id], "use_range = :use_range: AND FIND_IN_SET(:brand_id:,brand_ids)");
                foreach ($brand_list as $tmp) {
                    if ($tmp["start_provide_time"] < $start_provide_time && $tmp["end_provide_time"] > $end_provide_time) {
                        return 0;
                    }
                }
            }
            return 1;
        } else {
            return 1;
        }
    }

    /**
     * @desc 平台值设置
     * @param $platform
     * @return array
     * @author 邓永军
     */
    protected function PlatformSet($platform)
    {
        if (isset($platform["pc"]) && !empty($platform["pc"])) {
            $data["pc_platform"] = "1";
        } else {
            $data["pc_platform"] = "0";
        }
        if (isset($platform["app"]) && !empty($platform["app"])) {
            $data["app_platform"] = "1";
        } else {
            $data["app_platform"] = "0";
        }
        if (isset($platform["wap"]) && !empty($platform["wap"])) {
            $data["wap_platform"] = "1";
        } else {
            $data["wap_platform"] = "0";
        }
        if (isset($platform["wechat"]) && !empty($platform["wechat"])) {
            $data["wechat_platform"] = "1";
        } else {
            $data["wechat_platform"] = "0";
        }
        return [
            'pc_platform' => $data["pc_platform"],
            'app_platform' => $data["app_platform"],
            'wap_platform' => $data["wap_platform"],
            'wechat_platform' => $data["wechat_platform"]
        ];
    }

    /**
     * @desc 设置优惠券属性
     * @param $param
     * @param $is_update
     * @return mixed
     * @author 邓永军
     */
    protected function CouponProps($param, $is_update)
    {
        switch ($param["coupon_type"]) {
            case "1":
                $data["coupon_value"] = $param["coupon_value"];
                $data["min_cost"] = $param["min_cost"];
                if ($is_update == 1) {
                    $data['columnStr'] = "coupon_value = :coupon_value:,min_cost = :min_cost:,";
                }
                break;
            case "2":
                $data["coupon_value"] = $param["coupon_value"];
                $data["min_cost"] = $param["min_cost"];
                $data["discount_unit"] = $param["discount_unit"];
                if ($is_update == 1) {
                    $data['columnStr'] = "coupon_value = :coupon_value:,min_cost = :min_cost:,discount_unit = :discount_unit:,";
                }
                break;
            case "3":
                $data["coupon_value"] = '';
                $data["min_cost"] = $param["min_cost"];
                if ($is_update == 1) {
                    $data['columnStr'] = "coupon_value = :coupon_value:,min_cost = :min_cost:,";
                }
                break;
        }
        return $data;
    }

    /**
     * @author 邓永军
     * @desc 添加保存数据
     * @param $param
     * @return array
     */
    public function PostAddCoupon($param)
    {
        if(!empty($param['hiddenUseRange'])){
            $UseRangeIdsArray = explode(',', $param['hiddenUseRange']);
            if(count($UseRangeIdsArray) != count(array_unique($UseRangeIdsArray))){
                return $this->arrayData('添加失败！不能添加相同数据', '/coupon/add', '', 'error');
            }
        }
        $platform = self::getPlatform($param);
        $data["coupon_sn"] = self::makeCouponSn();
        $data["coupon_name"] = $param["coupon_name"];
        $data["start_provide_time"] = strtotime($param["start_provide_time"]);
        $data["end_provide_time"] = strtotime($param["end_provide_time"]);
        if ($this->BrandCheck($param["use_range"], $data["start_provide_time"], $data["end_provide_time"]) == 0) {
            return $this->arrayData('添加失败！同一时间段相同品牌不能添加', '/coupon/add', '', 'error');
        }

        if ($data["end_provide_time"] < $data["start_provide_time"]) {
            return $this->arrayData('添加失败！结束时间不能超过开始时间', '/coupon/add', '', 'error');
        }
        $data["validitytype"] = $param["validitytype"];
        if ($param["validitytype"] == "1") {
            //绝对有效期
            $data["start_use_time"] = strtotime($param["start_use_time"]);
            $data["end_use_time"] = strtotime($param["end_use_time"]);
            if ($data["end_use_time"] < $data["start_use_time"]) {
                return $this->arrayData('添加失败！结束时间不能超过开始时间', '/coupon/add', '', 'error');
            }
            $data["relative_validity"] = 0;
        } else {
            //相对有效期
            $data["relative_validity"] = $param["relative_validity"];
        }
        $platform_value = $this->PlatformSet($platform);
        $data["pc_platform"] = $platform_value['pc_platform'];
        $data["app_platform"] = $platform_value['app_platform'];
        $data["wap_platform"] = $platform_value['wap_platform'];
        $data["wechat_platform"] = $platform_value['wechat_platform'];
        $data["coupon_description"] = $param["coupon_description"];
        $data["provide_type"] = $param["provide_type"];
        $data["coupon_type"] = $param["coupon_type"];
        $data["drug_type"] = $param["medicine_type"];
        $couponProps = $this->CouponProps($param, 0);
        $data["coupon_value"] = $couponProps["coupon_value"];
        $data["min_cost"] = $couponProps["min_cost"];
        if ($param["coupon_type"] == 2) $data["discount_unit"] = $couponProps["discount_unit"];
        $data["channel_id"] = $param["channel_id"];
        $data["coupon_number"] = $param["coupon_number"];
        $data["limit_number"] = $param["limit_number"];
        if($data["limit_number"] > 100 ){
            return $this->arrayData('添加失败！每人限领张数不能大于100', '/coupon/add', '', 'error');
        }
        if($data["limit_number"] < 1 ){
            return $this->arrayData('添加失败！每人限领张数不能小于1', '/coupon/add', '', 'error');
        }
        $data["pc_url"] = $param["pc_url"];
        $data["app_url"] = $param["app_url"];
        $data["wap_url"] = $param["wap_url"];
        $data["wechat_url"] = $param["wechat_url"];
        if ($param["limit_number"] > $param["coupon_number"] && $param["coupon_number"] > 0) {
            return $this->arrayData('添加失败！每人限领不能超过总张数', '/coupon/add', '', 'error');
        }
        $data["group_set"] = $param["group"];
        if (empty($param["register_bonus"])) $data["register_bonus"] = 0;
        switch ($param["group"]) {
            case "0":
                $data["register_bonus"] = $param["register_bonus"];
                break;
            case "1":
                $data["register_bonus"] = $param["register_bonus"];
                break;
            case "2":
                $data["register_bonus"] = $param["register_bonus"];
                break;
            case "3":
                $param["register_bonus"] = 0;
                $data["tels"] = $param["tels"];
                break;
        }
        $data["condition"] = '';
        $data["use_range"] = $param["use_range"];
        switch ($data["use_range"]) {
            case "all":
                $info = [
                    "category" => $param["except_category_id"],
                    "brand" => $param["except_brand_id"],
                    "single" => $param["except_good_id"]
                ];
                $data["ban_join_rule"] = json_encode($info);
                break;
            case "category":
                if(!isset($param['shop_category'][2]) || empty($param['shop_category'][2]) || $param['shop_category'][2] == 0){
                    return $this->arrayData('品类设置不完整,请重新设置', '/coupon/add', '', 'error');
                }
                $info = [
                    "brand" => $param["except_brand_id"],
                    "single" => $param["except_good_id"]
                ];
                $data["ban_join_rule"] = json_encode($info);
                $data["category_ids"] = $param["hiddenUseRange"];
                break;
            case "brand":
                $info = [
                    "single" => $param["except_good_id"]
                ];
                $data["ban_join_rule"] = json_encode($info);
                $data["brand_ids"] = $param["shop_brand"];
                break;
            case "single":
                $data["product_ids"] = $param["hiddenUseRange"];
                break;
        }
        $data['goods_tag_id'] = $param['goods_tag'];
        if(!isset($param["is_present"]) || empty($param["is_present"])){
            $param["is_present"] = 0;
        }
        $data['is_present'] = $param['is_present'];
        $data["add_time"] = time();
        if ($param["provide_type"] == "2" || $param["provide_type"] == "3") {
            $data["is_activecode"] = $param["is_activecode"];
        }
        if (isset($data["is_activecode"]) && !empty($data["is_activecode"]) && isset($param["create_min_num"]) && !empty($param["create_min_num"])) {
            if ($param["create_min_num"] > $data["coupon_number"]) {
                return $this->arrayData('激活码数量超过限制', '/coupon/add', '', 'error');
            }
        }
        $data["is_delete"] = 0;
        $data["is_cancel"] = 0;
        if (($param["provide_type"] == "2" || $param["provide_type"] == "3") && $param["group"] == 3) {
            return $this->arrayData('暂不支持发放激活码和统一码类型优惠券到指定用户', '/coupon/add', '', 'error');
        }
        $insertID = CouponData::getInstance()->insert("\\Shop\\Models\\BaiyangCoupon", $data, true);

        if ($insertID > 0 && $param["group"] == 3) {
            //发放指定用户优惠券
            $coupon_id = $data["coupon_sn"];
            $tels = $data["tels"];
            if (!empty($tels)) {
                $tels_arr = explode(',', $tels);
                $tels_arr_count = count($tels_arr);
                if($param["coupon_number"] < ( $tels_arr_count * $param['limit_number']) && $param['coupon_number'] > 0){
                    BaseData::getInstance()->delete("\\Shop\\Models\\BaiyangCoupon",[
                        'couponId' => $insertID
                    ],"id = :couponId:");
                    return $this->arrayData('指定用户可领取数量超过发放数量', '/coupon/add', '', 'error');
                }

                foreach ($tels_arr as $phone) {
                    $user_id = BaiyangUserData::getInstance()->findUserIdByPhone($phone);
                    if($user_id != false){
                        for ($j = 0;$j < $param['limit_number'];$j++){
                            \Shop\Home\Services\CouponService::getInstance()->addCoupon([
                                'platform' => 'pc',
                                'coupon_sn' => $coupon_id,
                                'user_id' => $user_id
                            ]);
                        }
                    }
                }
                return $this->arrayData('添加成功！已经将优惠券发放给指定用户', '/coupon/list', '');
            }
        }

        if ($insertID > 0) {
            if (isset($data["is_activecode"]) && !empty($data["is_activecode"]) && isset($param["create_min_num"]) && !empty($param["create_min_num"])) {
                self::createCodeByQueue($param["create_min_num"], $data, $data["coupon_sn"],1);
            }
            return $this->arrayData('添加成功！', '/coupon/list', '');


        } else {
            return $this->arrayData('添加失败！', '/coupon/add', '', 'error');
        }
    }

    /**
     * in_Array 改写 提高性能
     * @param $item
     * @param $array
     * @return bool
     * @author 邓永军
     */
    public static function inArray($item, $array)
    {
        $flipArray = array_flip($array);
        return isset($flipArray[$item]);
    }

    private function makeRedisCodeSn()
    {
        $this->cache->selectDb(9);
        if ($this->cache->lLen('code_all') > 0) {
            $code_sn = mt_rand(1000000000, 9999999999);
            $code_all_array = $this->cache->lRange('code_all', 0, -1);
            if (self::inArray($code_sn, $code_all_array)) {
                return $this->makeRedisCodeSn();
            } else {
                $res = $this->cache->rPush('code_all', $code_sn);
                if ($res > 0) {
                    return $code_sn;
                } else {
                    return $this->makeRedisCodeSn();
                }
            }
        } else {
            $codeList = BaseData::getInstance()->select(
                'code_sn',
                '\Shop\Models\BaiyangCouponCode'
            );
            if (count($codeList) > 0) {
                foreach ($codeList as $codeData) {
                    $this->cache->rPush('code_all', $codeData['code_sn']);
                }
                return $this->makeRedisCodeSn();
            } else {
                return $this->makeCodeSn();
            }
        }
    }

    public function createCodeByQueue($num, $data, $coupon_sn = '', $is_queue = 0)
    {
        $this->cache->selectDb(9);
        if ($is_queue == 0) {
            for ($i = 0; $i < $num; $i++) {
                if ($data["validitytype"] == "1") {
                    //绝对时间
                    $data = [
                        "code_sn" => $this->makeRedisCodeSn(),
                        "coupon_sn" => $coupon_sn == '' ? $data["coupon_sn"] : $coupon_sn,
                        "is_exchange" => 0,
                        "exchange_time" => 0,
                        "exchange_user" => 0,
                        "start_provide_time" => $data["start_provide_time"],
                        "end_provide_time" => $data["end_provide_time"],
                        "start_use_time" => $data["start_use_time"],
                        "end_use_time" => $data["end_use_time"],
                        "add_time" => time(),
                        "validitytype" => $data["validitytype"],
                        "relative_validity" => 0
                    ];
                } else {
                    //相对时间
                    $data = [
                        "code_sn" => $this->makeRedisCodeSn(),
                        "coupon_sn" => $coupon_sn == '' ? $data["coupon_sn"] : $coupon_sn,
                        "is_exchange" => 0,
                        "exchange_time" => 0,
                        "exchange_user" => 0,
                        "start_provide_time" => $data["start_provide_time"],
                        "end_provide_time" => $data["end_provide_time"],
                        "start_use_time" => 0,
                        "end_use_time" => 0,
                        "add_time" => time(),
                        "validitytype" => $data["validitytype"],
                        "relative_validity" => $data["relative_validity"]
                    ];
                }
                BaseData::getInstance()->insert("\\Shop\\Models\\BaiyangCouponCode", $data, true);
            }
        } else {
            $info = [
                'coupon_sn' => $coupon_sn == '' ? $data['coupon_sn'] : $coupon_sn,
                'validitytype' => $data['validitytype'],
                'relative_validity' => $data["validitytype"] == "1" ? 0 : $data["relative_validity"],
                'start_provide_time' => $data['start_provide_time'],
                'end_provide_time' => $data['end_provide_time'],
                'start_use_time' => $data["validitytype"] == "1" ? $data["start_use_time"] : 0,
                'end_use_time' => $data["validitytype"] == "1" ? $data["end_use_time"] : 0,
                "add_time" => time()
            ];
            $json_data = json_encode($info, JSON_UNESCAPED_UNICODE);
            $client = $this->GearmanClient;
            $this->cache->setValue(CacheKey::COUPON_ADD_ACTCODE.$info['coupon_sn'], $num, CacheKey::HALF_AN_HOUR);
            for ($i = 0; $i < $num; $i++) {
                $client->addTaskBackground('create', $json_data);
            }
            $client->runTasks();
            //$res = system('/usr/local/php/bin/php /data/htdocs/www/cli/cli.php activecode worker 5');
        }

    }


    /**
     * @param $id
     * @param $num
     * @author 邓永军
     * @desc 激活码详细页面_添加激活码接口
     */
    public function addactcode($id, $num)
    {
        $baseData = BaseData::getInstance();
        $res = $baseData->select("id,coupon_sn,start_provide_time,end_provide_time,start_use_time,end_use_time,validitytype,relative_validity,coupon_number,bring_number,provide_type", "\\Shop\\Models\\BaiyangCoupon", ["id" => $id], "id = :id:")[0];
        $n = $res["coupon_number"] - $res["bring_number"] - $num;
        $couponRedis = $this->cache->getValue(CacheKey::COUPON_ADD_ACTCODE.$res['coupon_sn']);
        if(!empty($couponRedis)){
            return ["code" => "400", "info" => "激活码正在生成，请稍后再操作"];
        }
        $codeCounts = $baseData->countData([
            'table' => '\Shop\Models\BaiyangCouponCode',
            'where' => 'where coupon_sn = :coupon_sn:',
            'bind' => ['coupon_sn' => $res['coupon_sn']],
        ]);
        $codeNum = $res['provide_type'] == 2 ? 1 - $codeCounts : $res["coupon_number"] - $res["bring_number"] - $codeCounts;
        if($num > $codeNum){
            $tips = $res['provide_type'] == 2 ? '统一码' : '激活码';
            return ["code" => "400", "info" => "超过".$tips."剩余张数限制"];
        }
        if ($n >= 0) {
            $c = 0;
            for ($i = 0; $i < $num; $i++) {
                $c += 1;
                if ($res["validitytype"] == "1") {
                    $data = [
                        "code_sn" => $this->makeCodeSn(),
                        "coupon_sn" => $res["coupon_sn"],
                        "is_exchange" => 0,
                        "exchange_time" => 0,
                        "exchange_user" => 0,
                        "start_provide_time" => $res["start_provide_time"],
                        "end_provide_time" => $res["end_provide_time"],
                        "start_use_time" => $res["start_use_time"],
                        "end_use_time" => $res["end_use_time"],
                        "add_time" => time(),
                        "validitytype" => $res["validitytype"],
                        "relative_validity" => 0
                    ];
                } else {
                    $data = [
                        "code_sn" => $this->makeCodeSn(),
                        "coupon_sn" => $res["coupon_sn"],
                        "is_exchange" => 0,
                        "exchange_time" => 0,
                        "exchange_user" => 0,
                        "start_provide_time" => $res["start_provide_time"],
                        "end_provide_time" => $res["end_provide_time"],
                        "start_use_time" => 0,
                        "end_use_time" => 0,
                        "add_time" => time(),
                        "validitytype" => $res["validitytype"],
                        "relative_validity" => $res["relative_validity"]
                    ];
                }
                BaiyangCouponCodeData::getInstance()->insert("\\Shop\\Models\\BaiyangCouponCode", $data, true);
            }
            if ($c == $num) {
                return ["code" => "200", "info" => "生成成功"];
            } else {
                return ["code" => "400", "info" => "生成失败"];
            }
        } else {
            return ["code" => "400", "info" => "激活码数量超限"];
        }
    }

    /**
     * @author 邓永军
     * @param $input
     * @param int $type
     * @return mixed
     */
    public function getBrandList($input, $type = 0)
    {
        switch ($type) {
            case 0:
                $info = BrandData::getInstance()->select("id,brand_name", "\\Shop\\Models\\BaiyangBrands", ["id" => $input, "brand_name" => "%" . $input . "%"], "id in(:id:) OR brand_name LIKE :brand_name:");
                if ($info !== false) {
                    return $info;
                } else {
                    return "0";
                }
                break;
            case 1:
                $info = BrandData::getInstance()->select("id,brand_name", "\\Shop\\Models\\BaiyangBrands", ["id" => $input], "id = :id:");
                if ($info !== false) {
                    return $info;
                } else {
                    return "0";
                }
                break;
        }
    }

    /**
     * @author 邓永军
     * @param $ids
     * @desc 判断品牌是否存在ids
     * @return mixed
     */
    public function BrandValidIds($ids)
    {
        $ids_arr = explode(",", $ids);
        $v = "ok";
        $info = [];
        foreach ($ids_arr as $temp) {
            $result = BrandData::getInstance()->select("id,brand_name", "\\Shop\\Models\\BaiyangBrands", ["id" => $temp], "id = :id:");
            if ($result !== false) {
                $info[] = $result[0];
            } else {
                $v = "fail";
            }
        }
        if ($v == "ok") {
            return $info;
        } else {
            return "0";
        }
    }

    /**
     * @author 邓永军
     * @desc 生成优惠券编码
     * @return string
     */
    public function makeCouponSn()
    {
        $coupon_sn = date("ymd", time()) . str_pad("", 2, "0", STR_PAD_LEFT) . mt_rand(1000, 9999);
        $coupouCount = CouponData::getInstance()->count("\\Shop\\Models\\BaiyangCoupon", ["coupon_sn" => $coupon_sn], 'coupon_sn = :coupon_sn:');
        if ($coupouCount > 0) {
            return $this->makeCouponSn();
        }
        return $coupon_sn;
    }

    /**
     * @author 邓永军
     * @desc 生成6位激活码
     */
    public function makeCodeSn()
    {
        $code_sn = mt_rand(1000000000, 9999999999);
        $code_sn_count = BaiyangCouponCodeData::getInstance()->count("\\Shop\\Models\\BaiyangCoupon", ["code_sn" => $code_sn], 'code_sn = :code_sn:');
        if ($code_sn_count > 0) {
            $this->makeCodeSn();
        }
        return $code_sn;
    }

    /**
     * @desc 检查不参加活动的ids
     * @author 邓永军
     * @param $type
     * @param $ids
     * @return array|string
     */
    public function checkExistIds($type, $ids)
    {
        $ids_arr = explode(",", $ids);
        switch ($type) {
            case "category";
                $sign = "ok";
                $info = array_map(function ($v) use (&$sign) {
                    $result = BaiyangCategoryData::getInstance()->select("id,category_name", "\\Shop\\Models\\BaiyangCategory", ["id" => $v], "id = :id: ")[0];
                    if ($result !== false && count($result) > 0) {
                        return $result;
                    } else {
                        $sign = "fail";
                    }
                }, $ids_arr);
                if ($sign == "ok") {
                    return $info;
                } else {
                    return "0";
                }
                break;
            case "brand":
                $sign = "ok";
                $info = array_map(function ($v) use (&$sign) {
                    $result = BrandData::getInstance()->select("id,brand_name", "\\Shop\\Models\\BaiyangBrands", ["id" => $v], "id = :id: ")[0];
                    if ($result !== false && count($result) > 0) {
                        return $result;
                    } else {
                        $sign = "fail";
                    }
                }, $ids_arr);
                if ($sign == "ok") {
                    return $info;
                } else {
                    return "0";
                }
                break;
            case "single":
                $sign = "ok";
                $info = array_map(function ($v) use (&$sign) {
                    $result = BrandData::getInstance()->select(
                        "id,goods_name,goods_price as price,is_unified_price",
                        "\\Shop\\Models\\BaiyangGoods",
                        ["id" => $v],
                        "id = :id: ")[0];
                    if ($result !== false && count($result) > 0) {
                        if ($result['is_unified_price'] == '1') {
                            $info = BaseData::getInstance()->select('goods_price_pc,goods_price_app,goods_price_wap,goods_price_wechat',
                                "\\Shop\\Models\\BaiyangSkuInfo",
                                ["sku_id" => $v],
                                "sku_id = :sku_id: "
                            )[0];
                            $result['goods_price_pc'] = $info['goods_price_pc'];
                            $result['goods_price_app'] = $info['goods_price_app'];
                            $result['goods_price_wap'] = $info['goods_price_wap'];
                            $result['goods_price_wechat'] = $info['goods_price_wechat'];
                        }
                        return $result;
                    } else {
                        $sign = "fail";
                    }
                }, $ids_arr);
                if ($sign == "ok") {
                    return $info;
                } else {
                    return "0";
                }
                break;
            case "more":
                $sign = "ok";
                $info = array_map(function ($v) use (&$sign) {
                    $result = BrandData::getInstance()->select("id,goods_name,goods_price as price", "\\Shop\\Models\\BaiyangGoods", ["id" => $v], "id = :id: ")[0];
                    if ($result !== false && count($result) > 0) {
                        return $result;
                    } else {
                        $sign = "fail";
                    }
                }, $ids_arr);
                if ($sign == "ok") {
                    return $info;
                } else {
                    return "0";
                }
                break;
        }
    }


    /**
     * @author 邓永军
     * @desc 反向获取分类
     * @param $id
     * @return array
     */
    public function getReverseCategory($id)
    {
        $info = BaiyangCategoryData::getInstance()->select("id,category_name,has_child,pid,level", "\\Shop\\Models\\BaiyangCategory", ["id" => $id], "id = :id: ")[0];
        $this->revers_arr[] = [
            "level" => $info["level"],
            "cid" => $info["id"],
            "pid" => $info["pid"],
        ];
        if ($info["pid"] != "0") self::getReverseCategory($info["pid"]);
        sort($this->revers_arr);
        $_arr = [];
        array_map(function ($v) use (&$_arr) {
            $tmp = BaiyangCategoryData::getInstance()->select("id,category_name", "\\Shop\\Models\\BaiyangCategory", ["id" => $v["pid"]], "pid = :id: ");
            $_arr[] = [
                "level" => $v["level"],
                "cid" => $v["cid"],
                "info" => $tmp
            ];
        }, $this->revers_arr);
        return $_arr;
    }

    /**
     * @param $id
     * @return int
     * @author 邓永军
     * @desc 根据id获取激活码数量
     */
    public function getCodeCount($id)
    {
        $list = CouponData::getInstance()->select("b.coupon_sn", "\\Shop\\Models\\BaiyangCoupon as a", ["id" => $id], "a.id = :id:", "LEFT JOIN \\Shop\\Models\\BaiyangCouponCode as b ON a.coupon_sn = b.coupon_sn");
        if (empty($list[0]["coupon_sn"])) return 0;
        return count($list);
    }

    public function getDeliverList($param)
    {
        $whereStr = 'a.is_donate = 1';
        $bind = [];
        if (isset($param['param']['phone']) && !empty($param['param']['phone'])) {
            $whereStr .= ' AND b.phone = :phone:';
            $bind['phone'] = $param['param']['phone'];
        }
        if (isset($param['param']['user']) && !empty($param['param']['user'])) {
            $whereStr .= ' AND b.username = :username:';
            $bind['username'] = $param['param']['user'];
        }
        if (isset($param['param']['coupon_name']) && !empty($param['param']['coupon_name'])) {
            $whereStr .= ' AND c.coupon_name = :coupon_name:';
            $bind['coupon_name'] = $param['param']['coupon_name'];
        }
        if (isset($param['param']['coupon_scope']) && !empty($param['param']['coupon_scope']) && $param['param']['coupon_scope'] != '0') {
            $whereStr .= ' AND c.use_range = :coupon_scope:';
            $bind['coupon_scope'] = $param['param']['coupon_scope'];
        }
        if (isset($param['param']['code_sn']) && !empty($param['param']['code_sn'])) {
            $whereStr .= ' AND a.code_sn = :code_sn:';
            $bind['code_sn'] = $param['param']['code_sn'];
        }
        if(isset($param['param']['start_time']) && !empty($param['param']['start_time'])){
            $start_time = strtotime($param['param']['start_time']);
        }else{
            $start_time = time();
        }
        if(isset($param['param']['end_time']) && !empty($param['param']['end_time'])){
            $end_time = strtotime($param['param']['end_time']);
        }else{
            $end_time = time();
        }
        if ((isset($param['param']['start_time']) && !empty($param['param']['start_time'])) || (isset($param['param']['end_time']) && !empty($param['param']['end_time']))) {
            $whereStr .= ' AND a.add_time > :start_time: AND a.add_time < :end_time:';
            $bind['start_time'] = $start_time;
            $bind['end_time'] = $end_time;
        }
        $result = BaiyangCouponRecordData::getInstance()->select("a.remark,b.phone,b.username,c.use_range,c.coupon_name,a.add_time,a.is_donate,a.code_sn", "\\Shop\\Models\\BaiyangCouponRecord as a", $bind, $whereStr, "LEFT JOIN \\Shop\\Models\\BaiyangUser as b on a.user_id = b.id LEFT JOIN \\Shop\\Models\\BaiyangCoupon as c on a.coupon_sn = c.coupon_sn");
        if (empty($result)) {
            $count = 0;
        } else {
            $count = count($result);
        }
        if ($count < 1) {
            return ['res' => 'error', 'list' => '', 'voltValue' => $param['param']];
            exit;
        }
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $count;
        $pages['psize'] = isset($param['psize']) ? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size']) ? $param['size'] : 5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY a.add_time DESC LIMIT ' . $page['record'] . ',' . $page['psize'];
        $result = BaiyangCouponRecordData::getInstance()->select("a.remark,b.phone,b.username,c.use_range,c.coupon_name,a.add_time,a.is_donate,a.code_sn", "\\Shop\\Models\\BaiyangCouponRecord as a", $bind, $whereStr, "LEFT JOIN \\Shop\\Models\\BaiyangUser as b on a.user_id = b.id LEFT JOIN \\Shop\\Models\\BaiyangCoupon as c on a.coupon_sn = c.coupon_sn");
        foreach ($result as $key => $tmp) {
            switch ($result[$key]["use_range"]) {
                case Enum::ALL_RANGE:
                    $result[$key]["use_range"] = Enum::$ForScope[Enum::ALL_RANGE];
                    break;
                case Enum::CATEGORY_RANGE:
                    $result[$key]["use_range"] = Enum::$ForScope[Enum::CATEGORY_RANGE];
                    break;
                case Enum::BRAND_RANGE:
                    $result[$key]["use_range"] = Enum::$ForScope[Enum::BRAND_RANGE];
                    break;
                case Enum::SINGLE_RANGE:
                    $result[$key]["use_range"] = Enum::$ForScope[Enum::SINGLE_RANGE];
                    break;
            }
        }
        if (empty($result)) {
            return ['res' => 'error'];
        }
        foreach ($result as &$resultList) {
            if ($resultList['remark'] != '') {
                $resultList['phone'] = '未注册号码:' . $resultList['remark'];
            }
        }
        return ['res' => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }

    public function getCouponDetailList($param)
    {
        $get_coupon = CouponData::getInstance()->select("coupon_sn", "\\Shop\\Models\\BaiyangCoupon", ["id" => $param['id']], "id = :id:")[0];
        $table = '\Shop\Models\BaiyangCouponRecord';
        $selections = 'id,user_id,coupon_sn,order_sn,is_used,is_overdue,start_use_time,end_use_time,used_time,start_use_time,add_time';
        $conditions = ["coupon_sn" => $get_coupon['coupon_sn']];
        $whereStr = 'coupon_sn = :coupon_sn:';

        $counts = \Shop\Datas\BaseData::getInstance()->count($table, $conditions, $whereStr);
        if (empty($counts)) {
            return ['res' => 'error', 'list' => '', 'voltValue' => $param['param']];
            exit;
        }
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize']) ? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size']) ? $param['size'] : 5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY add_time DESC LIMIT ' . $page['record'] . ',' . $page['psize'];
        $result = \Shop\Datas\BaseData::getInstance()->select($selections, $table, $conditions, $whereStr);
        foreach ($result as $k => $userRes){
            $tmp = \Shop\Datas\BaseData::getInstance()->getData([
               'table' => '\Shop\Models\BaiyangUser',
                'column' => 'username',
                'where' => 'where id = :user_id: ',
                'bind' => [
                    'user_id' => $userRes['user_id']
                ]
            ],1);
            $result[$k]['username'] = $tmp['username'];
        }
        if (empty($result)) {
            return ['res' => 'error'];
        }
        return ['res' => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }


    /**
     * @author 邓永军
     * @desc 读取优惠券列表
     */
    public function getCouponList($param)
    {
        $table = '\Shop\Models\BaiyangCoupon';
        $selections = 'id,coupon_sn,coupon_name,provide_type,coupon_type,wap_platform,app_platform,pc_platform,wechat_platform,start_provide_time,end_provide_time,start_use_time,end_use_time,validitytype,relative_validity,register_bonus,coupon_number,use_range,is_cancel,bring_number,is_activecode';
        $conditions = [];
        $whereStr = 'id > 0';
        //输入的查询条件
        if (!empty($param['param']['coupon_sn'])) {
            $whereStr .= ' AND coupon_sn = :coupon_sn:';
            $conditions['coupon_sn'] = $param['param']['coupon_sn'];
        }
        if (!empty($param['param']['coupon_name'])) {
            $whereStr .= ' AND coupon_name like :coupon_name:';
            $conditions['coupon_name'] = '%' . $param['param']['coupon_name'] . '%';
        }
        if (!empty($param['param']['coupon_type'])) {
            $whereStr .= ' AND coupon_type = :coupon_type:';
            $conditions['coupon_type'] = $param['param']['coupon_type'];
        }
        if (!empty($param['param']['coupon_status'])) {
            switch ($param['param']['coupon_status']) {
                case Enum::COUPON_NOT_START:
                    $whereStr .= ' AND start_provide_time > :start_provide_time:';
                    $conditions['start_provide_time'] = time();
                    break;
                case Enum::COUPON_PROCESSING:
                    $whereStr .= ' AND start_provide_time < :start_provide_time:';
                    $conditions['start_provide_time'] = time();
                    $whereStr .= ' AND end_provide_time > :end_provide_time:';
                    $conditions['end_provide_time'] = time();
                    $whereStr .= ' AND ( coupon_number > bring_number OR coupon_number = 0 ) AND is_cancel = :is_cancel: ';
                    $conditions['is_cancel'] = 0;
                    break;
                case Enum::COUPON_HAVE_OVER;
                    $whereStr .= ' AND start_provide_time < :start_provide_time:';
                    $conditions['start_provide_time'] = time();
                    $whereStr .= ' AND end_provide_time > :end_provide_time:';
                    $conditions['end_provide_time'] = time();
                    $whereStr .= ' AND coupon_number <= bring_number AND coupon_number != 0 ';
                    break;
                case Enum::COUPON_HAVE_ENDED:
                    $whereStr .= ' AND end_provide_time < :end_provide_time: ';
                    $conditions['end_provide_time'] = time();
                    break;
                case Enum::COUPON_CANCEL:
                    $whereStr .= ' AND is_cancel = :is_cancel:';
                    $conditions['is_cancel'] = 1;
                    break;
            }

        }


        if (!empty($param['param']['coupon_platform']) && $param['param']['coupon_platform'] == Enum::SITE_PC) {
            $whereStr .= ' AND pc_platform = :pc_platform:';
            $conditions['pc_platform'] = 1;
        }
        if (!empty($param['param']['coupon_platform']) && $param['param']['coupon_platform'] == Enum::SITE_APP) {
            $whereStr .= ' AND app_platform = :app_platform:';
            $conditions['app_platform'] = 1;
        }
        if (!empty($param['param']['coupon_platform']) && $param['param']['coupon_platform'] == Enum::SITE_WAP) {
            $whereStr .= ' AND wap_platform = :wap_platform:';
            $conditions['wap_platform'] = 1;
        }
        if (!empty($param['param']['coupon_platform']) && $param['param']['coupon_platform'] == Enum::SITE_WECHAT) {
            $whereStr .= ' AND wechat_platform = :wechat_platform:';
            $conditions['wechat_platform'] = 1;
        }
        if (!empty($param['param']['coupon_scope'])) {
            $whereStr .= ' AND use_range = :use_range:';
            $conditions['use_range'] = $param['param']['coupon_scope'];
        }
        $counts = \Shop\Datas\BaseData::getInstance()->count($table, $conditions, $whereStr);
        if (empty($counts)) {
            return ['res' => 'error', 'list' => '', 'voltValue' => $param['param']];
            exit;
        }
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize']) ? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size']) ? $param['size'] : 5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY add_time DESC LIMIT ' . $page['record'] . ',' . $page['psize'];
        $result = \Shop\Datas\BaseData::getInstance()->select($selections, $table, $conditions, $whereStr);
        if (empty($result)) {
            return ['res' => 'error'];
        }
        foreach ($result as $key => $val) {
            $result[$key]['coupon_type'] = Enum::$OfferType[$val['coupon_type']];
            $result[$key]['use_range'] = Enum::$ForScope[$val['use_range']];

            //未开始
            if ($val["start_provide_time"] > time()) {
                $result[$key]['coupon_status'] = Enum::$CouponStatus[Enum::COUPON_NOT_START];
            }

            //领取中
            if ($val["start_provide_time"] < time() && $val["end_provide_time"] > time() && (($val["bring_number"] < $val["coupon_number"] && $val["coupon_number"] > 0) || ($val["coupon_number"] == 0 ))) {
                $result[$key]['coupon_status'] = Enum::$CouponStatus[Enum::COUPON_PROCESSING];
            }

            if ($val["start_provide_time"] < time() && $val["end_provide_time"] > time() && $val["coupon_number"] == 0) {
                $result[$key]['coupon_status'] = Enum::$CouponStatus[Enum::COUPON_PROCESSING];
            }
            //已领完
            if ($val["start_provide_time"] < time() && $val["end_provide_time"] > time() && ((($val["bring_number"] >= $val["coupon_number"]))) && $val["coupon_number"] != 0) {
                $result[$key]['coupon_status'] = Enum::$CouponStatus[Enum::COUPON_HAVE_OVER];
            }

            //已结束
            if ($val["end_provide_time"] < time()) {
                $result[$key]['coupon_status'] = Enum::$CouponStatus[Enum::COUPON_HAVE_ENDED];
            }

            //已取消
            if ($val["is_cancel"] == 1) {
                $result[$key]['coupon_status'] = Enum::$CouponStatus[Enum::COUPON_CANCEL];
            }
            $result[$key]['start_provide_time'] = date('Y-m-d H:i:s', $val['start_provide_time']);
            $result[$key]['end_provide_time'] = date('Y-m-d H:i:s', $val['end_provide_time']);
            $result[$key]['pc_platform'] = ($val['pc_platform'] == 1) ? 'PC、' : '';
            $result[$key]['app_platform'] = ($val['app_platform'] == 1) ? 'APP、' : '';
            $result[$key]['wap_platform'] = ($val['wap_platform'] == 1) ? 'WAP、' : '';
            $result[$key]['wechat_platform'] = ($val['wechat_platform'] == 1) ? '微商城、' : '';
            //活动平台
            $result[$key]['coupon_platform'] = rtrim($result[$key]['pc_platform'] . $result[$key]['app_platform'] . $result[$key]['wap_platform'] . $result[$key]['wechat_platform'],'、');
        }
        return ['res' => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }


    /**
     * @author 邓永军
     * @param $id
     * @desc 获取激活码详细
     */
    public function getCodeInfo($id, $current = 0)
    {
        $baseData = BaseData::getInstance();
        $couponArr = $baseData->getData([
            'table' => 'Shop\Models\BaiyangCoupon',
            'column' => 'id,coupon_sn,coupon_name,coupon_number,bring_number,provide_type',
            'where' => 'where id = :id:',
            'bind' => ['id' => $id],
        ],true);
        if(empty($couponArr)){
            return [
                'id' => 0,
                'coupon_sn' => '',
                'coupon_name' => '',
                'coupon_number' => 0,
                'bring_number' => 0,
                'provide_type' => 0,
                'code_sn_list' => [],
                'code_sn_list_count' => 0,
            ];
        }
        $res = $couponArr;
        $codeArr = $baseData->getData([
            'table' => 'Shop\Models\BaiyangCouponCode',
            'column' => 'code_sn',
            'where' => 'where coupon_sn = :coupon_sn:',
            'bind' => ['coupon_sn' => $res['coupon_sn']],
        ]);
        if(empty($codeArr)){
            $res['code_sn_list'] = [];
            $res['code_sn_list_count'] = 0;
            return $res;
        };
        $res['code_sn_list'] = array_column($codeArr,'code_sn');
        $res['code_sn_list_count'] = count($res["code_sn_list"]);
        try {
            if($res['provide_type'] == 2){
                $temp[] = $res["code_sn_list"][0];
                $res["code_sn_list_count"] = count($temp);
                $res["code_sn_list"] = $temp;
            }else{
                $it = new \LimitIterator(new \ArrayIterator($res["code_sn_list"]), $current, 100);
                foreach ($it as $k => $v) {
                    $temp[] = $it->current();
                }
                $res["code_sn_list"] = $temp;
            }
        } catch (\Exception $e) {
            $res["code_sn_list"] = 0;
        }
        return $res;
    }

    /**
     * @desc 修改是否取消
     * @param $mid
     * @param $request
     * @return array
     */
    public function postCancel($mid,$request = '')
    {
        if (empty($mid)) {
            return $this->arrayData('操作有误！', '', '', 'error');
        }
        $res = CouponData::getInstance()->update("is_cancel = :is_cancel:", "\\Shop\\Models\\BaiyangCoupon", ["id" => $mid, "is_cancel" => 1], "id = :id:");
        $url = $request ? '/coupon/list'.$request : '/coupon/list';
        if ($res !== false) {
            return $this->arrayData('取消成功！', $url);
        } else {
            return $this->arrayData('取消失败！', '', '', 'error');
        }
    }

    /**
     * @desc 修改注册送状态
     * @param $mid
     * @return int
     */
    public function postRegisterBonus($mid)
    {
        $result = CouponData::getInstance()->select("register_bonus", "\\Shop\\Models\\BaiyangCoupon", ["id" => $mid], "id = :id:")[0]["register_bonus"];
        if ($result == 0) {
            $result = 1;
        } else {
            $result = 0;
        }
        $res = CouponData::getInstance()->update("register_bonus = :register_bonus:", "\\Shop\\Models\\BaiyangCoupon", ["id" => $mid, "register_bonus" => $result], "id = :id:");
        if ($res !== false) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param $param
     * @author 邓永军
     * @desc 通过手机号码或者邮箱获取用户信息
     * @return array
     */
    public function getUserByInfo($param)
    {
        $input = $param["input"];
        if (isset($input) && !empty($input)) {
            $input_arr = explode(",", $input);
            $list_arr = [];
            foreach ($input_arr as $tmp) {
                $info = \Shop\Datas\BaiyangUserData::getInstance()->select("id,nickname,email,phone", '\Shop\Models\BaiyangUser', ["phone" => $tmp, "email" => $tmp], "phone = :phone: OR email = :email:")[0];
                if ($info == null) {
                    $info = [
                        'id' => 'unreg_' . $tmp,
                        'nickname' => '未注册用户',
                        'email' => '',
                        'phone' => $tmp
                    ];
                }
                $list_arr[] = $info;
            }
            return $list_arr;
        }
    }

    public function getUserByOrderId($order_ids)
    {
        $order_ids_arr = explode(",", $order_ids);
        $result = [];
        if (count($order_ids_arr) > 0) {
            foreach ($order_ids_arr as $order_id) {
                $result[] = BaiyangOrderData::getInstance()->select("a.order_sn,b.id,b.username as phone,b.nickname", "\\Shop\\Models\\BaiyangOrder as a", ["order_sn" => $order_id], "a.order_sn = :order_sn:", "LEFT JOIN \\Shop\\Models\\BaiyangUser as b on a.user_id = b.id")[0];
            }
            array_map(function ($v) {
                if ($v == null) return null;
            }, $result);
            return $result;
        }

    }

    /**
     * @author 邓永军
     * @desc 赠送优惠券_搜索活动
     * @param $sid
     * @param $coupon_name
     * @param $coupon_scope
     * @return mixed
     */
    public function getScopeActivities($sid, $coupon_name, $coupon_scope)
    {
        switch ($coupon_scope) {
            case "0":
                $concat_info = '';
                $where = [];
                if (isset($sid) && !empty($sid)) {
                    $concat_info .= "coupon_sn = :id: ";
                    $where["id"] = $sid;
                }
                if (isset($coupon_name) && !empty($coupon_name)) {
                    if ($concat_info == "") {
                        $concat_info .= "coupon_name LIKE :coupon_name:";
                    } else {
                        $concat_info .= "AND coupon_name LIKE :coupon_name:";
                    }
                    $where["coupon_name"] = "%" . $coupon_name . "%";
                }
                if ($concat_info == "") {
                    $concat_info .= 'start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                } else {
                    $concat_info .= ' AND start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                }
                $where["is_cancel"] = 0;
                $where['start_provide_time'] = time();
                $concat_info .= ' AND end_provide_time > :end_provide_time:';
                $where['end_provide_time'] = time();
                $concat_info .= ' AND ( (coupon_number > bring_number) OR (coupon_number = 0 ) )';
                $concat_info .= ' AND provide_type = 1 ';
                // $concat_info.=" ORDER BY add_time";
                $result = CouponData::getInstance()->select("id,coupon_sn,coupon_name,use_range,coupon_number", "\\Shop\\Models\\BaiyangCoupon", $where, $concat_info);

                return $result;
                break;
            case Enum::ALL_RANGE:
                $concat_info = '';
                $where = [];
                if (isset($sid) && !empty($sid)) {
                    $concat_info .= "coupon_sn = :id: ";
                    $where["id"] = $sid;
                }
                if (isset($coupon_name) && !empty($coupon_name)) {
                    if ($concat_info == "") {
                        $concat_info .= "coupon_name LIKE :coupon_name: ";
                    } else {
                        $concat_info .= "AND coupon_name LIKE :coupon_name: ";
                    }
                    $where["coupon_name"] = "%" . $coupon_name . "%";
                }
                if ($concat_info == "") {
                    $concat_info .= "use_range = :use_range:";
                } else {
                    $concat_info .= "AND use_range = :use_range:";
                }
                $where["use_range"] = Enum::ALL_RANGE;
                if ($concat_info == "") {
                    $concat_info .= 'start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                } else {
                    $concat_info .= ' AND start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                }
                $where["is_cancel"] = 0;
                $where['start_provide_time'] = time();
                $concat_info .= ' AND end_provide_time > :end_provide_time:';
                $where['end_provide_time'] = time();
                $concat_info .= ' AND ( (coupon_number > bring_number) OR (coupon_number = 0 ) )';
                $concat_info .= ' AND provide_type = 1 ';
                // $concat_info.=" ORDER BY add_time DESC";
                $result = CouponData::getInstance()->select("id,coupon_sn,coupon_name,use_range,coupon_number", "\\Shop\\Models\\BaiyangCoupon", $where, $concat_info);
                return $result;
                break;
            case Enum::CATEGORY_RANGE:
                $concat_info = '';
                $where = [];
                if (isset($sid) && !empty($sid)) {
                    $concat_info .= "coupon_sn = :id: ";
                    $where["id"] = $sid;
                }
                if (isset($coupon_name) && !empty($coupon_name)) {
                    if ($concat_info == "") {
                        $concat_info .= "coupon_name LIKE :coupon_name: ";
                    } else {
                        $concat_info .= "AND coupon_name LIKE :coupon_name: ";
                    }
                    $where["coupon_name"] = "%" . $coupon_name . "%";
                }
                if ($concat_info == "") {
                    $concat_info .= "use_range = :use_range:";
                } else {
                    $concat_info .= "AND use_range = :use_range:";
                }
                $where["use_range"] = Enum::CATEGORY_RANGE;
                if ($concat_info == "") {
                    $concat_info .= 'start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                } else {
                    $concat_info .= ' AND start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                }
                $where["is_cancel"] = 0;
                $where['start_provide_time'] = time();
                $concat_info .= ' AND end_provide_time > :end_provide_time:';
                $where['end_provide_time'] = time();
                $concat_info .= ' AND ( (coupon_number > bring_number) OR (coupon_number = 0 ) )';
                $concat_info .= ' AND provide_type = 1 ';
                //  $concat_info.=" ORDER BY add_time DESC";
                $result = CouponData::getInstance()->select("id,coupon_sn,coupon_name,use_range,coupon_number", "\\Shop\\Models\\BaiyangCoupon", $where, $concat_info);
                return $result;
                break;
            case Enum::BRAND_RANGE:
                $concat_info = '';
                $where = [];
                if (isset($sid) && !empty($sid)) {
                    $concat_info .= "coupon_sn = :id: ";
                    $where["id"] = $sid;
                }
                if (isset($coupon_name) && !empty($coupon_name)) {
                    if ($concat_info == "") {
                        $concat_info .= "coupon_name LIKE :coupon_name: ";
                    } else {
                        $concat_info .= "AND coupon_name LIKE :coupon_name: ";
                    }
                    $where["coupon_name"] = "%" . $coupon_name . "%";
                }
                if ($concat_info == "") {
                    $concat_info .= "use_range = :use_range:";
                } else {
                    $concat_info .= "AND use_range = :use_range:";
                }
                $where["use_range"] = Enum::BRAND_RANGE;
                if ($concat_info == "") {
                    $concat_info .= 'start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                } else {
                    $concat_info .= ' AND start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                }
                $where["is_cancel"] = 0;
                $where['start_provide_time'] = time();
                $concat_info .= ' AND end_provide_time > :end_provide_time:';
                $where['end_provide_time'] = time();
                $concat_info .= ' AND ( (coupon_number > bring_number) OR (coupon_number = 0 ) )';
                $concat_info .= ' AND provide_type = 1 ';
                $result = CouponData::getInstance()->select("id,coupon_sn,coupon_name,use_range,coupon_number", "\\Shop\\Models\\BaiyangCoupon", $where, $concat_info);
                return $result;
                break;
            case Enum::SINGLE_RANGE:
                $concat_info = '';
                $where = [];
                if (isset($sid) && !empty($sid)) {
                    $concat_info .= "coupon_sn = :id: ";
                    $where["id"] = $sid;
                }
                if (isset($coupon_name) && !empty($coupon_name)) {
                    if ($concat_info == "") {
                        $concat_info .= "coupon_name LIKE :coupon_name: ";
                    } else {
                        $concat_info .= "AND coupon_name LIKE :coupon_name: ";
                    }
                    $where["coupon_name"] = "%" . $coupon_name . "%";
                }
                if ($concat_info == "") {
                    $concat_info .= "use_range = :use_range:";
                } else {
                    $concat_info .= "AND use_range = :use_range:";
                }
                $where["use_range"] = Enum::SINGLE_RANGE;
                if ($concat_info == "") {
                    $concat_info .= 'start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                } else {
                    $concat_info .= ' AND start_provide_time < :start_provide_time: AND is_cancel = :is_cancel: ';
                }
                $where["is_cancel"] = 0;
                $where['start_provide_time'] = time();
                $concat_info .= ' AND end_provide_time > :end_provide_time:';
                $where['end_provide_time'] = time();
                $concat_info .= ' AND ( (coupon_number > bring_number) OR (coupon_number = 0 ) )';
                $concat_info .= ' AND provide_type = 1 ';
                //$concat_info.=" ORDER BY add_time DESC";
                $result = CouponData::getInstance()->select("id,coupon_sn,coupon_name,use_range,coupon_number", "\\Shop\\Models\\BaiyangCoupon", $where, $concat_info);
                return $result;
                break;
        }
    }

    /**
     * @author 邓永军
     * @desc 赠送优惠券_处理Logic
     */
    public function treatedCouponData($user_id, $coupon_info)
    {
        $message = [];
        if (empty($user_id) || count(json_decode($coupon_info)) > 0) {
            $message['status'] = 0;
            $message['info'] = '用户名或优惠券编号不能为空';
            return $message;
        } else {
            $user_id_arr = explode(",", $user_id);
            $coupon_info = str_replace("&quot;", "\"", $coupon_info);
            $coupon_info_arr = json_decode($coupon_info, JSON_UNESCAPED_UNICODE);


            $Is_unReg = function ($unreg_mobile) {
                preg_match('/unreg_[0-9]+/', $unreg_mobile, $mobile_match);
                return count($mobile_match);
            };

            try {

                foreach ($coupon_info_arr as $v) {
                    //id 优惠券编号
                    $id = $v["id"];
                    //num 赠送优惠券数量
                    $num = $v["num"];
                    $BaiyangCouponRecordData = BaiyangCouponRecordData::getInstance();
                    foreach ($user_id_arr as $uid) {
                        $new_uid = $uid;
                        for ($i = 0; $i < $num; $i++) {
                            //查找指定优惠券信息
                            $coupon = CouponData::getInstance()->select("a.id,a.coupon_sn,a.provide_type,a.coupon_number,a.start_use_time,a.end_use_time,a.limit_number,a.validitytype,a.relative_validity,a.bring_number,b.code_sn", "\\Shop\\Models\\BaiyangCoupon as a", ["id" => $id, "is_exchange" => 0], "a.id = :id: limit 1", "LEFT JOIN \\Shop\\Models\\BaiyangCouponCode as b ON a.coupon_sn = b.coupon_sn AND b.is_exchange = :is_exchange: ")[0];
                            if (empty($coupon)) {
                                throw new \Exception('获取优惠券信息失败');
                            }
                            if ($Is_unReg($new_uid) == 1) {
                                $uid = 0;
                                $this->cache->selectDb(9);
                                $mobile = explode('_', $new_uid);
                                $data = json_encode([
                                    'mobile' => $mobile[1],
                                    'msg' => '测试模板'
                                ], JSON_UNESCAPED_UNICODE);
                                $this->cache->rPush('sms.test', $data);
                                $g_bind = ["user_id" => $uid, "coupon_sn" => $coupon["coupon_sn"], "remark" => $mobile[1]];
                                $g_where = "user_id = :user_id: AND coupon_sn = :coupon_sn: AND remark = :remark: ";
                            } else {
                                $g_bind = ["user_id" => $uid, "coupon_sn" => $coupon["coupon_sn"]];
                                $g_where = "user_id = :user_id: AND coupon_sn = :coupon_sn:";
                            }

                            $count = BaseData::getInstance()->count("\\Shop\\Models\\BaiyangCouponRecord", $g_bind, $g_where);
                            if ($count >= $coupon["limit_number"] || $num > $coupon["limit_number"]) {
                                throw new \Exception('超过每人可领取的数量');
                            }
                            switch ($coupon["provide_type"]) {
                                case "1":
                                    //线上优惠券
                                    if (($coupon["coupon_number"] > $coupon["bring_number"]) || ($coupon["coupon_number"] == 0)) {
                                        $bring_coupon_number_one = BaseData::getInstance()->select('bring_number', '\Shop\Models\BaiyangCoupon', ["id" => $id], 'id = :id:')[0]['bring_number'];
                                        $coupon_number = $bring_coupon_number_one + 1;
                                        $res = BaseData::getInstance()->update("bring_number = :bring_number:", "\\Shop\\Models\\BaiyangCoupon", ["bring_number" => $coupon_number, "id" => $id], "id = :id:");
                                        if ($res) {
                                            if ($Is_unReg($new_uid) == 1) {
                                                $remark = explode('_', $new_uid);
                                                $rm = $remark[1];
                                            } else {
                                                $rm = '';
                                            }
                                            if ($coupon["validitytype"] == "2") {
                                                //相对时间
                                                $data = [
                                                    "user_id" => $uid,
                                                    "coupon_sn" => $coupon["coupon_sn"],
                                                    "order_sn" => "",
                                                    "is_used" => 0,
                                                    "is_overdue" => 0,
                                                    "start_use_time" => 0,
                                                    "end_use_time" => 0,
                                                    "used_time" => 0,
                                                    'add_time' => time(),
                                                    'validitytype' => $coupon["validitytype"],
                                                    'relative_validity' => $coupon["relative_validity"],
                                                    'remark' => $rm,
                                                    'is_donate' => 1,
                                                    'code_sn' => ''
                                                ];

                                            } else {
                                                $data = [
                                                    "user_id" => $uid,
                                                    "coupon_sn" => $coupon["coupon_sn"],
                                                    "order_sn" => "",
                                                    "is_used" => 0,
                                                    "is_overdue" => 0,
                                                    "start_use_time" => $coupon["start_use_time"],
                                                    "end_use_time" => $coupon["end_use_time"],
                                                    "used_time" => 0,
                                                    'add_time' => time(),
                                                    'validitytype' => $coupon["validitytype"],
                                                    'relative_validity' => $coupon["relative_validity"],
                                                    'remark' => $rm,
                                                    'is_donate' => 1,
                                                    'code_sn' => ''
                                                ];
                                            }

                                            $res = BaseData::getInstance()->insert("\\Shop\\Models\\BaiyangCouponRecord", $data, true);

                                            if ($res < 1) {
                                                throw new \Exception('领取失败');
                                            }
                                        } else {
                                            throw new \Exception('更新优惠券失败');
                                        }
                                    }
                                    break;
                                case "2":
                                    //统一码
                                    if (!empty($coupon['code_sn'])) {
                                        if (($coupon["coupon_number"] > $coupon["bring_number"]) || ($coupon["coupon_number"] == 0)) {
                                            $coupon_number = $coupon_number = $coupon["bring_number"] + 1;
                                            $res1 = BaseData::getInstance()->update("bring_number = :bring_number:", "\\Shop\\Models\\BaiyangCoupon", ["bring_number" => $coupon_number, "id" => $id], "id = :id:");
                                            $res2 = BaiyangCouponCodeData::getInstance()->update("exchange_user = :exchange_user: ,exchange_time = :exchange_time: ,is_exchange=:is_exchange:", "\\Shop\\Models\\BaiyangCouponCode", ["exchange_user" => $uid, "exchange_time" => time(), "is_exchange" => 1, "code_sn" => $coupon['code_sn'], "is_exchange_b" => 0], "code_sn = :code_sn: AND is_exchange = :is_exchange_b:");
                                            if ($coupon["validitytype"] == "2") {
                                                //相对时间
                                                $data = [
                                                    "user_id" => $uid,
                                                    "coupon_sn" => $coupon["coupon_sn"],
                                                    "order_sn" => "",
                                                    "is_used" => 0,
                                                    "is_overdue" => 0,
                                                    "start_use_time" => 0,
                                                    "end_use_time" => 0,
                                                    "used_time" => 0,
                                                    'add_time' => time(),
                                                    'validitytype' => $coupon["validitytype"],
                                                    'relative_validity' => $coupon["relative_validity"],
                                                    'is_donate' => 1,
                                                    'code_sn' => $coupon['code_sn']
                                                ];
                                                if ($Is_unReg($new_uid) == 1) {
                                                    $remark = explode('_', $new_uid);
                                                    $data['remark'] = $remark[1];
                                                }
                                            } else {
                                                $data = [
                                                    "user_id" => $uid,
                                                    "coupon_sn" => $coupon["coupon_sn"],
                                                    "order_sn" => "",
                                                    "is_used" => 0,
                                                    "is_overdue" => 0,
                                                    "start_use_time" => $coupon["start_use_time"],
                                                    "end_use_time" => $coupon["end_use_time"],
                                                    "used_time" => 0,
                                                    'add_time' => time(),
                                                    'validitytype' => $coupon["validitytype"],
                                                    'relative_validity' => $coupon["relative_validity"],
                                                    'is_donate' => 1,
                                                    'code_sn' => $coupon['code_sn']
                                                ];
                                                if ($Is_unReg($new_uid) == 1) {
                                                    $remark = explode('_', $new_uid);
                                                    $data['remark'] = $remark[1];
                                                }
                                            }
                                            $res = BaseData::getInstance()->insert("\\Shop\\Models\\BaiyangCouponRecord", $data, true);
                                            if ($res < 1) {
                                                throw new \Exception('领取失败');
                                            }
                                        }
                                    }
                                    break;
                                case "3":
                                    //激活码
                                    if (!empty($coupon['code_sn'])) {
                                        if (($coupon["coupon_number"] > $coupon["bring_number"]) || ($coupon["coupon_number"] == 0)) {
                                            $coupon_number = $coupon["bring_number"] + 1;
                                            $res1 = BaseData::getInstance()->update("bring_number = :bring_number:", "\\Shop\\Models\\BaiyangCoupon", ["bring_number" => $coupon_number, "id" => $id], "id = :id:");
                                            $res2 = BaseData::getInstance()->update("exchange_user = :exchange_user: ,exchange_time = :exchange_time: ,is_exchange=:is_exchange:", "\\Shop\\Models\\BaiyangCouponCode", ["exchange_user" => $uid, "exchange_time" => time(), "is_exchange" => 1, "code_sn" => $coupon['code_sn'], "is_exchange_b" => 0], "code_sn = :code_sn: AND is_exchange = :is_exchange_b: ");
                                            if ($coupon["validitytype"] == "2") {
                                                //相对时间
                                                $data = [
                                                    "user_id" => $uid,
                                                    "coupon_sn" => $coupon["coupon_sn"],
                                                    "order_sn" => "",
                                                    "is_used" => 0,
                                                    "is_overdue" => 0,
                                                    "start_use_time" => 0,
                                                    "end_use_time" => 0,
                                                    "used_time" => 0,
                                                    'add_time' => time(),
                                                    'validitytype' => $coupon["validitytype"],
                                                    'relative_validity' => $coupon["relative_validity"],
                                                    'is_donate' => 1,
                                                    'code_sn' => $coupon['code_sn']
                                                ];
                                                if ($Is_unReg($new_uid) == 1) {
                                                    $remark = explode('_', $new_uid);
                                                    $data['remark'] = $remark[1];
                                                }
                                            } else {
                                                $data = [
                                                    "user_id" => $uid,
                                                    "coupon_sn" => $coupon["coupon_sn"],
                                                    "order_sn" => "",
                                                    "is_used" => 0,
                                                    "is_overdue" => 0,
                                                    "start_use_time" => $coupon["start_use_time"],
                                                    "end_use_time" => $coupon["end_use_time"],
                                                    "used_time" => 0,
                                                    'add_time' => time(),
                                                    'validitytype' => $coupon["validitytype"],
                                                    'relative_validity' => $coupon["relative_validity"],
                                                    'is_donate' => 1,
                                                    'code_sn' => $coupon['code_sn']
                                                ];
                                                if ($Is_unReg($new_uid) == 1) {
                                                    $remark = explode('_', $new_uid);
                                                    $data['remark'] = $remark[1];
                                                }
                                            }
                                            $res = BaseData::getInstance()->insert("\\Shop\\Models\\BaiyangCouponRecord", $data, true);
                                            if ($res < 1) {
                                                throw new \Exception('领取失败');
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }

                }
            } catch (\Exception $e) {
                return ['code' => 400, 'info' => $e->getMessage()];
            }

            return ["code" => "200", "info" => "处理完成"];
        }

    }

    /**
     *
     * @desc 导出激活码csv
     * @param string $couponSn 优惠券编码
     * @author 吴俊华
     */
    public function exportData($couponSn)
    {
        $exportArr = BaseData::getInstance()->getData([
            'table' => 'Shop\Models\BaiyangCouponCode',
            'column' => 'code_sn',
            'where' => 'where coupon_sn = :coupon_sn: and exchange_time = 0',
            'bind' => ['coupon_sn' => $couponSn],
        ]);
        $csv = new Csv();
        $csv->export($exportArr);
    }

    /**
     * @author 邓永军
     * @desc 枚举Service
     * @return array
     */
    public function getCouponEnum()
    {
        $couponEnum = []; //促销活动枚举项
        //优惠类型
        $couponEnum['offerType'] = \Shop\Models\BaiyangCouponEnum::$OfferType;
        //适用范围
        $couponEnum['forScope'] = \Shop\Models\BaiyangCouponEnum::$ForScope;
        //适用平台
        $couponEnum['forPlatform'] = \Shop\Models\BaiyangCouponEnum::$ForPlatform;
        $configPlatform = (array)$this->config['shop_platform'];
	$configPlatform = $configPlatform ? array_values($configPlatform): ['WAP'];
        foreach ($couponEnum['forPlatform'] as $k => $platform) {
            if (!in_array($platform, $configPlatform)) {
                unset($couponEnum['forPlatform'][$k]);
            }
        }
        //活动状态
        $couponEnum['couponStatus'] = \Shop\Models\BaiyangCouponEnum::$CouponStatus;
        //药物类型
        $couponEnum['drugType'] = \Shop\Models\BaiyangCouponEnum::$CouponRx;
        return $couponEnum;

    }
}