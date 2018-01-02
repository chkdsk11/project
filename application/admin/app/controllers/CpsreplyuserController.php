<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;
class CpsReplyUserController extends ControllerBase
{
    public function listAction(){

        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        $this->view->setVar('channel',$param);
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $where_sql = ' WHERE 1=1 ';

        if(isset($param['mobile']) && $param['mobile'])
        {
            $where_sql .= ' AND cru.mobile like "%' . trim($param['mobile']) . '%" or u.username like "%' . trim($param['mobile']) . '%" ';
        }
        if(isset($param['id_cart']) && $param['id_cart'])
        {
            $where_sql .= ' AND cru.id_card like "%' . trim($param['id_cart']) . '%" ';
        }
        if(isset($param['user_name']) && $param['user_name'])
        {
            $where_sql .= ' AND cru.user_name like "%' . addslashes($param['user_name']) . '%" ';
        }
        if(isset($param['start_time']) && $param['start_time'])
        {
            $start_time = strtotime($param['start_time']);
            $where_sql .= " AND cru.add_time >= {$start_time} ";
        }
        if(isset($param['end_time']) && $param['end_time'])
        {
            $end_time = strtotime($param['end_time']);
            $where_sql .= " AND cru.add_time <= {$end_time} ";
        }
        if(isset($param['reply_status']) && $param['reply_status'] != '')
        {
            $where_sql .= " AND cru.reply_status = {$param['reply_status']} ";
        }

        $data['table'] = '\Shop\Models\BaiyangCpsReplyUser as cru';
        $user = '\Shop\Models\BaiyangUser as u';
        $data['join'] = ' LEFT JOIN  '.$user.' on cru.user_id = u.id';
        $data['where'] = $where_sql;
        $counts = $base->countData($data);

        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $data['column'] = "cru.reply_id,cru.mobile,cru.user_name,cru.channel,cru.add_time ,cru.reply_status,u.username,u.phone" ;
        $data['order'] = 'order by cru.add_time desc';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];

        $result =  $base->getData($data);
        foreach( $result as $key=>$v ){
            $result[$key]['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
        }
        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }

    public function examineAction()
    {
        $reply_id =  $_POST['reply_id'];
        $reply_status = $_POST['reply_status'];
        $reply_id = str_replace(':', ',', $reply_id);
        $arr = explode(':',$reply_id);
        $admim_user = $_SESSION['admin']['admin_account'];

        if(!$reply_id){
            $data['status']='success';
            die(json_encode($data));
        }

        $base = BaseData::getInstance();
        if($reply_status==2){
            $table = '\Shop\Models\BaiyangCpsReplyUser';

            $whereStr = " reply_id in ({$reply_id}) ";

            $dtat =   " reply_status = :reply_status: ,update_user = :update_user: ,update_time = :update_time:";
            $array = array(
                'reply_status'=>2,
                'update_user'=> $admim_user,
                'update_time'=>time()
            );
            $res = $base->update($dtat,$table,$array,$whereStr);

            if($res){
                $data['status']='ok';
                die(json_encode($data));
            }
        }else if($reply_status==1){
            $channel_id = $this->config->user_apply_channel[$this->config->environment];

            foreach ($arr as $reply_id)
            {
                $id  = explode(",",$reply_id);
                foreach($id as $v ){
                    $one_list = $this->get_cps_reply_one($v);
                    $username = $this->get_user_info($one_list['user_id']);//用户信息
                    $channel_info = $this->get_one_channel_info($channel_id);//渠道信息
                    $cps_user = $this->get_cps_user_info($username['username']);
                    if(empty($cps_user))
                    {
                        $up = $this->update_cps_reply_user($v,$admim_user);
                        if ($up) {
                            $data = array('user_id'=>$username['username'],
                                'user_name'=>$one_list['user_name'],
                                'channel_id'=>$channel_id,
                                'invite_code'=>$channel_info['tags'].$this->create_code(),
                                'add_time'=>time(),
                                'cps_status'=>1
                            );
                            $this->insert_cps_user($data);
                        }
                    }else{
                        $data['status']='on';
                        $data['id'] =  $channel_id;
                        die(json_encode($data));
                    }
                }

            }
            $data['status']='ok';
            die(json_encode($data));
        }


    }
    public function get_cps_reply_one($reply_id)
    {
        $base = BaseData::getInstance();
        $data['table'] = '\Shop\Models\BaiyangCpsReplyUser ';
        $data['where'] = " where reply_id = {$reply_id} ";
        $data['column'] = " user_name,user_id ";
        return  $base->getData($data,true);

    }
    public function get_user_info($id)
    {
        $base = BaseData::getInstance();
        $data['table'] = '\Shop\Models\BaiyangUser ';
        $data['where'] = " where id = {$id} ";
        $data['column'] = " phone,username ";
        return  $base->getData($data,true);
    }
    public function get_one_channel_info($channel_id)
    {
        $base = BaseData::getInstance();
        $data['table'] = '\Shop\Models\BaiyangCpsChannel ';
        $data['where'] = " where channel_id = {$channel_id} ";
        $data['column'] = "tags ";
        return  $base->getData($data,true);


    }
    public function get_cps_user_info($user_id)
    {
        $base = BaseData::getInstance();
        $data['table'] = '\Shop\Models\BaiyangCpsUser ';
        $data['where'] = " where user_id = {$user_id} ";
        $data['column'] = " cps_id ";
        return  $base->getData($data,true);


    }
    public function update_cps_reply_user($reply_id,$admim_user)
    {    $base = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangCpsReplyUser';
        $whereStr = " reply_id in ({$reply_id}) ";
        $dtat =   " reply_status = :reply_status: ,update_user = :update_user: ,update_time = :update_time:";
        $array = array(
            'reply_status'=>1,
            'update_user'=> $admim_user,
            'update_time'=>time()
        );
        return $base->update($dtat,$table,$array,$whereStr);
    }
    public function insert_cps_user($data)
    {
        $base = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangCpsUser';

        return $base->insert($table,$data);
    }
    public function create_code($invite_code = array())
    {
        $str = range('A','Z');
        $code = '';
        $len = count($str);
        $len--;
        for($i=0; $i<4; $i++) {
            $index = mt_rand(0, $len);
            $code .= $str[$index];
        }
        if (isset($invite_code[$code])) {
            $this->create_code($invite_code);
        } else {
            return $code;
        }
    }
}

?>
