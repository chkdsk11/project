<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/26 0026
 * Time: 上午 11:12
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaiyangProductRuleData;
use Shop\Datas\BaiyangCategoryProductRuleData;
use Shop\Datas\UpdateCacheSkuData;
use Shop\Models\CacheGoodsKey;

class CategoryProductRuleService extends BaseService
{
    protected static $instance = null;

    /**
     * 添加分类品规名
     * @param array(
     *      'category_id'   =>  int 分类ID(必须)
     *      'order'   =>  int 该品规在分类下的位置
     *      'id'   =>  int 品规id(存在修改|不存在添加)
     *      'name'   =>  string 品规名
     * )
     * @return array()
     * User: 梁伟
     * Date: 2016/91/
     * Time: 17:49
     */
    public function addCategoryProductRule($param)
    {
        $param['category_id'] = isset($param['category_id'])?(int)$param['category_id']:0;
        $param['order'] = isset($param['order'])?(int)$param['order']:0;
        $param['id'] = isset($param['id'])?(int)$param['id']:0;
        $param['name'] = isset($param['name'])?$param['name']:'';
        if(empty($param['category_id']) || empty($param['order'])){
            return $this->arrayData('参数丢失','','','error');
        }
        $ProductRuleData = BaiyangProductRuleData::getInstance();
        $table = '\Shop\Models\BaiyangProductRule';
        $CategoryProductRuleData = BaiyangCategoryProductRuleData::getInstance();
        $tableCate = '\Shop\Models\BaiyangCategoryProductRule';
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        //判断添加还是修改
        if($param['id']){
            $where = " id=:id:";
            //删除品规信息
            if(empty($param['name'])){
                //事务处理
                $this->dbWrite->begin();
                $res = $ProductRuleData->delete($table,array('id' => $param['id']),$where);
                if($res){
                    if($param['order'] == 1){
                        $param['order'] = '';
                    }else if($param['order'] != 2 && $param['order'] != 3){
                        return $this->arrayData('参数错误','','','error');
                    }
                    $dataCate = array('category_id'=>$param['category_id'],'name_id'.$param['order']=>0);
                    $res = $CategoryProductRuleData->update('name_id'.$param['order'].'=:name_id'.$param['order'].':',$tableCate,$dataCate,'category_id=:category_id:');
                }
                if($res){
                    //事务提交
                    $this->dbWrite->commit();
                    //更新缓存
                    $this->RedisCache->delete(CacheGoodsKey::RULE_NAME.(int)$param['id']);
                    $UpdateCacheSkuData->updateSkuRule($param['category_id']);
                    return $this->arrayData('编辑成功','',array('name'=>'','id'=>0),'success');
                }else{
                    $this->dbWrite->rollback();
                    return $this->arrayData('编辑失败','','','error');
                }
            }
            $data = array('name' => $param['name'],'id' => $param['id']);
            $columStr = "name=:name:";
            $res = $ProductRuleData->update($columStr,$table,$data,$where);
            if($res){
                $UpdateCacheSkuData->updateSkuRuleName($param['id']);
                return $this->arrayData('编辑成功','',array('name'=>$param['name'],'id'=>$param['id']),'success');
            }else{
                return $this->arrayData('编辑失败','','','error');
            }
        }else{
            if($param['order'] == 1){
                $param['order'] = '';
            }else if($param['order'] != 2 && $param['order'] != 3){
                return $this->arrayData('参数错误','','','error');
            }
            //事务处理
            $this->dbWrite->begin();
            //新增品规名
            $data = array('name' => $param['name'],'add_time' => time());
            $ruleId = $ProductRuleData->insert($table,$data,true);
            if(!$ruleId){
                $this->dbWrite->rollback();
                return $this->arrayData('编辑失败','','','error');
            }
            //判断分类品规模型是否新增
            $con = $CategoryProductRuleData->count($tableCate,['id'=>$param['category_id']],'category_id=:id:');
            $dataCate = array('category_id'=>$param['category_id'],'name_id'.$param['order']=>(int)$ruleId);
            if($con){
                //修改
                $res = $CategoryProductRuleData->update('name_id'.$param['order'].'=:name_id'.$param['order'].':',$tableCate,$dataCate,'category_id=:category_id:');
            }else{
                //添加
                $res = $CategoryProductRuleData->insert($tableCate,$dataCate,true);
            }

            if($res){
                //事务提交
                $this->dbWrite->commit();
                $UpdateCacheSkuData->updateSkuRule($con?$param['category_id']:$res);
                $UpdateCacheSkuData->updateSkuRuleName($ruleId);
                return $this->arrayData('编辑成功','',array('name'=>$param['name'],'id'=>$ruleId),'success');
            }else{
                $this->dbWrite->rollback();
                return $this->arrayData('编辑失败','','','error');
            }
        }
    }

}