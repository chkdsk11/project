<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/8/25
 * Time: 14:38
 */

namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\UpdateCacheSkuData;
use Shop\Models\CacheGoodsKey;
use Shop\Datas\BaseData;

class BrandsService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;
    private $table = '\Shop\Models\BaiyangBrands';
    private $BaseData = null;

    public function __construct()
    {
        $this->BaseData = BaseData::getInstance();
    }

    public function getBrandList($param)
    {
        //查询条件
        $data = array();
        $where = '';
        if(!empty($param['brand_name'])){
            $data['brand_name'] = '%'.$param['brand_name'].'%';
            $data['id'] = $param['brand_name'];
            $where .= "(b.brand_name like :brand_name: OR b.id = :id:)";
        }
        if($param['is_hot'] >= 0){
            $data['is_hot'] = $param['is_hot'];
            $where .= empty($where) ? "e.is_hot = :is_hot:" : " AND e.is_hot = :is_hot:";
        }
        $where .= $where ? '' : '1=1';
        //总记录数
        $counts = $this->BaseData->select('b.id', '\Shop\Models\BaiyangBrands as b', $data, $where, 'left join \Shop\Models\BaiyangBrandsExtend as e ON e.brand_id = b.id AND e.type = 1');
        $counts = $counts ? count($counts) : 0;
        if($counts <= 0){
            return array('res' => 'success', 'list' => array(), 'page' => '');
        }
        //分页
        $pages['page'] = $param['page'];//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $page = $this->page->pageDetail($pages);

        $where .= ' order by add_time desc limit '.$page['record'].','.$page['psize'];

        $result = $this->BaseData->select('b.id,b.brand_name,b.brand_desc,b.add_time,e.id as eid,e.is_hot,e.brand_sort', '\Shop\Models\BaiyangBrands as b', $data, $where, 'left join \Shop\Models\BaiyangBrandsExtend as e ON e.brand_id = b.id AND e.type = 1');
        if(!empty($result)){
            return array(
                'res'  => 'success',
                'list' => $result,
                'page' => $page['page']
            );
        }else{
            return ['res' => 'error'];
        }
    }

    /**
     * @remark 添加品牌
     * @param $param=array() 品牌数据
     * @return mixed array()结果
     * @author 杨永坚
     * @modify 梁伟 修改图片路径 2016-11-29
     * @modify 梁伟 添加更新缓存 2016-12-07
     */
    public function addBrand($param)
    {
        //品牌
        $result = $this->BaseData->insert($this->table, $param, true);
        if($result){
            UpdateCacheSkuData::getInstance()->updateSkuBrand($result);
        }
        //移动端
        $param['brand_id'] = $result;
        $param['type'] = 1;
        $param['sort'] = (int)$param['sort'];
        $param['brand_sort'] = (int)$param['brand_sort'];
        $moveRes = $this->BaseData->insert('\Shop\Models\BaiyangBrandsExtend', $param);
        //pc端
//        $data['brand_id'] = $param['brand_id'];
//        $data['brand_logo'] = $param['brand_logo_pc'];
//        $data['list_image'] = $param['list_image_pc'];
//        $data['brand_describe'] = $param['brand_describe_pc'];
//        $data['sort'] = (int)$param['sort_pc'];
//        $data['brand_sort'] = (int)$param['brand_sort_pc'];
//        $data['is_hot'] = $param['is_hot_pc'];
//        $data['status'] = $param['status_pc'];
//        $pcRes = $this->BaseData->insert('\Shop\Models\BaiyangBrandsExtend', $data);
//        return $result && $moveRes && $pcRes ? $this->arrayData('添加成功！', '/brands/list', '') : $this->arrayData('添加失败！', '', '', 'error');
        return $result && $moveRes ? $this->arrayData('添加成功！', '/brands/list', '') : $this->arrayData('添加失败！', '', '', 'error');
    }

    /**
     * @remark 编辑品牌
     * @param $param=array() 修改数据
     * @return array
     * @author 杨永坚
     * @modify 梁伟 修改图片路径 2016-11-29
     * @modify 梁伟 添加更新缓存 2016-12-07
     */
    public function editBrand($param)
    {
        $columStr = 'brand_name=:brand_name:,brand_desc=:brand_desc:,add_time=:add_time:';
        $where = 'id=:id:';
        $data['id'] = $param['id'];
        $data['brand_name'] = $param['brand_name'];
        $data['brand_desc'] = $param['brand_desc'];
        $data['add_time'] = time();

        $result = $this->BaseData->update($columStr, $this->table, $data, $where);
        if($result){
            UpdateCacheSkuData::getInstance()->updateSkuBrand($param['id']);
        }
        //移动端
        $moveStr = 'brand_logo=:brand_logo:,list_image=:list_image:,brand_describe=:brand_describe:,mon_title=:mon_title:,sort=:sort:,brand_sort=:brand_sort:,status=:status:,is_hot=:is_hot:';
        $moveWhere = 'id=:id: and brand_id=:brand_id:';
        $moveParam['id'] = $param['move_id'];
        $moveParam['brand_id'] = $param['id'];
        $moveParam['brand_logo'] = $param['brand_logo'];
        $moveParam['list_image'] = $param['list_image'];
        $moveParam['brand_describe'] = $param['brand_describe'];
        $moveParam['mon_title'] = $param['mon_title'];
        $moveParam['sort'] = !empty($param['sort']) ? $param['sort'] : 0;
        $moveParam['brand_sort'] = !empty($param['brand_sort']) ? $param['brand_sort'] : 1;
        $moveParam['status'] = $param['status'];
        $moveParam['is_hot'] = $param['is_hot'];
        $moveRes = $this->BaseData->update($moveStr, '\Shop\Models\BaiyangBrandsExtend', $moveParam, $moveWhere);
//        //pc端
//        $pcStr = 'brand_logo=:brand_logo:,list_image=:list_image:,brand_describe=:brand_describe:,sort=:sort:,brand_sort=:brand_sort:,is_hot=:is_hot:,status=:status:';
//        $pcWhere = 'id=:id: and brand_id=:brand_id:';
//        $pcParam['id'] = $param['pc_id'];
//        $pcParam['brand_id'] = $param['id'];
//        $pcParam['brand_logo'] = $param['brand_logo_pc'];
//        $pcParam['list_image'] = $param['list_image_pc'];
//        $pcParam['brand_describe'] = $param['brand_describe_pc'];
//        $pcParam['sort'] = !empty($param['sort_pc']) ? $param['sort_pc'] : 0;
//        $pcParam['brand_sort'] = !empty($param['brand_sort_pc']) ? $param['brand_sort_pc'] : 1;
//        $pcParam['is_hot'] = $param['is_hot_pc'];
//        $pcParam['status'] = $param['status_pc'];
//        $pcRes = $this->BaseData->update($pcStr, '\Shop\Models\BaiyangBrandsExtend', $pcParam, $pcWhere);
//
//        return $result && $moveRes && $pcRes ? $this->arrayData('修改成功！', '/brands/list', '') : $this->arrayData('修改失败！', '', '', 'error');
        return $result && $moveRes ? $this->arrayData('修改成功！', '/brands/list', '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    /**
     * @remark 删除品牌
     * @param $id=int 品牌id
     * @return array
     * @author 杨永坚
     * @modify 梁伟 添加删除缓存 2016-12-07
     */
    public function delBrand($id)
    {
        $data['id'] = $id;
        $where = 'id=:id:';
        $result = $this->BaseData->delete($this->table, $data, $where);
        $param['brand_id'] = $id;
        $map = 'brand_id=:brand_id:';
        $res = $this->BaseData->delete('\Shop\Models\BaiyangBrandsExtend', $param, $map);
        if($res){
            $this->RedisCache->delete(CacheGoodsKey::SKU_BRAND_NAME.$id);
        }
        return $result && $res ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    /**
     * @remark 获取品牌信息
     * @param $id=int 品牌id
     * @return array
     * @author 杨永坚
     * @modify 梁伟 修改图片路径 2016-11-29
     */
    public function getBrandInfo($id)
    {
        $data['id'] = $id;
        $where = 'id=:id:';
        $result['brandData'] = $this->BaseData->select('*', $this->table, $data, $where);
        $param['brand_id'] = $id;
        $map = 'brand_id=:brand_id:';
        $result['extendData'] = $this->BaseData->select('*', '\Shop\Models\BaiyangBrandsExtend', $param, $map);

        if($result['extendData'] && is_array($result['extendData'])){
            foreach($result['extendData'] as $k=>$v){
                if(!empty($v['brand_logo'])){
                    $result['extendData'][$k]['brand_logo'] = $result['extendData'][$k]['brand_logo'];
                }
                if(!empty($v['list_image'])){
                    $result['extendData'][$k]['list_image'] = $result['extendData'][$k]['list_image'];
                }
            }
        }

        return !empty($result) ? array('status'=>'success', 'data'=>$result) : array('status'=>'error');
    }

    public function getBrandUnique($brand_name)
    {
        $data['brand_name'] = $brand_name;
        $where = "brand_name=:brand_name:";
        $result = $this->BaseData->select('*', $this->table, $data, $where);
        return $result ? array('status'=>'success', 'data'=>$result) : $this->arrayData('请求失败或无数据！', '', '', 'error');
    }

    /**
     * @remark 获取、搜索品牌
     * @param string $brand_name 品牌名
     * @return array
     * @author 杨永坚
     */
    public function searchBrand($brand_name = '')
    {
        if(!empty($brand_name)){
            $data['brand_name'] = '%'.$brand_name.'%';
            $where = ' brand_name like :brand_name:';
        }
        $where .= ' limit 10';
        $result = $this->BaseData->select('*', $this->table, $data, $where);
        return $result ? $this->arrayData('请求成功！', '', $result) : $this->arrayData('请求失败或无数据！', '', '', 'error');
    }
    
    /**
     * @remark 获取所有品牌信息
     * @return array
     * @author 杨永坚
     */
    public function getBrandAll()
    {
        $result = $this->BaseData->select('id, brand_name', $this->table, array(), 1);
        return $result ? $this->arrayData('请求成功！', '', $result) : $this->arrayData('请求失败或无数据！', '', '', 'error');
    }

    /**
     * @remark 更新
     * @param $param = array
     * @return array
     * @author 杨永坚
     */
    public function updateBrands($param)
    {
        $columStr = $this->jointString($param, array('id'));
        $where = 'brand_id=:id:';
        $result = $this->BaseData->update($columStr, '\Shop\Models\BaiyangBrandsExtend', $param, $where);
        return $result ? $this->arrayData('修改成功！', '/brands/list', '') : $this->arrayData('修改失败！', '', '', 'error');
    }
}