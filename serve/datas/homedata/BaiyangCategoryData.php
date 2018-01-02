<?php
/**
 * Created by PhpStorm.
 * User: 李斌
 * Date: 2016/8/4 0004
 */
namespace Shop\Home\Datas;
use Composer\Package\Loader\ValidatingArrayLoader;
use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangGoodsCategoryApp;
use Shop\Models\BaiyangGoodsCat;

/**
 * Class BaiyangCategoryData
 * @package Shop\Home\Datas
 * @todo 把入参标注一下
 */
class BaiyangCategoryData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取wap分类数据
     * @return array() 结果信息
     * @author 李斌
     */
    public function getCategoryWapInfo()
    {
        //获取缓存信息
        $this->cache->selectDb(8);

        // 查询wap分类信息
        $categoryTable = '\Shop\Models\BaiyangGoodsCategoryApp a';
        $goodsCatTable   = '\Shop\Models\BaiyangGoodsCat b';
        $selections = 'a.category_id,a.parent_id,a.category_name,a.category_path,a.picture,b.cat_id';
        $phql = "SELECT {$selections} FROM {$categoryTable} 
            LEFT JOIN {$goodsCatTable} ON a.category_id = b.category_id 
            WHERE a.enable=1 GROUP BY a.category_id ORDER BY a.category_id";
        $result = $this->modelsManager->executeQuery($phql);
        if ( !count($result) ) {
            return false;
        }
        $category_list = $result->toArray();

        // 删除没产品的分类
        $category_list = $this->unset_null_product_category($category_list);
        if(!$category_list) {
            return false;
        }

        foreach ($category_list as $key => $category) {
            if (!empty($category['picture']) && !preg_match('/http:\/\/[^\/]+\//', $category['picture'])){
                //$category_list[$key]['picture'] = $this->config->item('image_url').$category['picture'];
            }
        }
        $categories_tree_data = $this->category_tree($category_list);
        
        return $categories_tree_data;
    }

    /**
     * @desc 分类树结构化
     * @return array() 结果信息
     * @author 李斌
     */
    public function category_tree($categories_all_list = array(), $parent_id = 0) {
        $return_list = array();
        foreach ($categories_all_list as $key => $category) {
            if ($category['parent_id'] == $parent_id) {
                unset($categories_all_list[$key]);
                $category['children_list'] = $this->category_tree($categories_all_list, $category['category_id']); //递归
                if (empty($category['children_list'])) {
                    unset($category['children_list']);
                }
                unset($category['sun']); // 删除多余值
                unset($category['cat_id']); // 删除多余值
                $return_list[] = $category;
            }
        }
        return $return_list;
    }

    /**
     * @desc 删除没产品的分类
     * @return array() 结果信息
     * @author 李斌
     */
    public function unset_null_product_category($category_list)
    {
        // 过滤没有商品的分类
        foreach ($category_list as $value) {
            $id                                     = $value['category_id']; // 分类id
            $parent_id                              = $value['parent_id']; // 分类pid
            $category_array[$id]['category_id']     = $value['category_id']; // 分类id
            $category_array[$id]['parent_id']       = $parent_id; // 分类pid
            $category_array[$id]['category_name']   = $value['category_name']; // 分类名称
            $category_array[$id]['picture']         = $value['picture']; // 图片路径
            $category_array[$id]['category_path']   = $value['category_path']; // 分类的路径（显示层级关系）
            $category_array[$id]['cat_id']          = $value['cat_id']; // 商品-分类关联id
            $category_array[$id]['sun']             = array(); // 子分类
            if ( isset($category_array[$parent_id]) ) {
                $category_array[$parent_id]['sun'][$id] = $id;
            }
        }
        while (true) {
            $cat_null = 0; // 跳出循环的标识
            foreach ($category_array as $category_id => $value) {
                if (!$value['cat_id'] && empty($value['sun'])) {
                    unset($category_array[$category_id]); // 删除没有关联商品的分类
                    $parent_id = $value['parent_id'];
                    unset($category_array[$parent_id]['sun'][$category_id]);  // 删除父分类的对应子分类
                    $cat_null = 1;
                }
            }
            if ($cat_null == 0 || empty($category_array) ) {
                break;
            }
        }
        return $category_array;
    }
}