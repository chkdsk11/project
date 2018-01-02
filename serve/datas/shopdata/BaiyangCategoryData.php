<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangCategory;

class BaiyangCategoryData extends BaseData
{
    protected static $instance=null;

    /**
     * @return \Shop\Models\BaiyangCategory[]
     * 得到分类表所有数据
     */
    public function getAllCategory()
    {
//        $this->cache->selectDb(8);
//        $ret=$this->cache->getValue('soa_CategoryAll');
//        if($ret){
//            return $ret;
//        }else{
            $ret=BaiyangCategory::find();
            if(count($ret)>0){
                $ret=$ret->toArray();
            }
//            $this->cache->setValue('soa_CategoryAll',$ret);
            return $ret;
//        }
    }

}