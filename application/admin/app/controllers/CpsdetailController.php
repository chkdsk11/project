<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;
class CpsDetailController extends ControllerBase
{
    public function listAction()
    {

        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $this->view->setVar('channel',$param);
        $count_sql=" where cu.invite_code <> '' ";
        if (isset($param['user_id']) && $param['user_id']) {
            $count_sql .= "and (u.user_id='{$param['user_id']}' OR u.phone='{$param['user_id']}') ";
        }
        if (isset($param['cps_user_id']) && $param['cps_user_id']) {
            $count_sql .= "and cu.user_id ='".$param['cps_user_id']."' ";
        }
        if (isset($param['user_name']) && $param['user_name']) {
            $count_sql .= "and cu.user_name like ".'"%'.$param['user_name'].'%"' ;
        }
        if (!empty($param['start_time']) && $param['end_time']) {
            $count_sql .= "and cuc.bind_time BETWEEN ".strtotime($param['start_time'])." AND ".strtotime($param['end_time']);
        }

        $data['table'] = '\Shop\Models\BaiyangCpsUserChannel as cuc ';
        $data['join'] = 'inner join \Shop\Models\BaiyangUser AS u ON cuc.user_id = u.id '
            . 'LEFT JOIN  \Shop\Models\BaiyangCpsUser as cu ON cuc.invite_code = cu.invite_code '
            . 'LEFT JOIN \Shop\Models\BaiyangCpsInviteLog AS cpil ON cpil.user_id = u.user_id ';
        $data['where'] = $count_sql;
        $counts =$base->countData($data);
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $data['column'] = "cu.cps_id,u.user_id,cu.user_id cps_user_id,u.channel_name,u.add_time,"
            . "IF(cpil.back_amount,cpil.back_amount,0.00) back_amount,cu.user_name,cu.invite_code,cuc.bind_time";
        $data['order'] = 'ORDER BY cuc.id DESC';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
//        echo '<pre>';print_r($data);exit;
        $result =  $base->getData($data);

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];

        $this->view->setVar('list',$list);
    }

    public function bangAction()
    {
        $param = array(
            'stu'=>'',
            'user_id'=>'',
            'cps_user_id'=>'',

        );
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        $data['code'] = 1;
        if($param['stu']==1){
            if(!$this->func->isPhone($param['user_id']) || !$this->func->isPhone($param['cps_user_id'])){
                $data['msg'] = '请输入正确的手机号！';
            }else{
                if($param['cps_user_id']==$param['user_id']){
                    $data['msg'] = '推广员不能成为自己的下线';
                }else{
                    //查询cps用户
                    $cps_user = $this->get_cps_user(['user_id'=>$param['cps_user_id']],'invite_code,user_name,user_id,cps_status,cps_id,channel_id');
                    if($cps_user){
                        //判断推广员状态
                        if($cps_user['cps_status']==1){
                            //判断被邀请人是否在黑名单中
                            $blacklist_status = $this->get_blacklist($param['user_id']);
                            if(!$blacklist_status){
                                //查询绑定用户信息
                                $where = "(username={$param['user_id']} OR user_id={$param['user_id']} OR phone={$param['user_id']})";
                                $user = $this->get_user($where,'id,invite_code');
                                if($user){
                                    if($user['invite_code']){
                                        $old_cps_user = $this->get_cps_user(['invite_code'=>$user['invite_code']],'user_name,user_id');
                                        if($old_cps_user){
                                            $data['msg'] = "用户已绑定  用户手机号：{$param['user_id']}推销员：{$old_cps_user['user_name']}手机号为：{$old_cps_user['user_id']}";
                                        }else{
                                            $data['msg'] ='上级用户不存在！';
                                        }
                                    }else{
                                        if($this->add_baiyang_cps_user_channel_log($user['id'],$cps_user['cps_id'],$cps_user['channel_id'],$cps_user['invite_code'])){
                                            $data['msg'] = "绑定成功！ 用户手机号：{$param['user_id']}推销员：{$cps_user['user_name']} 手机号为：{$cps_user['user_id']}";
                                            $data['code'] = 0;
                                        }else{
                                            $data['msg'] = '绑定失败，请重试！';
                                        }
                                    }
                                }else{
                                    $data['msg'] = '无此用户！';
                                }
                            }else{
                                $data['msg'] = '被邀请人在黑名单中！';
                            }
                        }else{
                            $data['msg'] = '推广员被禁用';
                        }
                    }else{
                        $data['msg'] = '无此推广员';
                    }
                }

            }
            echo json_encode($data);exit;
        }
    }
    //获取cps会员信息
    public function get_cps_user($search,$fields ='*'){
        $base = BaseData::getInstance();
        $where ='where 1=1';
        if(is_array($search)){
            foreach ($search as $k =>$v){
                $where .=" AND $k ='$v'";
            }
        }else{
            $where .= ' AND '.$search;
        }
        $data['table'] = '\Shop\Models\BaiyangCpsUser';
        $data['column'] = $fields ;
        $data['where'] = $where;
        $result =  $base->getData($data, true);

        return $result;
    }
    //获取黑名单用户
    public function get_blacklist($phone){
        $base = BaseData::getInstance();
        $data['table'] = '\Shop\Models\BaiyangCpsBigbrandBlacklist';
        $data['column'] = 'list_id' ;
        $data['where'] = "  WHERE phone={$phone} ";
        return $base->getData($data,true);
    }
    //获取单个指定会员的信息
    public function get_user($search,$fields ='*'){
        $base = BaseData::getInstance();
        $where =' where 1=1';
        if(is_array($search)){
            foreach ($search as $k =>$v){
                $where .=" AND $k='$v'";
            }
        }else{
            $where .= ' AND '.$search;
        }

        $data['table'] = '\Shop\Models\BaiyangUser';
        $data['column'] = $fields ;
        $data['where'] = $where;
        $result =  $base->getData($data,true);

        return $result;
    }
    //添加绑定
    public function add_baiyang_cps_user_channel_log($userId,$cps_id,$channel_id,$code){
        $base = BaseData::getInstance();
        $time = time();
        $table = '\Shop\Models\BaiyangCpsUserChannel';
        $p =  $base->insert($table,['user_id'=>$userId,'invite_code'=>$code,'cps_id'=>$cps_id,'channel_id'=>$channel_id,
            'bind_time'=>$time],true);

        if($p){
            $table = '\Shop\Models\BaiyangUser';
            $res = $base->update("invite_code = :invite_code:",$table,['invite_code'=>$code],"id={$userId}");
            if($res){
                return true;
            }
        }
        return false;
    }
}
?>
