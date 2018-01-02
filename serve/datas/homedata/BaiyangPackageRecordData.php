<?php
/**
 * Created by PhpStorm.
 * User: 梁育权
 * Date: 2017/06/20
 */
namespace Shop\Home\Datas;
use Composer\Package\Loader\ValidatingArrayLoader;
use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\BaiyangSkuInfo;
use Shop\Models\BaiyangSkuDefault;
use Shop\Models\BaiyangVideo;
use Shop\Models\BaiyangSkuAd;
use Shop\Models\BaiyangSpu;
use Shop\Models\BaiyangGoodsImages;
use Shop\Models\BaiyangCategory;
use Shop\Models\BiayangMedicineTag;
use Shop\Models\BaiyangCategoryProductRule;
use Shop\Models\BaiyangProductRule;
use Shop\Models\BaiyangOrderForUser;
use Shop\Models\CacheGoodsKey;
use Shop\Models\BaiyangGoodsQuestionsAnswers;
use Shop\Models\BaiyangGoodsQuestionsAnswersRelation;
use Shop\Models\BaiyangBrand;
use Shop\Models\BaiyangSkuSupplier;

class BaiyangPackageRecordData extends BaseData
{
    protected static $instance=null;

    public static function getInstance($className = __CLASS__){
        return parent::getInstance($className);
    }
    /**
     * 获取优惠券大礼包数据
     *
     * @param int $user_id  用户id
     * @param int $package_id  大礼包id
     * @return array
     */
    public function getPackageRecordInfo($user_id, $package_id,$column = '*')
    {

        $PackageInfo = $this->getData([
            'column'=> $column,
            'table' => '\Shop\Models\BaiyangPackageRecord',
            'where' => 'where user_id = :user_id: and package_id = :package_id:',
            'bind'  => ['user_id'=> (int)$user_id,'package_id'=> (int)$package_id]
        ],1);
        return $PackageInfo;
    }
    /**
     * 获取优惠券大礼包数据
     *
     * @param int $phone  用户手机
     * @param int $package_id  大礼包id
     * @return array
     */
    public function getPackageRecordInfoByPhone($phone, $package_id,$column = '*')
    {

        $PackageInfo = $this->getData([
            'column'=> $column,
            'table' => '\Shop\Models\BaiyangPackageRecord',
            'where' => 'where phone = :phone: and package_id = :package_id:',
            'bind'  => ['phone'=> $phone,'package_id'=> (int)$package_id]
        ],1);
        return $PackageInfo;
    }
    /**
     * @desc 插入领取记录
     * @param $param
     * @return bool
     */
    public function insertPackageRecord($param){
        $addData = array(
            'table' => '\Shop\Models\BaiyangPackageRecord',
            'bind'  => array(
                'package_id'        => (int)$param['package_id'],
                'user_id'         => isset($param['user_id'])?(int)$param['user_id']:0,
                'phone'         => isset($param['phone'])?(string)$param['phone']:'',
                'add_time'    =>  time()
            )
        );
        if (!$this->addData($addData)) {
            return false;
        }
        return true;
    }

    /**
     * @desc 插入领取记录
     * @param $param
     * @return bool
     */
    public function updatePackageRecord($param){
        if(!$this->updateData([
            'column' => "user_id = {$param['user_id']}",
            'table' => "\\Shop\\Models\\BaiyangPackageRecord",
            'where' => "where phone = " . $param['phone']
        ])) return false;
        return true;
    }
}