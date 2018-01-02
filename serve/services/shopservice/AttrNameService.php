<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/5
 * Time: 9:52
 */

namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class AttrNameService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;
    private $table = '\Shop\Models\BaiyangAttrName';
    private $BaseData = null;

    public function __construct()
    {
        $this->BaseData = BaseData::getInstance();
    }

    /**
     * @remark 获取对应分类的商品属性与属性值
     * @param $category_id=int 分类id
     * @return array
     * @author 杨永坚
     */
    public function getCategoryAttr($category_id)
    {
        $data['category_id'] = $category_id;
        $where = 'category_id=:category_id:';
        $result = $this->BaseData->select('*', $this->table, $data, $where);
        foreach($result as $k => $v){
            $vdata['attr_name_id'] = $v['id'];
            $map = 'attr_name_id=:attr_name_id:';
            $res = $this->BaseData->select('attr_value ', '\Shop\Models\BaiyangAttrValue', $vdata, $map);
            $result[$k]['attr_value'] = $res ? implode('，', array_column($res, 'attr_value')) : '';
        }
        return $result ? $this->arrayData('', '', $result) : $this->arrayData('暂无数据！', '', '', 'error');
    }

    /**
     * @remark 获取对应分类的商品属性与属性值
     * @param $category_id=int 分类id
     * @return array
     * @author 梁伟
     */
    public function getCategoryAttrAll($category_id)
    {
        $data['category_id'] = $category_id;
        $data['status'] = 1;
        $where = 'category_id=:category_id: and status=:status:';
        $result = $this->BaseData->select('id,attr_name,is_null', $this->table, $data, $where);
        if( $result ){
            foreach($result as $k => $v){
                $vdata['attr_name_id'] = $v['id'];
                $map = 'attr_name_id=:attr_name_id:';
                $res = $this->BaseData->select('id,attr_value ', '\Shop\Models\BaiyangAttrValue', $vdata, $map);
                $result[$k]['attr_value'] = $res;
            }
        }
        return $result ? $this->arrayData('', '', $result) : $this->arrayData('暂无数据！', '', '', 'error');
    }

    public function getCategory($category_id)
    {
        //获取分类路径
        $data['id'] = $category_id;
        $where = 'id=:id:';
        $categoryData = $this->BaseData->select('category_path', '\Shop\Models\BaiyangCategory', $data, $where);
        $categoryPath = explode('/', $categoryData[0]['category_path']);
        $result['category'] = $categoryPath;
        //获取二级分类
        $vdata['pid'] = $categoryPath[0];
        $map = 'pid=:pid:';
        $result['two_category'] = $this->BaseData->select('id,category_name', '\Shop\Models\BaiyangCategory', $vdata, $map);
        //获取三级分类
        $vdata['pid'] = $categoryPath[1];
        $result['three_category'] = $this->BaseData->select('id,category_name', '\Shop\Models\BaiyangCategory', $vdata, $map);
        return !empty($result) ? $this->arrayData('', '', $result) : $this->arrayData('暂无数据！', '', '', 'error');
    }

    /**
     * @remark 添加商品属性与属性值
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function addAttrName($param)
    {
        $result = $this->BaseData->insert($this->table, $param, true);
        $arr = json_decode($param['attrValueJson'], true);
        foreach($arr as $k => $v){
            $data['attr_name_id'] = $result;
            $data['attr_value'] = $v['attr_value'];
            if(empty($v['attr_value']))break;
            $this->BaseData->insert('\Shop\Models\BaiyangAttrValue', $data);
        }
        return $result ? $this->arrayData('添加成功！', '/attrname/list?category_id='.$param['category_id'], '') : $this->arrayData('添加失败！', '', '', 'error');
    }

    /**
     * @remark 更新商品属性与属性值
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function editAttrName($param)
    {
        $columStr = 'attr_name=:attr_name:';
        $where = 'id=:id: and category_id=:category_id:';
        $data['id'] = $param['id'];
        $data['attr_name'] = $param['attr_name'];
        $data['category_id'] = $param['category_id'];
        $result = $this->BaseData->update($columStr, $this->table, $data, $where);

        $arr = json_decode($param['attrValueJson'], true);
        $str = 'attr_value=:attr_value:';
        $map = 'id=:id: and attr_name_id=:attr_name_id:';
        $vdata['attr_name_id'] = $param['id'];
        //删除不存在的属性值
        $dataArr['attr_name_id'] = $param['id'];
        if(!empty(array_filter(array_column($arr, 'id')))){
            $jointStr = '';
            //拼接not in值
            foreach($arr as $key => $val){
                if(!empty($val['id'])){
                    $dataArr['id'. $val['id']] = $val['id'];
                    $jointStr .= empty($jointStr) ? ":id{$val['id']}:" : ",:id{$val['id']}:";
                }
            }
            $condition = "attr_name_id=:attr_name_id: and id not in({$jointStr})";
            $this->BaseData->delete('\Shop\Models\BaiyangAttrValue', $dataArr, $condition);
        }else{
            //空则删除所有
            $condition = 'attr_name_id=:attr_name_id:';
            $this->BaseData->delete('\Shop\Models\BaiyangAttrValue', $dataArr, $condition);
        }
        //更新属性值
        foreach($arr as $k => $v){
            //有id则更新，无id则添加
            if(!empty($v['id'])){
                $vdata['id'] = $v['id'];
                $vdata['attr_value'] = $v['attr_value'];
                $this->BaseData->update($str, '\Shop\Models\BaiyangAttrValue', $vdata, $map);
            }else{
                unset($vdata['id']);
                $vdata['attr_value'] = $v['attr_value'];
                $this->BaseData->insert('\Shop\Models\BaiyangAttrValue', $vdata);
            }
        }
        return $result ? $this->arrayData('修改成功！', '/attrname/list?category_id='.$param['category_id'], '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    /**
     * @remark 更新商品属性启用、必填状态
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function updateAttrString($param)
    {
        $columStr = $this->jointString($param, array('id'));
        $where = 'id=:id:';
        $result = $this->BaseData->update($columStr, $this->table, $param, $where);
        return $result ? $this->arrayData('修改成功！') : $this->arrayData('修改失败！', '', '', 'error');
    }

    /**
     * @remark 删除商品属性与属性值
     * @param $id=int 商品属性id
     * @return array
     * @author 杨永坚
     */
    public function delAttrName($id)
    {
        $data['id'] = $id;
        $where = 'id=:id:';

        $vdata['attr_name_id'] = $id;
        $map = 'attr_name_id=:attr_name_id:';
        $result = $this->BaseData->delete($this->table, $data, $where);
        $this->BaseData->delete('\Shop\Models\BaiyangAttrValue', $vdata, $map);
        return $result ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
    }

    /**
     * @remark 根据分类id获取商品属性与属性值
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function getAttrInfo($param)
    {
        $data['id'] = $param['id'];
        $data['category_id'] = $param['category_id'];
        $where = 'id=:id: and category_id=:category_id:';
        $result = $this->BaseData->select('id,attr_name', $this->table, $data, $where);

        $vdata['attr_name_id'] = $param['id'];
        $map = 'attr_name_id=:attr_name_id:';
        $result['valueData'] = $this->BaseData->select('*', '\Shop\Models\BaiyangAttrValue', $vdata, $map);
        return $result ? array('status'=>'success', 'data'=>$result) : array('status'=>'error');
    }

    /**
     * @remark 导入商品属性数据
     * @param $fileName=string 文件路径
     * @return array
     * @author 杨永坚
     */
    public function importAttr($fileName)
    {
        set_time_limit(0);
        $fp = fopen($fileName, "r");
        $csvData = array();
        while($data = fgetcsv($fp, 1000))
        {
            $count = count($data);
            for($i = 0; $i < $count; $i++)
            {
                $csvData[$i][] = iconv("gbk",'utf-8',$data[$i]);
            }
        }
        fclose($fp);
        $param = array();
        $count = count($csvData[0]);
        for ($j = 0; $j < $count; $j++)
        {
            //取出添加数据
            if ($j > 0)
            {
                $data = array_column($csvData, $j);//取出行记录
                $categoryData = BaseData::getInstance()->getData([
                    'table'=>'\Shop\Models\BaiyangCategory as c',
                    'column'=>'c.id',
                    'join'=>"inner join \Shop\Models\BaiyangCategory as fc on c.pid = fc.id and  fc.category_name = '{$data[1]}' 
                     inner join \Shop\Models\BaiyangCategory as ffc on fc.pid = ffc.id and  ffc.category_name = '{$data[0]}'",
                    'where'=>"where c.category_name = '{$data[2]}'"
                ],true);

                if(isset($categoryData['id'])){
                    $param['category_id'] = $categoryData['id'];
                    $param['attr_name'] = trim($data[3]);
                    $attrValue = explode(',', str_replace('，', ',', trim($data[4])));
                    $valueData = array();
                    for($i=0; $i<count($attrValue); $i++){
                        $valueData[]['attr_value'] = $attrValue[$i];
                    }
                    $param['attrValueJson'] = json_encode($valueData);
                    if(!empty($param['attr_name'])){
                        $result = $this->addAttrName($param);
                    }
                }else{
                    return $this->arrayData('导入失败，'.$data[2].'分类不存在！', '', [], 'error');
                }


            }
        }
        unlink($fileName);
        return $result ? $this->arrayData('导入成功！') : $this->arrayData('导入失败！', '', '', 'error');

    }

}