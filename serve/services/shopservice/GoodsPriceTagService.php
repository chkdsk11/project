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
use Shop\Datas\GoodsPriceTagData;


class GoodsPriceTagService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * @remark 获取会员标签
     * @param $param=array
     * @return array
     * @author 杨永坚
     */
    public function getTagList($param)
    {
        //查询条件
        $where = '';
        $data = '';
        $table = array(
            'priceTable' => '\Shop\Models\BaiyangGoodsPrice as p',
            'tagTable' => '\Shop\Models\BaiyangGoodsPriceTag as t'
        );
        if(!empty($param['seaData']['tag_name'])){
            $data['tag_name'] = '%'.$param['seaData']['tag_name'].'%';
            $where .= "t.tag_name LIKE :tag_name:";
        }
        if($param['seaData']['status'] >= 0){
            $data['status'] = $param['seaData']['status'];
            $where .= empty($where) ? "t.status = :status:" : " AND t.status = :status:";
        }

        $where .= empty($where) ? " 1 GROUP BY t.tag_id" : " GROUP BY t.tag_id";
        //总记录数
        $count = GoodsPriceTagData::getInstance()->selectJoin('t.tag_id', $table, $data, $where);
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

        $selections = 't.tag_id,t.tag_name,t.status,t.remark,t.add_time,count(p.goods_id) as goods_number';
        $where .= ' order by t.add_time desc limit '.$page['record'].','.$page['psize'];

        $result = GoodsPriceTagData::getInstance()->selectJoin($selections, $table, $data, $where);
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
     * @desc 判断会员标签是否重复
     * @param $tagName
     * @author 邓永军
     */
    private function isRepeatTag($tagName)
    {
        $baseData = BaseData::getInstance();
        $count = $baseData->countData([
            'table' => '\Shop\Models\BaiyangGoodsPriceTag',
            'where' => 'where tag_name = :tag_name:',
            'bind' => [
                'tag_name' => $tagName
            ]
        ]);
        return $count > 0 ? 1:0;
    }

    public function addGoodsPriceTag($param)
    {
        if($this->isRepeatTag($param['tag_name']) == 1) return $this->arrayData('会员标签名称不能重复,添加失败！', '', '', 'error');
        $result = BaseData::getInstance()->insert('\Shop\Models\BaiyangGoodsPriceTag', $param);
        return $result ? $this->arrayData('添加成功！', '/goodspricetag/list', '') : $this->arrayData('添加失败！', '', '', 'error');
    }

    /**
     * @remark 更新会员标签
     * @param $param=array
     * @return array
     * @author 罗毅庭
     */
    public function editGoodsPriceTag($param)
    {
        if(isset($param['tag_name'])) {
            $count = BaseData::getInstance()->countData([
                'table' => '\Shop\Models\BaiyangGoodsPriceTag',
                'where' => 'where tag_name = :tag_name:',
                'bind' => [
                    'tag_name' => $param['tag_name']
                ]
            ]);
            if ($count > 1) {
                return $this->arrayData('会员标签名称不能重复,添加失败！', '', '', 'error');
            }
        }
        $columStr = $this->jointString($param, array('tag_id'));
        $where = 'tag_id=:tag_id:';
        $resultEdit = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangGoodsPriceTag', $param, $where);
        if($resultEdit){
            $columStr = $this->jointString($param, array('tag_id','tag_name','remark'));
     $params = array(
         'status'=>$param['status'],
         'add_time'=>$param['add_time'],
         'tag_id'=>$param['tag_id']
     );
            $where = 'tag_id=:tag_id:';
            $result = BaseData::getInstance()->update($columStr,'\Shop\Models\BaiyangUserGoodsPriceTag', $params, $where);
        }
        return $result ? $this->arrayData('修改成功！', '/goodspricetag/list', '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    /**
     * @remark 更新单个会员标签状态
     * @param $param=array
     * @return array
     * @author 罗毅庭
     */

    public function editUserGoodsPriceTag($param)
    {
        $data['tag_id'] = $param['tag_id'];
        $where = 'tag_id=:tag_id:';
        $status = BaseData::getInstance()->select('status', '\Shop\Models\BaiyangGoodsPriceTag', $data, $where);
        if($status[0]['status'] != 1){
            return $this->arrayData('会员标签总状态关闭，禁止编辑', '', '', 'error');
        }
        $columStr = $this->jointString($param, array('tag_id','user_id'));

        $where = 'tag_id=:tag_id: AND user_id=:user_id:';
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangUserGoodsPriceTag', $param, $where);
        return $result ? $this->arrayData('修改成功！', '/goodspricetag/bindmemberlist', '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    public function delGoodsPriceTag($param)
    {
        $request = isset($param['request']) ? $param['request'] : '';
        $data['tag_id'] = $param['tag_id'];
        $where = 'tag_id=:tag_id:';
        $where .= " limit 1";
        $tag_id = BaseData::getInstance()->select('tag_id', '\Shop\Models\BaiyangGoodsPrice', $data, $where);
        if($tag_id[0]['tag_id'] != ''){
            return $this->arrayData('商品已添加此标签，禁止删除！', '', '', 'error');
        }
        $user_tag_id = BaseData::getInstance()->select('tag_id', '\Shop\Models\BaiyangUserGoodsPriceTag', $data, $where);
        if($user_tag_id[0]['tag_id'] != ''){
            return $this->arrayData('会员已绑定此标签，禁止删除！', '', '', 'error');
        }
        $result = BaseData::getInstance()->delete('\Shop\Models\BaiyangGoodsPriceTag', $data, $where);
        $url = $request ? '/goodspricetag/list'.$request : '/goodspricetag/list';
        return $result ? $this->arrayData('删除成功！', $url) : $this->arrayData('删除失败！', '', '', 'error');
    }

    /**
     * @remark 根据id获取对应的会员标签
     * @param $id=int
     * @return array
     * @author 杨永坚
     */
    public function getGoodsPriceTagInfo($id)
    {
        $data['tag_id'] = $id;
        $where = 'tag_id=:tag_id:';
        $result = BaseData::getInstance()->select('*', '\Shop\Models\BaiyangGoodsPriceTag', $data, $where);
        return $result ? array('status'=>'success', 'data'=>$result) : array('status'=>'error');
    }

    /**
     * @remark 获取会员标签
     * @return array
     * @author 杨永坚
     */
    public function getGoodsPriceTag($is_true = false)
    {
        $data['status'] = 1;
        $where = 'status=:status:';
        $result = !$is_true ? BaseData::getInstance()->select('tag_id,tag_name', '\Shop\Models\BaiyangGoodsPriceTag', $data, $where) : BaseData::getInstance()->select('tag_id,tag_name', '\Shop\Models\BaiyangGoodsPriceTag');
        return $result ? array('status'=>'success', 'data'=>$result) : array('status'=>'error');
    }

    public function getBindMemberList($param)
    {
        //查询条件
        $where = '';
        $data = '';
        $table = array(
            'priceTable' => '\Shop\Models\BaiyangGoodsPriceTag as g',
            'tagTable' => '\Shop\Models\BaiyangUserGoodsPriceTag as t',
            'userTable' => '\Shop\Models\BaiyangUser as u',
        );
        if(!empty($param['seaData']['keyword'])){
            $data['keyword'] = '%'.$param['seaData']['keyword'].'%';
            $where .= "(u.phone like :keyword: OR u.username like :keyword: OR g.tag_name like :keyword:)";
        }
        if($param['seaData']['searchTag'] > 0){
            $data['tag_id'] = $param['seaData']['searchTag'];
            $where .= empty($where) ? "t.tag_id = :tag_id:" : " AND t.tag_id = :tag_id:";
        }
        $where = empty($where) ? 1 : $where;
        //总记录数
        $count = GoodsPriceTagData::getInstance()->selectJoinUser('t.tag_id', $table, $data, $where);
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

        $selections = 'u.phone,u.username,g.tag_name,t.add_time,t.remark,t.tag_id,t.user_id,t.status';
        $where .= ' order by t.add_time desc limit '.$page['record'].','.$page['psize'];

        $result = GoodsPriceTagData::getInstance()->selectJoinUser($selections, $table, $data, $where);
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

    public function addTag($param)
    {
        $baseData = BaseData::getInstance();
        $checkNum = $this->checkAddData($param['tag_id'], $param['phone']);
        if( $checkNum == 0) {
            $userInfo = $baseData->select('id', '\Shop\Models\BaiyangUser', array('phone' => $param['phone']), 'phone = :phone:');
            $param['user_id'] = $userInfo[0]['id'];
            $tagStatus = $baseData->select('status', '\Shop\Models\BaiyangGoodsPriceTag', array('tag_id' => $param['tag_id']), 'tag_id = :tag_id:');
            $param['status'] = $tagStatus[0]['status'];
            $result = $baseData->insert('\Shop\Models\BaiyangUserGoodsPriceTag', $param);
            $mes = $result ? '添加成功！' : '添加失败！';
        } elseif ( $checkNum == 1) {
            $mes = '用户手机账号 和 标签不能为空！';
        } elseif ( $checkNum == 2) {
            $mes = '找不到该手机对应的用户！';
        } else {
            $mes = '该用户已有该标签！';
        }
        return $result ? $this->arrayData($mes, '/goodspricetag/bindmemberlist', '') : $this->arrayData($mes, '', '', 'error');
    }

    public function editTag($param)
    {
        $columStr = $this->jointString($param, array('tag_id', 'user_id'));
        $where = 'tag_id=:tag_id: and user_id=:user_id:';
        $result = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangUserGoodsPriceTag', $param, $where);
        return $result ? $this->arrayData('修改成功！', '/goodspricetag/bindMemberList', '') : $this->arrayData('修改失败！', '', '', 'error');
    }

    public function delTag($param)
    {
        $request = isset($param['request']) ? $param['request'] : '';
        if(empty($param['tag_id']) || empty($param['user_id'])) {
            $this->arrayData('参数不完，请重试！', '', '', 'error');
        }
        $where = 'tag_id=:tag_id: and user_id=:user_id:';
        unset($param['request']);
        $url = $request ? '/goodspricetag/bindmemberlist'.$request : '/goodspricetag/bindmemberlist';
        $result = BaseData::getInstance()->delete('\Shop\Models\BaiyangUserGoodsPriceTag', $param, $where);
        return $result ? $this->arrayData('删除成功！', $url) : $this->arrayData('删除失败！', '', '', 'error');
    }

    /**
     * @remark 批量删除绑定会员
     * @author 罗毅庭
     */
    public function betchDelTag($param){
        foreach($param as $data){
            if(empty($data['tag_id']) || empty($data['user_id'])) {
                $this->arrayData('参数不完，请重试！', '', '', 'error');
            }
            $where = 'tag_id=:tag_id: and user_id=:user_id:';
            BaseData::getInstance()->delete('\Shop\Models\BaiyangUserGoodsPriceTag', $data, $where);
        }
        return $this->arrayData('删除成功！');
    }

    public function importTag($params)
    {
        $fileName = $params['filename'];
        $addLargeTag = $params['tag_id'];
        $baseData = BaseData::getInstance();
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
        $count = count($csvData[0]);
        //检查数据是否合法
        $repeatData = array_unique($this->FetchRepeatMemberInArray($count));
        //检查是否存在重复手机账号的情况
        if( $repeatData ) {
            $this->arrayData('12313');
            $repeatStr = implode(', ', $repeatData);
            return $this->arrayData('导入失败！存在重复的手机账户有 : '. $repeatStr, '', '', 'error');
        }
        $addCount = 0;//统计添加成功数量
        for ($j = 0; $j < $count; $j++)
        {
            //取出添加数据
            if ($j > 0)
            {
                $data = array_column($csvData, $j);//取出行记录
                //合法数据情况
                if($this->checkAddData( $addLargeTag , $data[0]) == 0 ) {
                    $userInfo = $baseData->select('id', '\Shop\Models\BaiyangUser', array('phone' => $data[0]), 'phone = :phone:');
                    $memberTagData['user_id'] = $userInfo[0]['id'];
                    $memberTagData['tag_id'] = $addLargeTag;
                    $memberTagData['remark'] = trim($data[1]);
                    $memberTagData['add_time'] = time();
                    $res = $baseData->insert('\Shop\Models\BaiyangUserGoodsPriceTag', $memberTagData);
                    $addCount = $res ? $addCount + 1 : $addCount;
                    //非法数据情况1:存在空数据
                } elseif ( $this->checkAddData( $addLargeTag , $data[0] ) == 1 ) {
                    $error1Data[$j]['phone'] = $data[0];
                    //非法数据情况2:手机账号无对应的userid
                } elseif ( $this->checkAddData( $addLargeTag , $data[0] ) == 2 ) {
                    $error2Data[$j]['phone'] = $data[0];
                    //非法数据情况3:该手机用户已拥有该标签
                } else {
                    $error3Data[$j]['phone'] = $data[0];
                }
            }
        }
        unlink($fileName);
        $result = $addCount == 0 ? false : true;
        //组合问题情况说明字符串
        if(!empty($error1Data)) {
            $str1 = '存在手机号为空的情况(共'.count($error1Data).'条).';
        }
        if(!empty($error2Data)) {
            $str2 = '手机账号用户不存在情况(共'.count($error2Data).'条): ';
            foreach($error2Data as $v2){
                $str2.= ' '.implode(',',$v2);
            }
        }
        if(!empty($error3Data)) {
            $str3 = '手机账户已拥有该标签情况(共'.count($error3Data).'条): ';
            foreach($error3Data as $v3){
                $str3.= ' '.implode(',',$v3);
            }
        }
        $returnStr = $str1.' '.$str2.' '.$str3;
        //数据插入为空情况
        if(empty($result)) {
            $mes = '导入失败！'.$returnStr;
            //非法数据为空情况
        } elseif (empty($error1Data) && empty($error2Data) && empty($error3Data) && $res > 0){
            $mes = '导入成功！成功插入'. $addCount .'条数据。';
            //存在插入数据和非法数据情况
        } else {
            $mes = '导入成功！成功插入'. $addCount .'合法数据,存在非法数据,情况如下 : <br/>'. $returnStr;
        }
        return $result ? $this->arrayData($mes) : $this->arrayData($mes, '', '', 'error');
    }

    public function getMemberTagList()
    {
        return BaseData::getInstance()->select('tag_id,tag_name', '\Shop\Models\BaiyangGoodsPriceTag');
    }

    /**
     * @remark 检测标签
     * @param $tag_id 标签id
     * @param $phone 手机号
     * @return int 0:true 1用户手机账号 和 标签不能为空 2找不到该手机对应的用户 3该用户已有该标签
     * @author 杨永坚
     */
    protected function checkAddData($tag_id, $phone)
    {
        //检查参数是否为空
        if(empty($tag_id) || empty($phone)) {
            return 1;
        }
        $baseData = BaseData::getInstance();
        //查询手机账号对应userid
        $userInfo = $baseData->select('id', '\Shop\Models\BaiyangUser', array('phone' => $phone), 'phone=:phone:');
        $user_id = $userInfo[0]['id'];
        if($user_id){
            //检查该用户是否已有该标签
            $checkExist = $baseData->select('tag_id', '\Shop\Models\BaiyangUserGoodsPriceTag', array('user_id' => $user_id, 'tag_id' => $tag_id), 'user_id = :user_id: and tag_id = :tag_id: limit 1');
            if($checkExist) {
                return 3;
            } else {
                return 0;
            }
        } else {
            return 2;
        }
    }

    /**
     * @remark 查询数组中重复的值
     * @param $array
     * @return array 数组中重复的部分
     * @author 杨永坚
     */
    protected function FetchRepeatMemberInArray($array) {
        $len = count ( $array );
        for($i = 0; $i < $len; $i ++) {
            for($j = $i + 1; $j < $len; $j ++) {
                if ($array [$i] == $array [$j]) {
                    $repeat_arr [] = $array [$i];
                    break;
                }
            }
        }
        return $repeat_arr;
    }
}