<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaiyangProductRuleData;

class ProductRuleService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 获取分类信息列表
     * @param  array(
                    'page'=>int,          当前页数，默认1页(非必须)
                    'psize'=>int,         每页显示条数，默认15(非必须)
                     'url'=>string,        跳转链接前缀(必须)
                     'url_back'=>string,   跳转链接后缀(非必须)
                     'home_page'=>string,  首页链接(必须)
                     'size'=>int,          中间显示页数,默认为5(非必须)
                )
     * @return array()
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:21
     */
    public function categoryLists($param)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangCategory';
        $where = ' pid=:pid: ';
        $data['pid'] = 0;
        $BaiyangCategoryData = BaiyangCategoryData::getInstance();
        //总记录数
        $counts = $BaiyangCategoryData->count($table,$data,$where);
        if(!$counts){
            return array(['res' => 'succcess'],['list' => 0]);
            exit;
        }
        //分页
        $pages['page'] = isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])?$param['psize']:15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        //查询
        $selections = 'id,category_name,pid,alias,meta_title,meta_keyword,meta_description';
        $where .= ' limit '.$page['record'].','.$page['psize'];
        $result['lists'] = $BaiyangCategoryData->select($selections,$table,$data,$where);
        $result['page'] = $page['page'];
        return $result;
    }

    /**
     * 获取单个或同一父亲的分类信息
     * @param array(
            'id' => int, //分类ID
            'pid' => int, //父分类ID
       )
     * $atc bool 是否根据条件获取分类(默认为true)
     * @return array()
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:58
     */
    public function getCategory($param,$act = true)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangCategory';
        if($act){
            if(isset($param['id']) && $param['id'] > 0){
                $where = ' id=:id: ';
                $data['id'] = $param['id'];
            }else{
                $where = ' pid=:pid: ';
                $data['pid'] = isset($param['pid'])?$param['pid']:0;
            };
        }else{
            $where = 1;
        }
        $selections = 'id,category_name,pid,alias,meta_title,meta_keyword,meta_description';;
        $result = BaiyangCategoryData::getInstance()->select($selections,$table,$data,$where);
        return $result;
    }

    /**
     * 新增一个分类信息
     * @param array(
        'id' => int, //分类ID
        'pid' => int, //父分类ID
    )
     * @param $act bool true为调用添加页面,false为保存添加数据(默认为true)
     * @return  arra() | bool 数组为父级分类信息，布尔为是否添加成功
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:58
     */
    public function insertCategory($param = array(),$act = true)
    {
        $table = '\Shop\Models\BaiyangCategory';
        $BaiyangCategoryData = BaiyangCategoryData::getInstance();
        if($act){
            $selections = 'id,category_name,pid,alias,meta_title,meta_keyword,meta_description';
            $res = $BaiyangCategoryData->select($selections,$table);
            $res = $this->tree->structureTree($res,'id','pid');
            return $res;
        }else{
            if(empty($param)){
                return false;
            }
            $param['has_child'] = 0;
            $param['add_time'] = time();
            return $BaiyangCategoryData->insert($table,$param);
        }
    }

    /**
     * 修改一个分类信息
     * @param array(
        'id' => int, //分类ID
        'pid' => int, //父分类ID
    )
     * @param $act bool (默认为true)
     * @return bool | arra()
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:58
     */
    public function updateCategory($param = array(),$act = true)
    {
        $table = '\Shop\Models\BaiyangCategory';
        $BaiyangCategoryData = BaiyangCategoryData::getInstance();
        if($act){
            $selections = 'id,category_name,pid,alias,meta_title,meta_keyword,meta_description';
            $res = $BaiyangCategoryData->select($selections,$table);
            $res = $this->tree->structureTree($res,'id','pid');
            return $res;
        }else{
            if(empty($param)){
                return false;
            }
            $param['has_child'] = 0;
            $param['add_time'] = time();
            return $BaiyangCategoryData->insert($table,$param);
        }
    }

    /**
     * @desc 分类删除
     * @param $id int 要删除的分类的ID
     * User: 梁伟
     * Date: 2016/8/23
     * Time: 17:37
     */
    public function delCategory($id)
    {
        $table = '\Shop\Models\BaiyangCategory';
        $data['id'] = $id;
        $where = " id=:id: ";
        return BaiyangCategoryData::getInstance()->delete($table,$data,$where);
    }
    
}
