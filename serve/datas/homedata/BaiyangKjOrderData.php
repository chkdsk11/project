<?php
/**
 * @author sarcasme
 */
namespace Shop\Home\Datas;

use Phalcon\Paginator\Adapter\Model as PageModel;
use Shop\Models\BaiyangOrder;
use Shop\Models\BaiyangOrderDetail;
use Shop\Models\BaiyangKjOrder;
use Shop\Models\BaiyangKjOrderDetail;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Shop\Queue\Redis\Cli\Models\BaseModel;
use Shop\Models\BaiyangOrderShipping;
use Shop\Models\BaiyangCheckGlobalLogistrack;
use Shop\Models\BaiyangUserConsignee;
use Shop\Models\OrderEnum;


class BaiyangKjOrderData extends BaseData
{
	protected static $instance=null;
	// 海外购订单表
	private $table = 'Shop\Models\BaiyangKjOrder';
	
	/**
	 * @desc 插入订单
	 * @param array $param
	 * @return bool true|false 结果信息
	 * @author sarcasme
	 */
	public function insertOrder($param) {
		$now = time();
		//海关协议
		$protocol = '本人承诺所购买商品系个人合理自用，现委托商家代理申报、代缴税款等通关事宜，本人保证遵守《海关法》和国家相关法律法规，保证所提供的身份信息和收货信息真实完整，无侵犯他人权益的行为，以上委托关系系如实填写，本人愿意接受海关、检验检疫机构及其他监管部门的监管，并承担相应法律责任.';
		$businessNo = ($param['bond'] == '1') ?? $this->getBusinessNo() ?? '';
		if (empty($param['channelName'])) {
			switch ($this->config->channel_subid) {
				case '95':
					$param['channelName'] = 'pc';break;
				case '91':
					$param['channelName'] = 'wap';break;
				case '90':
					$param['channelName'] = 'android';break;
				case '89':
					$param['channelName'] = 'ios';break;
				case '85':
					$param['channelName'] = 'weixin';break;
				default :
					$param['channelName'] = 'pc';
			}
		}
		$addData = array(
			'table' => '\Shop\Models\BaiyangKjOrder',
			'bind'  => array(
				'agent_id'        => 1,
				'user_id'         => $param['userId'],
				'total_sn'        => $param['orderSn'],
				'order_sn'        => $param['orderSn'],
				'business_no'     => $businessNo,
				'ie_flag'         => 'I',
				'total_count'     => $param['totalCount'],
				'delivery_status' => 0,
				'consignee'       => $param['consigneeInfo']['consignee'],
				'consignee_id'    => $param['consigneeInfo']['consignee_id'],
				'telephone'       => $param['consigneeInfo']['telphone'],
				'zipcode'         => $param['consigneeInfo']['zipcode'],
				'province'        => $param['consigneeInfo']['province'],
				'city'            => $param['consigneeInfo']['city'],
				'county'          => $param['consigneeInfo']['county'],
				'address'         => $param['consigneeInfo']['address'],
				'express'         => '无',
				'express_sn'      => '无',
				'express_type'    => 0,
				'total'           => $param['goodsTotalPrice'],
				'order_discount_money'  =>  0,
				'detail_discount_money' =>  0,
				'discount_remark'   =>  '',
				'ad_source_id'  =>  0,
				'ad_web_id'     =>  0,
				'ad_by_id'  =>  0,
				'ad_click_time'     =>  0,
				'pay_remark'        =>  '无',
				'real_pay'      =>  0,
				'carriage'        => $param['freight'],
				'is_pay'          => 0,
				'pay_time'        => 0,
				'pay_type'      =>  $param['paymentId'],
				'pay_total'     =>  0,
				'delivery_time'     =>  0,
				'received_time'     =>  0,
				'received'        => 0,
				'invoice_type'    => 0,
				'invoice_info'    => '无',
				'buyer_message'   => $param['buyerMessage'],
				'cancel_reason'     =>  '无',
				'is_remind'     =>  0,
				'is_out_date'   =>  0,
				'is_comment'      => 0,
				'is_return'       => 0,
				'is_delete'     =>  0,
				'add_time'        => $now,
				'audit_time' => $now,
				'custom_id'     =>  '0',
				'custom_pay_id'     =>  '0',
				'user_procotol'     =>  $protocol,//$hz_custom['user_protocol'],
				'insure_amount'     =>  $param['insure_amount'],
				'order_tax_amount'      =>  $param['order_tax_amount'],
				'order_total_amount'    =>  $param['costPrice'],
				'curr_code'     =>  '142',
				'pay_company_code'      =>  '0',
				'pay_number'        =>  '0',
				'status' => $param['status'],
				'last_status' => OrderEnum::ORDER_PAYING,
				'addr_id'         => $param['addressId'],
				'goods_price'     => $param['goodsTotalPrice'],
				'user_coupon_id'  => '0',
				'user_coupon_price' =>  '0',
				'youhui_price'    =>    0,
				'balance_price'   =>    0,
				'payment_name'    =>    '无',
				'payment_id'      =>    '0',
				'payment_code'    =>    '0',
				'channel_subid'   => $this->config->channel_subid,
				'channel_name'    => $param['channelName'],
				'trade_no'        =>    '',
				'express_status'  =>    0,
				'express_time'    =>    0,
				'allow_comment'   =>    1,
				'callback_phone'  =>    $param['consigneeInfo']['telphone'],
				'ordonnance_photo'=>    '无',
				'shop_id'   =>  $param['shopId'],
				'order_bond'    =>  $param['bond']
			)
		);
		if (!$this->addData($addData)) {
			return false;
		}
		return true;
	}
	
	/**
	 * @desc 插入订单
	 * @param array $param
	 * @return bool true|false 结果信息
	 * @author sarcasme
	 */
	public function insertOrderExtend($param) {
		$condition = [
			'table' => 'Shop\Models\BaiyangKjOrder',
			'column' => 'shop_id = :shop_id:,order_bond = :order_bond:',
			'where' => 'where order_sn = ' . $param['order_sn'],
			'bind' => [
				'shop_id' => $param['shopId'],
				'order_bond'    =>  $param['bond']
			],
		];
		return $this->updateData($condition);
	}
	
	/**
	 * 生成一个随机海关业务编号
	 */
	private function getBusinessNo()
	{
		$appId = '1a5fe662-fb13-4643-af41-aeaebd915cfc28945';
		$now = date('YmdHis');
		$rand = mt_rand(100, 999);
		$str = 'JKF_ORDER_'.$appId.'_'.$now.'_'.$rand;
		return $str;
	}
	
}