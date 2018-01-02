<?php

/**
 * 短信发送记录
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaiyangSmsRecordsData;
use Shop\Datas\BaseData;

class SmsRecordsService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * 发送短信记录
     * @param type $param
     * @return type
     */
    public function getAll($param) {
        //获取服务商列表
        $param1 = array(
            'column' => '*',
            'table' => "\Shop\Models\BaiyangSmsProvider",
        );
        $providers = BaseData::getInstance()->getData($param1);
        //获取客户端列表
        $param2 = array(
            'column' => 'client_id,client_name,client_code',
            'table' => '\Shop\Models\BaiyangSmsClient',
        );
        $clients = BaseData::getInstance()->getData($param2);
        //获取短信列表
        $conditions = [];
        $whereStr = "";
        if (!empty($param['option']['provider_code'])) {
            $whereStr .= empty($whereStr) ? "r.provider_code = :provider_code:" : " AND r.provider_code = :provider_code:";
            $conditions['provider_code'] = $param['option']['provider_code'];
        }
        if (!empty($param['option']['client_code'])) {
            $whereStr .= empty($whereStr) ? "r.client_code = :client_code:" : " AND r.client_code = :client_code:";
            $conditions['client_code'] = $param['option']['client_code'];
        }
        if (!empty($param['option']['content'])) {
            $whereStr .= empty($whereStr) ? "(r.ip_address = :content: OR r.phone = :content:)" : " AND (r.ip_address = :content: OR r.phone = :content:)";
            $conditions['content'] = $param['option']['content'];
        }
        if (!empty($param['option']['starttime'])) {
            $whereStr .= empty($whereStr) ? "r.create_time >= :starttime:" : " AND r.create_time >= :starttime:";
            $conditions['starttime'] = $param['option']['starttime'];
        }
        if (!empty($param['option']['endtime'])) {
            $whereStr .= empty($whereStr) ? "r.create_time <= :endtime:" : " AND r.create_time <= :endtime:";
            $conditions['endtime'] = $param['option']['endtime'];
        }
        $whereStr = empty($whereStr) ? $whereStr : "WHERE " . $whereStr;
        $selections = "r.record_id,r.provider_code,r.client_code,r.ip_address,r.phone,r.content,r.create_time,p.provider_name,c.client_name";
        $tables = array(
            'records' => '\Shop\Models\BaiyangSmsRecords as r',
            'provider' => '\Shop\Models\BaiyangSmsProvider as p',
            'client' => '\Shop\Models\BaiyangSmsClient as c'
        );
        $count = BaiyangSmsRecordsData::getInstance()->countJoin($tables, $conditions, $whereStr);
        if (!$count) {
            return array(
                'status' => 'success',
                'providers' => $providers,
                'clients' => $clients,
                'count' => 0,
                'page' => ''
            );
        }
        //分页
        $pages['page'] = $param['page']; //当前页
        $pages['counts'] = $count;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $page = $this->page->pageDetail($pages);
        //排序和分页
        $order = "ORDER BY r.create_time DESC";
        $limit = "LIMIT {$page['record']},{$page['psize']}";
        $result = BaiyangSmsRecordsData::getInstance()->selectJoin($selections, $tables, $conditions, $whereStr, $order, $limit);
        return array(
            'status' => 'success',
            'list' => $result,
            'providers' => $providers,
            'clients' => $clients,
            'count' => $count,
            'page' => $page['page']
        );
    }

    /**
     * 区别短信数据或者曲线图
     * @param type $param
     * @return array
     */
    public function statement($param) {
        if (isset($param['name']) && $param['name'] == 'recordes') { //短信数据
            $arrtime = ['today', 'onemonth', 'threemonth'];
            if (isset($param['seltime']) && in_array($param['seltime'], $arrtime)) {
                return $this->recordes($param['seltime']);
            }
        } else if (isset($param['name']) && $param['name'] == 'curves') {  //曲线图
            $sendname = ['send', 'rsend'];
            $arrtime = ['onehour', 'threehour', 'seltime'];
            if (isset($param['sendname']) && in_array($param['sendname'], $sendname) && isset($param['seltime']) && in_array($param['seltime'], $arrtime)) {
                return $this->curves($param);
            }
        }
        return $this->arrayData('请求参数错误', '', '', 'error');
    }

    /**
     * 短信数据
     * @param type $time 传递时间参数
     * @return array
     */
    protected function recordes($time) {
        //获取每个服务商的剩余短信数
        $param1 = array(
            'column' => 'provider_id,provider_name,provider_code,remainder_quantity',
            'table' => "\Shop\Models\BaiyangSmsProvider",
        );
        $providers = BaseData::getInstance()->getData($param1);
        if (!$providers) {
            return $this->arrayData('无短信服务商', '', '', 'error');
        }
        $starttime = date("Y-m-d"); //当天
        if ($time == 'onemonth') { //最近一个月
            $starttime = date("Y-m-d H:i:s", strtotime("-1 month"));
        } else if ($time == 'threemonth') { //最近三个月
            $starttime = date("Y-m-d H:i:s", strtotime("-3 month"));
        }
        $conditions['create_time'] = $starttime;
        $column = "provider_code,count(record_id) as count";
        $table = "\Shop\Models\BaiyangSmsRecords";
        //获取短信发送记录(正常发送)
        $where1 = "WHERE is_success = 0 AND send_type = 0 AND create_time >= :create_time:";
        $send = BaiyangSmsRecordsData::getInstance()->countRecord($column, $table, $conditions, $where1);
        //获取短信发送记录(补发)
        $where2 = "WHERE is_success = 0 AND send_type = 1 AND create_time >= :create_time:";
        $rsend = BaiyangSmsRecordsData::getInstance()->countRecord($column, $table, $conditions, $where2);
        //合并数据
        $return_arr = array();
        $return_arr['send'] = 0;  //发送数
        if ($send && is_array($send)) {
            foreach ($send as $k1 => $v1) {
                $return_arr['send'] += (int) $v1['count'];
            }
        }
        $return_arr['rsend'] = 0;  //补发数
        if ($rsend && is_array($rsend)) {
            foreach ($rsend as $k2 => $v2) {
                $return_arr['rsend'] += (int) $v2['count'];
            }
        }
        $return_arr['residue'] = 0;  //剩余数
        $return_arr['statement'] = array();
        foreach ($providers as $k => $v) {
            $num1 = 0;
            if ($send && is_array($send)) {
                foreach ($send as $k1 => $v1) {
                    if ($v['provider_code'] == $v1['provider_code']) {
                        $num1 = $v1['count'];
                        break;
                    }
                }
            }
            $return_arr['statement'][$v['provider_code']][] = $v['provider_name'] . ":" . $num1;
            $num2 = 0;
            if ($rsend && is_array($rsend)) {
                foreach ($rsend as $k2 => $v2) {
                    if ($v['provider_code'] == $v2['provider_code']) {
                        $num2 = $v2['count'];
                        break;
                    }
                }
            }
            $return_arr['residue'] += (int) $v['remainder_quantity'];
            $return_arr['statement'][$v['provider_code']][] = $v['provider_name'] . ":" . $num2;
            $return_arr['statement'][$v['provider_code']][] = $v['provider_name'] . ":" . $v['remainder_quantity'];
        }
        return $this->arrayData('请求成功', '', $return_arr, 'success');
    }

    /**
     * 曲线图
     * @param type $param
     * * @return array
     */
    protected function curves($param) {
        $starttime = time() - 3600; //默认最近一小时
        $endtime = time(); //默认结束时间为当前时间
        if ($param['seltime'] == "threehour") {
            $starttime = time() - 3600 * 3;
        } else if ($param['seltime'] == "seltime") {
            if (empty($param['starttime'])) {
                return $this->arrayData('请输入开始时间', '', [], 'error');
            } else {
                $starttime = strtotime($param['starttime']);
            }
            if (empty($param['endtime']) == false) {
                $endtime = strtotime($param['endtime']);
            }
        }
        //判断开始结束时间差，如果大于一天，按小时为单位处理数据，否则按分钟处理
        $unit = "minute";
        if ($endtime - $starttime <= 0) {
            return $this->arrayData('结束时间必须大于开始时间', '', '', 'error');
        }
        if ($endtime - $starttime >= 24 * 3600 * 30) {
            return $this->arrayData('时间段最多跨度一个月', '', '', 'error');
        }
        if ($endtime - $starttime >= 24 * 3600) {
            $unit = "hour";
        }
        //处理数据
        return $this->dataFormat($param['sendname'], $unit, $starttime, $endtime);
    }

    /**
     *  获取数据且格式化
     * * @param type $sendname 请求类型
     * @param type $unit 单位
     * @param type $starttime
     * @param type $endtime
     * @return array
     */
    protected function dataFormat($sendname, $unit, $starttime, $endtime) {
        //将开始结束时间做xunit分段
        $units = array();
        if ($unit == "minute") { //分钟
            $starttime = strtotime(date('Y-m-d H:i:00', $starttime));
            $endtime = strtotime(date('Y-m-d H:i:00', $endtime));
            $num = intval(($endtime - $starttime) / 60);
            for ($i = 0; $i < $num; $i++) {
                $time = $starttime + $i * 60;
                $x_unit = date('H', $time) . '时' . date('i', $time) . '分';
                array_push($units, $x_unit);
            }
        } else {
            $starttime = strtotime(date('Y-m-d H:00:00', $starttime));
            $endtime = strtotime(date('Y-m-d H:00:00', $endtime));
            $num = intval(($endtime - $starttime) / 3600);
            for ($i = 0; $i <= $num; $i++) {
                $time = $starttime + $i * 3600;
                $x_unit = date('d', $time) . '日' . date('H', $time) . '时';
                array_push($units, $x_unit);
            }
        }
        //获取供应商列表
        $param = array(
            'column' => 'provider_code,provider_name',
            'table' => "\Shop\Models\BaiyangSmsProvider",
        );
        $providers = BaseData::getInstance()->getData($param);
        $provider_arr = array(); //处理数组
        foreach ($providers as $k => $v) {
            $provider_arr[$v['provider_code']] = $v['provider_name'];
        }
        //获取分段数据
        $record = BaiyangSmsRecordsData::getInstance()->groupCount($sendname, $unit, date('Y-m-d H:i:s', $starttime), date('Y-m-d H:i:s', $endtime));
        $record_arr = array(); //处理数组
        foreach ($record as $k => $v) {
            $record_arr[$v['provider_code']][$v['xunit']] = $v['count'];
        }
        //重组数据
        $return_arr = array();
        $return_arr['units'] = $units;
        $return_arr['provider'] = array();
        $return_arr['seriesdata'] = array();
        foreach ($provider_arr as $key => $value) {
            $return_arr['provider'][] = $value;
            foreach ($record_arr as $k => $v) {
                if ($k == $key) { //对应服务商
                    foreach ($units as $kk => $vv) {
                        $return_arr['seriesdata'][$k]['name'] = $value;
                        $return_arr['seriesdata'][$k]['records'][] = isset($v[$vv]) ? $v[$vv] : 0;
                    }
                    break;
                }
            }
            if (!isset($return_arr['seriesdata'][$key])) {
                $return_arr['seriesdata'][$key]['name'] = $value;
                $return_arr['seriesdata'][$key]['records'] = array();
            }
        }
        return $this->arrayData('请求成功！', '', $return_arr, 'success');
    }

}
