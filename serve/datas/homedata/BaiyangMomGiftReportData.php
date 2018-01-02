<?php
/**
 * Created by PhpStorm.
 * User: ZHQ
 * Date: 2016/12/27
 * Time: 16:39
 */
namespace Shop\Home\Datas;

class BaiyangMomGiftReportData extends BaseData
{

    protected static $instance=null;


    /**
     * 按数量获取最新妈妈礼包试用报告
     *
     * @return array|bool
     */
    public function getNewestMomTrailReportList($strReportId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomTrialReport AS r',
            'column' => 'r.id report_id,r.title,r.content,r.images,r.add_time,u.nickname,u.headimgurl,u.phone',
            'join' => 'LEFT JOIN Shop\Models\BaiyangUser AS u ON r.user_id = u.id',
            'order' => 'ORDER BY r.id DESC',
            'where' => "WHERE r.id IN({$strReportId})",
        );
        $ret = $this->getData($param);
        return $ret;
    }


    /**
     * 按数量获取最新数量报告id列表
     *
     * @param $limitNumber
     * @return string
     */
    public function getNewestMomTrailStrReportId($limitNumber)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomTrialReport',
            'column' => 'id',
            'order' => 'ORDER BY id DESC',
            'limit' => 'LIMIT 0,' . $limitNumber
        );
        $fiveMomTrailReportList = $this->getData($param);
        $strReportId = '';
        if ($fiveMomTrailReportList) {
            foreach ($fiveMomTrailReportList as $report) {
                $strReportId .= "{$report['id']},";
            }
            $strReportId = trim($strReportId, ',');
        }
        return $strReportId;
    }

    /**
     * 获取用户已经评价的礼包列表
     *
     * @param int $userId 用户id
     * @param string $relationKey 按字段返回关联数组
     * @return array|bool
     */
    public function getUserHadReportGiftList($userId, $relationKey='')
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomTrialReport',
            'column' => 'gifts_id gift_id',
            'where' => "WHERE user_id=:user_id:",
            'bind' => array(
                'user_id' => $userId
            )
        );
        $data = $this->getData($param);
        if ($relationKey && $data) {
            return $this->relationArray($data, $relationKey);
        }
        return $data;
    }

    /**
     * 按报告id获取报告详情
     *
     * @param int $reportId
     * @return array|bool
     */
    public function getGiftReportDetailByReportId($reportId)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomTrialReport AS tp',
            'column' => 'tp.id,tp.gifts_id gift_id,tp.user_id,tp.gifts_id gift_id,tp.title,tp.content,tp.images,tp.tag_name,tp.is_good,tp.star,tp.add_time,
            u.headimgurl,u.nickname,gtp.goods_id,gtp.content g_content,gtp.images g_images,g.goods_name,g.goods_image,g.market_price,g.goods_price as price',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangUser AS u ON tp.user_id = u.id 
            LEFT JOIN \Shop\Models\BaiyangMomGoodsTrialReport AS gtp ON tp.id = gtp.report_id 
            LEFT JOIN \Shop\Models\BaiyangGoods AS g ON gtp.goods_id = g.id',
            'where' => "WHERE tp.id=:report_id:",
            'bind' => array(
                'report_id' => $reportId
            )
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 按报告id和商品id获取单品报告详情
     *
     * @param int $reportId
     * @return array|bool
     */
    public function getGiftGoodsReportDetail($reportId, $goodsId)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomTrialReport AS tp',
            'column' => 'tp.id report_id,tp.gifts_id gift_id,tp.add_time,u.nickname,u.headimgurl,gtp.goods_id,g.goods_name title,gtp.content,gtp.images,g.id goods_id',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangUser AS u ON tp.user_id = u.id 
            LEFT JOIN \Shop\Models\BaiyangMomGoodsTrialReport AS gtp ON tp.id = gtp.report_id 
            LEFT JOIN \Shop\Models\BaiyangGoods AS g ON gtp.goods_id = g.id',
            'where' => "WHERE tp.id=:report_id: AND gtp.goods_id=:goods_id:",
            'bind' => array(
                'report_id' => $reportId,
                'goods_id' => $goodsId
            )
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 按数量获取精品报告列表
     *
     * @param int $limitNumber 限制数量
     * @return array|bool
     */
    public function getGiftReportLimitList($gift_id, $page, $size, $isGood=false)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomTrialReport AS tp',
            'column' => 'tp.id report_id,tp.user_id,tp.gifts_id gift_id,tp.title,tp.content,tp.is_good,tp.images,tp.add_time,u.nickname,u.headimgurl,u.phone',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangUser AS u ON tp.user_id = u.id',
            'order' => 'ORDER BY tp.id DESC',
            'limit' => "LIMIT {$page},{$size}"
        );
        if ($gift_id) {
            $param['where'] = 'WHERE tp.gifts_id=' . intval($gift_id);
        }
        if ($isGood) {
            $param['where'] = 'WHERE tp.is_good=1';
        }
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 按礼包商品id报告列表
     *
     * @param int $limitNumber 限制数量
     * @return array|bool
     */
    public function getGiftGoodsReportByIdList($goodsReportId)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomGoodsTrialReport AS gtp',
            'column' => 'tp.id report_id,tp.user_id,tp.add_time,tp.is_good,gtp.goods_id,g.goods_name title,gtp.content,gtp.images,u.nickname,u.headimgurl,u.phone',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangMomTrialReport AS tp ON gtp.report_id=tp.id 
            LEFT JOIN \Shop\Models\BaiyangUser AS u ON tp.user_id = u.id
            LEFT JOIN \Shop\Models\BaiyangGoods AS g ON gtp.goods_id = g.id',
            'where' => "WHERE gtp.id IN({$goodsReportId})",
            'order' => 'ORDER BY tp.id DESC',
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 按数量获取礼包商品报告id列表
     *
     * @param int $limitNumber 限制数量
     * @return array|bool
     */
    public function getGiftGoodsReportIdLimit($limitNumber)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomGoodsTrialReport AS gtp',
            'join' => 'INNER JOIN \Shop\Models\BaiyangMomTrialReport AS tp ON gtp.report_id=tp.id',
            'column' => 'gtp.id',
            'order' => 'ORDER BY gtp.id DESC',
            'limit' => 'LIMIT 0,' . $limitNumber
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 妈妈精品报告数量
     *
     * @return bool|int
     */
    public function getBestGiftReportCount()
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomTrialReport',
            'where' => "WHERE is_good=1",
        );
        $ret = $this->countData($param);
        return $ret;
    }

    /**
     * 所有妈妈礼包报告数量
     *
     * @return bool|int
     */
    public function getGiftReportCount($giftId=0)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomTrialReport',
        );
        if ($giftId) {
            $param['where'] = "WHERE gifts_id=:gift_id:";
            $param['bind'] = array(
                'gift_id' => ($giftId)
            );
        }
        $ret = $this->countData($param);
        return $ret;
    }

    /**
     * 所有妈妈礼包商品报告数量
     *
     * @return bool|int
     */
    public function getGiftGoodsReportCount()
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGoodsTrialReport',
        );
        $ret = $this->countData($param);
        return $ret;
    }

    /**
     * 获取礼包商品列表
     *
     * @param int $page 页码
     * @param int $size 页数
     * @return array|bool
     */
    public function getGiftGoodsReportLimitList($page, $size)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomGoodsTrialReport AS gtp',
            'column' => 'gtp.report_id,gtp.goods_id,tp.add_time,g.goods_name title,gtp.content,gtp.images,u.nickname,u.headimgurl,u.phone',
            'join' => 'LEFT JOIN \Shop\Models\BaiyangMomTrialReport AS tp ON gtp.report_id=tp.id 
            LEFT JOIN \Shop\Models\BaiyangUser AS u ON tp.user_id = u.id
            LEFT JOIN \Shop\Models\BaiyangGoods AS g ON gtp.goods_id = g.id',
            'order' => 'ORDER BY gtp.id DESC',
            'limit' => "LIMIT {$page},{$size}"
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 添加礼包报告
     *
     * @param array $param
     * @return bool|string
     */
    public function addGiftReport($param)
    {
        $data = array(
            'table' => 'Shop\Models\BaiyangMomTrialReport',
            'bind' => array(
                'gifts_id' => $param['gift_id'],
                'user_id' => $param['user_id'],
                'title' => $param['title'],
                'content' => $param['content'],
                'images' => $param['tmpImageList'],
                'tag_name' => $param['tag_name'],
                'star' => $param['star'],
                'add_time' => $param['add_time']
            )
        );
        $ret = $this->addData($data, true);
        return $ret;
    }

    /**
     * 添加礼包商品报告
     *
     * @param int $reportId 报告id
     * @param $param
     * @return bool|string
     */
    public function addGiftGoodsReport($reportId, $param)
    {
        $data = array(
            'table' => 'Shop\Models\BaiyangMomGoodsTrialReport',
        );

        foreach ($param['goods_id_list'] as $key => $goodsId) {
            $data['bind'] = array(
                'report_id' => $reportId,
                'goods_id' => $goodsId,
                'content' => $param['goods_content_list'][$key],
                'images' => $param['tmpGoodsImageList'][$key],
            );
            $ret = $this->addData($data, true);
        }
        return $ret;
    }
}