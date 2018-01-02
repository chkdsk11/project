<?php
/**
 * Created by PhpStorm.
 * @author 李斌
 * @date: 2016/10/27
 */

namespace Shop\Home\Services;
use Shop\Home\Datas\BaiyangCategoryData;
use Shop\Home\Services\BaseService;

class CategoryService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;


    /**
     * 获取分类详细信息
     * @param array() $param = [
     *      'type'      =>  客户端,如：pc，app，wap
     *      'filter'    =>  需要的字段，默认全部
     * ]
     * @return array()
     * @author 李斌
     */
    public function getCategoryList($param)
    {
        if( !isset($param['type']) || empty($param['type']) ){
            return $this->responseResult(\Shop\Models\HttpStatus::FAILED,'参数丢失');
        }

        // wap端
        if ($param['type'] == 'wap') {
            $res = BaiyangCategoryData::getInstance()->getCategoryWapInfo();
        }

        if( !$res ){
            return $this->responseResult(\Shop\Models\HttpStatus::NOT_FOUND,'无符合数据');
        }

        return $this->responseResult(\Shop\Models\HttpStatus::SUCCESS,'成功', $res);
    }
}
