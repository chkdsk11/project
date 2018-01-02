<?php
/**
 * @author 柯琼远
 */
namespace Shop\Home\Datas;

class BaiyangUserInvoiceData extends BaseData
{
    protected static $instance=null;

    /**
     * 获取用户最新的发票记录
     *
     * @param int $userId  用户id
     * @return array
     */
    public function getUserInvoice($userId)
    {
        $userInfo = $this->getData([
            'column'=> "invoice_type,title_name,content_type,taxpayer_number",
            'table' => '\Shop\Models\BaiyangUserInvoice',
            'where' => 'where user_id = :user_id:',
            'order' => 'order by id desc',
            'bind'  => ['user_id'=> (int)$userId]
        ],1);
        return $userInfo;
    }

    /**
     * @desc 插入发票信息
     * @param array $param
     * @return bool true|false 结果信息
     * @author 柯琼远
     */
    public function insertUserInvoice($param) {
        if ($param['invoiceType'] > 0) {
            $contentType = (isset($param['rxExist']) and $param['rxExist']== 1) ? 10 : 16;
            $addData = array(
                'table' => '\Shop\Models\BaiyangUserInvoice',
                'bind'  => array(
                    'user_id'      => $param['userId'],
                    'invoice_type' => $param['invoiceType'],
                    'title_name'   => $param['invoiceHeader'],
                    'content_type' => $contentType,
                    'content'      => \Shop\Models\OrderEnum::$receiptContent[$contentType],
                    'taxpayer_number' => isset($param['taxpayerNumber']) ? $param['taxpayerNumber'] : '',
                    'add_time'     => time(),
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        return true;
    }
}