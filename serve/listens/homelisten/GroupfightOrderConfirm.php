<?php
/**
 * @author: 文和
 * @copyright: 2017/5/22 10:21
 * @link chenxudaren.com
 * @internal
 * @license
 */

namespace Shop\Home\Listens;


use Phalcon\Http\Client\Exception;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangAnnouncementData;
use Shop\Home\Datas\BaiyangConfigData;
use Shop\Home\Datas\BaiyangGroupFightBData;
use Shop\Home\Datas\BaiyangGroupFightOrderData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaiyangUserInvoiceData;
use Shop\Models\HttpStatus;

class GroupfightOrderConfirm extends BaseGroupfight
{
    protected static $instance=null;

    public function __construct()
    {
        $this->requiredParam = [
            'user_id' => [
                'require' => 1,
            ],
            'is_dummy' => [
                'require' => 1,
            ],
            'is_open' => [
                'require' => 1,
            ],
            'group_id' => [
                'require' => 1,
            ],
            'goods_num' => [
                'require' => 1,
            ],
            'channel_subid' => [
                'require' => 1,
                'value' => [85, 91]//89, 90, , 95
            ],
            'platform' => [
                'require' => 1,
                'value' => ['wap', 'wechat']//'pc', 'app',
            ],
            'is_check' => [
                'require' => 0,
            ],
            'address_id' => [
                'require' => 0,
            ]
        ];
        if (empty($this->data)) {
            $this->data = [
                'param' => [],
                'order' => [],
                'user' => [],
                'group' => [],
                'product' => [],
                'address' => [],
                'invoice' => []
            ];
        }
    }



    public function confirm(array $param)
    {
        $this->stepGet01($param);

        $this->stepChk02();

        $this->stepGetGroupData03();

        $this->stepChkGroup04();

        $this->stepPorduct05();

        return $this->step06();
    }

    private function stepGet01(array $param)
    {
        //检测必填参数
        if (($this->data['param'] = $this->verifyRequiredParam($param)) === false) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }


        $this->data['param']['is_check'] = (isset($param['is_check']) and $param['is_check']) ? true : false;
        $this->data['param']['goods_num'] = 1;
        $this->data['param']['is_balance'] = 1; //默认使用余额

        //判断是否虚拟用户
        if ($this->data['param']['is_dummy']) {
            throw new Exception('', HttpStatus::USER_DUMMY_ERROR);
        }

        if (empty($this->data['param']['user_id'])) {
            throw new Exception('', HttpStatus::USER_NOT_EXIST);
        }

