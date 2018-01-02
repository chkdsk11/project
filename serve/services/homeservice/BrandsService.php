<?php
/**
 * Created by PhpStorm.
 * User: 林晓聪
 * Date: 2016/10/19
 * Time: 14:38
 */

namespace Shop\Home\Services;
use Shop\Home\Datas\BaiyangBrandsData;
use Shop\Models\CacheGoodsKey;

/**
 * Class BrandsService
 * @package Shop\Home\Services
 */
class BrandsService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;


    public function __construct()
    {
        $this->cache->selectDb(8);//选择库
        $this->BaiyangBrandsData = BaiyangBrandsData::getInstance();
    }

    /**
     * 获取品牌列表信息
     * @param $params['type'] （类型：0为pc端 1为移动端） （是否必传:是）
     * @param $params['page'] 第几页（是否必传:否）
     * @param $params['size'] 每页显示多少条（是否必传:否）
     * @param $platform 站点标识 app、wap、pc（默认pc）
     * @return array
     */
    public function getBrandsList($params=[],$platform='pc')
    {
        $params['page'] = (isset($params['page']) && !empty($params['page']) && is_numeric($params['page'])) ? trim($params['page']) : 1;
        $params['size'] = (isset($params['size']) && !empty($params['size']) && is_numeric( $params['size'])) ? trim($params['size']) : 99999;
        $params['limitStart'] = ($params['page'] - 1) *  $params['size'];
        $params['limitSize'] =  $params['size'];

        $cacheKeyCount = CacheGoodsKey::BRAND_COUNT . $platform;
        $cacheKeyData = CacheGoodsKey::BRAND_LIST . "{$platform}_{$params['page']}_{$params['size']}";
        $data = $this->cache->getValue($cacheKeyData);//获取缓存中品牌数据
        if(!$data)
        {
            //获取符合条件的品牌总数并缓存
            $this->cache->setValue($cacheKeyCount,$count = $this->BaiyangBrandsData->getBrandsCount($platform));
            //获取符合条件的品牌数据并缓存
            $this->cache->setValue($cacheKeyData,$data = $this->BaiyangBrandsData->getBrandsData($params,$platform),CacheGoodsKey::BRAND_CACHE_TIME);
        }
        //获取缓存中品牌总数
        $count = $this->cache->getValue($cacheKeyCount);//获取缓存
        //分页总数
        $pageCount = ceil($count/$params['size']);
        $pageCount = $pageCount > 0 ? $pageCount : 1;
        //返回数据
        if( empty($data) ){
            return $this->responseResult(\Shop\Models\HttpStatus::NOT_FOUND,\Shop\Models\HttpStatus::$HttpStatusMsg[\Shop\Models\HttpStatus::NOT_FOUND]);
        }else{
            return $this->responseResult(\Shop\Models\HttpStatus::SUCCESS,\Shop\Models\HttpStatus::$HttpStatusMsg[\Shop\Models\HttpStatus::SUCCESS],['count'=>$count,'pageCount'=>$pageCount,'data'=>$data]);
        }
    }

    /**
     * 根据品牌ID获取品牌商品信息
     * @param $param['brand_id'] 品牌ID（是否必传:是）
     * @param $params['page'] 第几页（是否必传:否 默认第一页）
     * @param $params['size'] 每页显示多少条（是否必传:否 默认显示20条）
     * @param $platform 站点标识 app、wap、pc（默认pc）
     * @return array
     */
    public function getBrandsGoodsList($params=[],$platform='pc')
    {
        if( empty($params['brand_id']) ){
            return $this->responseResult(\Shop\Models\HttpStatus::FAILED,\Shop\Models\HttpStatus::$HttpStatusMsg[\Shop\Models\HttpStatus::PARAM_ERROR]);
        }
        $params['brand_id'] = (isset($params['brand_id']) && is_numeric($params['brand_id'])) ? trim($params['brand_id']) : trim($params['app_brand_id']);
        $page = (isset($params['page']) && !empty($params['page']) && is_numeric($params['page'])) ? trim($params['page']) : 1;
        $size = (isset($params['size']) && !empty($params['size']) && is_numeric( $params['size'])) ? trim($params['size']) : 20;
        $params['limitStart'] = ($page - 1) * $size;
        $params['limitSize'] = $size;
        if($params['brand_id'])
        {
            $keyCacheCount = CacheGoodsKey::BRAND_GOODS_COUNT . "{$platform}_{$params['brand_id']}";
            $keyCacheData = CacheGoodsKey::BRAND_GOODS_LIST . "{$platform}_{$params['brand_id']}_{$params['page']}_{$params['size']}";
            //根据brand_id获取缓存中对应的品牌商品数据
            $data = $this->cache->getValue($keyCacheData);
            if(!$data)
            {
                //取符合条件的品牌商品总数并缓获存
                $this->cache->setValue($keyCacheCount,$count = $this->BaiyangBrandsData->getBrandsGoodsCount($params,$platform));
                //获取符合条件的品牌商品数据并缓存
                $this->cache-> setValue($keyCacheData,$data = $this->BaiyangBrandsData->getBrandsGoodsData($params,$platform),CacheGoodsKey::BRAND_CACHE_TIME);
            }
            //根据sku_id获取sku信息
            $skuData = SkuService::getInstance()->getSkuAll($data,$platform);
            //根据品牌ID获取缓存中对应的品牌商品总数
            $count = $this->cache->getValue($keyCacheCount);
            //分页总数
            $pageCount = ceil($count/$size);
            $pageCount = $pageCount > 0 ? $pageCount : 1;
        }
        //返回数据
        return $this->responseResult($skuData['status'],$skuData['explain'],['count'=>$count,'pageCount'=>$pageCount,'data'=>$skuData['data']]);
    }

    /**
     * 根据品牌ID获取一条品牌信息
     * @param $param['brand_id'] 品牌ID（是否必传:是）
     * @param $platform 站点标识 app、wap、pc（默认pc）
     * @return array
     */
    public function getOneBrandsInfo($brand_id,$platform='pc')
    {
        if( empty($brand_id) ){
            return $this->responseResult(\Shop\Models\HttpStatus::FAILED,\Shop\Models\HttpStatus::$HttpStatusMsg[\Shop\Models\HttpStatus::PARAM_ERROR]);
        }
        $cacheKey = CacheGoodsKey::ONE_BRAND_INFO . "{$brand_id}_{$platform}";
        //$this->cache->delete($cacheKey);
        $data = $this->cache->getValue($cacheKey);
        if(!$data)
        {
            $this->cache->setValue($cacheKey,$data = $this->BaiyangBrandsData->getBrandsOneInfo($brand_id,$platform),CacheGoodsKey::BRAND_CACHE_TIME);
        }

        if( empty($data) ){
            return $this->responseResult(\Shop\Models\HttpStatus::NOT_FOUND,\Shop\Models\HttpStatus::$HttpStatusMsg[\Shop\Models\HttpStatus::NOT_FOUND]);
        }else{
            return $this->responseResult(\Shop\Models\HttpStatus::SUCCESS,\Shop\Models\HttpStatus::$HttpStatusMsg[\Shop\Models\HttpStatus::SUCCESS],$data);
        }
    }


}