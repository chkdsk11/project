<?php

/**
 * 短信服务商
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaiyangSmsProviderData;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangSmsProvider;
use Shop\Models\BaiyangSmsProviderPasswords;
use Shop\Home\Services\SmsService;

class SmsProviderService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     *  获取列表,所有信息
     * @return array
     */
    public function getAll() {
        $param = array(
            'column' => '*',
            'table' => "\Shop\Models\BaiyangSmsProvider",
        );
        $result = BaseData::getInstance()->getData($param);
        return $result;
    }

    /**
     *  更新服务商状态
     * @param type $param
     * @return array
     */
    public function updateState($param) {
        if (!isset($param['provider_id']) || !isset($param['provider_state'])) {
            return $this->arrayData('参数错误', '', '', 'error');
        }
        $provider_id = (int) $param['provider_id'];
        $provider_state = (int) $param['provider_state'];
        $prov = BaiyangSmsProvider::findFirst("provider_id = {$provider_id}");
        if ($prov) {
            if ($prov->provider_state == $provider_state) {
                return $this->arrayData('该服务商状态已更新请刷新后操作');
            }
            $this->dbWrite->begin();
            $prov->provider_state = $provider_state;
            $prov->modify_time = date('Y-m-d H:i:s');
            $prov->modify_at = $_SESSION['user_id'];
            $prov->priority = 0;
            $prov->scale = 0;
            if ($provider_state == 1) { //关闭时平均分配剩下的启用的服务商比例
                $scale = $this->allotScale($prov->provider_id);
                if (empty($scale) == false && is_array($scale)) {//修改分配比例
                    $flag = true;
                    $data['modify_time'] = date('Y-m-d H:i:s');
                    $data['modify_at'] = $_SESSION['user_id'];
                    foreach ($scale as $k => $v) {
                        $data['provider_id'] = $k;
                        $data['scale'] = $v;
                        $columStr = $this->jointString($data, array('provider_id'));
                        $where = 'provider_id=:provider_id:';
                        $ret = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangSmsProvider', $data, $where);
                        if (!$ret) {
                            $flag = false;
                            break;
                        }
                    }
                    if ($flag == false) {
                        $this->dbWrite->rollback();
                        return $this->arrayData('比例分配修改失败！', '', '', 'error');
                    }
                }
            }
            if ($prov->save()) {
                $this->dbWrite->commit();
                //关闭时将该关闭比例平均到其他开启的服务商
                return $this->arrayData('更新成功！');
            } else {
                $this->dbWrite->rollback();
                return $this->arrayData('更新失败', '', '', 'error');
            }
        } else {
            return $this->arrayData('服务商不存在', '', '', 'error');
        }
    }

    /**
     * 禁用服务商时重新分配其他启用服务商发送比例,返回对应比例数组
     * @param type $provider_id
     * @return type arr
     */
    protected function allotScale($provider_id) {
        $scale = array();  //返回需要更新的数组
        //获取正在启用的服务商
        $results = BaiyangSmsProvider::query()
                ->where('provider_state = 0')
                ->andWhere("provider_id != {$provider_id}")
                ->execute()
                ->toArray();
        if (count($results) >= 1) {
            $sum = 0;
            foreach ($results as $item) {
                $sum = bcadd($sum, $item['scale'], 2);
            }
            if ($sum > 1) { //大于1说明原先设置有问题
                return $scale;
            }

            //重新计算分配发送比例
            if (bccomp($sum, '1', 2) !== 0) { //和不正好为1
                $sub = bcsub('1', $sum, 2);
                $div = bcdiv($sub, count($results), 2); //剩余平均值
                $div_rem = $sub - $div * count($results);  //除不尽的余数
                foreach ($results as $k => $v) {
                    if ($k == 0) { //第一个加上余数
                        $scale[$v['provider_id']] = bcadd($v['scale'], $div, 2) + $div_rem;
                    } else {
                        $scale[$v['provider_id']] = bcadd($v['scale'], $div, 2);
                    }
                }
            }
        }
        return $scale;
    }

    /**
     * 修改比例
     * @param type $param
     * @return array
     */
    public function editScale($param) {
        if (is_array($param) && empty($param) == false) {
            $arr = [];
            $strids = "";
            $sum = 0;
            foreach ($param as $k => $v) {
                $arr[(int) $v->name] = $v->value;
                $strids .= $v->name . ",";
                if (!is_numeric($v->value) || $v->value < 0 || $v->value > 1) {
                    return $this->arrayData('比例必须在0~1之间', '', '', 'error');
                }
                $sum += $v->value;
            }
            if ($sum != 1) {
                return $this->arrayData('比例之和必须为1', '', '', 'error');
            }
            //判断是否是开启状态
            $strids = rtrim($strids, ',');
            $pa = array(
                'column' => '*',
                'table' => '\Shop\Models\BaiyangSmsProvider',
                'where' => "where provider_id in ({$strids}) and provider_state = 1"
            );
            $res = BaseData::getInstance()->getData($pa);
            if ($res) {
                return $this->arrayData('有服务商已停用，请重新设置！', '', '', 'error');
            }
            //更新数据
            $this->dbWrite->begin();
            $flag = true;
            $data['modify_time'] = date('Y-m-d H:i:s');
            $data['modify_at'] = $_SESSION['user_id'];
            foreach ($arr as $k => $v) {
                $data['provider_id'] = $k;
                $data['scale'] = $v;
                $columStr = $this->jointString($data, array('provider_id'));
                $where = 'provider_id=:provider_id:';
                $ret = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangSmsProvider', $data, $where);
                if (!$ret) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $this->dbWrite->commit();
                return $this->arrayData('修改成功！');
            } else {
                $this->dbWrite->rollback();
                return $this->arrayData('比例分配修改失败！', '', '', 'error');
            }
        }
        return $this->arrayData('参数错误！', '', '', 'error');
    }

    /**
     * 修改补发优先级
     * @param type $param
     * @return array
     */
    public function editPriority($param) {
        if (is_array($param) && empty($param) == false) {
            $arr = [];
            $strids = "";
            foreach ($param as $k => $v) {
                $arr[(int) $v->name] = (int) $v->value;
                $strids .= $v->name . ",";
            }
            //判断有无重复值
            if (count($arr) !== count(array_unique($arr))) {
                return $this->arrayData('优先级不可重复！', '', '', 'error');
            }
            //判断是否是开启状态
            $strids = rtrim($strids, ',');
            $pa = array(
                'column' => '*',
                'table' => '\Shop\Models\BaiyangSmsProvider',
                'where' => "where provider_id in ({$strids}) and provider_state = 1"
            );
            $res = BaseData::getInstance()->getData($pa);
            if ($res) {
                return $this->arrayData('有服务商已停用，请重新设置！', '', '', 'error');
            }
            //更新数据
            $this->dbWrite->begin();
            $flag = true;
            $data['modify_time'] = date('Y-m-d H:i:s');
            $data['modify_at'] = $_SESSION['user_id'];
            foreach ($arr as $k => $v) {
                $data['provider_id'] = $k;
                $data['priority'] = $v;
                $columStr = $this->jointString($data, array('provider_id'));
                $where = 'provider_id=:provider_id:';
                $ret = BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangSmsProvider', $data, $where);
                if (!$ret) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $this->dbWrite->commit();
                return $this->arrayData('更新成功！');
            } else {
                $this->dbWrite->rollback();
                return $this->arrayData('修改补发优先级失败！', '', '', 'error');
            }
        }
        return $this->arrayData('操作失败！', '', '', 'error');
    }

    /**
     * -----------------------------------------------------------------------
     * 手动修改密码和修改自动更新密码周期，记录
     * -----------------------------------------------------------------------
     */

    /**
     * 密码修改记录
     * @param type $param
     * @return type []
     */
    public function getLogs($param) {
        //获取服务商列表
        $param1 = array(
            'column' => '*',
            'table' => "\Shop\Models\BaiyangSmsProvider",
        );
        $providers = BaseData::getInstance()->getData($param1);
        //TODO 密码修改记录
        $conditions = [];
        $whereStr = "";
        if (!empty($param['option']['provider_id'])) {
            $conditions['provider_id'] = $param['option']['provider_id'];
            $whereStr .= empty($whereStr) ? "d.provider_id = :provider_id:" : " AND d.provider_id = :provider_id:";
        }
        //处理时间
        if (!empty($param['option']['selecttime'])) {
            if ($param['option']['selecttime'] == 1) { //最近一个月
                $starttime = date('Y-m-d H:i:s', strtotime('-1 month'));
            } else if ($param['option']['selecttime'] == 2) { //最近三个月
                $starttime = date('Y-m-d H:i:s', strtotime('-3 month'));
            } else if ($param['option']['selecttime'] == 3) { //最近半年
                $starttime = date('Y-m-d H:i:s', strtotime('-6 month'));
            } else if ($param['option']['selecttime'] == 4) { //最近一年
                $starttime = date('Y-m-d H:i:s', strtotime('-12 month'));
            }
            $conditions['create_time'] = $starttime;
            $whereStr .= empty($whereStr) ? "d.create_time >= :create_time:" : " AND d.create_time >= :create_time:";
        }
        $whereStr = empty($whereStr) ? $whereStr : "WHERE " . $whereStr;
        $selections = "d.id,d.provider_id,d.new_password,d.modify_type,d.create_time,d.create_at,p.provider_name,a.admin_account";
        $tables = array(
            'password' => '\Shop\Models\BaiyangSmsProviderPasswords as d',
            'provider' => '\Shop\Models\BaiyangSmsProvider as p',
            'admin' => '\Shop\Models\BaiyangAdmin as a'
        );
        //获取数量
        $count = BaiyangSmsProviderData::getInstance()->countJoin($tables, $conditions, $whereStr);
        if (!$count) {
            return array(
                'status' => 'success',
                'providers' => $providers,
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
        $order = "ORDER BY d.create_time DESC";
        $limit = "LIMIT {$page['record']},{$page['psize']}";
        $result = BaiyangSmsProviderData::getInstance()->selectJoin($selections, $tables, $conditions, $whereStr, $order, $limit);
        //搜索各个供应商最新一条修改记录
        $ids = [];
        if (is_array($providers) && empty($providers) == false) {
            foreach ($providers as $k => $v) {
                $ids[] = $v['provider_id'];
            }
        }
        $newids = BaiyangSmsProviderData::getInstance()->newIds($ids);
        return array(
            'status' => 'success',
            'list' => $result,
            'providers' => $providers,
            'newids' => $newids,
            'page' => $page['page']
        );
    }

    /**
     * 修改密码或周期
     * 接受参数，分别周期或密码修改
     * @param type $param
     */
    public function editPw($param) {
        if ($param['name'] == 'editFrequency') { //修改周期
            return $this->editFrequency($param);
        } else if ($param['name'] == 'editPw') {  //手动修改密码
            return $this->editPassword($param);
        } else {
            return $this->arrayData('参数错误', '', '', 'error');
        }
    }

    /**
     * 修改周期
     * @param type $param
     * @return type
     */
    protected function editFrequency($param) {
        $provider_id = (int) $param['provider_id'];
        if ($param['frequency'] <= 0 || !preg_match("/^\d+$/", $param['frequency'])) {
            return $this->arrayData('周期必须为正整数', '', '', 'error');
        }
        //判断是否是可自动修改密码的服务商
        $prov = BaiyangSmsProvider::findFirst("provider_id = {$provider_id}");
        if ($prov && $prov->is_auto_change_password == 1) { //修改周期
            //获取最后一次修改时间
            $arr = array(
                'provider_id = :provider_id:',
                'order' => 'create_time DESC',
                'bind' => array(
                    'provider_id' => $provider_id
                )
            );
            $time = time(); //最后更新时间
            $record = BaiyangSmsProviderPasswords::findFirst($arr);
            if ($record) {
                $time = strtotime($record->create_time);
            }
            $prov->frequency = $param['frequency'];
            $prov->modify_time = date('Y-m-d H:i:s');
            $prov->modify_at = $_SESSION['user_id'];
            $prov->next_change_password_time = date('Y-m-d H:i:s', $time + $param['frequency'] * 24 * 3600);
            if ($prov->save()) {
                return $this->arrayData('修改成功');
            } else {
                return $this->arrayData('修改失败，请刷新后重试', '', '', 'error');
            }
        } else {
            return $this->arrayData('该服务商不存在或不可自动修改密码', '', '', 'error');
        }
    }

    /**
     * 手动修改密码
     * @param type $param
     * @return type
     */
    protected function editPassword($param) {
        if (empty($param['password'])) {
            return $this->arrayData('密码不可为空', '', '', 'error');
        }
        $provider_id = (int) $param['provider_id'];
        //判断是否是可自动修改密码的服务商
        $prov = BaiyangSmsProvider::findFirst("provider_id = {$provider_id}");
        //TODO 修改密码，测试环境注意，不要把真正服务商密码修改了
        if ($prov) {
            //判断是否有修改接口
            $user_id = $_SESSION['user_id'];
            $isAuto = true; //是否是手动修改
            $phone = null;
            if ($prov->is_auto_change_password != 1) {  //无接口时验证手机号
                $isMob = "/^1[3,4,5,7,8]{1}[0-9]{9}$/";
                if (!preg_match($isMob, $param['phone'])) {
                    return $this->arrayData('手机号格式不正确', '', [], 'error');
                }
                $phone = $param['phone'];
            }
            $return = SmsService::getInstance()->changePassword($prov->provider_code, $param['password'], $isAuto, $phone, $user_id);
            if ($return['errcode'] == 0) {
                return $this->arrayData('修改密码成功');
            } else {
                return $this->arrayData($return['errmsg'], '', [], 'error');
            }
        } else {
            return $this->arrayData('该服务商不存在', '', '', 'error');
        }
    }

}
