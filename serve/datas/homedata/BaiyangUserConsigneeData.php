<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2017/01/19 0004
 */
namespace Shop\Home\Datas;

class BaiyangUserConsigneeData extends BaseData
{
    protected static $instance=null;
    /**
     * @desc 获取用户收货地址（默认地址放第一位）
     * @param array $userId
     * @return array [] 用户收货地址信息|[]
     * @author 吴俊华
     */
    public function getUserConsigneeList($userId)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangUserConsignee as a',
            'join' => 'left join \Shop\Models\BaiyangConsigneeLimitBuy as b on a.consignee_id = b.card_sn',
            'column' => 'a.id,a.user_id,a.consignee,a.province,a.city,a.county,a.address,a.telphone,a.fix_line,a.email,a.zipcode,a.add_time,a.tag_id,a.default_addr,a.sale_addr,a.addr_group,a.consignee_id,a.identity_confirmed,b.bought_limit',
            'where' => "where a.user_id = :user_id: order by a.default_addr desc,a.id desc",
            'bind' => [
                'user_id' => $userId
            ],
        ];
        $consigneeList = $this->getData($condition);
        if (!empty($consigneeList)) {
            foreach ($consigneeList as $key => $val){
                if(empty($val['bought_limit'])){
                    $consigneeList[$key]['bought_limit'] = 0;
                }
                // 获取地区名称
                $consigneeList[$key]['province_name'] = $this->getRegionName($val['province']);
                $consigneeList[$key]['city_name'] = $this->getRegionName($val['city']);
                $consigneeList[$key]['county_name'] = $this->getRegionName($val['county']);
            }
        }
        return $consigneeList;
    }

    /**
     * @desc 获取地区名称
     * @param int $regionId 地区表主键id
     * @return string 地区名称
     * @author 吴俊华
     */
    public function getRegionName($regionId)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangRegion',
            'column' => 'region_name',
            'where' => "where id = :id: ",
            'bind' => [
                'id' => $regionId
            ],
        ];
        $ret = $this->getData($condition,true);
        return  $ret ? $ret['region_name'] : '';
    }

    /**
     * @desc 获取收货地址信息
     * @param array $param
     *      -string column 修改的字段
     *      -string where 条件
     *      -array bind 数据
     * @param bool $returnOne 返回单条或多条数据(true:单条 false:多条)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getConsigneeInfo($param, $returnOne = true)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangUserConsignee',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition,$returnOne);
    }

    /**
     * @desc 获取用户的收货地址数量
     * @param int $userId 用户id
     * @return int  结果信息
     * @author 吴俊华
     */
    public function getConsigneeNumber($userId)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangUserConsignee',
            'column' => 'id',
            'where' => "where user_id = :user_id:",
            'bind' => [
                'user_id' => $userId,
            ],
        ];
        return $this->countData($condition);
    }

    /**
     * @desc 获取地区列表信息
     * @param int $pid 地区的父id
     * @return array [] 地区列表信息
     * @author 吴俊华
     */
    public function getRegionList(int $pid = 1)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangRegion',
            'column' => '*',
            'where' => "where pid = :pid: ",
            'bind' => [
                'pid' => $pid
            ],
        ];
        return $this->getData($condition);
    }

    /**
     * @desc 获取所有地区列表信息
     * @return array [] 所有地区列表信息
     * @author 吴俊华
     */
    public function getAllRegionList()
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangRegion',
            'column' => 'id,pid,region_name',
            'where' => "where id > 1",
        ];
        return $this->getData($condition);
    }

}