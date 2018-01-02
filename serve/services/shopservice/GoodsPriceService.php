<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/10/8
 * Time: 14:01
 */

namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\GoodsPriceData;


class GoodsPriceService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * @remark 获取会员商品列表
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function getGoodsPriceList($param)
    {
        //查询条件
        $where = '';
        $data = '';
        $table = array(
            'priceTable' => '\Shop\Models\BaiyangGoodsPrice as p',
            'skuTable' => '\Shop\Models\BaiyangGoods as s',
            'tagTable' => '\Shop\Models\BaiyangGoodsPriceTag as t'
        );
        if(!empty($param['seaData']['goods_name'])){
            $data['goods_name'] = '%'.$param['seaData']['goods_name'].'%';
            $where .= "(s.goods_name LIKE :goods_name: OR p.goods_id LIKE :goods_name:)";
        }
        if(!empty($param['seaData']['tag_id'])){
            $data['tag_id'] = $param['seaData']['tag_id'];
            $where .= empty($where) ? "p.tag_id = :tag_id:" : " AND p.tag_id = :tag_id:";
        }
        if(!empty($param['seaData']['platform'])){
            $string = $param['seaData']['platform'];
            $data[$string] = 1;
            $where .= empty($where) ? "p.$string = :$string:" : " AND p.$string = :$string:";
        }

        $where .= empty($where) ? ' 1 ' : '';
        //总记录数
        $count = GoodsPriceData::getInstance()->selectJoin('count(p.tag_id) as count', $table, $data, $where);
        $counts = is_array($count) ? $count[0]['count'] : 0;
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

        $selections = 'p.tag_goods_id,p.tag_id,p.goods_id,t.tag_name,p.price as member_price,p.rebate,p.limit_number,p.type,p.platform_pc,p.platform_app,p.platform_wap,p.platform_wechat,p.add_time,s.id,s.goods_name,s.goods_price as price,s.product_code';
        $where .= ' order by p.add_time desc limit '.$page['record'].','.$page['psize'];

        $result = GoodsPriceData::getInstance()->selectJoin($selections, $table, $data, $where);
        foreach($result as $k => $v){
            $platform = $v['platform_pc'] ? 'PC、' : '';
            $platform .= $v['platform_app'] ? 'APP、' : '';
            $platform .= $v['platform_wap'] ? 'WAP' : '';
            $platform .= $v['platform_wechat'] ? '微商城' : '';
            $result[$k]['platform'] = rtrim($platform, '、');
            if($v['tag_name'] == ''){
                $result[$k]['tag_id']=0;
            }
        }
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
     * @remark 添加会员商品
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function addGoodsPrice($param)
    {
        foreach ($param['gooddata'] as $key=>$val)
        {
            $data['tag_id'] = $val['tag_id'];
            $data['goods_id'] = $val['goods_id'];
            $data['type'] = $val['type'];
            if($val['type'] == 1){
                $data['price'] = $val['value'];
            }else{
                if (!preg_match('/^[1-9](\.\d)?$|^0(\.[1-9])$/', $val['value'])) {
                    return $this->arrayData('请输入正确的折扣！', '', '', 'error');
                }
                $data['rebate'] = $val['value'];
            }
            $data['limit_number'] = isset($val['limit_number']) ? $val['limit_number'] : 0;
            $data['platform_pc'] = isset($val['platform_pc']) ? $val['platform_pc'] : 0;
            $data['platform_app'] = isset($val['platform_app']) ? $val['platform_app'] : 0;
            $data['platform_wap'] = isset($val['platform_wap']) ? $val['platform_wap'] : 0;
            $data['platform_wechat'] = 0;
            if(isset($val['good_set_mutex']) && !empty($val['good_set_mutex'])){
                $data['mutex'] = implode(',',$val['good_set_mutex']);
            }else{
                $data['mutex'] = '';
            }
            $data['add_time'] = time();
            $check = BaseData::getInstance()->select('tag_goods_id', '\Shop\Models\BaiyangGoodsPrice', array('goods_id'=>$val['goods_id'],'tag_id'=>$val['tag_id']), 'goods_id=:goods_id: and tag_id=:tag_id:');
            if(!empty($check)){
                return $this->arrayData($val['goods_id']. '此商品与标签已存在！', '', '', 'error');
            }
            $result = BaseData::getInstance()->insert('\Shop\Models\BaiyangGoodsPrice', $data);
        }
        return $result ? $this->arrayData('添加成功！', '/goodsprice/list', '') : $this->arrayData('添加失败！', '', '', 'error');
    }

    /**
     * @remark 更新会员商品
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function editGoodsPrice($param)
    {

        if(isset($param['good_set_mutex']) && !empty($param['good_set_mutex'])){
            $good_set_mutex = implode(',',$param['good_set_mutex']);
            unset($param['good_set_mutex']);
        }else{
            $good_set_mutex = '';
        }
        $columStr = $this->jointString($param, array('tag_goods_id','value'));
        $where = 'tag_goods_id=:tag_goods_id:';
        if($param['type'] == 1){
            $param['price'] = $param['value'];
            $columStr .= ',price=:price:';
        }else{
            $param['rebate'] = $param['value'];
            $columStr .= ',rebate=:rebate:';
        }
        unset($param['value']);
        if(!isset($param['platform_pc'])){
            $columStr .= ',platform_pc=:platform_pc:';
            $param['platform_pc'] = 0;
        }
        if(!isset($param['platform_app'])){
            $columStr .= ',platform_app=:platform_app:';
            $param['platform_app'] = 0;
        }
        if(!isset($param['platform_wap'])){
            $columStr .= ',platform_wap=:platform_wap:';
            $param['platform_wap'] = 0;
        }
        if(!isset($param['platform_wechat'])){
            $columStr .= ',platform_wechat=:platform_wechat:';
            $param['platform_wechat'] = 0;
        }
        $param['platform_wechat'] = 0; //微商城暂时写死屏蔽
        $columStr .= ',mutex=:mutex:';
        $param['mutex'] = $good_set_mutex;
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangGoodsPrice', $param, $where);
        return $result ? $this->arrayData('修改成功！', '/goodsprice/list', '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    /**
     * @remark 根据id删除会员商品
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function delGoodsPrice($param)
    {
        $request = isset($param['request']) ? $param['request'] : '';
        $data['tag_goods_id'] = $param['tag_goods_id'];
        $where = 'tag_goods_id=:tag_goods_id:';
        $result = BaseData::getInstance()->delete('\Shop\Models\BaiyangGoodsPrice', $data, $where);
        $url = $request ? '/goodsprice/list'.$request : '/goodsprice/list';
        return $result ? $this->arrayData('删除成功！', $url) : $this->arrayData('删除失败！', '', '', 'error');
    }

    /**
     * @remark 根据id获取对应的会员商品记录
     * @param $tag_goods_id=int
     * @return array
     * @author 杨永坚
     */
    public function getGoodsPriceInfo($tag_goods_id)
    {
        $table = array(
            'priceTable' => '\Shop\Models\BaiyangGoodsPrice as p',
            'skuTable' => '\Shop\Models\BaiyangGoods as s',
        );
        $data['tag_goods_id'] = $tag_goods_id;
        $where = 'p.tag_goods_id=:tag_goods_id:';
        $result = GoodsPriceData::getInstance()->joinSku('p.mutex,p.tag_goods_id,p.tag_id,p.goods_id,p.price as value,p.rebate,p.limit_number,p.type,p.platform_pc,p.platform_app,p.platform_wap,p.platform_wechat,s.goods_name,s.goods_price as purchase_price', $table, $data, $where);
        if($result){
            //检查会员标签
            $tagdata['tag_id'] = $result[0]['tag_id'];
            $where = 'tag_id=:tag_id:';
            $tag_id = BaseData::getInstance()->select('tag_id', '\Shop\Models\BaiyangGoodsPriceTag', $tagdata, $where);
            if(empty($tag_id[0]['tag_id'])){
                $result[0]['tag_id']=0;
            }
            $result[0]['value'] = $result[0]['type'] == 1 ? $result[0]['value'] : $result[0]['rebate'];
            if($result[0]['mutex'] != '') {
                $result[0]['mutex'] = explode(',',$result[0]['mutex']);
            }
        }
        return $result ? array('status'=>'success', 'data'=>$result) : array('status'=>'error');
    }
}