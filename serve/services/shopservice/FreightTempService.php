<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：SkuAd_id_5     一个skuad信息
 */

namespace Shop\Services;
use Shop\Datas\BaiyangFreightTempData;
use Shop\Services\BaseService;

class FreightTempService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;


    /**
     * 获取全部SkuAd信息
     * @return array()
     * User: 梁伟
     * Date: 2016/9/21
     * Time: 18:27
     */
    public function getFreightAll()
    {
        //查询条件
        $table = '\Shop\Models\BaiyangFreightTemplateGroup';
        $selections = 'id,template_name,value_type,is_default,is_global,state';
        $data = array(
            'state'=>1
        );
        $where = 'state=:state:';
        $result = BaiyangFreightTempData::getInstance()->select($selections,$table,$data,$where);
        return $result;
    }
}
