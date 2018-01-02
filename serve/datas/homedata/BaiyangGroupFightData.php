<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 9:01
 */

namespace Shop\Home\Datas;
use Shop\Models\BaiyangGroupFight;
use Shop\Models\BaiyangGroupFightActivity;
use Shop\Models\BaiyangOrder;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 拼团方法集合
 * @package Shop\Home\Datas
 */
class BaiyangGroupFightData extends BaseData
{
    /**
     * @var BaiyangGroupFightData
     */
    protected static $instance=null;

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
        $phql = 'SELECT act.gfa_user_count,act.gfa_state,act.goods_id,act.goods_name,act.gfa_is_draw,act.gfa_allow_num,act.gfa_draw_num,act.gfa_way,act.gfa_type,act.gfa_user_type,act.gfa_join_num,act.share_title,act.share_content,act.share_image,act.goods_slide_images,buy.gfb_id,buy.gf_id,buy.gfa_id,buy.is_win,buy.user_id,buy.nickname,buy.order_sn,buy.is_head,buy.add_time,buy.edit_time,buy.gfu_state,buy.is_overtime,act.gfa_user_count,act.gfa_user_type,act.gfa_type,act.gfa_is_draw,act.gfa_allow_num,fight.gf_join_num,fight.gf_start_time,fight.gf_end_time,fight.gf_over_time 
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
        return $result;
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
    public function isNewUser($user_id)
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
            ->where('act.gfa_state = 0 AND act.gfa_starttime <= :start_time: AND act.gfa_endtime >= :end_time: AND act.is_show_hot = 0',['start_time' => time(),'end_time' => time()])
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
}

























