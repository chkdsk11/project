<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/29
 * Time: 9:52
 */

namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\GoodsTreatmentData;

class GoodsTreatmentService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * @remark 获取疗程列表
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function getGoodsTreatmentList($param)
    {
        //查询条件
        $where = '';
        $data['status'] = 0;
        $table = array(
            'goodsTreatmentTable' => '\Shop\Models\BaiyangGoodsTreatment as t',
            'skuTable' => '\Shop\Models\BaiyangGoods as g'
        );
        if(!empty($param['seaData']['goods_name'])){
            $data['goods_name'] = '%'.$param['seaData']['goods_name'].'%';
            $where .= "(g.goods_name LIKE :goods_name: OR g.id LIKE :goods_name:)";
        }
        if(!empty($param['seaData']['platform'])){
            $string = $param['seaData']['platform'];
            $data[$string] = 1;
            $where .= empty($where) ? "t.$string = :$string:" : " AND t.$string = :$string:";
        }
        if(!empty($param['seaData']['status'])){
            $data['status'] = $param['seaData']['status'];
            $where .= empty($where) ? "t.status = :status:" : " AND t.status = :status:";
        }else{
            $where .= empty($where) ? "t.status > :status:" : " AND t.status > :status:";
        }

        $where .= " GROUP BY goods_id";
        //总记录数
        $count = GoodsTreatmentData::getInstance()->selectJoin('t.id', $table, $data, $where);
        $counts = $count ? count($count) : 0;
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

        $selections = 't.id,g.id as goods_id,g.goods_name,t.create_time,t.status,t.platform_pc,t.platform_app,t.platform_wap,t.platform_wechat';
        $where .= ' order by t.create_time desc limit '.$page['record'].','.$page['psize'];

        $result = GoodsTreatmentData::getInstance()->selectJoin($selections, $table, $data, $where);
        if(!empty($result)){
            foreach($result as $k => $v){
                $tData['status'] = 0;
                $tData['goods_id'] = $v['goods_id'];
                $condition = 'status > :status: and goods_id = :goods_id:';
                $TreatmentData = BaseData::getInstance()->select('promotion_msg,unit_price,min_goods_number', '\Shop\Models\BaiyangGoodsTreatment', $tData, $condition);
                $v['t_count'] = count($TreatmentData);
                $v['ladder'] = $TreatmentData;
                $platform = $v['platform_pc'] ? 'PC、' : '';
                $platform .= $v['platform_app'] ? 'APP、' : '';
                $platform .= $v['platform_wap'] ? 'WAP、' : '';
                $platform .= $v['platform_wechat'] ? '微商城、' : '';
                $v['platform'] = rtrim($platform, '、');
                $result[$k] = $v;
            }
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
     * @remark 添加疗程
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function addGoodsTreatment($param)
    {
        if(empty($param['goods_id'])){
            return $this->arrayData('请选择商品！', '', '', 'error');
        }
        if($param['min_goods_number'] <= 1){
            return $this->arrayData('件数要大于等于2！', '', '', 'error');
        }
        if(!is_numeric($param['unit_price'])){
            return $this->arrayData('请输入正确的价格！', '', '', 'error');
        }
        if($param['unit_price'] <= 0){
            return $this->arrayData('价格不能小于0！', '', '', 'error');
        }
        if(strlen($param['promotion_msg']) > 48){
            return $this->arrayData('促销语不能大于16字！', '', '', 'error');
        }
        $data['goods_id'] = $param['goods_id'];
        $data['min_goods_number'] = $param['min_goods_number'];
        $where = 'goods_id=:goods_id: AND min_goods_number=:min_goods_number: AND status<>0';
        $val = BaseData::getInstance()->select('id', '\Shop\Models\BaiyangGoodsTreatment', $data, $where);
        if($val){
            return $this->arrayData('已有相同数量的疗程！', '', '', 'error');
        }
        $resVal = $this->getTreatmentNumber($param);
        if($resVal){
            return $this->arrayData($resVal, '', '', 'error');
        }
        $param['status'] = 1;
        $result = BaseData::getInstance()->insert('\Shop\Models\BaiyangGoodsTreatment', $param, true);
        $map['goods_id'] = (int)$param['goods_id'];
        $map['platform_pc'] = (int)$param['platform_pc'];
        $map['platform_app'] = (int)$param['platform_app'];
        $map['platform_wap'] = (int)$param['platform_wap'];
        $map['platform_wechat'] = 0;
        $map['promotion_mutex'] = $param['promotion_mutex'];
        $condition = 'goods_id=:goods_id:';
        $res = BaseData::getInstance()->update('platform_pc=:platform_pc:,platform_app=:platform_app:,platform_wap=:platform_wap:,platform_wechat=:platform_wechat:,promotion_mutex=:promotion_mutex:', '\Shop\Models\BaiyangGoodsTreatment', $map, $condition);
        return $result && $res ? $this->arrayData('添加成功！', '/goodstreatment/list', $result) : $this->arrayData('添加失败！', '', '', 'error');
    }

    /**
     * @remark 修改疗程
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function editGoodsTreatment($param)
    {
        if($param['min_goods_number'] <= 1){
            return $this->arrayData('件数要大于等于2！', '', '', 'error');
        }
        if(!is_numeric($param['unit_price'])){
            return $this->arrayData('请输入正确的价格！', '', '', 'error');
        }
        if($param['unit_price'] <= 0){
            return $this->arrayData('价格不能小于0！', '', '', 'error');
        }
        if(strlen($param['promotion_msg']) > 48){
            return $this->arrayData('促销语不能大于16字！', '', '', 'error');
        }
        $resVal = $this->getTreatmentNumber($param);
        if($resVal){
            return $this->arrayData($resVal, '', '', 'error');
        }
        $data['id'] = $param['id'];
        $data['goods_id'] = $param['goods_id'];
        $data['min_goods_number'] = $param['min_goods_number'];
        $where = 'id <> :id: AND goods_id=:goods_id: AND min_goods_number=:min_goods_number: AND status<>0';
        $val = BaseData::getInstance()->select('id', '\Shop\Models\BaiyangGoodsTreatment', $data, $where);
        if($val){
            return $this->arrayData("已有相同数量为（{$data['min_goods_number']}）的疗程！", '', '', 'error');
        }
        $columStr = $this->jointString($param, array('id'));
        $where = 'id=:id:';
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangGoodsTreatment', $param, $where);
        $map['goods_id'] = (int)$param['goods_id'];
        $map['platform_pc'] = (int)$param['platform_pc'];
        $map['platform_app'] = (int)$param['platform_app'];
        $map['platform_wap'] = (int)$param['platform_wap'];
        $map['platform_wechat'] = 0;
        $map['promotion_mutex'] = $param['promotion_mutex'];
        $condition = 'goods_id=:goods_id:';
        $res = BaseData::getInstance()->update('platform_pc=:platform_pc:,platform_app=:platform_app:,platform_wap=:platform_wap:,platform_wechat=:platform_wechat:,promotion_mutex=:promotion_mutex:', '\Shop\Models\BaiyangGoodsTreatment', $map, $condition);
        return $result && $res ? $this->arrayData('修改成功！', '/brands/list', '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    /**
     * @remark 更新疗程status状态 相当删除
     * @param $param
     * @return array
     * @author 杨永坚
     */
    public function updateGoodsTreatment($param)
    {
        $request = isset($param['request']) ? $param['request'] : '';
        unset($param['request']);
        $columStr = $this->jointString($param, array('goods_id'));
        $where = 'goods_id=:goods_id: and status <> 0';
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangGoodsTreatment', $param, $where);
        $msg = $param['status'] == 0 ? '删除' : '修改';
        $url = $request ? '/goodstreatment/list'.$request : '/goodstreatment/list';
        return $result ? $this->arrayData($msg. '成功！',$url) : $this->arrayData($msg. '修改失败！', '', '', 'error');
    }

    /**
     * @remark 获取对应商品id的疗程
     * @param $goods_id=int
     * @return array
     * @author 杨永坚
     */
    public function getGoodsTreatmentInfo($goods_id)
    {
        $data['goods_id'] = $goods_id;
        $data['status'] = 0;
        $where = 'goods_id=:goods_id: and status > :status:';
        $result = BaseData::getInstance()->select('*', '\Shop\Models\BaiyangGoodsTreatment', $data, $where);
        foreach($result as $k => $v){
            $result[$k]['promotion_mutex'] = explode(',', $v['promotion_mutex']);
        }
        return $result ? array('status'=>'success', 'data'=>$result) : array('status'=>'success', 'data'=>$result, 'info'=>'无数据');
    }

    /**
     * @remark 删除疗程
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function delGoodsTreatment($param)
    {
        $columStr = $this->jointString($param, array('id'));
        $where = 'id=:id:';
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangGoodsTreatment', $param, $where);
        return $result ? $this->arrayData('删除成功！','/goodstreatment/list') : $this->arrayData('删除失败！', '', '', 'error');
    }

    /**
     * @remark 判断疗程价格规则
     * @param array $param
     * @return bool|string
     * @author 杨永坚
     */
    public function getTreatmentNumber($param = array())
    {
        $numData['goods_id'] = $param['goods_id'];
        $numWhere = 'goods_id=:goods_id: AND status<>0';
        $maxNum = BaseData::getInstance()->select('min_goods_number,unit_price', '\Shop\Models\BaiyangGoodsTreatment', $numData, $numWhere. ' order by min_goods_number desc limit 1');
        $minNum = BaseData::getInstance()->select('min_goods_number,unit_price', '\Shop\Models\BaiyangGoodsTreatment', $numData, $numWhere. ' order by min_goods_number limit 1');
        $maxNumber = $maxNum[0]['min_goods_number'];
        $maxPrice = $maxNum[0]['unit_price'];
        $minNumber = $minNum[0]['min_goods_number'];
        $minPrice = $minNum[0]['unit_price'];
        //单价 数量大价格一定要比数量小的低；数量小价格一定要比数量大的高
//        if(((($param['min_goods_number'] > $maxNumber && $param['unit_price'] >= $maxPrice) || ($param['min_goods_number'] < $maxNumber && $param['unit_price'] <= $maxPrice)) || (($param['min_goods_number'] < $minNumber && $param['unit_price'] <= $minPrice) || ($param['min_goods_number'] > $minNumber && $param['unit_price'] >= $minPrice))) && !empty($maxNumber)){
//            return '请按照件数越多价格越低的规则设置';
//        }
        if((($param['min_goods_number'] > $maxNumber && $param['unit_price'] >= $maxPrice)
                || ($param['min_goods_number'] > $minNumber && $param['unit_price'] >= $minPrice)
            ) && !empty($maxNumber)){
            return '请按照件数越多价格越低的规则设置';
        }
        return false;
    }

}