        $this->myhook();

    }

    private function stepChk02()
    {
    }

    private function stepGetGroupData03()
    {
        $this->getGroupData($this->data['param']['group_id']);
    }

    private function stepChkGroup04()
    {
        $this->chkGroup();
    }

    private function stepPorduct05()
    {
        $product = $this->getPorduct();
        $this->data['product'] = [
            'brand_id' => $product['brand_id'],
            'brand_name' => $product['brand_name'],
            'goods_id' => $this->data['group']['goods_id'],
            'goods_name' => $this->data['group']['goods_name'],
            'goods_image' => $this->data['group']['goods_image'],
            'first_image' => $this->data['group']['goods_image'],
            'goods_number' => $this->data['param']['goods_num'],
            'price' => $this->data['group']['gfa_price'],
            'is_use_stock' => $product['is_use_stock'],
        ];

        $this->chkStock();

        unset($product);
    }

    private function step06()
    {
        $this->data['user'] = BaiyangUserData::getInstance()->getUserInfo($this->data['param']['user_id']);
        if (empty($this->data['user'])) {
            throw new Exception('', HttpStatus::USER_NOT_EXIST);
        }

        //拼团剩余时间
        if ($this->data['param']['is_open']) {
            $endTime = $this->gfEndTime();
            $this->data['group']['left_time'] = $endTime - time();
        } else {
            $this->data['group']['left_time'] = $this->data['group']['gf_end_time'] - time();
        }

        //是否获取地址和发票数据
        if ($this->data['param']['is_check'] === false) {
            //收货地址
            $this->getConsignee();
            $this->getInvoice();
        }

        return $this->returnData();
    }


    private function returnData()
    {
        $result =  [
                'sn' => $this->getSn(),
                'act_id' => $this->data['param']['group_id'],
                'is_open_group' => $this->data['param']['is_open'],
                'left_time' => $this->data['group']['left_time'],
                'use_stock' => $this->data['product']['is_use_stock'],
                'real_pay' => $this->data['product']['price'] * $this->data['product']['goods_number'],
                'cart_amount' => $this->data['product']['price'] * $this->data['product']['goods_number'],
                'gfa_user_type' => $this->data['group']['gfa_user_type'], //0 不限制 , 1新用户可参团
                'gfa_type' => $this->data['group']['gfa_type'], //0 不抽奖 , 1 抽奖
                'balance' => sprintf("%.2f", $this->data['user']['balance']), // 余额金额
                'is_set_pay_password' => empty($this->data['user']['pay_password']) ? 0 : 1, // 用户是否已经设置密码
                'free_password_amount' => 0,// 免支付密码金额
                'is_balance' => $this->data['param']['is_balance'], //是否启余额
                'affix_money' => 0,
                'addr_id' => '',
                'telephone' => '',
                'addr_detail' => '',
                'default_addr' => 0
        ];
        $result['free_password_amount'] = $this->func->getConfigValue('min_amount_for_password');


        if($this->data['address']){
            $result['addr_id'] =  $this->data['address']['addr_id'] ;
            $result['receiver_name'] =  $this->data['address']['receiver_name'] ;
            $result['telephone'] =  $this->data['address']['telephone'] ;
            $result['default_addr'] =  $this->data['address']['default_addr'] ;
            $result['tag_id'] =  $this->data['address']['tag_id'] ;
            $result['province'] =  $this->data['address']['province'] ;
            $result['city'] =  $this->data['address']['city'] ;
            $result['county'] =  $this->data['address']['county'] ;
            $result['identity_number'] =  $this->data['address']['identity_number'];
            $regionId = [];
            if ($result['province'] && is_numeric($result['province'])) {
                $regionId[] = $result['province'];
            }
            if ($result['city'] && is_numeric($result['city'])) {
                $regionId[] = $result['city'];
            }
            if ($result['county'] && is_numeric($result['county'])) {
                $regionId[] = $result['county'];
            }
            $regionAll = $regionId ? BaseData::getInstance()->getData([
                'column' => 'id,region_name',
                'table' => 'Shop\Models\BaiyangRegion',
                'where' => 'WHERE id IN ('.implode(',', $regionId).')',
            ]) : [];
            $regionAll = $regionAll ? array_column($regionAll, "region_name", "id") : [];
            $addr_detail = $result['province'].$result['city'].$result['county'].$this->data['address']['address'];
            $result['addr_detail'] = isset($regionAll[$result['province']])
                ? (isset($regionAll[$result['city']]) ? (isset($regionAll[$result['county']])
                    ? $regionAll[$result['province']].$regionAll[$result['city']].$regionAll[$result['county']].
                    $this->data['address']['address'] : $addr_detail) : $addr_detail) : $addr_detail;

            //配送公告
            $result['announcement'] = BaiyangAnnouncementData::getInstance()->getAnnouncement($this->data['address']);
            unset($this->data['address']);
        }
        $result['invoiceInfo'] = [];
        if($this->data['invoice']){
            $result['invoiceInfo'] = $this->data['invoice'];
        }
        $result['product'] = $this->data['product'];
        unset($this->data);
        return $result;
    }

    //把拼团失败的  fight 表 和buy表数据状态 改成  拼团失败 3
    private function myhook()
    {
        BaiyangGroupFightBData::getInstance()->upGroupFightFail();
    }
    /**
     * 获取地址信息
     */
    private function getConsignee()
    {
        $condition = [
            'user_id' => $this->data['param']['user_id']
        ];
        if (isset($this->data['param']['address_id']) and empty($this->data['param']['address_id']) === false ) {
            $condition['address_id'] = $this->data['param']['address_id'];
        }

        $consignee = $this->getConsigneeInfo($condition);

        $this->data['address']['addr_id'] = isset($consignee['id'])? $consignee['id'] : '';
        $this->data['address']['receiver_name'] = isset($consignee['consignee'])? $consignee['consignee'] : '';
        $this->data['address']['telephone'] =isset($consignee['telphone'])? $consignee['telphone'] : '';
        $this->data['address']['default_addr'] = isset($consignee['default_addr'])? $consignee['default_addr'] : '';
        $this->data['address']['tag_id'] = isset($consignee['tag_id'])? $consignee['tag_id'] : '';
        $this->data['address']['province'] = isset($consignee['province'])? $consignee['province'] : '';
        $this->data['address']['city'] = isset($consignee['city'])? $consignee['city'] : '';
        $this->data['address']['county'] = isset($consignee['county'])? $consignee['county'] : '';
        $this->data['address']['identity_number'] = isset($consignee['identity_confirmed'])? $consignee['identity_confirmed'] : '';
        $this->data['address']['address'] = isset($consignee['address'])? $consignee['address'] : '';
    }

    //获取使用过的发票记录
    private function getInvoice(){
        $this->data['invoice']  = BaiyangUserInvoiceData::getInstance()->getUserInvoice($this->data['param']['user_id']);
        $this->data['invoice']['if_receipt'] = 1;
    }
}