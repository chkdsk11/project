<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 9:01
 */

namespace Shop\Home\Datas;
use Phalcon\Http\Client\Exception;
use Shop\Models\BaiyangGroupFight;
use Shop\Models\BaiyangGroupFightActivity;
use Shop\Models\BaiyangOrder;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Shop\Models\HttpStatus;

/**
 * 拼团方法集合
 * @package Shop\Home\Datas
 */
class BaiyangGroupFightBData extends BaseData
{
    /**
     * @var BaiyangGroupFightData
     */
    protected static $instance=null;
    const PAY_EXPIRY_DATE = 30 *60;
    /**
     * 获取指定活动的详情
     * @param int|array $act_id
     * @return \Shop\Models\BaiyangGroupGoods[]
     */
    public function getGroupFightActivityDetailed($act_id)
    {
        if(is_array($act_id)){
            $result = BaiyangGroupFightActivity::find([
                'gfa_id IN :act_id:',
                'bind' => [
                    'act_id' => $act_id
                ]
            ]);
        }else{
            $result = BaiyangGroupFightActivity::find([
                'gfa_id = :act_id:',
                'bind' => [
                    'act_id' => $act_id
                ]
            ]);

        }
        $group_list = [];

        foreach ($result as $index => $item) {
            $item->first_image = '';

            if (empty($item->goods_slide_images) === false) {
                $item->goods_slide_images = json_decode($item->goods_slide_images);
                $item->first_image = $item->goods_slide_images[0];
            }
            $group_list[] = $item;
        }
        return is_array($act_id) ? $group_list : (count($group_list) > 0 ? $group_list[0] : null);
    }

    /**
     * 查询拼团用户信息
     * @param string|array $order_sn 单个订单编号或订单编号数组
     * @return mixed|array
     */
    public function getGroupFightBuyByOrderSn($order_sn, $user_id = null)
    {
        $phql = 'SELECT act.gfa_user_count,act.gfa_state,act.goods_id,act.goods_name,act.gfa_is_draw,act.gfa_allow_num,act.gfa_draw_num,act.gfa_way,act.gfa_type,act.gfa_user_type,act.gfa_join_num,act.share_title,act.share_content,act.share_image,act.goods_slide_images,buy.gfb_id,buy.gf_id,buy.gfa_id,buy.is_win,buy.user_id,buy.nickname,buy.order_sn,buy.is_head,buy.add_time,buy.edit_time,buy.gfu_state,buy.is_overtime,act.gfa_user_count,act.gfa_user_type,act.gfa_type,act.gfa_is_draw,act.gfa_allow_num,fight.gf_join_num,fight.gf_start_time,fight.gf_end_time,fight.gf_over_time,fight.gf_state  
                FROM \Shop\Models\BaiyangGroupFightBuy AS buy 
                LEFT JOIN \Shop\Models\BaiyangGroupFightActivity AS act ON buy.gfa_id = act.gfa_id
                LEFT JOIN \Shop\Models\BaiyangGroupFight AS fight ON buy.gf_id = fight.gf_id';

      //  $result = $this->modelsManager->executeQuery($phql);

        $params = array($order_sn);
        if(is_array($order_sn)){
            $phql .= ' WHERE order_sn IN :order_sn:';
            if(empty($user_id) === false){
                $phql .= ' AND buy.user_id = ' . $user_id;
                $params[] = $user_id;
            }
            $result = $this->modelsManager->executeQuery($phql,['order_sn' => $order_sn]);

        }else {
            $phql .= ' WHERE order_sn = :order_sn:';
            if(empty($user_id) === false){
                $phql .= ' AND buy.user_id = ' . $user_id;
                $params[] = $user_id;
            }

            $result = $this->modelsManager->executeQuery($phql,['order_sn' => $order_sn]);
        }
        return $result->toArray();
    }

    /**
     * 获取指定用户在指定拼团活动中的参与列表
     * @param int $act_id
     * @param int $user_id
     * @return null|array
     */
    public function getGroupFightListByUserId($act_id,$user_id)
    {
        $phql = 'SELECT buy.gfb_id,buy.gfa_id,buy.gf_id,buy.is_win,buy.nickname,buy.order_sn,buy.is_head,buy.is_overtime,buy.gfu_state,fight.gf_start_time,fight.gf_end_time,fight.gf_state 
                FROM \Shop\Models\BaiyangGroupFightBuy AS buy 
                LEFT JOIN \Shop\Models\BaiyangGroupFight AS fight ON buy.gf_id = fight.gf_id
                WHERE buy.gfa_id = :act_id: AND buy.user_id = :user_id: 
                ORDER BY buy.gf_id DESC ';

        $result = $this->modelsManager->executeQuery($phql,['act_id' => $act_id,'user_id' => $user_id]);

        if($result === null || count($result) <= 0){
            return null;
        }
        return $result->toArray();
    }

