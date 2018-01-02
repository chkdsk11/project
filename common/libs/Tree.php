<?php
/**
 * Created by Zend Studio.
 * User: Administrator
 * Date: 2016/3/23
 * Time: 14:00
 * 
 * 生成树与结构树
 * 使用前提：已经在di中注入该Library
 * 使用方法：$this->tree->spanningTree($data,0,0,'category_id','category_parent_id','level');
 *        $this->tree->structureTree($data,'category_id','category_parent_id','child');
 * 返回结果：自行var_dump查看
 * 
 * ***************************************************
 * 生成树和结构树，前者用到了递归，后者用到了引用传递，且在同一个Tree的class里，
 * 因此在把此类注入到di服务（单例模式，只存在一个Tree?）的时候，不能用静态变量来存储运算后的值，
 * 因为当同时需要调用多次的时候，第一次运算结果会影响到后面所有的运算结果，需要特别注意。
 * ****************************************************
 */
namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class Tree extends LibraryBase
{
    /**
     * 生成树,取出父级分类为parent_id下的所有子类
     * @param array $data => 源数组
     * @param number $parent_id => 父级id编号
     * @param number $level => 级别
     * @param string $id_name => id名称
     * @param string $parent_id_name => parent_id名称
     * @param string $level_name => 级别键名
     */
    public function spanningTree($data = array(), $parent_id = 0, $level = 0, $id_name = 'id', $parent_id_name = 'parent_id', $level_name = 'show_level')
    {
        $spanningTreeData = array();
        foreach ($data as $key => $value) {
            if ($value[$parent_id_name] == $parent_id) {
                $value[$level_name] = $level;
                $spanningTreeData[] = $value;
                unset($data[$key]);
                $spanningTreeData = array_merge($spanningTreeData, $this->spanningTree($data, $value[$id_name], $level + 1, $id_name, $parent_id_name, $level_name));
            }
        }
        return $spanningTreeData;
    }
    
    /**
     * 结构树
     * @param array $data => 源数组
     * @param string $id_name => id名称
     * @param string $parent_id_name => parent_id名称
     * @param string $son => 孩子键名
     */
    public function structureTree($data = array(), $id_name = 'id', $parent_id_name = 'parent_id', $son = 'son')
    {
        $structureTreeData = array();
        $tmpData = array();
        foreach ($data as $dataValue) {
            $tmpData[$dataValue[$id_name]] = $dataValue;
        }
        foreach ($tmpData as $tmpValue) {
            if (isset($tmpData[$tmpValue[$parent_id_name]])) {
                $tmpData[$tmpValue[$parent_id_name]][$son][] = &$tmpData[$tmpValue[$id_name]];
            } else {
                $structureTreeData[] = &$tmpData[$tmpValue[$id_name]];
            }
        }
        return $structureTreeData;
    }

    /**
     * [menuStructureTree 菜单结构树]
     * @param  array   $data           [源数组]
     * @param  string  $id_name        [id名称]
     * @param  string  $parent_id_name [parent_id名称]
     * @param  string  $son            [孩子键名]
     * @param  integer $current_id     [当前id]
     * @param  array   $current_ids    [当前路由的ids]
     * @param  string  $current_name   [字段]
     * @param  string  $show_nav       [字段]
     * @return [type]                  [description]
     */
    public function menuStructureTree($data = array(), $id_name = 'id', $parent_id_name = 'parent_id', $son = 'son',$current_id = 0,$current_ids = array(),$current_name = "current",$show_nav = 'show_nav')
    {
        $structureTreeData = array();
        $tmpData = array();
        foreach ($data as $dataValue) {
            $dataValue[$current_name] = 0;
            if($current_ids && in_array($dataValue[$id_name],$current_ids)){
                // 当前的可能不显示
                if($current_id == $dataValue[$id_name]){
                    if($dataValue[$show_nav] == 1){
                        $dataValue[$current_name] = 1;
                    }
                }else{
                    $dataValue[$show_nav] = 1;
                    $dataValue[$current_name] = 1;
                }
            }
            if($dataValue[$show_nav]){
                $tmpData[$dataValue[$id_name]] = $dataValue;
            }
        }
        foreach ($tmpData as $tmpValue) {
            if (isset($tmpData[$tmpValue[$parent_id_name]])) {
                $tmpData[$tmpValue[$parent_id_name]][$son][] = &$tmpData[$tmpValue[$id_name]];
            } else {
                $structureTreeData[] = &$tmpData[$tmpValue[$id_name]];
            }
        }
        return $structureTreeData;
    }

}