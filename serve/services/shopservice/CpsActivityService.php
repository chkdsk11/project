<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/6/26
 * Time: 15:55
 */

namespace Shop\Services;

use Shop\Datas\BaiyangGoodsData;
use Shop\Datas\BaiyangBrandsData;

class CpsActivityService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 导出模板
     * @param $param
     *              - type_id int 活动类型 3 品牌 4 商品
     * @author Chensonglu
     */
    public function exportTemplate($param)
    {
        $text = isset($param['type_id']) && $param['type_id'] == 3 ? '品牌ID' : '商品ID';
        $fileName = isset($param['type_id']) && $param['type_id'] == 3 ? 'brand' : 'product';
        $headArray = [$text,'首单返利','正常返利'];
        $result = [
            [8013298,10,2],
            [8024512,10,2],
            [8024513,10,2],
        ];
        $this->excel->exportExcel($headArray,$result,$fileName,'发货单','xls');
    }

    /**
     * 批量导入品牌或商品处理
     * @param $param
     *              - filePath string 上传文件路径
     *              - fileType string 上传文件类型
     *              - type_id int 活动类型 3 品牌 4 商品
     * @return array
     * @author Chensonglu
     */
    public function getImportData($param)
    {
        if (!isset($param['filePath']) || !$param['filePath']) {
            return $this->arrayData('请上传文件','',$param,'error');
        }
        if (!isset($param['fileType']) || !in_array($param['fileType'], ['xlsx','xls'])) {
            return $this->arrayData('请上传xlsx或xls格式文件','',$param,'error');
        }
        if (!isset($param['type_id']) || !in_array($param['type_id'], [3,4])) {
            return $this->arrayData('请选择品牌或单品活动类型','',$param,'error');
        }
        $import = $this->excel->importExcel($param['filePath'], $param['fileType']);
        if (!$import) {
            return $this->arrayData('上传的文件没有数据','',$import,'error');
        }
        $id = array_unique(array_column($import, '0'));
        $first = array_column($import, '1', '0');
        $normal = array_column($import, '2', '0');
        if ($param['type_id'] == 3) {
            $where = "id IN (".implode(',',$id).") ";
            $data = BaiyangBrandsData::getInstance()->getBrand($where, 'id,brand_name name');
            if (!$data) {
                return $this->arrayData('所有品牌ID都不存在','',$import,'error');
            }
        } else {
            $where = "id IN (".implode(',',$id).") AND gift_yes = 1 AND (sale_timing_app = 1 OR sale_timing_wap = 1 OR "
                . "sale_timing_wechat = 1 OR is_on_sale = 1) ";
            $data = BaiyangGoodsData::getInstance()->getGoods($where, 'id,goods_name name');
            if (!$data) {
                return $this->arrayData('所有商品四个端都没上架或都是赠品','',$import,'error');
            }
        }
        $newId = array_column($data, 'id');
        foreach ($data as $k => $val) {
            $val['first'] = '';
            $val['normal'] = '';
            if (isset($first[$val['id']])) {
                $val['first'] = $first[$val['id']];
            }
            if (isset($normal[$val['id']])) {
                $val['normal'] = $normal[$val['id']];
            }
            $data[$k] = $val;
        }
        $noSelect = array_diff($id, $newId);
        return [
            'data' => $data,
            'errorData' => $noSelect ? implode(',', $noSelect) : '',
        ];
    }
}