    /**
     * 判断一个用户是否是第一次成功下单
     * @param int $user_id
     * @return bool
     */
    public function isOldUser($user_id)
    {
        $result = BaiyangOrder::count([
            'user_id = :user_id: AND payment_id > 0',
            'bind' => ['user_id' => $user_id]
        ]);

        return $result > 0;
    }

    /**
     * 获取指定拼团的拼团信息以及参团用户信息列表
     * @param $fight_id
     * @return array|null
     */
    public function getGroupFightAndUserList($fight_id)
    {
        $fight = BaiyangGroupFight::findFirst(['gf_id = :gf_id:','bind'=>['gf_id'=>$fight_id]]);

        if(empty($fight)){
            return null;
        }

        $phql = 'SELECT buy.*,u.headimgurl,ord.status,u.nickname as nick_name FROM \Shop\Models\BaiyangGroupFightBuy AS buy 
                 LEFT JOIN \Shop\Models\BaiyangUser as u ON u.id = buy.user_id
                 LEFT JOIN \Shop\Models\BaiyangOrder as ord ON buy.order_sn = ord.order_sn
                 WHERE buy.gf_id = :gf_id: AND ord.status IN (\'await\',\'draw\',\'shipping\',\'shipped\',\'evaluating\',\'refund\',\'finished\')';

        $params = [
            'gf_id' => $fight_id
        ];

        $result = $this->modelsManager->executeQuery($phql,$params);


        $fight_result = $fight->toArray();

        unset($fight_result['phone']);
        unset($fight_result['edit_time']);
        unset($fight_result['add_time']);
        unset($fight_result['goods_id']);
        unset($fight_result['goods_name']);
        unset($fight_result['goods_image']);
        unset($fight_result['gfa_price']);
        unset($fight_result['gfa_cycle']);
        unset($fight_result['gfa_name']);
        //unset($fight_result['gfa_id']);

        if(empty($result) === false){
            $user_result = [];

            foreach ($result as $item){

                $buy = $item->buy->toArray();

                $buy['headimgurl'] = $item->headimgurl;
                $buy['status'] = $item->status;
                $buy['join_time'] = intval($buy['add_time']);
                if(empty($buy['nickname'])){
                    $buy['nickname'] = $item->nick_name;
                }

                unset($buy['sync_erp']);
                unset($buy['phone']);
                unset($buy['edit_time']);
                unset($buy['add_time']);
                unset($buy['gf_start_time']);
                unset($buy['gf_end_time']);
                unset($buy['is_overtime']);

                $user_result[] = $buy;
            }
            $fight_result['user_list'] = $user_result;
        }else{
            $fight_result['user_list'] = [];
        }

        return $fight_result;
    }

    /**
     * 分页获取拼团列表
     * @param array|null $params
     * @param int $page_index
     * @param int $page_size
     * @return mixed
     */
    public function getGroupList(array $params = null, $page_index = 1, $page_size = 10)
    {

        $builder = $this->modelsManager->createBuilder()
            ->columns('*')
            ->from(['act'=>'\Shop\Models\BaiyangGroupFightActivity'])
            ->where('act.gfa_state = 0 AND act.gfa_starttime <= :start_time: AND act.gfa_endtime >= :end_time:',['start_time' => time(),'end_time' => time()])
        ;

        if(empty($params) === false && empty($params['not_in']) === false){
            $builder->notInWhere('act.gfa_id',$params['not_in']);
        }

       $builder->orderBy('act.gfa_sort DESC,act.gfa_id DESC');

        $paginator = new PaginatorQueryBuilder(
            array(
                "builder" => $builder,
                "limit"   => $page_size,
                "page"    => $page_index
            )
        );

        $pager =  $paginator->getPaginate();

        $act_list = [];

        if(empty($pager->items) === false){
            foreach ($pager->items as $item){
              //  print_r($temp);
                $temp = $item->toArray();
                $temp['first_image'] = '';
                if(empty($item->goods_slide_images) === false){
                    $json = json_decode($item->goods_slide_images);
                    if($json !== false) {
                        $temp['goods_slide_images'] = $json;
                        if(count($temp) > 0){
                            $temp['first_image'] = $temp['goods_slide_images'][0];
                        }
                    }
                }
                $act_list[] = $temp;
            }
        }
        $result['lists'] = $act_list;
        $result['total_items'] = $pager->total_items;
        $result['total_pages'] = $pager->total_pages;
        $result['page_index'] = $page_index;
        $result['page_size'] = $page_size;

        return $result;
    }

    /**
     * 获取我参与的拼团列表
     * @param $user_id
     * @param int $page_index
     * @param int $page_size
     * @return mixed
     */
    public function getFightListByUserId($user_id, $page_index = 1, $page_size = 10)
    {
        $builder = $this->modelsManager->createBuilder()
            ->columns(['buy.*','fight.gf_state','act.gfa_name','act.gfa_join_num','fight.gf_join_num','fight.gf_start_time','fight.gf_end_time','act.goods_id','act.goods_slide_images','act.goods_name','act.goods_introduction','act.goods_image','act.gfa_price','act.gfa_user_type','act.gfa_type','act.gfa_is_draw','act.gfa_allow_num','fight.gfa_user_count'])
            ->from(['buy' => '\Shop\Models\BaiyangGroupFightBuy'])
            ->leftJoin('\Shop\Models\BaiyangGroupFight','buy.gf_id=fight.gf_id','fight')
            ->leftJoin('\Shop\Models\BaiyangGroupFightActivity','act.gfa_id = buy.gfa_id','act')
            ->leftJoin('\Shop\Models\BaiyangOrder','o.order_sn=buy.order_sn','o')
            ->where('buy.user_id = :user_id:',['user_id' => $user_id])
            ->inWhere('o.status',['await','draw','shipping','shipped','evaluating','refund','finished'])
            ->orderBy('buy.gfb_id DESC');


        $paginator = new PaginatorQueryBuilder(
            array(
                "builder" => $builder,
                "limit"   => $page_size,
                "page"    => $page_index
            )
        );
        $pager =  $paginator->getPaginate();

        $lists = [];
        if(empty($pager->items) === false) {
            foreach($pager->items as $item){
                $buy = $item->buy->toArray();
                $data = $item->toArray();
                unset($buy['gfa_type']);

                $data = array_merge($data,$buy);

                unset($data['buy']);
                unset($data['sync_erp']);
                unset($data['phone']);

                $data['first_image'] = '';
                $json = json_decode($data['goods_slide_images']);
                if($json !== false){
                    $data['first_image'] = $json[0];
                }

                $lists[] = $data;
            }
        }
        $result['lists'] = $lists;
        $result['total_items'] = $pager->total_items;
        $result['total_pages'] = $pager->total_pages;
        $result['page_index'] = $page_index;
        $result['page_size'] = $page_size;

        return $result;
    }

    /**
     * 获取参团次数
     * @param $act_id
     * @param $user_id
     * @return int
     */
    public function getGroupJoinNumber($act_id,$user_id){
        $builder = $this->modelsManager->createBuilder()
            ->from(['buy' => '\Shop\Models\BaiyangGroupFightBuy'])
            ->leftJoin('\Shop\Models\BaiyangGroupFightActivity','act.gfa_id = buy.gfa_id','act')
            ->where('act.gfa_id = :act_id: AND buy.user_id = :user_id: AND buy.gfu_state <> 3',['act_id' => $act_id,'user_id' => $user_id])
            ->columns('count(buy.gfb_id) as json_count');

        $result = $builder->getQuery()->execute();

        if($result !== null ){
            return $result[0]->json_count;
        }
        return 0;
    }


    public function getGroupFightWonList($act_id)
    {
        $phql = 'SELECT buy.gfb_id,buy.gfa_id,buy.gf_id,buy.is_win,buy.nickname,buy.order_sn,buy.is_head,buy.is_overtime,buy.gfu_state,fight.gf_start_time,fight.gf_end_time,fight.gf_state 
                FROM \Shop\Models\BaiyangGroupFightBuy AS buy 
                LEFT JOIN \Shop\Models\BaiyangGroupFight AS fight ON buy.gf_id = fight.gf_id
                WHERE buy.gfa_id = :act_id: AND buy.is_win = 1
                ORDER BY buy.gf_id DESC ';

        $result = $this->modelsManager->executeQuery($phql,['act_id' => $act_id]);

        if($result === null || count($result) <= 0){
            return null;
        }
        return $result->toArray();
    }

    public function getGroupFight(array $param, $fields = null){
        is_null($fields) and  $fields = 'gf_id';
        $sql = "select {$fields} from \Shop\Models\BaiyangGroupFight where 1=1";

        if(empty($param) === false){

            if(isset($param['gf_state'])){
                if($param['gf_state'] = intval($param['gf_state'])){
                    $sql .= " and gf_state=:gf_state:";
                }else{
                    unset($param['gf_state']);
                }
            }

            //ltend_time  是结束时间小于当前时间
            if(isset($param['ltend_time'])){
                if($param['ltend_time'] = intval($param['ltend_time'])){
                    $sql .= " and gf_end_time<:ltend_time:";
                }else{
                    unset($param['ltend_time']);
                }
            }

            if(isset($param['gfa_id'])){
                if($param['gfa_id'] = intval($param['gfa_id'])){
                    $sql .= " and gfa_id=:gfa_id:";
                }else{
                    unset($param['gfa_id']);
                }
            }

            if(isset($param['gfa_user_count'])){

                $sql .= " and gf_join_num < gfa_user_count";
                unset($param['gfa_user_count']);
            }

            if(isset($param['gf_id'])){
                if($param['gf_id'] = intval($param['gf_id'])){
                    $sql .= " and gf_id=:gf_id:";
                }else{
                    unset($param['gf_id']);
                }
            }
        }
        $result = $this->modelsManager->executeQuery($sql,$param);
        if($result === null || count($result) <= 0){
            return null;
        }
        return $result->toArray();
    }

    public function getGroupFightBuy(array $param, $fields = null){
        is_null($fields) and  $fields = 'gf_id';
        $sql = "select {$fields} from \Shop\Models\BaiyangGroupFightBuy  ";
        $band = [];
        if(empty($param) === false){
            $whereStr = '';

            if(isset($param['gfu_state'])){
                if(is_array($param['gfu_state'])){

                    $inStr = '';
                    array_walk($param['gfu_state'], function($item) use (&$inStr){
                        $inStr .= ",'{$item}'";
                    });
                    $inStr = trim($inStr, ',');
                    $whereStr .= " and gfu_state in ({$inStr})";
                    unset($param['gfu_state']);

                }elseif($param['gfu_state'] = intval($param['gfu_state'])){
                    $whereStr .= " and gfu_state=:gfu_state:";
                    $band['gfu_state'] = $param['gfu_state'];
                }else{
                    throw new Exception('',HttpStatus::SYS_ERROR);
                }
            }

            if(isset($param['gfa_id'])){
                if($param['gfa_id'] = intval($param['gfa_id'])){
                    $whereStr .= " and gfa_id=:gfa_id:";
                    $band['gfa_id'] = $param['gfa_id'];
                }else{
                    throw new Exception('',HttpStatus::SYS_ERROR);
                }
            }

            if(isset($param['gf_id']) and intval($param['gf_id'])){
                if($param['gf_id'] = intval($param['gf_id'])){
                    $whereStr .= " and gf_id=:gf_id:";
                    $band['gf_id'] = $param['gf_id'];
                }else{
                    throw new Exception('',HttpStatus::SYS_ERROR);
                }
            }

            if(isset($param['user_id']) and intval($param['user_id'])){
                if($param['user_id'] = intval($param['user_id'])){
                    $whereStr .= " and user_id=:user_id:";
                    $band['user_id'] = $param['user_id'];
                }else{
                    throw new Exception('',HttpStatus::SYS_ERROR);
                }
            }

            if($whereStr){
                $sql .= ' where 1=1 ' . $whereStr;
            }
        }
        $result = $this->modelsManager->executeQuery($sql,$band);
        if($result == false || count($result) <= 0){
            return null;
        }
        return $result->toArray();
    }


    public function getGroupFightBuyCount(array $param, $fields = null){
       $result =  $this->getGroupFightBuy($param, $fields);
        if($result == false){
            return 0;
        }
        return count($result);
    }
    /**修改拼团表
     * @param array $param
     * @param array $cond
     * @return bool
     * @throws Exception
     */
    public function upGroupFight(array $param, array $cond){
        if(empty($param) or empty($cond)){
            throw new Exception('',HttpStatus::SYS_ERROR);
        }

        //拼字段串
        $column = '';
        foreach($param as $k=>$v){
            $column .= ",{$k}='{$v}'";
        }
        $column = trim($column, ',');

        //拼where sql串
        $where = '';
        foreach($cond as $k=>$v){
            if(is_array($v)){
                $inStr = '';
                array_walk($v, function($item) use (&$inStr){
                    $inStr .= ",'{$item}'";
                });
                $inStr = trim($inStr, ',');
                $where .= " and {$k} in ({$inStr})";
            }else{
                $where .= " and {$k}='{$v}'";
            }
        }
        $where  = trim($where);
        $where  = trim($where, 'and');
        empty($where) === false and $where = 'where ' . $where;
        $condition = [
            'table' => 'Shop\Models\BaiyangGroupFight',
            'column' => $column,
            'where' => $where,
        ];
        return $this->updateData($condition);
    }

    /**修改拼团用户表
     * @param array $param
     * @param array $cond
     * @return bool
     * @throws Exception
     */
    public function upGroupFightBuy(array $param, array $cond){
        if(empty($param) or empty($cond)){
            throw new Exception('',HttpStatus::SYS_ERROR);
        }

        //拼字段串
        $column = '';
        foreach($param as $k=>$v){
            $column .= ",{$k}='{$v}'";
        }
        $column = trim($column, ',');

        //拼where sql串
        $where = '';
        foreach($cond as $k=>$v){
            if(is_array($v)){
                $inStr = '';
                array_walk($v, function($item) use (&$inStr){
                    $inStr .= ",'{$item}'";
                });
                $inStr = trim($inStr, ',');
                $where .= " and {$k} in ({$inStr})";
            }else{
                $where .= " and {$k}='{$v}'";
            }
        }
        $where  = trim($where);
        $where  = trim($where, 'and');
        empty($where) === false and $where = 'where ' . $where;

        $condition = [
            'table' => 'Shop\Models\BaiyangGroupFightBuy',
            'column' => $column,
            'where' => $where,
        ];
        return $this->updateData($condition);
    }

    /**把到拼团结束时间 批团状态还是拼团中的  改为  拼团失败 等待退款
     * @return null
     */
    public function upGroupFightFail(){

        $result = $this->getGroupFight(['gf_state' => 1, 'ltend_time'=>time(), 'gfa_user_count' =>true]);
        if($result === null){
            return null;
        }
        $condition = [];
        if($condition['gf_id'] = array_column($result,'gf_id')){
            if($this->upGroupFight(['gf_state'=>3], $condition)){

                $condition['gfu_state'] = 1;
                $this->upGroupFightBuy(['gfu_state'=>3], $condition);

            }
        }
    }

    /**获取活动表的数据
     * @param $gfaId
     * @return \Shop\Models\BaiyangGroupGoods
     */
    public function getGroupFightActOne($gfaId){
        $result = BaiyangGroupFightActivity::findFirst(['gfa_id = :gfa_id:','bind'=>['gfa_id'=>$gfaId]]);
        if($result == false){
            return [];
        }
        return $result->toArray();
    }

    /**获取开团表的数据
     * @param $gfId
     * @return \Shop\Models\BaiyangGroupGoods
     */
    public function getGroupFightOne($gfId){
        $result = BaiyangGroupFight::findFirst(['gf_id = :gf_id:','bind'=>['gf_id'=>$gfId]]);
        if($result == false){
            return [];
        }
        return $result->toArray();
    }

    public function getGroupFightBuyExpCancel($userId, $gfaId){
        //付款结束时间
        $payEndTime  =  time() + self::PAY_EXPIRY_DATE;

        $fields = 'b.gfb_id,b.order_sn';
        $sql = "select {$fields} from Shop\Models\BaiyangGroupFightBuy as b where b.gfu_state=0 and b.user_id=:userId: and b.gfa_id=:gfaId: ";
        $sql .= " and  EXISTS (select o.order_sn from Shop\Models\BaiyangOrder as o where o.order_sn=b.order_sn and o.status='paying' and o.audit_time < $payEndTime)  ";


        $result = $this->modelsManager->executeQuery($sql,[
            'userId' => $userId,
            'gfaId' => $gfaId
        ]);
        if($result === null || count($result) <= 0){
            return null;
        }
        return $result->toArray();
    }

    public function getFightBuyExpCancelCount($userId, $gfaId){
        $result = $this->getGroupFightBuyExpCancel($userId, $gfaId);
        if($result == false){
            return 0;
        }
        return count($result);
    }


    public function insertGroupFight(array $bindData){
        $addData = array(
            'table' => '\Shop\Models\BaiyangGroupFight',
            'bind' => $bindData
        );
        if ($gfId = $this->addData($addData, true)) {
            return $gfId;
        }
        return false;
    }

    public function insertGroupFightBuy(array $bindData){
        $addData = array(
            'table' => '\Shop\Models\BaiyangGroupFightBuy',
            'bind' => $bindData
        );
        if (!$this->addData($addData)) {
            return false;
        }
        return true;
    }
}

























