<?php
/**
 * Created by PhpStorm.
 * @author 梁伟
 * @date: 2026/8/16
 */

namespace Shop\Home\Services;
use Phalcon\Annotations\Adapter\Base;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaiyangUserGoodsPriceTagData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Services\BaseService;
use Shop\Home\Listens\PromotionGoodsDetail;
use Shop\Home\Datas\BaiyangGoodsStockChangeLogData;
use Shop\Models\HttpStatus;
use Phalcon\Events\Manager as EventsManager;

class SkuService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * @desc 重写实例化方法
     * @return class
     * @author 吴俊华
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance = new SkuService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('promotionInfo',new PromotionGoodsDetail());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }


    /**
     * 根据sku id 获取sku详细信息
     * @param array $param [一维数组]
     *          -int        sku_id      商品id
     *          -string     platform    平台【pc、app、wap】
     *          -int        user_id     用户id (临时用户或真实用户id)
     *          -int        is_temp     是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 梁伟
     */
    public function getSku($param)
    {
        if(!isset($param['sku_id']) || empty($param['sku_id']) || !isset($param['platform']) || empty($param['platform'])  || !isset($param['user_id']) || !isset($param['is_temp']) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();

        $res = $BaiyangSkuData->getSkuInfo($param['sku_id'],$param['platform']);
        if(!$res) return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO);
        //品规信息
        if($res['spu_id'] > 1){
            $param['spu_id'] = $res['spu_id'];
            $ruleTmp = $this->getSkuRuleAll($param);
            if($ruleTmp['status'] == 200){
                $res['ruleApp'] = $ruleTmp['data'];
            }else{
                $res['ruleApp'] = array();
            }
        }else{
            $res['ruleApp'] = array();
        }
        //商品药品标签
        $res['serviceInfoList'] = $BaiyangSkuData->getMedicineTag($res['is_global']?0:$res['drug_type'],$param['platform']);
        //判断是否已收藏
        if($param['is_temp'] == 0 && $param['user_id']){
            $isCollect = $BaiyangSkuData->getData(array(
                'table' => '\Shop\Models\BaiyangUserCollect',
                'column' => 'id',
                'where' => 'where user_id = :user_id: and goods_id = :goods_id:',
                'bind' => array(
                    'user_id' => $param['user_id'],
                    'goods_id' => $param['sku_id']
                )
            ));
            $res['isCollect'] = ($isCollect)?1:0;
        }else{
            $res['isCollect'] = 0;
        }
        //海外购商品逻辑
        if($res['is_global']){
            //商品图片信息
            $res['sku_img'] = $this->filterData('sku_image,sku_big_image',$BaiyangSkuData->getGoodsImg($res['id']));
            $sku_desc = $BaiyangSkuData->getGoodsExtension($res['id']);
            $res['sku_desc'] = ($param['platform']=='pc')?$sku_desc['goods_desc']:$sku_desc['body'];
            //获取品牌名
            $res['brand_name'] = '';
            if( $res['brand_id'] > 0 ){
                $brand = $BaiyangSkuData->getGoodsBrand($res['brand_id']);
                $res['brand_name'] = isset($brand['brand_name'])?$brand['brand_name']:'';
                $res['brand_logo'] = isset($brand['brand_logo'])?$brand['brand_logo']:'';
            }
            $res['sku_stock'] = $this->stock($param['sku_id'],$res['sku_stock']);
            $res['kindly_reminder'] = '海外购商品暂不支持开发票与换货操作';
            //促销信息
            $params['goods_id'] = $param['sku_id'];
            $params['platform'] = $param['platform'];
            $params['user_id'] = $param['user_id'];
            $params['is_temp'] = $param['is_temp'];
            $params['channel_subid'] = $param['channel_subid'];
            $params['udid'] = isset($param['udid'])?$param['udid']:'';
            $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId',$this,$params);

            $res['promotion'] = ($promotion['error'])?array():$promotion['data'];
            //处方药商品是否显示"提交需求"按钮 (1:显示 0:不显示)
            $res['display_add_cart'] = isset($res['promotion']['discountInfo']['display_add_cart'])?$res['promotion']['discountInfo']['display_add_cart']:0;
            $res['is_show_buy_now'] = isset($res['promotion']['discountInfo']['goods_status']) && $res['promotion']['discountInfo']['goods_status'] > 0 ? 0 : 1;
            return $this->uniteReturnResult(HttpStatus::SUCCESS,$res);
        }

        //获取视频信息
        if( isset($res['video']) && $res['video'] > 0 ){
            $video = $this->filterData('video_id,extend_images,video_unique,video_duration,video_desc',$BaiyangSkuData->getSkuVideo($res['video']));

            switch($param['platform']){
                case 'app':
                    $video = $this->getSkuVideoHtmlBlock($video);
                    $res['sku_desc'] = $video.$res['sku_desc'];
                    break;
                default:
                    $res['video'] = $video;
            }
        }
        //商品图片信息
        $sku_img = $this->filterData('sku_image,sku_big_image',$BaiyangSkuData->getSkuImg($res['id'],$res['spu_id']));

        //判断主图图片是否重复
        if(!(empty($res['small_path']) || empty($res['big_path']))){
            if($sku_img){
                $tmpAct = false;
                foreach($sku_img as $v){
                    if($v['sku_image'] == $res['small_path']){
                        $tmpAct = true;
                        break;
                    }
                }
                if($tmpAct){
                    $res['sku_img'] = $sku_img;
                }else{
                    $res['sku_img'] = array_merge([0=>array('sku_image'=>$res['small_path'],'sku_big_image'=>$res['big_path'])],is_array($sku_img)?$sku_img:array());
                }
            }else{
                $res['sku_img'] = [0=>array('sku_image'=>$res['small_path'],'sku_big_image'=>$res['big_path'])];
            }
        }else{
            $res['sku_img'] = $sku_img;
        }
        //获取品牌名
        $res['brand_name'] = '';
        $res['brand_logo'] = '';
        if( $res['brand_id'] > 0 ){
            $brand = $BaiyangSkuData->getSkuBrand($res['brand_id']);
            $res['brand_name'] = isset($brand['brand_name'])?$brand['brand_name']:'';
            $res['brand_logo'] = isset($brand['brand_logo'])?$brand['brand_logo']:'';
        }
        //说明书
        $res['instruction'] = array();
//        $instruction = $BaiyangSkuData->getSkuInstruction($param['sku_id']);
//        if($instruction){
//            foreach($instruction as $k=>$v){
//                if($k!='id' and $k!='sku_id'){
//                    if(!empty($v)){
//                        $res['instruction'] = $instruction;
//                    }
//                }
//            }
//        }
        //是否显示加入购物车
        $res['is_show_button'] = ($res['drug_type'] == 1)?0:1;

        //处理pc特有信息
        if($param['platform'] == 'pc'){
            //获取广告信息
            $ad = array();
            if( !empty($res['ad']) ){
                $adTmp = explode(':',$res['ad']);
                if( isset($adTmp[0]) && !empty($adTmp[0]) ){
                    $front = explode(',',$adTmp[0]);
                    foreach( $front as $k=>$v ){
                        $adTTmp = $BaiyangSkuData->getSkuAd($v);
                        if($adTTmp){
                            $ad['front'][$k] = $adTTmp;
                        }
                    }
                }
                if( isset($adTmp[1]) && !empty($adTmp[1]) ) {
                    $back = explode(',', $adTmp[1]);
                    foreach ($back as $k => $v) {
                        $adTTmp = $BaiyangSkuData->getSkuAd($v);
                        if($adTTmp){
                            $ad['back'][$k] = $adTTmp;
                        }
                    }
                }
            }
            $res['ad'] = $ad;
            //隐私配送和关于我们
            $article = $BaiyangSkuData->getArticle(19);
            $res['shipArticle'] = $article['content'];
            $article = $BaiyangSkuData->getArticle(20);
            $res['aboutArticle'] = $article['content'];
            //热门商品
            $hot = $this->getHotRecommendSku(['data'=>'hot','platform'=>$param['platform'],'channel_subid'=>$param['channel_subid'],'user_id'=>$param['user_id'],'is_temp'=>$param['is_temp'],'udid'=>isset($param['udid'])?$param['udid']:'']);

            if(isset($hot['data']['goods'])){
                $res['hot'] = $hot['data']['goods'];
            }else{
                $res['hot'] = array();
            }
            //获取分类信息
            $category = array();
            if(!empty($res['category_path']) && $res['category_path'] != -1){
                $categoryTmp = explode('/',$res['category_path']);
                foreach($categoryTmp as $k=>$v){
                    $category[$k] = $this->filterData('id,category_name,alias,pid',$BaiyangSkuData->getCategory($v));
                    $brother = $BaiyangSkuData->getSonCategory($category[$k]['pid']);
                    foreach($brother as $k1=>$v1){
                        // Hprose不能unset下标,否则/shop/vendor/hprose/hprose/src/Hprose/Writer.php line226 会报错
                        // if($v1['is_enable']) unset($brother[$k1]);
                        if(!$v1['is_enable']){
                            $brother_tmp[] = $v1;
                        }
                    }
                    $category[$k]['brother'] = $brother;
                }
            }
            $res['category'] = $category;
        }else{
            //处方药提示
            $res['kindly_reminder'] = '';
            if($res['drug_type'] == 1){
                $res['kindly_reminder'] = '本品为处方药。购买需凭医生有效处方，服用请遵医嘱，有关用药信息请咨询药师。';
            }
            //促销信息
            $params['goods_id'] = $param['sku_id'];
            $params['platform'] = $param['platform'];
            $params['user_id'] = $param['user_id'];
            $params['is_temp'] = $param['is_temp'];
            $params['channel_subid'] = $param['channel_subid'];
            $params['udid'] = isset($param['udid'])?$param['udid']:'';
            $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId',$this,$params);
            $res['promotion'] = ($promotion['error'])?array():$promotion['data'];
            //处方药商品是否显示"提交需求"按钮 (1:显示 0:不显示)
            $res['display_add_cart'] = isset($res['promotion']['discountInfo']['display_add_cart'])?$res['promotion']['discountInfo']['display_add_cart']:0;
            $res['is_show_buy_now'] = (isset($res['promotion']['discountInfo']['goods_status']) && $res['promotion']['discountInfo']['goods_status'] > 0) || $res['drug_type'] == 1 ? 0 : 1;
        }
        unset($res['bind_gift']);
        unset($res['is_use_stock']);
        unset($res['packaging_type']);
        unset($res['bind_gift']);
        unset($res['attribute_value_id']);
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$res);
    }

    /*
     * 视频
     */
    private function getSkuVideoHtmlBlock($value)
    {
        //视频html播放块
        $video_html_block = '';
        if (!empty($value['video_id'])) {
            $video['video_css'] = $this->config->domain->appImg . "/video/css/style.css";
            if ($value['extend_images']) {
                $extend_images = unserialize($value['extend_images']);
                $extend_images = json_decode($extend_images, true);
                $video['img_video_bg'] = $extend_images['img1'];
            } else {
                $video['img_video_bg'] = $this->config->domain->appImg . "/video/images/style.css";
            }
            $video['video_uv'] = $value['video_unique'];
            $video['img_video_play'] = $this->config->domain->appImg . "/video/images/play.png";
            $video['video_time'] = (int)($value['video_duration'] / 60);
            $second = $value['video_duration'] % 60;
            $second = ($second > 9) ? $second : "0{$second}";
            $video['video_time'] .= ":" . $second;
            $video['video_desc'] = $value['video_desc'];
            $video_html_block = "<link rel=\"stylesheet\" href=\"{$video['video_css']}\" /><div class=\"content\"><img src=\"{$video['img_video_bg']}\"/><div class=\"background\"></div><a class=\"play\" href=\"App://type=11&&&value={$video['video_uv']}\"><img src=\"{$video['img_video_play']}\"/></a><div class=\"text time\"><span>时长{$video['video_time']}</span></div><div class=\"text title\">{$video['video_desc']}</div></div>";

        }
        return $video_html_block;
    }

    /**
     * 计算可售库存
     */
    protected function stock($id,$stock)
    {
        $goodsStockChange = BaiyangGoodsStockChangeLogData::getInstance()->getGoodsStockChange(['goods_id'=>$id]);
        foreach($goodsStockChange as $v){
            if($stock <= 0) break;
            $stock = $stock + $v['change_num'];
        }
        return $stock;
    }

    /**
     * 根据sku id 获取商品较少数据
     * @param array $param [一维数组]
     *          -int        sku_id      商品id
     *          -string     platform    平台【pc、app、wap】
     *          -int        user_id     用户id (临时用户或真实用户id)
     *          -int        is_temp     是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 梁伟
     */
    public function getGoodsBear($param)
    {
        if(!isset($param['sku_id']) || empty($param['sku_id']) || !isset($param['platform']) || empty($param['platform'])  || !isset($param['user_id']) || !isset($param['is_temp']) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $res = $BaiyangSkuData->getSkuInfoLess($param['sku_id'],$param['platform']);
        if(!$res) return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO);
        //获取品牌名
        $res['brand_name'] = '';
        if( $res['brand_id'] > 0 ){
            $brand = $BaiyangSkuData->getSkuBrand($res['brand_id']);
            $res['brand_name'] = isset($brand['brand_name'])?$brand['brand_name']:'';
        }
        //获取分类名
        $res['category_name'] = '';
        if($res['category_id'] > 0){
            $category = $BaiyangSkuData->getCategory($res['category_id']);
            $res['category_name'] = $category['category_name'];
        }
        //获取最优促销价
        $params = array();
        $params['goodsList'][] = array(
            'goodsId'=>$res['id'],
            'price'=>$res['sku_price'],
        );
        $params['platform']=$param['platform'];
        $params['user_id']=$param['user_id'];
        $params['is_temp']=$param['is_temp'];
        // 判断用户是否绑定标签
        $params['channel_subid']=$param['channel_subid'];
        $params['udid']=isset($param['udid'])?$param['udid']:'';
        $params['tag_sign'] = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $param['user_id'], 'is_temp' => $param['is_temp']]);
        $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice',$this,$params);
        //获取销售价
        if($promotion && $promotion[0]['price']){
            $res['sku_price'] = $promotion[0]['price'];
            $res['discount_type'] = $promotion[0]['discount_type'];
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$res);
    }

    /**
     * 分类列表
     * @param array $param [一维数组]
     *          -string     platform  平台【pc、app、wap】
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @author 梁伟
     */
    public function mainCategory($param)
    {
        if(!$this->verifyRequiredParam($param)) return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        if($param['platform'] == 'pc'){
            //pc端
            $categuryList = $BaiyangSkuData->getMainCategoryPc();
        }else{
            $categuryList = $BaiyangSkuData->getMainCategoryApp();
            foreach($categuryList as $k=>$v){
                $categuryList[$k]['id']=$v['product_category_id'];
                unset($categuryList[$k]['product_category_id']);
                if(!empty($v['son'])){
                    foreach($v['son'] as $k1=>$v1){
                        $categuryList[$k]['son'][$k1]['id']=$v1['product_category_id'];
                        unset($categuryList[$k]['son'][$k1]['product_category_id']);
                        if(!empty($v1['son'])){
                            foreach($v1['son'] as $k2=>$v2){
                                $categuryList[$k]['son'][$k1]['son'][$k2]['id']=$v2['product_category_id'];
                                unset($categuryList[$k]['son'][$k1]['son'][$k2]['product_category_id']);
                            }
                        }
                    }
                }
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$categuryList);
    }

    /**
     * 促销活动
     * @param array $param [一维数组]
     *          -int        sku_id    商品id(必须)
     *          -string     platform  平台【pc、app、wap】
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @author 梁伟
     */
    public function getSkuPromotion($param)
    {
        if(!isset($param['sku_id']) || empty($param['sku_id'])  || !$this->verifyRequiredParam($param) || !isset($param['user_id']) || !isset($param['is_temp'])){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $params['goods_id'] = $param['sku_id'];
        $params['platform'] = $param['platform'];
        $params['user_id'] = $param['user_id'];
        $params['is_temp'] = $param['is_temp'];
        $params['channel_subid'] = $param['channel_subid'];
        $params['udid'] = isset($param['udid'])?$param['udid']:'';
        $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId',$this,$params);
        return $this->uniteReturnResult($promotion['code'],$promotion['data']);
    }

    /**
     * 商品相关问答
     * @param array $param [一维数组]
     *          -int    sku_id  商品id
     *          -int    pageStart    页数
     *          -int    pageSize   每页显示条数
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     *          -string     platform  平台【pc、app、wap】
     * @author 梁伟
     */
    public function getQuestionsAnswers($param)
    {
        if(!$param['sku_id'] || !$this->verifyRequiredParam($param)) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        $id    = $param['sku_id'];
        $page = isset($param['pageStart'])?$param['pageStart']:1;
        $limit = isset($param['pageSize'])?$param['pageSize']:8;
        $res = BaiyangSkuData::getInstance()->getQuestionsAnswers($id,$page,$limit);
        if(isset($res['content']) && $res['content']) return $this->uniteReturnResult(HttpStatus::SUCCESS,$res);
        if($param['platform']=='pc'){
            return $this->uniteReturnResult(HttpStatus::NO_DATA,[
                'pageCount' => 0,
                'pageStart' => 1,
                'pageSize' => 0,
                'pageNum' => 0,
                'content' => []
            ]);
        }else{
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
    }

    /**
     * 海外购商品逻辑
     * @param int $goods_id
     * @return array 商品集合
     * @author 梁伟
     */
    private function getGlobal($goods_id)
    {
        $goods_id = (int)$goods_id;
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $res = $BaiyangSkuData->getGlobalGoods($goods_id);
        if(!$res){
            return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO);
        }
        //获取税保、国旗、税保地区等信息
        $bonded = $BaiyangSkuData->getGoodsBonded($goods_id);
        $extend = $BaiyangSkuData->getGoodsExtend($goods_id);
        $res = array_merge($res,($bonded)?$bonded:array(),($extend)?$extend:array());
        $res['taxation'] = sprintf("%.2f", $res['sku_price']*$res['tax_rate']/100);
        return $res;
    }

    /**
     * 商品说明书和详情接口
     * @param array $param [二维数组]
     *          -int        sku_id      商品id
     *          -string     platform    平台【pc、app、wap】(必须)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array
     * @author 梁伟
     */
    public function getDetails($param)
    {
        if(!isset($param['sku_id']) || empty($param['sku_id']) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $res = $BaiyangSkuData->getSkuInfo($param['sku_id'],$param['platform']);
        //查询是否为海外购商品
        if(!$res){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        //海外购商品详情
        if($res['is_global'] == 1){
            $details = $BaiyangSkuData->getGoodsDetails($param['sku_id']);
            $details['ad'] = array();
            $details['instruction'] = '';
            return $this->uniteReturnResult(HttpStatus::SUCCESS,$details);
        }
        if($res['drug_type'] == 1 || $res['drug_type'] == 2 || $res['drug_type'] == 3){
            if(!$res){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            $res['instruction'] = array();
            $instruction = $BaiyangSkuData->getSkuInstruction($param['sku_id']);
            if($instruction){
                foreach($instruction as $k=>$v){
                    if($k!='id' and $k!='sku_id'){
                        if(!empty($v)){
                            $res['instruction'] = $instruction;
                        }
                    }
                }
            }
        }
        //获取广告信息
        if( !empty($res['ad']) ){
            $adTmp = explode(':',$res['ad']);
            $ad = array();
            if( isset($adTmp[0]) && !empty($adTmp[0]) ){
                $ad['front'] = explode(',',$adTmp[0]);
                foreach( $ad['front'] as $k=>$v ){
                    $ad['front'][$k] = $BaiyangSkuData->getSkuAd($v);
                }
            }
            if( isset($adTmp[1]) && !empty($adTmp[1]) ) {
                $ad['back'] = explode(',', $adTmp[1]);
                foreach ($ad['back'] as $k => $v) {
                    $ad['back'][$k] = $BaiyangSkuData->getSkuAd($v);
                }
            }
            $res['ad'] = $ad;
        }
        //获取视频信息
        if( isset($res['video']) && $res['video'] > 0 ){
            $video = $BaiyangSkuData->getSkuVideo($res['video']);
            $res['video'] = $this->filterData('video_id,extend_images,video_unique,video_duration,video_desc',$video);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$this->filterData('sku_desc,instruction,ad,video',$res));
    }

    /**
     * 我的收藏
     * @param array $param [二维数组]
     *          -string     platform      平台【pc、app、wap】
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        pageStart      页数
     *          -int        pageSize       条数
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     *          -string     act         [all全部|yes有货|no缺货]默认全部
     * @return array
     * @author 梁伟
     */
    public function getCollect($param)
    {
        if(!isset($param['platform']) || !$this->verifyRequiredParam($param) || empty($param['platform'])  || !isset($param['user_id']) || !isset($param['is_temp'])){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if($param['platform'] != 'pc'){
            return $this->getCollectModel($param);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $table = '\Shop\Models\BaiyangUserCollect';
        $collect = $BaiyangSkuData->getData([
            'column'=>'id collection_id,goods_id,add_time',
            'table'=>$table,
            'where'=>'where user_id=:user_id:',
            'order'=>' order by add_time desc limit 0,150',
            'bind'=>['user_id'=>$param['user_id']]
        ]);
        if(!$collect) return  $this->uniteReturnResult(HttpStatus::NO_DATA);

        $arr = array();
        $i = 0;
        $act = isset($param['act'])?$param['act']:'all';
        foreach($collect as $v){
            $sku = $this->filterData('id,spu_id,goods_image,sku_price,sku_stock,name,ruleName,sale,subheading_name,sku_market_price,drug_type',$BaiyangSkuData->getSkuInfo($v['goods_id'],$param['platform']));
            $sku['sku_stock'] = $this->stock($sku['id'],$sku['sku_stock']);
            if($act == 'all'){
                $arr[$i] = $sku;
                $arr[$i]['add_time'] = $v['add_time'];
                $arr[$i]['collection_id'] = $v['collection_id'];
                $arr[$i]['is_promote'] = 0;
                $i++;
            }else if($act == 'yes'){
                if($sku['sku_stock'] > 0 && $sku['sale']==1){
                    $arr[$i] = $sku;
                    $arr[$i]['add_time'] = $v['add_time'];
                    $arr[$i]['collection_id'] = $v['collection_id'];
                    $arr[$i]['is_promote'] = 0;
                    $i++;
                }
            }else if($act == 'no'){
                if($sku['sku_stock'] == 0 || $sku['sale']==0){
                    $arr[$i] = $sku;
                    $arr[$i]['add_time'] = $v['add_time'];
                    $arr[$i]['collection_id'] = $v['collection_id'];
                    $arr[$i]['is_promote'] = 0;
                    $i++;
                }
            }
        }
        if(!$arr) return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        $page['pageSize'] = isset($param['pageSize'])?$param['pageSize']:8;
        $page['pageSize'] = ($page['pageSize']<=40)?$page['pageSize']:40;
        $page['pageNum'] = $i;
        $page['pageCount'] = ceil($i/$page['pageSize']);
        $page['pageStart'] = isset($param['pageStart'])?$param['pageStart']:1;
        $page['pageStart'] = ($page['pageStart'] <= 1)?1:(($page['pageStart'] > $page['pageCount'])?$page['pageCount']:$page['pageStart']);

        $i = ($page['pageStart']-1)*$page['pageSize'];
        $len = $page['pageStart']*$page['pageSize'];
        $len = ($len > $page['pageNum'])?$page['pageNum']:$len;
        for($i;$i<$len;$i++){
            $param['goodsList'][] = $arr[$i];
        }
        $page['goods'] = $this->getPromotionGoodsInfo($param);
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$page);
    }

    /**
     * 获取移动端我的收藏
     */
    public function getCollectModel($param)
    {
        $page['pageSize'] = isset($param['pageSize'])?$param['pageSize']:8;
        $page['pageSize'] = ($page['pageSize']<=40)?$page['pageSize']:40;
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $table = '\Shop\Models\BaiyangUserCollect';
        $data = [
            'column'=>'id collection_id,goods_id,add_time',
            'table'=>$table,
            'where'=>'where user_id=:user_id:',
            'order'=>' order by add_time desc',
            'bind'=>['user_id'=>$param['user_id']]
        ];
        $num = $BaiyangSkuData->countData($data);
        if(!$num) return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        $page['pageNum'] = $num;
        $page['pageCount'] = ceil($num/$page['pageSize']);
        $page['pageStart'] = isset($param['pageStart'])?$param['pageStart']:1;
        $page['pageStart'] = ($page['pageStart'] <= 1)?1:(($page['pageStart'] > $page['pageCount'])?$page['pageCount']:$page['pageStart']);
        $limit = ' limit '.($page['pageStart']-1)*$page['pageSize'].','.$page['pageSize'];
        $data['order'] = $data['order'].$limit;
        $collect = $BaiyangSkuData->getData($data);
        foreach($collect as $k=>$v){
            $param['goodsList'][$k] = $this->filterData('id,spu_id,goods_image,sku_price,sku_stock,name,sale,subheading_name,sku_market_price,drug_type',$BaiyangSkuData->getSkuInfoLess($v['goods_id'],$param['platform']));
            $param['goodsList'][$k]['add_time'] = $v['add_time'];
            $param['goodsList'][$k]['collection_id'] = $v['collection_id'];
            $param['goodsList'][$k]['is_promote'] = 0;
            $param['goodsList'][$k]['ruleName'] = '';
        }
        $page['goods'] = $this->getPromotionGoodsInfo($param);
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$page);
    }

    /**
     * 获取浏览商品
     * @param array $param [二维数组]
     *          -string     platform      平台【pc、app、wap】
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        pageStart      页数
     *          -int        pageSize       条数
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array    浏览商品信息
     * @author 梁伟
     */
    public function getBrowse($param)
    {
        if(!isset($param['platform']) || !$this->verifyRequiredParam($param) || empty($param['platform'])  || !isset($param['user_id']) || !isset($param['is_temp'])){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        //获取浏览记录
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $page = isset($param['pageStart'])?(int)$param['pageStart']:1;
        $num = isset($param['pageSize'])?(int)$param['pageSize']:30;
        $num = ($num<=40)?$num:40;
        $browse = $BaiyangSkuData->getBrowse(array(
            'user_id'=>$param['user_id'],
            'is_temp'=>$param['is_temp'],
            'page'=>$page,
            'num'=>$num,
        ));
        if(!$browse) return $this->uniteReturnResult(HttpStatus::NO_DATA);
        $arr = array();
        $param['goodsList'] = array();
        foreach($browse as $k=>$v){
            if($k !== 'pages' && $k !== 'pageNum'){
                $tmp = $this->filterData('id,spu_id,goods_image,is_global,sale,sku_stock,sku_price,sku_market_price,name,subheading_name,drug_type',$BaiyangSkuData->getSkuInfoLess($v['goods_id'],$param['platform']));
                if(!$tmp)continue;
                //判断商品是否有效
                $param['goodsList'][$k] = $tmp;
                $param['goodsList'][$k]['drug_type'] = ($tmp['drug_type']>=0)?$tmp['drug_type']:0;
                $param['goodsList'][$k]['record_id'] = $v['id'];
                $param['goodsList'][$k]['time'] = $v['add_time'];
                $param['goodsList'][$k]['is_promote'] = 0;
                $param['goodsList'][$k]['goodsId'] = $tmp['id'];
                $param['goodsList'][$k]['price'] = $tmp['sku_price'];
                $param['goodsList'][$k]['goodsId'] = $tmp['id'];
                $param['goodsList'][$k]['price'] = $tmp['sku_price'];
            }else{
                if($k=='pages'){
                    $arr['pageCount'] = $v;
                    $arr['pageStart'] = $page;
                    $arr['pageSize'] = $num;
                }else if($k=='pageNum'){
                    $arr['pageNum'] = $v;
                }
            }
        }
        $arr['goods'] = $this->getPromotionGoodsInfo($param);
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$arr);
    }

    /**
     * 调用促销，获取最优价格和可售库存
     */
    private function getPromotionGoodsInfo($params)
    {
        $tag_sign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $params['user_id'], 'is_temp' => $params['is_temp']]);
        // 判断用户是否绑定标签
        $params['tag_sign'] = $tag_sign;
        foreach($params['goodsList'] as $k=>$v){
            $params['goodsList'][$k]['goodsId'] = $v['id'];
            $params['goodsList'][$k]['price'] = $v['sku_price'];
            $params['goodsList'][$k]['stock'] = $v['sku_stock'];
        }
        $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice',$this,$params);
        foreach($promotion as $k=>$v){
            $promotion[$k]['sku_stock'] = $v['stock'];
            $promotion[$k]['sku_price'] = $v['price'];
            unset($promotion[$k]['stock']);
            unset($promotion[$k]['price']);
            unset($promotion[$k]['goodsId']);
        }
        return $promotion;
    }

    /**
     * 添加浏览商品接口
     * @param array $param [二维数组]
     *          -int        user_id   用户id (临时用户或真实用户id)(必须)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)(必须)
     *          -int        category_id   分类ID
     *          -int        sku_id   商品ID
     *          -string     platform      平台【pc、app、wap】
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array
     * @author 梁伟
     */
    public function setBrowse($param)
    {
        if(!isset($param['sku_id']) || !$this->verifyRequiredParam($param) || empty($param['sku_id']) || !isset($param['category_id']) || !isset($param['user_id']) || !isset($param['is_temp'])){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        //查询记录是否存在
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $browse = $BaiyangSkuData->getBrowse($param);
        if(!$browse){
            $browsing_number=0;
        }else{
            $browsing_number = (int)$browse[0]['browsing_number'];
        }
        //存在修改,不存在添加
        $table = '\Shop\Models\BaiyangBrowingHistory';
        $sql['table'] = $table;
        if($browse){
            $sql['column'] = 'browsing_number=:browsing_number:,add_time=:add_time:';
            $sql['where'] = 'where id=:id:';
            $sql['bind'] = array(
                'browsing_number'=>$browsing_number+1,
                'add_time'=>time(),
                'id'=>$browse[0]['id'],
            );
            $res = $BaiyangSkuData->updateData($sql);
        }else{
            $sql['bind'] = array(
                'user_id'=>$param['user_id'],
                'goods_id'=>$param['sku_id'],
                'category_id'=>$param['category_id'],
                'is_temp'=>$param['is_temp'],
                'browsing_number'=>$browsing_number+1,
                'add_time'=>time(),
            );
            $res = $BaiyangSkuData->addData($sql);
        }
        if($res){
            return $this->uniteReturnResult(HttpStatus::SUCCESS);
        }else{
            return false;
        }
    }

    /**
     * 商品列表信息
     * @param array $param [二维数组]
     *          -array      goods_id    [商品id,商品id,商品id]
     *          -string     platform      平台【pc、app、wap】(必须)
     *          -int        user_id   用户id (临时用户或真实用户id)(必须)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)(必须)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array    商品信息
     * @author 梁伟
     */
    public function getSkuAll($param)
    {
        if(!isset($param['goods_id'])  || !$this->verifyRequiredParam($param) || !isset($param['user_id']) || !isset($param['is_temp']) ||
            empty($param['goods_id'])  || !is_array($param['goods_id']) ){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $array = array();
        $filter = 'id,spu_id,sku_price,sku_market_price,name,subheading_name,goods_image,drug_platform';
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $params = $param;
        unset($params['goods_id']);
        unset($params['platform']);
        foreach( $param['goods_id'] as $v ){
            $tmp = $BaiyangSkuData->getSkuInfo($v,$param['platform']);
            if( $tmp['sale'] == 1 && $tmp['spu_id'] > 0 ){
                $array[$tmp['id']] = $this->filterData($filter,$tmp);
                $params['goods_id'] = $v;
                $params['platform'] = $param['platform'];
                $params['channel_subid'] = $param['channel_subid'];
                $params['udid'] = isset($param['udid'])?$param['udid']:'';
                $array[$tmp['id']]['promotion'] = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId',$this,$params);
            }
        }
        if( !empty($array) ){
            return $this->uniteReturnResult(HttpStatus::SUCCESS,$array);
        }else{
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
    }

    /**
     * 多品规信息
     * @param $spu_id
     * @param $platform 客户端,如：pc，app，wap
     * @return array
     * @author 梁伟
     */
    private function getRule($spu_id,$platform)
    {
        if(!$spu_id || $spu_id <= 0) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $spu = $BaiyangSkuData->getSkuSpu((int)$spu_id);
        if( !isset($spu) || empty($spu['category_id']) ){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $rule = $BaiyangSkuData->getSkuRule($BaiyangSkuData->getCategory3($spu['category_id']));
        if( !$rule ){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $ruleName = array();
//        if( !empty($rule['name_id']))
        $ruleName[]['name'] = $BaiyangSkuData->getSkuRuleName($rule['name_id']);
//        if( !empty($rule['name_id2']))
        $ruleName[]['name'] = $BaiyangSkuData->getSkuRuleName($rule['name_id2']);
//        if( !empty($rule['name_id3']))
        $ruleName[]['name'] = $BaiyangSkuData->getSkuRuleName($rule['name_id3']);

        $rules = $BaiyangSkuData->getSkuRules($spu_id,$platform,false);
        if( !empty($rules) && is_array($rules) ){
            foreach( $rules as $v){
                $i = 0;
                $len = count($ruleName);
                for($i;$i<$len;$i++){
                    if( isset($v["rule_value$i"]) && !empty($v["rule_value$i"]) ){
                        if( !in_array($v["rule_value$i"],isset($ruleName[$i]['value'])?$ruleName[$i]['value']:array()) ){
                            $ruleName[$i]['value'][$v["rule_value$i"]] = $BaiyangSkuData->getSkuRuleName($v["rule_value$i"]);
                        }
                    }
                }
            }
        }
        //删除品规名为空的值
        foreach($ruleName as $k=>$v){
            if(!$v['name']){
                unset($ruleName[$k]);
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$ruleName);
    }

    /**
     * 获取可显示的多品规信息
     * @param $spu_id 商品id
     * @param $platform 客户端,如：pc，app，wap
     * @return array
     * @author 梁伟
     */
    private function getRuleAll($sku_id,$platform)
    {
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $sku = $this->filterData('spu_id,rule_value0,rule_value1,rule_value2',$BaiyangSkuData->getSkuInfo($sku_id,$platform));
        if(!$sku) return false;
        $rules = $BaiyangSkuData->getSkuRules($sku['spu_id'],$platform);
        if(!$rules) return false;
        $array = array();
        foreach($rules as $k=>$v){
            $array[0][] = $v['rule_value0'];
            $array[1][] = $v['rule_value1'];
            $array[2][] = $v['rule_value2'];
//            if($v['rule_value0'] != 0 && $v['rule_value1'] == $sku['rule_value1'] && $v['rule_value2'] == $sku['rule_value2']){
//                if(!isset($array[0]) || !in_array($v['rule_value0'],$array[0])){
//                    $array[0][] = $v['rule_value0'];
//                }
//            }
//            if($v['rule_value1'] != 0 && $v['rule_value0'] == $sku['rule_value0'] && $v['rule_value2'] == $sku['rule_value2']){
//                if(!isset($array[1]) || !in_array($v['rule_value1'],$array[1])) {
//                    $array[1][] = $v['rule_value1'];
//                }
//            }
//            if($v['rule_value2'] != 0 && $v['rule_value1'] == $sku['rule_value1'] && $v['rule_value0'] == $sku['rule_value0']){
//                if(!isset($array[2]) || !in_array($v['rule_value2'],$array[2])) {
//                    $array[2][] = $v['rule_value2'];
//                }
//            }
        }
        return $array;
    }

    /**
     * 药师回拨接口
     * @param array $param [一维数组]
     *          -string     uid         用户id或说明
     *          -int        is_temp     是否为临时用户(0不是1是)
     *          -string     recall      回访电话号码
     *          -int        gid         咨询商品id
     *          -int        channel_subid     渠道来源，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -int        group_id    套餐ID
     *          -string     udid        手机唯一id(app端必填)
     *          -string     platform      平台【pc、app、wap】
     */
    public function RecallDoc($param)
    {
        if(!preg_match("/^0?1[3|4|5|7|8][0-9]\d{8}$/", $param['recall'])) return $this->uniteReturnResult(HttpStatus::PHONE_ERROR);
        if(!isset($param['gid']) || !isset($param['group_id'])) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        if(!$this->verifyRequiredParam($param) || !isset($param['uid']) || !isset($param['uid']) || !isset($param['is_temp'])) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        //判断是否存在
        $addTime = $BaiyangSkuData->getRecallDoc(['recall'=>$param['recall'],'gid'=>$param['gid'],'group_id'=>$param['group_id']]);

        if($addTime && $addTime['add_time'] > 0 && $addTime['add_time'] > time()-(60*10))   return $this->uniteReturnResult(HttpStatus::RECALL_DOC);
        //获取用户信息
        if(!$param['is_temp']){
            $user = BaiyangUserData::getInstance()->getUserInfo((int)$param['uid']);
            if(!$user) return $this->uniteReturnResult(HttpStatus::RECALL_DOC_ERROR);
            $nickname = $user['nickname'];
            $phone = $user['phone'];
        }else{
            $nickname = '游客';
            $phone = '游客无账号';
        }
        //获取商品信息
        if((int)$param['gid']){
            $goods = $BaiyangSkuData->getSkuInfo((int)$param['gid'],$param['platform']);
        }

        $data = array(
            'uid'           => $phone,
            'nickname'      => $nickname,
            'is_temp'       => $param['is_temp'],
            'recall_phone'  => $param['recall'],
            'gid'           => isset($param['gid'])?(int)$param['gid']:0,
            'goods_name'    => isset($goods['name'])?$goods['name']:'',
            'goods_price'   => isset($goods['sku_price'])?$goods['sku_price']:'',
            'specifications'=> isset($goods['specifications'])?$goods['specifications']:'',
            'add_time'      => time(),
            'channel'       => $param['channel_subid'],
            'group_id'      => isset($param['group_id'])?(int)$param['group_id']:0,
        );
        $res = $BaiyangSkuData->setRecallDoc($data);
        if($res){
            return $this->uniteReturnResult(HttpStatus::SUCCESS,[
                'title'=>'请药师联系我',
                'message'=>"药师已经收到您的信息，\n将在1小时内跟您联系！",
            ]);
        }else{
            return $this->uniteReturnResult(HttpStatus::RECALL_DOC_ERROR);
        }
    }

    /**
     * 根据品规获取sku信息
     * @param array $param [一维数组]
     *          -int        spu_id    spu id(必须)
     *          -string     platform  平台【pc、app、wap】(必须)
     *          -string     ruleValue 品规信息 如：品规1 id+品规2 id+品规3 id
     *          -int        user_id   用户id (临时用户或真实用户id)(必须)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)(必须)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array
     * @author 梁伟
     */
    public function getRuleSku($param)
    {
        if(!$this->verifyRequiredParam($param) || !isset($param['spu_id']) || !isset($param['ruleValue']) || !isset($param['user_id']) || !isset($param['is_temp']) ||
            empty($param['spu_id']) || empty($param['ruleValue'])){
            return $this->uniteReturnResult(HttpStatus::RECALL_DOC_ERROR);
        }
        $rules = BaiyangSkuData::getInstance()->getSkuRules((int)$param['spu_id'],$param['platform']);
        if(!$rules) return $this->uniteReturnResult(HttpStatus::NOT_FOUND);
        foreach( $rules as $v ){
            $tmp = implode('+',[$v['rule_value0'],$v['rule_value1'],$v['rule_value2']]);
            if( $tmp == $param['ruleValue'] ){
                unset($param['spu_id']);
                unset($param['ruleValue']);
                $param['sku_id'] = $v['id'];
                return $this->getSku($param);
            }
        }
        return $this->uniteReturnResult(HttpStatus::NOT_FOUND);
    }

    /**
     * 根据spu id获取全部品规信息
     * @param array $param [一维数组]
     *          -int        spu_id    spu id
     *          -int        sku_id    商品ID
     *          -string     platform  平台【pc、app、wap】
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array
     * @author 梁伟
     */
    public function getSkuRuleAll($param)
    {
        if(!isset($param['is_temp']) || !isset($param['user_id']) || !$this->verifyRequiredParam($param) || empty($param['spu_id'])){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $spuAll = $BaiyangSkuData->getSkuRules($param['spu_id'],$param['platform'],false);
        if(!$spuAll) $this->uniteReturnResult(HttpStatus::NO_DATA,[]);
        $spu = $BaiyangSkuData->getSkuSpu($param['spu_id']);
        $rule = $BaiyangSkuData->getSkuRule($spu['category_id']);
        $ruleName1 = $BaiyangSkuData->getSkuRuleName($rule['name_id']);
        $ruleName2 = $BaiyangSkuData->getSkuRuleName($rule['name_id2']);
        $ruleName3 = $BaiyangSkuData->getSkuRuleName($rule['name_id3']);
        $array = array();
        $ruels = array();
        $ruels[0]['className'] = $ruleName1;
        $ruels[0]['classId'] = $rule['name_id'];
        $ruels[0]['valueList'] = array();
        $ruels[1]['className'] = $ruleName2;
        $ruels[1]['classId'] = $rule['name_id2'];
        $ruels[1]['valueList'] = array();
        $ruels[2]['className'] = $ruleName3;
        $ruels[2]['classId'] = $rule['name_id3'];
        $ruels[2]['valueList'] = array();
        $i = 0;
        foreach($spuAll as $k => $v){
            if($param['platform']!='pc'){
                $key = '';
                if($v['rule_value0'] ){
                    $tmp = $BaiyangSkuData->getSkuRuleName($v['rule_value0'],$rule['name_id']);
                    if($tmp){
                        $key .= $v['rule_value0'].';';
                    }
                }
                if($v['rule_value1']){
                    $tmp = $BaiyangSkuData->getSkuRuleName($v['rule_value1'],$rule['name_id2']);
                    if($tmp){
                        $key .= $v['rule_value1'].';';
                    }
                }
                if($v['rule_value2']){
                    $tmp = $BaiyangSkuData->getSkuRuleName($v['rule_value2'],$rule['name_id3']);
                    if($tmp){
                        $key .= $v['rule_value2'];
                    }
                }
                $key = trim($key,';');
            }else{
                $key = $v['rule_value0'].';'.$v['rule_value1'].';'.$v['rule_value2'];
            }

            //获取品规值信息
            $ruleValue1 = $BaiyangSkuData->getSkuRuleName($v['rule_value0'],empty($rule['name_id'])?-1:$rule['name_id']);
            $ruleValue2 = $BaiyangSkuData->getSkuRuleName($v['rule_value1'],empty($rule['name_id2'])?-1:$rule['name_id2']);
            $ruleValue3 = $BaiyangSkuData->getSkuRuleName($v['rule_value2'],empty($rule['name_id3'])?-1:$rule['name_id3']);

            if(!(empty($ruleValue1) && empty($ruleValue2) && empty($ruleValue3))){//获得商品信息
                $tmp = $this->filterData('small_path,sku_price,sku_stock,sale',$BaiyangSkuData->getSkuInfo($v['id'],$param['platform']));

                $array[$key]['small_path'] = $tmp['small_path'];
                $array[$key]['sku_id'] = $v['id'];
                $array[$key]['goods_price'] = $tmp['sku_price'];
                $stock = $this->func->getCanSaleStock(['goods_id'=>$v['id'],'platform'=>$this->config->platform]);
                $array[$key]['stock'] = (int)$stock;
                $sale = 0;
                if($tmp['sale']==0){
                    $sale = 1;
                }else if($stock == 0){
                    $sale = 2;
                }
                $array[$key]['goods_status'] = $sale;
                $array[$key]['rules'] = array(
                    ['name'=>$ruleName1,'value'=>$ruleValue1],
                    ['name'=>$ruleName2,'value'=>$ruleValue2],
                    ['name'=>$ruleName3,'value'=>$ruleValue3],
                );
            }
            $ruels[0]['valueList'] = $this->handleRule($ruels[0]['valueList'],$v,$ruleValue1,$param['sku_id']);
            $ruels[1]['valueList'] = $this->handleRule($ruels[1]['valueList'],$v,$ruleValue2,$param['sku_id'],1);
            $ruels[2]['valueList'] = $this->handleRule($ruels[2]['valueList'],$v,$ruleValue3,$param['sku_id'],2);

            $i++;
        }
        //处理APP格式
        if($param['platform']!='pc'){
            if(!empty($ruels[0]['className']) && !empty($ruels[0]['valueList'][0]['name'])) $res['gaugeList'][] = $ruels[0];
            if(!empty($ruels[1]['className']) && !empty($ruels[1]['valueList'][0]['name'])) $res['gaugeList'][] = $ruels[1];
            if(!empty($ruels[2]['className']) && !empty($ruels[2]['valueList'][0]['name'])) $res['gaugeList'][] = $ruels[2];
        }else{
            $res['gaugeList'] = $ruels;
        }
        $res['skuData'] = empty($array)?null:$array;
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$res);
    }

    /**
     * 处理品规信息
     * @param $param
     * @return bool
     * @author 梁伟
     */
    private function handleRule($rules,$v,$ruleValue,$sku_id,$key=0)
    {
        $tmp = false;
        foreach($rules as $k1 => $v1){
            if($v1['name']==$ruleValue){
                $tmp = $k1;
            }
        }
        if($tmp !== false){
            if($sku_id==$v['id']) $rules[$tmp]['isSelected'] = 1;
        }else{
            if(!empty($ruleValue)){
                $rules[] = ['name'=>$ruleValue,'valueId'=>$v['rule_value'.$key],'isSelected'=>($sku_id==$v['id'])?1:0];
            }
        }
        return $rules;
    }

    /**
     * 热门或推荐商品
     * @param $param =
     *          -string     data        推荐或热门(Recommend||hot)
     *          -int        pageSize    获取条数(默认5)
     *          -int        pageStart   当前页(默认1)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -string     udid        手机唯一id(app端必填)
     *          -string     platform  平台【pc、app、wap】
     * @return bool
     * @author 梁伟
     */
    public function getHotRecommendSku($param)
    {
        if(!$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if(!isset($param['user_id']) || !isset($param['is_temp'])){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        switch($param['data']){
            case 'hot':
                $data = 'is_hot';
                break;
            case 'Recommend':
                $data = 'is_recommend';
                break;
            default:
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $result = $BaiyangSkuData->getHotSku($data);
        if(!$result){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $array = array();
        $filter = 'id,spu_id,sku_price,sku_market_price,name,sku_stock,subheading_name,goods_image,small_path,drug_type,sale';

        foreach( $result as $v ){
            $tmp = $BaiyangSkuData->getSkuInfoLess($v['id'],$param['platform']);
            if( isset($tmp['sale']) && $tmp['sale'] == 1 ){
                $array[] = $this->filterData($filter,$tmp);
            }
        }
        $page['pageStart'] = isset($param['pageStart'])?$param['pageStart']:1;
        $page['pageSize'] = isset($param['pageSize'])?$param['pageSize']:5;
        $page['pageNum'] = count($array);
        $page['pageCount'] = ceil($page['pageNum']/$page['pageSize']);
        if($page['pageStart'] <= 0){
            $page['pageStart']=1;
        }
        if($page['pageStart'] > $page['pageCount']){
            $page['pageStart']=$page['pageCount'];
        }
        $start = ($page['pageStart']-1)*$page['pageSize'];
        $end = $start+$page['pageSize'];
        $end = ($end>$page['pageNum'])?$page['pageNum']:$end;

        for($i=$start;$i<$end;$i++){
            $param['goodsList'][] = $array[$i];
        }
        $goods = $this->getPromotionGoodsInfo($param);
        foreach($goods as $k=>$v){
            $goods[$k]['stock'] = $v['sku_stock'];
            unset($goods[$k]['sku_stock']);
        }
        $page['goods'] = $goods;
        if( $array ){
            return $this->uniteReturnResult(HttpStatus::SUCCESS,$page);
        }else{
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
    }

    /**
     * 评价订单商品列表
     * @param $param$param [一维数组]
     *          -int        user_id     用户id
     *          -string     order_sn    订单号
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     *          -string     platform  平台【pc、app、wap】
     * @return array
     * @author 梁伟
     */
    public function OrderCommentGoodsList($param)
    {
        if(!$this->verifyRequiredParam($param) || !isset($param['order_sn'])) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        if(!isset($param['user_id']) || empty($param['user_id'])) return $this->uniteReturnResult(HttpStatus::NOT_LOGINED);
        $baseData = BaseData::getInstance();
        $goodsList = $baseData->getData(array(
            'column'=>'goods_id,goods_name,goods_image,unit_price',
            'table'=>'\Shop\Models\BaiyangOrderDetail',
            'where'=>' where order_sn=:order_sn:',
            'bind'=>['order_sn'=>$param['order_sn']],
        ));
        foreach($goodsList as $k=>$v){
            $comment = $baseData->getData(array(
                'column'=>'id',
                'table'=>'\Shop\Models\BaiyangGoodsComment',
                'where'=>' where order_sn=:order_sn: and user_id=:user_id: and goods_id=:goods_id:',
                'bind'=>['order_sn'=>$param['order_sn'],'goods_id'=>$v['goods_id'],'user_id'=>$param['user_id']],
            ));
        }
        return $goodsList;
    }

    /**
     * 评价订单商品
     * @param $param [一维数组]
     *          -int        user_id     用户id
     *          -string     order_sn    订单号
     *          -int        sku_id      商品id
     *          -int        star        评级
     *          -string     contain     评价内容
     *          -int        is_anonymous    是否匿名
     *          -int        is_global   是否跨境评论 0否 1是
     *          -Array      upload_image    上传文件的可访问地址数组['url1','url2']
     *          -int        comment_id  评论id
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     *          -string     platform  平台【pc、app、wap】
     * @return array
     * @author 梁伟
     */
    public function addOrderComment($param)
    {
        //判断数据是否完整
        if(!$this->verifyRequiredParam($param)) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        if(!isset($param['order_sn']) || !isset($param['user_id'])) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);

        $keyword_filter = $this->KeywordFilter->keyword_match($param['contain']);
        if($keyword_filter){
            return $this->uniteReturnResult(HttpStatus::NOT_ENOUGH_KEYWORD_FILTER,[],[$keyword_filter]);
        }

        //判断是否为海外购
        if(strpos($param['order_sn'],'G')!==false || (isset($param['is_global']) && $param['is_global'] == 1)){
            $orderTable = '\Shop\Models\BaiyangKjOrder';
            $orderDetailTable = '\Shop\Models\BaiyangKjOrderDetail';
        }else{
            $orderTable = '\Shop\Models\BaiyangOrder';
            $orderDetailTable = '\Shop\Models\BaiyangOrderDetail';
        }

        $baseData = BaseData::getInstance();

        if(!isset($param['comment_id']) || empty($param['comment_id'])){
            //查看是否已经评价
            $comment = $baseData->getData(array(
                'table'=>'\Shop\Models\BaiyangGoodsComment',
                'column' => 'id',
                'where'=> 'where order_sn=:order_sn: and goods_id=:goods_id: and user_id=:user_id:',
                'bind'=>array(
                    'order_sn'=>$param['order_sn'],
                    'goods_id'=>$param['sku_id'],
                    'user_id'=>$param['user_id'],
                )
            ));
            if($comment) return $this->uniteReturnResult(HttpStatus::HAVE_ASSESS_GOOD);
        }

        //获得商品评论数和好评率
        $goods = BaiyangSkuData::getInstance()->getSkuInfo($param['sku_id'],$param['platform']);
        if($goods['drug_type'] == 1) return $this->uniteReturnResult(HttpStatus::COMMENT_DRUG_TYPE);
        $rate_of_praise = $baseData->countData(array(
            'table'=>'\Shop\Models\BaiyangGoodsComment',
            'where'=> 'where goods_id=:goods_id: and star>3',
            'bind'=>array(
                'goods_id'=>$param['sku_id'],
            )
        ));
        $comment_number = $baseData->countData(array(
            'table'=>'\Shop\Models\BaiyangGoodsComment',
            'where'=> 'where goods_id=:goods_id:',
            'bind'=>array(
                'goods_id'=>$param['sku_id'],
            )
        ));

        if(!isset($param['comment_id']) || !$param['comment_id']){
            //查询订单状态
            $orders  = $baseData->getData(array(
                'table'=>$orderTable,
                'column' => 'is_comment,status',
                'where'=> 'where user_id=:user_id: and order_sn=:order_sn:',
                'bind'=>array(
                    'order_sn'=>  $param['order_sn'],
                    'user_id'=>$param['user_id'],
                )
            ),true);
            if(empty($orders) || $orders['is_comment'] != 0 || $orders['status'] != 'evaluating'){
                return $this->uniteReturnResult(HttpStatus::RECALL_DOC_ERROR);
            }
        }

        //获得订单的所有商品
        $orderGoods = $baseData->getData(array(
            'table'=>$orderDetailTable,
            'column' => 'goods_id',
            'where'=> 'where order_sn=:order_sn: and goods_type=0',
            'bind'=>array(
                'order_sn'=>$param['order_sn'],
            )
        ));
        if(empty($orderGoods)) return $this->uniteReturnResult(HttpStatus::RECALL_DOC_ERROR);
        $act = true;
        foreach($orderGoods as $v){
            if($v['goods_id'] != $param['sku_id']){
                $tmp = $baseData->getData(array(
                    'table'=>'\Shop\Models\BaiyangGoodsComment',
                    'column' => 'id',
                    'where'=> 'where order_sn=:order_sn: and user_id=:user_id: and goods_id=:goods_id:',
                    'bind'=>array(
                        'order_sn'=>$param['order_sn'],
                        'user_id'=>$param['user_id'],
                        'goods_id'=>$v['goods_id'],
                    )
                ));
                if(!$tmp) $act = false;
            }else{
                $goodsTmp = 1;
            }
        }
        if(!isset($goodsTmp)) return $this->uniteReturnResult(HttpStatus::RECALL_DOC_ERROR);
        //获取用户信息
        $user = BaiyangUserData::getInstance()->getUserInfo($param['user_id']);

        //事务处理
        $this->dbWrite->begin();
        //初次评价
        if(!isset($param['comment_id']) || !$param['comment_id']){
            if (!isset($param['user_id']) || empty($param['user_id'])) return $this->uniteReturnResult(HttpStatus::NOT_LOGINED);
            if (!isset($param['sku_id']) || empty($param['sku_id'])) return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);

            if (!$user) return $this->uniteReturnResult(HttpStatus::ACCOUNT_NOT_EXISTED);
            $data = array(
                'user_id' => $param['user_id'],
                'order_sn' => isset($param['order_sn']) ? $param['order_sn'] : '',
                'goods_id' => $param['sku_id'],
                'star' => $param['star'],
                'contain' => $param['contain'],
                'nickname' => !empty($user['nickname']) ? $user['nickname'] : $user['username'],
                'headimgurl' => $user['headimgurl'],
                'level' => $user['level'],
                'is_anonymous' => isset($param['is_anonymous']) ? $param['is_anonymous'] : 0,
                'is_global' => isset($param['is_global']) ? $param['is_global'] : 0,
                'comment_id' => isset($param['comment_id']) ? $param['comment_id'] : 0,
                'created_at' => time(),
                'updated_at' => time(),
                'add_time' => time(),
                'agent_id' => 0,
                'title' => '',
                'click_number' => 0,
                'goods_name' => '',
                'message_reply' => '',
                'serv_id'=>'',
                'serv_nickname'=>'',
                'serv_created_at'=>0,
            );
            //添加数据
            $comment_id = $baseData->addData(['table'=>'\Shop\Models\BaiyangGoodsComment','bind'=>$data],true);
            if($comment_id){
                //修改商品信息
                if($param['star'] > 3){
//                    $rate_of_praise = ceil(($rate_of_praise*$goods['comment_number']+1)/($comment_number*100));
                    $rate_of_praise++;
                }
                $comment_number++;
//                    var_dump(ceil($rate_of_praise/$comment_number*100));die;
                    $baseData->updateData(array(
                    'table' => '\Shop\Models\BaiyangGoods',
                    'column'=> 'comment_number=:comment_number:,rate_of_praise=:rate_of_praise:',
                    'where'=> 'where id=:id:',
                    'bind'=> array(
                        'id'=>$param['sku_id'],
                        'comment_number'=>$comment_number,
                        'rate_of_praise'=>ceil($rate_of_praise/$comment_number*100),
                    ),
                ));
//                var_dump($updateGoods);die;
                if($act){
                    $updateOrder = $baseData->updateData(array(
                            'table' => $orderTable,
                            'column'=> 'is_comment=:is_comment:,status=:status:',
                            'where'=> 'where user_id=:user_id: and order_sn=:order_sn:',
                            'bind'=> array(
                                'user_id'=>$param['user_id'],
                                'order_sn'=>$param['order_sn'],
                                'is_comment'=>1,
                                'status'=>'finished',
                            ),
                        ));
                    if(!$updateOrder){
                        $this->dbWrite->commit();
                        return $this->uniteReturnResult(HttpStatus::COMMENT_ADD_FAILED);
                    }
                }
                //修改评论商品
                $updateOrder = $baseData->updateData(array(
                    'table' => $orderDetailTable,
                    'column'=> 'is_comment=:is_comment:',
                    'where'=> 'where goods_id=:goods_id: and order_sn=:order_sn:',
                    'bind'=> array(
                        'goods_id'=>$param['sku_id'],
                        'order_sn'=>$param['order_sn'],
                        'is_comment'=>1,
                    ),
                ));
                if(!$updateOrder){
                    $this->dbWrite->commit();
                    return $this->uniteReturnResult(HttpStatus::COMMENT_ADD_FAILED);
                }

            }
        }else{
            $comment_id = $param['comment_id'];
        }
        if($comment_id){
            if(!isset($param['upload_image']) || !is_array($param['upload_image']) || empty($param['upload_image'])){
                $this->dbWrite->commit();
                return $this->uniteReturnResult(HttpStatus::SUCCESS);
            }
            foreach($param['upload_image'] as $v){
                //把数据下载到本地处理
                $url = $this->moveImg($v);
                if($url && !is_array($url)){
                    $res = $baseData->addData([
                        'table'=>'\Shop\Models\BaiyangGoodsCommentImage',
                        'bind'=>array(
                            'comment_id' => $comment_id,
                            'comment_image' => $url,
//                            'status'=>0,
                        )],true);
                    if(!$res){
                        $this->dbWrite->rollback();
                        return $this->uniteReturnResult(HttpStatus::COMMENT_IMG_UPLOAD_FAILED);
                    }
                }else{
                    $this->dbWrite->rollback();
                    if($url && is_array($url)){
                        return $url;
                    }else{
                        return $this->uniteReturnResult(HttpStatus::COMMENT_IMG_UPLOAD_FAILED);
                    }
                }
            }
        }else{
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::COMMENT_ADD_FAILED);
        }
        //修改订单信息
        //事务提交
        $this->dbWrite->commit();
        //调用易复诊接口
        if($act){
            $this->func->prescriptionMatchOrder($user['union_user_id'], $param['order_sn'], 'finished', 1);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }


    /**
     * 更新商品缓存信息
     * @param $param array
     *          -sku_id int 商品ID
     * @return bool
     * @author 梁伟
     */
    public function updateGoodsRedis($param)
    {
        $id = (int)$param['sku_id'];
        if(!$id){
            return false;
        }
        $res = BaiyangSkuData::getInstance()->getGoodsRedis($id);
        if($res){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 更新商品缓存
     * @param string goodsIds 多个商品ID用英文逗号隔开
     * @return bool
     * @author 梁伟
     */
    public function updateGoodsAllRedis($goodsIds)
    {
        if(!$goodsIds){
            return false;
        }
        $res = BaiyangSkuData::getInstance()->delGoodsRedis($goodsIds);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @remark 同类推荐
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getSameSku($param = array())
    {
        //格式化参数
        $esUrl = $this->config->es->pcUrl. 'pces/getDataBySameCategoryId.do';
        $param['categoryId'] = isset($param['categoryId']) ? $param['categoryId'] : null;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['categoryId'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param, true);
    }

    /**
     * @remark 根据分类id获取商品列表
     * @param array $param array(
    'platform' => 'pc',
    'searchAttr' => '',
    'categoryId' => 75,
    'platform' => '',
    'pageStart' => 1,
    'pageSize' => 10
    )
     * @return \array[]
     * @author 杨永坚
     */
    public function getPcCategoryList($param = array())
    {
        //格式化参数
        $stime=microtime(true);
        $esUrl = $this->config->es->pcUrl. 'pces/getDataByCategoryId.do';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['categoryId'] = isset($param['categoryId']) ? (int)$param['categoryId'] : 0;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string)$param['searchAttr'] : null;
        $param['isGlobal'] = isset($param['isGlobal']) ? (int)$param['isGlobal'] : '';
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice']) ? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice'])? $param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['searchName'] = isset($param['searchName']) ? (string)$param['searchName'] : '';
        $param['brandId'] = isset($param['brandId']) && !empty($param['brandId']) ? (string)$param['brandId'] : '';
        $param['promotionType'] = isset($param['promotionType']) ? (string)$param['promotionType'] : '';
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        //参数错误
        if($param['platform'] != 'pc' || !$this->verifyRequiredParam($param) || $param['categoryId'] == 0 || $param['searchAttr'] === null || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        //取出分类面包屑
        $baseData = BaseData::getInstance();
        $catePath = $baseData->getData(array(
                'table' => '\Shop\Models\BaiyangCategory',
                'column' => 'category_path,category_name,level,meta_title,meta_keyword,meta_description',
                'where' => 'where id = :id:',
                'bind' => array(
                    'id' => $param['categoryId']
                )
            ),true
        );
        $categoryData = $goodsCategoryInfo = $tdkArr = array();
        if($catePath){
            $goodsCategoryInfo = $catePath;
            $catePathArr = explode('/', $catePath['category_path']);
            $count = count($catePathArr);
            foreach($catePathArr as $ck => $cv){
                if($ck == 0){
                    $categoryData[] = $this->getCategory($cv);
                    $categoryData[] = $count >= 2 ? $this->getCategory($catePathArr[$ck+1], $cv) : $this->getCategory($cv, $cv);
                }else{
                    $cData = $count - 1 > $ck ? $this->getCategory($catePathArr[$ck+1], $cv) : $this->getCategory($cv, $cv);
                    if($cData){
                        $categoryData[] = $cData;
                    }
                }
            }
            //取出tdk
            $cateParent = $catePath['category_name'];
            if($catePath['level'] != 1){
                $cateParent = $baseData->getData(array(
                    'table' => '\Shop\Models\BaiyangCategory',
                    'column' => 'category_name',
                    'where' => 'where id = :id:',
                    'bind' => array(
                        'id' => $catePathArr[0]
                    )
                ),true)['category_name'];
            }
            //最大tdk级别为3
            $level = $catePath['level'] > 3 ? 3 : $catePath['level'];
            $categoryEnum = \Shop\Models\BaiyangCategoryEnum::TDK;
            if(!isset($categoryEnum[$cateParent])){
                $cateParent = '默认';
            }
            $TDK = $categoryEnum[$cateParent][$level];
            $tdkArr = isset( $TDK )  ? $TDK : array() ;
            $tdkArr['title'] = empty($catePath['meta_title']) && isset($tdkArr['title']) ? str_replace('xx', $catePath['category_name'], $tdkArr['title']) : $catePath['meta_title'];
            $tdkArr['keyword'] = empty($catePath['meta_keyword']) && isset($tdkArr['keyword'])  ? str_replace('xx', $catePath['category_name'], $tdkArr['keyword']) : $catePath['meta_keyword'];
            $tdkArr['description'] = empty($catePath['meta_description'])  && isset($tdkArr['description']) ? str_replace('xx', $catePath['category_name'], $tdkArr['description']) : $catePath['meta_description'];
        }
        $isSelect = $baseData->countData(array(
            'table' => '\Shop\Models\BaiyangCategory',
            'where' => 'where pid = :pid:',
            'bind' => array(
                'pid' => $param['categoryId']
            )
        ));
        //请求es接口查询
        $data = $this->getEsApi($esUrl, $param);
        $data['data']['categoryData'] = $categoryData;
        $data['data']['goodsCategoryInfo'] = $goodsCategoryInfo;
        $data['data']['isSelect'] = $isSelect ? 1 : 0;
        $data['data']['tdk'] = $tdkArr;
        return $data;
    }

    /**
     * @remark 根据
     * @param array $param
     * array(
    'platform' => 'pc',
    'brandId' => '155',
    'platform' => '',
    'pageStart' => 1,
    'pageSize' => 10
    )
     * @return \array[]
     * @author 杨永坚
     */
    public function getPcBrandList($param = array())
    {
        //格式化参数
        $esUrl = $this->config->es->pcUrl. 'pces/getDataByBrandId.do';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['brandId'] = isset($param['brandId']) && !empty($param['brandId']) ? (int)$param['brandId'] : 0;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice']) ? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice'])? $param['upPrice'] : null;
        // 默认为""表示全部包括普通和海外购
        $param['isGlobal'] = isset($param['isGlobal']) ? $param['isGlobal'] : '';
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['promotionType'] = isset($param['promotionType']) ? (string)$param['promotionType'] : '';
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        //参数错误
        if($param['platform'] != 'pc' || !$this->verifyRequiredParam($param) || $param['brandId'] == 0 || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $brandsInfo = BaseData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangBrands as bs',
            'join' => 'INNER JOIN \Shop\Models\BaiyangBrandsExtend as be ON bs.id = be.brand_id',
            'column' => 'bs.id,bs.brand_name,bs.brand_desc,be.brand_logo,be.list_image',
            'where' => ' where bs.id = :id: and be.type = :type:',
            'bind' => array(
                'id' => $param['brandId'],
                'type' => 1
            )
        ), true);
        $brandsInfo['brand_desc'] = strip_tags(htmlspecialchars_decode($brandsInfo['brand_desc']));
        //请求es接口查询
        $data = $this->getEsApi($esUrl, $param);
        $data['data']['brandsInfo'] = $brandsInfo;
        $activity = $this->_eventsManager->fire('promotionInfo:getPromotionsByBrandId', $this, array(
            'platform' => $param['platform'],
            'brand_id' => $param['brandId'],
            'user_id' => isset($param['userId']) ? $param['userId'] : 0,
            'is_temp' => isset($param['isTemp']) ? $param['isTemp'] : 0
        ));
        $data['data']['activity'] = $activity['data'];
        return $data;
    }

    /**
     * @remark 搜索列表
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getPcKeywordList($param = array())
    {
        //格式化参数
        $esUrl = $this->config->es->pcUrl. 'pces/getDataBySearchId.do';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['searchName'] = isset($param['searchName']) ? (string)$param['searchName'] : null;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string)$param['searchAttr'] : null;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice']) ? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice'])? $param['upPrice'] : null;
        $param['isGlobal'] = isset($param['isGlobal']) ? $param['isGlobal'] : '';
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['brandId'] = isset($param['brandId']) && !empty($param['brandId']) ? (string)$param['brandId'] : '';
        $param['promotionType'] = isset($param['promotionType']) ? (string)$param['promotionType'] : '';
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        //参数错误
        if($param['platform'] != 'pc' || !$this->verifyRequiredParam($param) || $param['searchName'] === null || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }
    /**
     * @remark app根据分类id搜索
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getCategoryList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['categoryId'] = isset($param['categoryId']) ? (int)$param['categoryId'] : 0;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string)$param['searchAttr'] : '';
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : 0;
        $param['downPrice'] = isset($param['downPrice']) && !empty($param['downPrice']) ? (int)$param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice']) && !empty($param['upPrice']) ? (int)$param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch($param['platform']){
            case 'app':
                $esUrl = $this->config->es->appUrl. 'pces/getAppDataByCategory.do';
                break;
            case 'wap':
                $esUrl = $this->config->es->wapUrl. 'pces/getWapDataByCategory.do';
                break;
            case 'wechat':
                $esUrl = $this->config->es->wechatUrl. 'pces/getWapDataByCategory.do';
                break;
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }

    /**
     * @remark app关键词搜索
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getKeywordList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['searchName'] = isset($param['searchName']) ? (string)$param['searchName'] : null;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string)$param['searchAttr'] : null;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice']) ? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice']) ? $param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        $this->RedisCache->setValue('KeyWord1',$param);
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['searchName'] === null || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch($param['platform']){
            case 'app':
                $esUrl = $this->config->es->appUrl. 'pces/getAppData.do';
                break;
            case 'wap':
                $esUrl = $this->config->es->wapUrl. 'pces/getWapData.do';
                break;
            case 'wechat':
                $esUrl = $this->config->es->wechatUrl. 'pces/getWeChatData.do';
                break;
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }
    /**
     * @remark app联想词搜索
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getNumList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['searchName'] = isset($param['searchName']) ? (string)$param['searchName'] : null;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['searchName'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch($param['platform']){
            case 'app':
                $esUrl = $this->config->es->appUrl. 'pces/getAppNum.do';
                break;
            case 'wap':
                $esUrl = $this->config->es->wapUrl. 'pces/getWapNum.do';
                break;
            case 'wechat':
                $esUrl = $this->config->es->wechatUrl. 'pces/getWeChatNum.do';
                break;
            case 'pc':
                $param['word'] =  $param['searchName'];
                unset($param['searchName']);
                $esUrl = $this->config->es->pcUrl. 'pces/getWords.do';
                break;
        }

        //请求es接口查询
        return $this->getEsApi($esUrl, $param, 2);
    }

    /**
     * @remark 移动端品牌id商品列表
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getBrandList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['brandId'] = isset($param['brandId']) && !empty($param['brandId']) ? (int)$param['brandId'] : 0;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice'])? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice'])? $param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['brandId'] == 0 || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch($param['platform']){
            case 'app':
                $esUrl = $this->config->es->appUrl. 'pces/getAppDataByBrandId.do';
                break;
            case 'wap':
                $esUrl = $this->config->es->wapUrl. 'pces/getWapDataByBrandId.do';
                break;
            case 'wechat':
                $esUrl = $this->config->es->wechatUrl. 'pces/getWeChatDataByBrandId.do';
                break;
        }
        //请求es接口查询
        $res = $this->getEsApi($esUrl, $param);
        $brandInfo = BaseData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangBrands as bs',
            'join' => 'INNER JOIN \Shop\Models\BaiyangBrandsExtend as be ON bs.id = be.brand_id',
            'column' => 'bs.brand_name,be.list_image',
            'where' => ' where bs.id = :id: and be.type = :type:',
            'bind' => array(
                'id' => $param['brandId'],
                'type' => 1
            )
        ), true);
        $res['data']['brand'] = $brandInfo ? $brandInfo : array('brand_name' => '', 'list_image' => '');
        return $res;
    }

    /**
     * @remark 评论列表接口
     * @param array $param
     *        array(
     *          'platform' => 'pc', //pc、app、wap
     *          'goods_id' => '800' //商品id
     *          'page' => 1,        //分页
     *          'pageSize' => 10,   //每页显示数
     *          'type' => ''        //all所有评论，best好评，middle中评，bad差评，image图片评论
     *        )
     * @return \array[]
     * @author 杨永坚
     */
    public function getCommentList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['goods_id'] = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : '';
        if(!$this->verifyRequiredParam($param) || $param['goods_id'] <= 0 || $param['pageStart'] < 0 || $param['pageSize'] <= 0 || empty($param['type'])){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        //计算总评论数和好、中、差评数
        $commentCounts = BaiyangSkuData::getInstance()->getCommentCount($param);
        $data['commentCount']['all'] = count($commentCounts); //总评论数
        $data['commentCount']['best'] = 0;   //好评数
        $data['commentCount']['middle'] = 0; //中评数
        $data['commentCount']['bad'] = 0;    //差评数
        foreach($commentCounts as $value){
            if($value['star'] > 3){
                $data['commentCount']['best']++;
            }elseif($value['star'] == 3){
                $data['commentCount']['middle']++;
            }else{
                $data['commentCount']['bad']++;
            }
        }
        //评率
        $data['rateList']['bestRate'] = $data['commentCount']['all'] ? round($data['commentCount']['best'] / $data['commentCount']['all'] * 100) : 100;
        $data['rateList']['middleRate'] = $data['commentCount']['all'] ? round($data['commentCount']['middle'] / $data['commentCount']['all'] * 100) : 0;
        $data['rateList']['badRate'] = $data['commentCount']['all'] ? round($data['commentCount']['bad'] / $data['commentCount']['all'] * 100) : 0;
        //有图评论数
        $imgCommentArr = $baseData->getData(array(
            'table' => '\Shop\Models\BaiyangGoodsComment',
            'column' => 'id',
            'where' => 'where goods_id = :goods_id:',
            'bind' => array(
                'goods_id' => $param['goods_id']
            )
        ));
        if(!empty($imgCommentArr)){
            $imgCommentArr = implode(',', array_column($imgCommentArr, 'id'));
            $imageArr = $baseData->getData(array(
                'table' => '\Shop\Models\BaiyangGoodsCommentImage',
                'column' => 'comment_id',
                'where' => "where status = :status: and comment_id in($imgCommentArr) group by comment_id",
                'bind' => array(
                    'status' => 1
                )
            ));
        }
        $data['commentCount']['image'] = empty($imgCommentArr) || empty($imageArr) ? 0 : count($imageArr);
        $map['table'] = '\Shop\Models\BaiyangGoodsComment';
        $map['column'] = 'id,user_id,headimgurl,contain,star,is_anonymous,nickname,message_reply,serv_created_at,add_time';
        $map['order'] = 'order by add_time desc';
        if($param['type'] == 'image'){//图片评论
            $map['where'] = 'where goods_id = :goods_id:';
            $map['bind'] = ['goods_id' => $param['goods_id']];
            $commnetList = $baseData->getData($map);
            if(!empty($commnetList)){
                $commnetList = $this->forCommentVal($commnetList, $param, true);
                //分页
                $pageNum = count($commnetList);
                $count = ceil($pageNum / $param['pageSize']);
                $page = ($param['pageStart'] - 1) < 0 ? 0 : ($param['pageStart'] - 1);
                $pageCount = $count > 0 ? $count - 1 : 0;
                $page = $page > $pageCount ? $pageCount : $page;
                $start = $page * $param['pageSize'];

                $commnetList = array_slice($commnetList, $start, $param['pageSize']);
            }
        }else{
            switch($param['type']){
                case 'all':
                    $map['where'] = 'where goods_id = :goods_id:';
                    $map['bind'] = ['goods_id' => $param['goods_id']];
                    break;
                case 'best':
                    $map['where'] = 'where goods_id = :goods_id: and star > :star:';
                    $map['bind'] = ['goods_id' => $param['goods_id'], 'star' => 3];
                    break;
                case 'middle':
                    $map['where'] = 'where goods_id = :goods_id: and star = :star:';
                    $map['bind'] = ['goods_id' => $param['goods_id'], 'star' => 3];
                    break;
                case 'bad':
                    $map['where'] = 'where goods_id = :goods_id: and star < :star:';
                    $map['bind'] = ['goods_id' => $param['goods_id'], 'star' => 3];
                    break;
            }
            $pageNum = $baseData->countData($map);
            //分页
            $count = ceil($pageNum / $param['pageSize']);
            $page = ($param['pageStart'] - 1) < 0 ? 0 : ($param['pageStart'] - 1);
            $pageCount = $count > 0 ? $count - 1 : 0;
            $page = $page > $pageCount ? $pageCount : $page;
            $start = $page * $param['pageSize'];

            $map['limit'] = "limit $start,{$param['pageSize']}";
            $commnetList = $baseData->getData($map);
            $commnetList = empty($commnetList) ? '' : $this->forCommentVal($commnetList, $param);
        }
        $data['commentList'] = empty($commnetList) ? array() : $commnetList;
        $data['pageCount'] = $count;
        $data['pageNum'] = $pageNum;
        $data['pageStart'] = $param['pageStart'];
        $data['pageSize'] = $param['pageSize'];
        $code = empty($commnetList) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @remark 相关分类接口
     * @param $param=array
     * array(
     *      'cateogry_id'=>''分类id
     *          'type'=>'' 获取类型,list商品列表相关分类、detail详情页相关分类
     * )
     * @return \array[]
     * @author 杨永坚
     */
    public function getRelateCateogry($param)
    {
        //格式化参数
        $param['category_id'] = isset($param['category_id']) ? (int)$param['category_id'] : 0;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : '';
        if(!in_array($param['type'], ['list', 'detail']) || $param['category_id'] <= 0){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangCategory';
        $map['table'] = $table;
        $map['column'] = 'id,category_name';
        $map['limit'] = 'limit 10';
        switch($param['type']){
            case 'list':
                $map['where'] = 'where pid=:pid:';
                $catePid = $baseData->getData(array(
                    'table' => $table,
                    'column' => 'pid',
                    'where' => 'where id=:id:',
                    'bind' => array(
                        'id' => $param['category_id']
                    )
                ), true);
                //下一级
                $map['bind'] = array(
                    'pid' => $param['category_id']
                );
                $data = $baseData->getData($map);
                //为空取同级
                if(empty($data)){
                    $map['bind'] = array(
                        'pid' => $catePid['pid']
                    );
                    $data = $baseData->getData($map);
                }
                break;
            case 'detail':
                $cateList = $this->getCateBreadcrumb($param['category_id']);
                $map['where'] = 'where id != :id: and pid in('. implode(',', array_column($cateList, 'id')) .')';
                $map['bind'] = array(
                    'id' => $param['category_id']
                );
                $data = $baseData->getData($map);
                break;

        }
        return $this->uniteReturnResult(\Shop\Models\HttpStatus::SUCCESS, $data);
    }

    /**
     * @remark 收藏商品
     * @param $param = array
     *        array(
     *          'platform' => 'pc', //pc、app、wap
     *          'user_id' => '6' //用户id
     *          'goods_id' => '800' //商品id
     *        )
     * @return \array[]
     * @author 杨永坚
     */
    public function addCollect($param)
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['goods_id'] = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['user_id'] <= 0 || $param['goods_id'] <= 0){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        //判断是否已收藏
        $isCollect = $baseData->getData(array(
            'table' => '\Shop\Models\BaiyangUserCollect',
            'column' => 'id',
            'where' => 'where user_id = :user_id: and goods_id = :goods_id:',
            'bind' => array(
                'user_id' => $param['user_id'],
                'goods_id' => $param['goods_id']
            )
        ));
        if($isCollect){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::IS_COLLECT);
        }
        //添加到收藏
        $data = $baseData->addData(array(
            'table' => '\Shop\Models\BaiyangUserCollect',
            'bind' => array(
                'user_id' => $param['user_id'],
                'goods_id' => $param['goods_id'],
                'add_time' => time()
            )
        ));
        return $data ? $this->uniteReturnResult(\Shop\Models\HttpStatus::SUCCESS) : $this->uniteReturnResult(\Shop\Models\HttpStatus::FAILED);
    }

    /**
     * @remark 相关资讯
     * @param $param = array
     *        array(
     *          'platform' => 'pc', //pc、app、wap
     *          'goods_id' => '800' //商品id
     *        )
     * @return \array[]
     * @author 杨永坚
     */
    public function getRelatedList($param)
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['goods_id'] = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['goods_id'] <= 0){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $skuInfo = BaiyangSkuData::getInstance()->getSkuInfo($param['goods_id'], $param['platform']);
        $requestData['size'] = 40;
        $requestData['tags'] = trim(trim($skuInfo['sku_label'],','),',');
		$url = $this->config->infoUrl;
        $relatedData = json_decode($this->curl->sendPost($url, http_build_query($requestData)), true);
        $data = array();
        if(is_array($relatedData)){
            foreach ($relatedData as $k=>$v)
            {
                $search = array(" ","　","\n","\r","\t",'&nbsp;');
                $replace = array("","","","","","");
                $data[$k]['id'] = $v['id'];
                $data[$k]['url'] = $v['url'];
                $data[$k]['title'] = $v['post_title'];
                $data[$k]['post_content'] = str_replace($search, $replace, strip_tags($v['post_excerpt']));
            }
        }
        $code = empty($data) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @remark pc品牌馆
     * @param array $param
     * array(
     *      'platform' => 'pc',
     *      'page' => 1,
     *      'pageSize' => 10
     * );
     * @return \array[]
     * @author 杨永坚
     */
    public function getPcBrandStreetList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        if($param['platform'] != 'pc' || !$this->verifyRequiredParam($param) || $param['pageStart'] < 0 || $param['pageSize'] <= 0){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $map['table'] = '\Shop\Models\BaiyangBrandsExtend';
        $map['column'] = 'brand_id,list_image';
        $map['where'] = 'where type = :type: AND status = :status: AND is_hot = :is_hot:';
        $map['bind'] = array(
            'type' => 0,
            'status' => 1,
            'is_hot' => 1
        );
        $pageNum = $baseData->countData($map);
        //分页
        $count = ceil($pageNum / $param['pageSize']);
        $page = ($param['pageStart'] - 1) < 0 ? 0 : ($param['pageStart'] - 1);
        $pageCount = $count > 0 ? $count - 1 : 0;
        $page = $page > $pageCount ? $pageCount : $page;
        $start = $page * $param['pageSize'];

        $map['order'] = 'order by sort';
        $map['limit'] = "limit $start,{$param['pageSize']}";
        $brandList = $baseData->getData($map);
        foreach($brandList as $k => $v){
            $goodsList = $baseData->getData(array(
                'table' => '\Shop\Models\BaiyangSpu as sp',
                'join' => 'INNER JOIN \Shop\Models\BaiyangGoods as sk ON sk.spu_id = sp.spu_id',
                'column' => 'sk.id',
                'where' => ' where sp.brand_id = :brand_id:',
                'bind' => array(
                    'brand_id' => $v['brand_id']
                ),
                'order' => 'order by sales_number desc',
                'limit' => 'limit 3'
            ));
            $v['goodsList'] = array();
            foreach($goodsList as $item => $iv){
                $v['goodsList'][] = $this->filterData('id,name,goods_image,sku_price,sku_market_price', $BaiyangSkuData->getSkuInfo($iv['id'], $param['platform']));
            }
            $brandList[$k] = $v;
        }
        $data['brandList'] = $brandList;
        $data['pageCount'] = $count;
        $data['pageNum'] = $pageNum;
        $data['pageStart'] = $param['pageStart'];
        $data['pageSize'] = $param['pageSize'];
        $code = empty($brandList) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @remark 移动端品牌街
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getBrandStreetList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        if(!$this->verifyRequiredParam($param) || $param['pageStart'] < 0 || $param['pageSize'] <= 0){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        $data = $baseData->getData(array(
            'table' => '\Shop\Models\BaiyangBrands',
            'column' => 'add_time as last_time',
            'limit' => 'limit 1',
            'order' => "order by add_time desc"
        ), true);
        $data['brand_ad'] = null;
        $brandHot = $baseData->getData(array(
            'table' => '\Shop\Models\BaiyangBrands as br',
            'join' => 'INNER JOIN \Shop\Models\BaiyangBrandsExtend as be ON be.brand_id = br.id',
            'column' => 'br.id as brand_id,br.brand_name,be.brand_logo,be.mon_title as show_title,brand_describe as summary,be.list_image',
            'where' => "where be.type = :type: AND be.status = :status: AND is_hot = :is_hot: AND list_image != ''",
            'bind' => array(
                'type' => 1,
                'status' => 1,
                'is_hot' => 1
            ),
            'order' => 'order by be.sort asc'
        ));
        //判断品牌是否有优惠券
        $data['brand_hot']['total'] = count($brandHot);
        $data['brand_hot']['brand_list'] = $this->isCoupon($brandHot,$param['platform']);
        $map['table'] = '\Shop\Models\BaiyangBrands as br';
        $map['join'] = 'INNER JOIN \Shop\Models\BaiyangBrandsExtend as be ON be.brand_id = br.id';
        $map['column'] = 'br.id as brand_id,br.brand_name,be.brand_logo,be.mon_title as show_title,brand_describe as summary,be.list_image';
        $map['where'] = "where be.type = :type: AND be.status = :status: AND list_image != ''";
        $map['bind'] = array(
            'type' => 1,
            'status' => 1
        );
        $total = $baseData->countData($map);
        //分页
        $count = ceil($total / $param['pageSize']);
        $page = ($param['pageStart'] - 1) < 0 ? 0 : ($param['pageStart'] - 1);
        $pageCount = $count > 0 ? $count - 1 : 0;
        $page = $page > $pageCount ? $pageCount : $page;
        $start = $page * $param['pageSize'];

        $map['order'] = 'order by be.sort asc';
        $map['limit'] = "limit $start,{$param['pageSize']}";
        $brandList = $baseData->getData($map);
        $data['brand_all']['total'] = $total;
        $data['brand_all']['brand_list'] = $this->isCoupon($brandList,$param['platform']);
        $code = empty($data['brand_all']['brand_list']) && empty($data['brand_hot']['brand_list']) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @remark pc活动凑单列表
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getPcCollectList($param = array())
    {
        //格式化参数
        $esUrl = $this->config->es->pcUrl. 'pces/gettCollectData.do';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['promotionId'] = isset($param['promotionId']) ? (int)$param['promotionId'] : 0;
        $param['searchName'] = isset($param['searchName']) ? (string)$param['searchName'] : null;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string)$param['searchAttr'] : null;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice'])? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice']) ? $param['upPrice'] : null;
        $param['isGlobal'] = isset($param['isGlobal']) ? (int)$param['isGlobal'] : '';
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['brandId'] = isset($param['brandId']) && !empty($param['brandId']) ? (string)$param['brandId'] : '';
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;
        //参数错误
        if($param['platform'] != 'pc' || !$this->verifyRequiredParam($param) || $param['promotionId'] === 0 || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }

    /**
     * @remark 移动端活动凑单列表
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getCollectList($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['promotionId'] = isset($param['promotionId']) ? (int)$param['promotionId'] : 0;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string)$param['searchAttr'] : null;
        $param['type'] = isset($param['type']) ? (string)$param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int)$param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice']) ? $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice'])? $param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int)$param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int)$param['isTemp'] : null;

        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['promotionId'] === 0 || $param['userId'] === null || $param['isTemp'] === null){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch($param['platform']){
            case 'app':
                $esUrl = $this->config->es->appUrl. 'pces/getAppCollectData.do';
                break;
            case 'wap':
                $esUrl = $this->config->es->wapUrl. 'pces/getWapCollectData.do';
                break;
            case 'wechat':
                $esUrl = $this->config->es->wechatUrl. 'pces/getWeChatCollectData.do';
                break;
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }

    /**
     * @remark pc产品对比
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getCompareList($param = array())
    {
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['goodsId'] = isset($param['goodsId']) ? (string)$param['goodsId'] : '';
        //参数错误
        if($param['platform'] != 'pc' || !$this->verifyRequiredParam($param) || empty($param['goodsId'])){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $goodsId = explode(',', $param['goodsId']);
        $data = array();
        $attrDatas = array();
        $drugType = array(1, 2, 3);
        $prod_name_common_two = '';
        $brand_name_common_two = '';
        foreach($goodsId as $k => $v){
            $goodsList = $this->filterData('id,prod_name_common,name,goods_image,sku_price,drug_type,sale,sku_stock,manufacturer,specifications,period,comment_number,rate_of_praise,brand_id,category_id,attribute_value_id,spu_name', $BaiyangSkuData->getSkuInfo($v, $param['platform']));
            $attr = explode(',', trim($goodsList['attribute_value_id'], ','));
            $attrArr = array();
            foreach($attr as $key){
                $attrArr = $key ? array_merge($attrArr, explode(':', $key)) : '';
            }
            //品牌名
            $brandArr = $baseData->getData(array(
                'table' => '\Shop\Models\BaiyangBrands',
                'column' => 'brand_name',
                'where' => ' where id = :id:',
                'bind' => array(
                    'id' => $goodsList['brand_id']
                )
            ), true);
            $attrDatas['品牌'][$k] = empty($brandArr) ? '_ _' : $brandArr['brand_name'];
            $attrDatas['评价'][$k] = array($goodsList['rate_of_praise'], $goodsList['comment_number']);
            $attrDatas['规格'][$k] = $goodsList['specifications'];
            $attrDatas['生产厂家'][$k] = $goodsList['manufacturer']; 
            $attrDatas['保质期'][$k] = $goodsList['period'];
            //判断是否为药品
            if(in_array($goodsList['drug_type'], $drugType)){
                $drugData = $BaiyangSkuData->getSkuInstruction($v);
                $attrDatas['成份'][$k] = $drugData['component'];
                $attrDatas['适用症状'][$k] = $drugData['indication'];
                $attrDatas['用法用量'][$k] = $drugData['dosage'];
                $attrDatas['不良反应'][$k] = $drugData['adverse_reactions'];
                $attrDatas['禁忌'][$k] = $drugData['contraindications'];
            }

            //取出属性名
            $attrData = $baseData->getData(array(
                'table' => '\Shop\Models\BaiyangAttrName',
                'column' => 'id,attr_name',
                'where' => ' where category_id = :category_id: and status = :status:',
                'bind' => array(
                    'category_id' => $goodsList['category_id'],
                    'status' => 1
                )
            ));
            //取出属性名跟属性值
            if(!empty($attrArr)){
                foreach($attrData as $akey => $aval){
                    $attrVal = $baseData->getData(array(
                        'table' => '\Shop\Models\BaiyangAttrValue',
                        'column' => 'attr_value',
                        'where' => ' where attr_name_id = :attr_name_id: and id = :id:',
                        'bind' => array(
                            'attr_name_id' => $aval['id'],
                            'id' => $attrArr[array_search($aval['id'], $attrArr) + 1]
                        )
                    ), true);
                    $attrDatas[$aval['attr_name']][$k] = $attrVal ? $attrVal['attr_value'] : '_ _';
                }
            }
            //取出第一个商品的分类id跟通用名
            if($k == 0){
                $categoryId = $goodsList['category_id'];
                $prod_name_common = $goodsList['prod_name_common'] ? $goodsList['prod_name_common'] : $goodsList['name'];
                $brand_name_common = !empty($brandArr)?$brandArr['brand_name']:$goodsList['manufacturer'];
            }
            //取出第二个商品通用名或品牌名
            if($k == 1){
                $prod_name_common_two = $goodsList['prod_name_common'] ? $goodsList['prod_name_common'] : $brandArr['brand_name'];
                $brand_name_common_two = !empty($brandArr)?$brandArr['brand_name']:$goodsList['manufacturer'];
            }
            unset($goodsList['category_id']);
            unset($goodsList['attribute_value_id']);
            unset($goodsList['brand_id']);
            unset($goodsList['specifications']);
            unset($goodsList['manufacturer']);
            unset($goodsList['period']);
            unset($goodsList['comment_number']);
            unset($goodsList['rate_of_praise']);
            $data['goodsList'][] = $goodsList;
        }
        //处理属性格式
        $length = count($goodsId);
        $attrList = array();
        foreach($attrDatas as $key => $val){
            for($i=0; $i<$length; $i++){
                $val[$i] = !empty($val[$i]) ? $val[$i] : '_ _';
            }
            $attrValArr['attrName'] = $key;
            $attrValArr['attrVal'] = $val;
            //是否为相同
            if($key != '评价'){
                $attrValArr['isSame'] = count(array_unique($val)) == 1 ? 1 : 0;
            }else{
                //$attrValArr['isSame'] = count(array_unique(array_column($val, 0))) == 1 ? 1 : 0;//评价对比问题
                $isSameTemp1 = count(array_unique(array_column($val, 0))) == 1 ? 1 : 0;
                $isSameTemp2 = count(array_unique(array_column($val, 1))) == 1 ? 1 : 0;
                $attrValArr['isSame'] = $isSameTemp1 == 1 ? $isSameTemp2 : 0;
            }
            $attrList[] = $attrValArr;
        }
        $data['attrList'] = $attrList;
        switch($length) {
            case '2':
                $data['otherList']['tdk'] = array(
                    'title' => "({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}区别_价格对比_诚仁堂商城",
                    'keyword' => "({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}区别，{$brand_name_common}{$prod_name_common}的价格",
                    'description' => "{$prod_name_common}({$brand_name_common})和{$prod_name_common_two}({$brand_name_common_two})有什么区别，上诚仁堂商城药店查看({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}价格，适应病症，用法用量等药品信息区别，帮你找到最合适你的商品。",
                );
        break;
            case '3':
                $data['otherList']['tdk'] = array(
                    'title' => "{$prod_name_common}({$brand_name_common})和({$brand_name_common_two}){$prod_name_common_two}哪个牌子好_作用对比_诚仁堂商城",
                    'keyword' => "{$prod_name_common}({$brand_name_common})和({$brand_name_common_two}){$prod_name_common_two}哪个牌子好，({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}作用对比",
                    'description' => "{$prod_name_common}({$brand_name_common})和({$brand_name_common_two}){$prod_name_common_two}哪个牌子好，诚仁堂商城网上药店告诉你({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}哪个好，({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}作用对比,副作用,说明书,生产厂家比较。帮你找到最合适的商品。",
                );
                break;
            case '4':
                $data['otherList']['tdk'] = array(
                    'title'=>"药品对比-诚仁堂商城-要健康到诚仁堂",
                    'keyword'=>'',
                    'description'=>'',
                );
                break;
            default:
                $data['otherList']['tdk'] = array(
                    'title' => "({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}_区别_价格对比_诚仁堂商城",
                    'keyword' => "({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}区别，{$brand_name_common}{$prod_name_common}的价格",
                    'description' => "{$prod_name_common}({$brand_name_common})和{$prod_name_common_two}({$brand_name_common_two})有什么区别，上诚仁堂商城药店查看({$brand_name_common}){$prod_name_common}和({$brand_name_common_two}){$prod_name_common_two}价格，适应病症，用法用量等药品信息区别，帮你找到最合适你的商品。",
                );
                break;
        }
        $data['otherList']['category'] = $this->getCateBreadcrumb($categoryId);
        $data['otherList']['prod_name_common'] = $prod_name_common;
        return $this->uniteReturnResult(\Shop\Models\HttpStatus::SUCCESS, $data);
    }

    /**
     * @remark 移动端品牌详情
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getBrandDetail($param = array())
    {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['brandId'] = isset($param['brandId']) ? (int)$param['brandId'] : 0;
        //参数错误
        if(!$this->verifyRequiredParam($param) || $param['brandId'] === 0){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $data = BaseData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangBrands',
            'column' => 'brand_desc as body',
            'where' => ' where id = :id:',
            'bind' => array(
                'id' => $param['brandId']
            )
        ), true);
        $code = empty($data['body']) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @remark 设置图片绝对地址
     * @param array $data
     * @param string $str 字段
     * @param string $url 地址
     * @return mixed
     * @author 杨永坚
     */
    public function setUrl($data, $str, $url = '')
    {
        $url = empty($url) ? $this->config['domain']['img'] : $url;
        foreach($data as $k => $v){
            $data[$k][$str] = $url. $v[$str];
        }
        return $data;
    }

    /**
     * @remark 遍历评论，取出评论图片
     * @param $commentList=array 评论数组
     * @param bool $isImg true只返回有图片评论的数据
     * @return array
     * @author 杨永坚
     */
    private function forCommentVal($commentList, $param = array(), $isImg = false)
    {
        $list = array();
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        foreach ($commentList as $k => $v) {
            if($v['is_anonymous'] == 1){
                $v['nickname'] = $this->config->company_name.'会员';
            }else{
                $v['nickname'] = $this->nicknameReplace($v['nickname']);
                $v['nickname'] = !empty($v['nickname']) ? $v['nickname'] : $this->config->company_name.'会员';
            }
            $v['imageList'] = BaseData::getInstance()->getData(array(
                'table' => '\Shop\Models\BaiyangGoodsCommentImage',
                'column' => 'comment_image',
                'where' => 'where comment_id = :comment_id: and status = :status:',
                'bind' => array(
                    'comment_id' => $v['id'],
                    'status' => 1
                )
            ));
            $skuData = $BaiyangSkuData->getSkuInfo($param['goods_id'], $param['platform']);
            $v['ruleList'] = !empty($skuData['ruleList']) ? $skuData['ruleList'] : [];
            $v['ruleName'] = $skuData['ruleName'];
            if(!empty($v['imageList'])){
                $list[] = $v;
            }
            $commentList[$k] = $v;
        }
        return $isImg ? $list : $commentList;
    }

    /**
     * 匿名评论:账号显示为“诚仁堂会员”
     * 非匿名规则：
     *  1、昵称（除一个字外，两个字以上展示，取前后一个字展示，中间用三个***代替）：
     *          一个字：我***（后缀加3个***)
     *          两个字：我***你
     *          三个字：我***大
     *          四个字：我***高
     *          五个字或以上：我***丽
     *  2、邮箱：131***304@qq.com(前后三位数展示，加后缀，如@qq.com)
     *  3、手机号码：137***1065(前三位数字后四位数展示，中间三个***代替)
     * @param $str
     * @return mixed|string
     * @author 杨永坚
     */
    private function nicknameReplace($str){
        //手机号正则匹配
        $phoneChar = "/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/";
        //邮箱正则匹配
        $emailChar = "/^[0-9a-zA-Z]+(?:[\_\.\-][a-z0-9\-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+$/i";
        $arr = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
        $length = count($arr);
        if(!is_array($arr) || empty($arr) || $length < 0){
            return '';
        }
        if(preg_match($phoneChar, $str) &&  $length ===11){
            return substr_replace($str,'****',3,4);

//            return $this->smartSubstr($str,1,1);
        }

        if(preg_match($emailChar, $str)){
            $i = strripos($str, '@');
            $tem = substr($str, 0, $i);
            $suffix = substr($str ,$i);
            return substr($tem, 0, 3) . "***" . $suffix;
        }

        $returnStr = '';



        switch ($length) {
            case 1:
                $returnStr = $str . "***";
                break;
            default:
                $returnStr = $arr[0] . "***" . $arr[$length-1];
                break;
        }
        return $returnStr;
    }

    //判断字符串长度
    private function smartStrlen($string)
    {
        $result = 0;

        $number = 3;

        for ($i = 0; $i < strlen($string); $i += $bytes) {
            $bytes = ord(substr($string, $i, 1)) > 127 ? $number : 1;

            $result += $bytes > 1 ? 1.0 : 0.5;
        }

        return $result;
    }
    //截取字符串
    private function smartSubstr($string, $start, $length = null)
    {
        $result = '';

        $number = 3;

        if ($start < 0) {
            $start = max($this->smartStrlen($string) + $start, 0);
        }

        for ($i = 0; $i < strlen($string); $i += $bytes) {
            if ($start <= 0) {
                break;
            }

            $bytes = ord(substr($string, $i, 1)) > 127 ? $number : 1;

            $start -= $bytes > 1 ? 1.0 : 0.5;
        }

        if (is_null($length)) {
            $result = substr($string, $i);
        } else {
            for ($j = $i; $j < strlen($string); $j += $bytes) {
                if ($length <= 0) {
                    break;
                }

                if (($bytes = ord(substr($string, $j, 1)) > 127 ? $number : 1) > 1) {
                    if ($length < 1.0) {
                        break;
                    }

                    $result .= substr($string, $j, $bytes);
                    $length -= 1.0;
                } else {
                    $result .= substr($string, $j, 1);
                    $length -= 0.5;
                }
            }
        }

        return $result;
    }

    /**
     * @remark 发送es请求
     * @param $esUrl = string es地址
     * @param $param = array 请求参数
     * @param bool $isArr 是否创建新数组
     * @return \array[]
     * @author 杨永坚
     */

    private function getEsApi($esUrl, $param, $isArr = false)
    {
        //筛选促销活动
        if(!empty($param['promotionType'])){
            /*$activity = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsInfoByType', $this, array(
                'platform' => $param['platform'],
                'promotion_type' => $param['promotionType'],
                'user_id' => $param['userId'],
                'is_temp' => 0
            ));*/
            $param['activity'] = $param['promotionType'];
        }
        //活动凑单
        if(!empty($param['promotionId'])){
            $activity = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsInfoById', $this, array(
                'platform' => $param['platform'],
                'promotion_id' => $param['promotionId'],
                'user_id' => isset($param['userId']) ? $param['userId'] : 0,
                'is_temp' => isset($param['isTemp']) ? $param['isTemp'] : 0,
                'channel_subid' => isset($param['channel_subid']) ? $param['channel_subid'] : '',
                'udid' => isset($param['udid']) ? $param['udid'] : ''
            ));
            if($activity['error'] === 1){
                return $this->uniteReturnResult($activity['code']);
            }
            $data['promotionInfo'] = $activity['data']['promotionInfo'];
            $data['changeGroup'] = $activity['data']['changeGroup'];
            $param['activity'] = json_encode($activity['data']['goodsInfo']);
            //因为es接口改变参数，故修改如下
            $param['promotion'] = $param['promotionId'];
            unset($param['promotionId']);
        }
        //请求es接口查询
        if(isset($param['upPrice']) && ($param['upPrice'] == null || $param['upPrice'] == '') ) unset($param['upPrice']);
        $requestData = http_build_query($param);
        $responseResult = json_decode($this->curl-> sendPost($esUrl,$requestData),true);
        if($responseResult['code'] == 200){
            if(empty($responseResult['listData']) && empty($isArr)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA,['param' =>$param,'pageCount' => 0,'pageNum' => 0,'pageStart' => $param['pageStart'],'pageSize' => $param['pageSize'],'listData' => [],'attrName' => []]);
            }
            if($isArr){
                switch($isArr){
                    case 1://同类推荐
                        $data = $responseResult['listData'];
                        break;
                    case 2://app联词搜索
                        $data = [];
                        foreach($responseResult['dataNum'] as $k => $v){
                            $data[] = array(
                                'name' => $k,
                                'result_count' => $v
                            );
                        }
                }
                $status = empty($data) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
            }else{
                $data['pageCount'] = !empty($responseResult['pageCount']) ? $responseResult['pageCount'] : 0;
                $param['pageSize'] =  $param['pageSize'] > 0 ? $param['pageSize'] : 1;
                $pageCount = $pageNum = 0;
                if(!empty($responseResult['pageCount'])){
                    $pageNum = $responseResult['pageCount'];
                    $pageCount = ceil($pageNum / $param['pageSize']);
                }
                $data['param'] = $param;
                $data['pageCount'] = $pageCount;
                $data['pageNum'] = $pageNum;
                $data['pageStart'] = $param['pageStart'];
                $data['pageSize'] = $param['pageSize'];
                $userId = isset($param['userId']) ? $param['userId'] : 0;
                $isTemp = isset($param['isTemp']) ? $param['isTemp'] : 0;
                // 判断用户是否绑定标签
                $tagSign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $userId, 'is_temp' => $isTemp]);
                //是否有优惠价格跟库存
                $resData = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice', $this, array(
                    'platform' => $param['platform'],
                    'goodsList' => $responseResult['listData'],
                    'user_id' => $userId,
                    'is_temp' => $isTemp,
                    'tag_sign' => $tagSign,
                ));
                //促销活动
                $pf_arr = array(
                    'pc'=>1,
                    'app'=>1,
                    'wap'=>1,
                    'wechat'=>1
                );
                if(1 == $pf_arr[$param['platform']]){
                    $result = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionSign', $this, array(
                        'platform' => $param['platform'],
                        'goods_id' => implode(',', array_column($resData, 'goodsId')),
                        'user_id' => $param['userId'],
                        'is_temp' => 0
                    ));
                    //取出筛选的促销活动
                    foreach($resData as $k => $v){
                        $v['promotionData'] = array();
                        $v['promotionData']['fullMinus'] = in_array($v['goodsId'], $result['data']['fullMinus']) ? 1 : 0;
                        $v['promotionData']['fullGift'] = in_array($v['goodsId'], $result['data']['fullGift']) ? 1 : 0;
                        $v['promotionData']['expressFree'] = in_array($v['goodsId'], $result['data']['expressFree']) ? 1 : 0;
                        $v['promotionData']['coupon'] = in_array($v['goodsId'], $result['data']['coupon']) ? 1 : 0;
                        $v['promotionData']['fullDiscount'] = in_array($v['goodsId'], $result['data']['fullOff']) ? 1 : 0;
                        $v['promotionData']['limited'] = in_array($v['goodsId'], $result['data']['limitBuy']) ? 1 : 0;
                        $v['promotionData']['farePurchase'] = in_array($v['goodsId'], $result['data']['increaseBuy']) ? 1 : 0;
                        $resData[$k] = $v;
                    }
                }
                //促销价格有变，影响es价格排序问题，重置排序
                if($param['type'] == 'price' && !empty($responseResult['listData'])){
                    $resData = $this->sortArr($resData, array(
                        'direction' => $param['typeStatus'] == 1 ? 'SORT_ASC' : 'SORT_DESC',
                        'field' => 'price'
                    ));
                }
                $data['listData'] = $resData;
                $status = empty($data['listData']) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
                //插入收缩记录
            if(!empty($param['searchName'])&&!empty($param['platform'])){
                $table = '\Shop\Models\BaiyangHistoricalOrigin';
                $searchLog = [
                'keywords' => $param['searchName'],//542728,
                'platform_id'=> $param['platform'],//7990,72489
                'count'=> 1,//7990,72489
                'min_res'=> $pageNum,
                'max_res'=> $pageNum,
                'at'=> date('Y-m-d',time()),
                 ];
				 $rt = BaseData::getInstance()->getData(array(
					'table' => '\Shop\Models\BaiyangHistoricalOrigin',
					'column' => 'id',
					'where' => 'where keywords = :keywords: and at = :at: and platform_id = :platform_id:',
                    'bind' =>array("keywords"=>$searchLog['keywords'],'at'=>$searchLog['at'],'platform_id'=>$searchLog['platform_id'])));
                $update = [
                    'table' => $table,
                    'column' => 'count = count+1',
                    'where' => 'where keywords = :keywords: and at = :at: and platform_id = :platform_id:',
                    'bind' =>array("keywords"=>$searchLog['keywords'],'at'=>$searchLog['at'],'platform_id'=>$searchLog['platform_id']),
                ];
                //$rt = BaseData::getInstance()->updateData($update);
                if($rt){
                    BaseData::getInstance()->updateData($update);
                }else{
                    $log['table']='\Shop\Models\BaiyangHistoricalOrigin';
                    $log['bind'] = $searchLog;
					BaseData::getInstance()->addData($log);
                } 
            }
            }
            if(isset($responseResult['brandMap'])){
                $data['brandMap'] = $responseResult['brandMap'];
            }
            if(isset($responseResult['attrName'])){
                $attrName = array();
                $attrNumber = 0;
                foreach($responseResult['attrName'] as $k => $v){
                    $attrName[$attrNumber]['attrNames'] = $k;
                    $attrName[$attrNumber]['attrValue'] = $v;
                    $attrNumber++;
                }
                $data['attrName'] = $attrName;
            }
            return $this->uniteReturnResult($status, $data);
        }else{
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::FAILED);
        }
    }

    /**
     * @remark 分类面包屑
     * @param $categoryId=int 分类id
     * @return array
     * @author 杨永坚
     */
    private function getCateBreadcrumb($categoryId)
    {
        if($categoryId < 0 ){
            return false;
        }
        $result = BaseData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangCategory',
            'column' => 'id,pid,category_name',
            'where' => 'where id=:id:',
            'bind' => array(
                'id' => $categoryId
            )
        ), true);
        if($result['pid'] <= 0){
            return array($result);
        }else{
            $categoryList = $this->getCateBreadcrumb($result['pid']);
            array_push($categoryList, $result);
            return $categoryList;
        }
    }

    /**
     * @remark 获取分类数据
     * @param int $keyId 查找key的分类id
     * @param int $pid 分类id
     * @return mixed
     * @author 杨永坚
     */
    private function getCategory($keyId, $pid = 0)
    {
        $data = BaseData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangCategory',
            'column' => 'id,category_name',
            'where' => 'where pid = :pid:',
            'bind' => array(
                'pid' => $pid
            )
        ));
        if($keyId && $data){
            $key = array_search($keyId, array_column($data, 'id'));
            array_unshift($data, $data[$key]);
            array_splice($data, $key + 1, 1);
        }
        return $data;
    }

    private function isCoupon($data,$pf)
    {
        $CouponService = CouponService::getInstance();
        foreach($data as $k => $v){
            $data[$k]['exist_coupon'] = $CouponService->IsExistCouponInBrand($v['brand_id'],$pf);
        }
        return $data;
    }

    /**
     * 根据sku id 获取sku详细信息
     * @param array $param [一维数组]
     *          -int        sku_id      商品id
     *          -string     platform    平台【pc、app、wap】
     * @return array [] 结果信息
     * @author 梁伟
     */
    public function getOneSku($param)
    {
        if (!isset($param['sku_id']) || empty($param['sku_id']) || !isset($param['platform']) || empty($param['platform'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();

        $res = $BaiyangSkuData->getSkuInfo($param['sku_id'], $param['platform']);
        return $this->filterData('id,supplier_id,name,category_id,brand_id,goods_number,drug_type,goods_name,is_use_stock,sale,sku_price,sku_market_price,goods_image,specifications',$res);
    }

    /**
     * @remark 多维数组某个字段排序
     * @param $data = array() 数组数据
     * @param $sort = array(
     *          'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
     *          'field'     => 'price',       //排序字段
     *        );
     * @return mixed
     * @author 杨永坚
     */
    public function sortArr($data, $sort)
    {
        $arrSort = array();
        foreach($data AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $data);
        }
        return $data;
    }

    /**
     * @desc 获取商品可售库存
     * @param array $param
     *      -string goods_id 商品单个或多个id【多个以逗号隔开】
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getGoodsCanSaleStock($param)
    {
        // 格式化参数
        $param['goods_id'] = isset($param['goods_id']) ? (string)$param['goods_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($param['goods_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $result = $this->func->getCanSaleStock(['goods_id' => $param['goods_id'], 'platform' => $param['platform']]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['stock' => $result]);
    }

    /**
     * @desc 根据商品id获取商品最优惠价、可售库存、商品状态等信息【商品详情】
     * @param array  $param [一维数组]
     *       -int     goods_id  商品id
     *       -string  platform  平台【pc、app、wap】
     *       -int     user_id   用户id (临时用户或真实用户id)
     *       -int     is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *       -int     channel_subid  渠道号
     *       -string  udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getPromotionInfo($param)
    {
        // 格式化参数
        $param['goods_id'] = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['is_temp'] = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($param['goods_id']) || empty($param['user_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $promotion = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionInfoById',$this,$param);
        return $this->uniteReturnResult($promotion['code'],$promotion['data']);
    }
	
	/**
     * @desc 根据专题id获取专题商品标签与价格信息
     * @param array  $param [一维数组]
     *       -int     subject_id  专题id
     *       -string  platform  平台【pc、app、wap】
     *       -int     channel_subid  渠道号
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getSubjectTagInfoById($param)
    {
        // 格式化参数
        $param['subject_id'] = isset($param['subject_id']) ? (int)$param['subject_id'] : 11;
        if (!$param['subject_id'] || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $userId = isset($param['userId']) && $param['userId'] ? $param['userId'] : 0;
        $isTemp = isset($param['isTemp']) ? $param['isTemp'] : 1;
        // 判断用户是否绑定标签
        $tagSign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $userId, 'is_temp' => $isTemp]);
		//缓存
		$result = $this->RedisCache->getValue('tag_subjectid_'.$param['subject_id']);
		$BaiyangSkuData = BaiyangSkuData::getInstance();
		$array = array('spu_id','barcode','manufacturer','weight','volume','prod_code','period','usage','meta_title','meta_keyword','meta_description','specifications','ad','rule_value0','rule_value1','rule_value2','sku_desc','comment_number','rate_of_praise','virtual_stock_default','virtual_stock_pc','virtual_stock_app','virtual_stock_wap','virtual_stock_wechat','v_stock','ruleName','ruleList','rule_value_id','supplier_id','category_id','category_path','brand_id','drug_type','product_type','sku_alias_name','attribute_value_id','bind_gift','video','packaging_type','returned_goods_time');
		if(!$result){
			$baseData = BaseData::getInstance();
			$table = '\Shop\Models\BaiyangSubjectTag';
			$map['table'] = $table;
			$map['column'] = '*';
//			$map['where'] = 'where status = 1 and (tag_id IN (select  tag_id  from '.$table.' where subject_ids like :subject_id:))';
			$map['where'] = "where status = 1 and (subject_ids = {$param['subject_id']} or subject_ids like '%,{$param['subject_id']},%' "
                . "or subject_ids like '%,{$param['subject_id']}%' or subject_ids like '%{$param['subject_id']},%')";
			/*$map['bind'] = array(
				'subject_id' => '%'.$param['subject_id'].'%'
			);*/
			$data = $baseData->getData($map);
			
			if($data){
				foreach($data as $k => $v){
					if($v['type']==1){
						$product_ids = array_unique(array_filter(explode(',',$v['product_ids'])));
                        $kan = [];
						foreach($product_ids as  $key => $sku_id){
							$res = $BaiyangSkuData->getSkuInfo($sku_id,$param['platform']);
                            if (!$res) {
                                continue;
                            }
							foreach($array as $val){
								unset($res[$val]);
							}
                            $res['goodsId'] = $res['id'];
                            $res['price'] = $res['sku_price'];
							if(!empty($res)){
								$kan[$key] = $res;
							}
						}
						//重排数组
						/*if(isset($kan) && !empty($kan)){
							shuffle($kan);
						}*/
						$result['product_tag_list'][] = array(
							'tag_id' => $v['tag_id'],
							'tag_name' => $v['tag_name'],
							'start_time' => date('Y-m-d H:i:s', $v['start_time']),
							'end_time' => date('Y-m-d H:i:s', $v['end_time']),
							'product_list' => $kan,
						);
					} elseif ($v['type']==2) {
						$result['price_tag'] = array(
							'tag_id' => $v['tag_id'],
							'tag_name' => $v['tag_name'],
							'start_time' => date('Y-m-d H:i:s', $v['start_time']),
							'end_time' => date('Y-m-d H:i:s', $v['end_time']),
							'img_url' => $v['img_url'],
							'background' => $v['background'],
						);
					}
				}
			}
			$this->RedisCache->setValue('tag_subjectid_'.$param['subject_id'],$result,1800);
		}
        if (isset($result['product_tag_list']) && $result['product_tag_list']) {
            foreach ($result['product_tag_list'] as $key => $product_tag) {
                //是否有优惠价格跟库存
                $result['product_tag_list']['product_list'] = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice', $this, array(
                    'platform' => $param['platform'],
                    'goodsList' => $product_tag['product_list'],
                    'user_id' => $userId,
                    'is_temp' => $isTemp,
                    'tag_sign' => $tagSign,
                ));
                foreach ($product_tag['product_list'] as $k => $val) {
                    $val['sku_price'] = $val['price'] ? $val['price'] : $val['sku_price'];
                    $product_tag['product_list'][$k] = $val;
                }
                $result['product_tag_list'][$key] = $product_tag;
            }
        }
		if(isset($param['product_ids'])&&!empty($param['product_ids'])){
			//将商品id中的，号空格替换成,号
//			$product_ids = str_replace(['，',' ','|'],',',$param['product_ids']);
			//将商品id转成数组并去除重复值与空值
			$product_ids = array_unique(array_filter(explode(',',$param['product_ids'])));
			foreach($product_ids as $key => $sku_id){
				$res = $BaiyangSkuData->getSkuInfo($sku_id,$param['platform']);
                if (!$res) {
                    continue;
                }
				foreach($array as $val){
					unset($res[$val]);
				}
                $res['goodsId'] = $res['id'];
                $res['price'] = $res['sku_price'];
				if(!empty($res)){
					$result['product_list'][] = $res;
				}
			}
            if (isset($result['product_list']) && $result['product_list']) {
                //是否有优惠价格跟库存
                $result['product_list'] = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice', $this, array(
                    'platform' => $param['platform'],
                    'goodsList' => $result['product_list'],
                    'user_id' => $userId,
                    'is_temp' => $isTemp,
                    'tag_sign' => $tagSign,
                ));
                foreach ($result['product_list'] as $k => $val) {
                    $val['sku_price'] = $val['price'] ? $val['price'] : $val['sku_price'];
                    $result['product_list'][$k] = $val;
                }
            }
			/*if(isset($result['product_list']) && !empty($result['product_list'])){
				shuffle($result['product_list']);
			}*/
		}
        $code = empty($result) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
        return $this->uniteReturnResult($code, $result);
		
    }

    /**
     * @desc 根据商品id获取商品最优惠价
     * @param array  $param [一维数组]
     *       -int|string  goods_id  单个/多个商品id
     *       -string      platform  平台【pc、app、wap】
     *       -int         user_id   用户id (临时用户或真实用户id)
     *       -int         is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *       -int         channel_subid  渠道号
     *       -string      udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getDiscountedPrice($param)
    {
        // 格式化参数
        $param['goods_id'] = isset($param['goods_id']) ? (string)$param['goods_id'] : 0;
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['is_temp'] = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($param['goods_id']) || empty($param['user_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $promotion = $this->_eventsManager->fire('promotionInfo:getDiscountedPrice',$this,$param);
        return $this->uniteReturnResult($promotion['code'],$promotion['data']);
    }

}
