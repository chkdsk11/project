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

class BaiyangPackageData extends BaseData
{
    protected static $instance=null;

    public static function getInstance($className = __CLASS__){
        return parent::getInstance($className);
    }
    /**
     * 获取优惠券大礼包数据
     *
     * @param int $userId  用户id
     * @return array
     */
    public function getPackageInfo($package_id, $column = '*')
    {
        $PackageInfo = $this->getData([
            'column'=> $column,
            'table' => '\Shop\Models\BaiyangPackage',
            'where' => 'where package_id = :package_id:',
            'bind'  => ['package_id'=> (int)$package_id]
        ],1);
        return $PackageInfo;
    }
    /**
     * 更新大礼包获取人数
     *
     * @param int $package_id  大礼包id
     * @return array
     */
    public function updatePackagePeopleNumber($param){
        if(!$this->updateData([
            'column' => "package_people_number = package_people_number+1",
            'table' => "\\Shop\\Models\\BaiyangPackage",
            'where' => "where package_id = {$param['package_id']}"
        ])) return false;
        return true;
    }
}