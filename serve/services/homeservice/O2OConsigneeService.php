<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2017/02/07 1106
 * Time: 上午 11:06
 */

namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangO2oData;
use Shop\Home\Datas\BaseData;
use Shop\Models\HttpStatus;
use Shop\Home\Datas\BaiyangUserConsigneeData;
use Shop\Models\BaiyangConfigEnum;
use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Listens\AuthListener;
use Shop\Models\OrderEnum;

class O2OConsigneeService extends BaseService
{
    protected static $instance = null;

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new O2OConsigneeService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('auth', new AuthListener());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * @desc 收货地址列表
     * @param array $param
     *      -int user_id 用户id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     *
     */
    public function getConsigneeList($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($param['user_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        // 收货地址信息
        $consigneeList = $userConsigneeData->getUserConsigneeList($param['user_id']);
        if (empty($consigneeList)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, ['consigneeList' => []]);
        }
        // 用户收货地址数量
        $consigneeNumber = $userConsigneeData->getConsigneeNumber($param['user_id']);
        // 配置表的用户收货信息最大值
        $configNumber = $this->func->getConfigValue(BaiyangConfigEnum::CONSIGNEE_NUM);
        $remainNumber = ($configNumber - $consigneeNumber > 0) ? $configNumber - $consigneeNumber : 0;


        $consigneeList_rs = $consigneeList;
        //获取o2o 的配送范围
        $range = $this->getOtoDeliveryArea();
        $consigneeList = [
            'oto' => [],
            'other' => []
        ];

        if ($consigneeList_rs and is_array($consigneeList_rs)) {
            foreach ($consigneeList_rs as $k => $v) {
                if ($range) {
                    if (in_array($v['county'], $range)
                        and
                        !(
                            $v['city'] == 284
                            and
                            (
                                strpos($v['address'] , '开封路88号') !== false
                                or strpos($v['address'] , '百洋科技园') !== false
                                or strpos($v['address'] , '百洋健康科技园') !== false
                            )
                        )
                    ) {
                        $consigneeList['oto'][] = $v;
                    } else {
                        $consigneeList['other'][] = $v;
                    }
                } else {
                    $consigneeList['other'][] = $v;
                }
            }
        }
        unset($rangeRs);

        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeList' => $consigneeList]);
    }

    /**
     * @desc 修改默认收货地址
     * @param array $param
     *      -int user_id 用户id
     *      -int consignee_id 收货地址主键id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function editDefaultConsignee($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['consignee_id'] = isset($param['consignee_id']) ? (int)$param['consignee_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($param['user_id']) || empty($param['consignee_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        // 验证收货地址信息
        $consigneeInfo = $userConsigneeData->getConsigneeInfo([
            'column' => 'id',
            'where' => 'id = :id: and user_id = :user_id:',
            'bind' => [
                'id' => $param['consignee_id'],
                'user_id' => $param['user_id'],
            ],
        ]);
        if (empty($consigneeInfo)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $baseData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangUserConsignee';
        $bind = [
            'consignee_id' => $param['consignee_id'],
            'user_id' => $param['user_id'],
        ];
        // 设为默认收货地址
        $result1 = $baseData->updateData([
            'table' => $table,
            'column' => 'default_addr = 1',
            'where' => 'where id = :consignee_id: and user_id = :user_id:',
            'bind' => $bind,
        ]);
        // 其他地址设为非默认
        $result2 = $baseData->updateData([
            'table' => $table,
            'column' => 'default_addr = 0',
            'where' => 'where id != :consignee_id: and user_id = :user_id:',
            'bind' => $bind,
        ]);
        if (!$result1 || !$result2) {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
        }
        // 收货地址信息
        $consigneeList = $userConsigneeData->getUserConsigneeList($param['user_id']);
        // 用户收货地址数量
        $consigneeNumber = $userConsigneeData->getConsigneeNumber($param['user_id']);
        // 配置表的用户收货信息最大值
        $configNumber = $this->func->getConfigValue(BaiyangConfigEnum::CONSIGNEE_NUM);
        $remainNumber = ($configNumber - $consigneeNumber > 0) ? $configNumber - $consigneeNumber : 0;
        if (empty($consigneeList)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, ['consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeList' => []]);
        }
        $consigneeList_rs = $consigneeList;
        //获取o2o 的配送范围
        $range = $this->getOtoDeliveryArea();
        $consigneeList = [
            'oto' => [],
            'other' => []
        ];

        if ($consigneeList_rs and is_array($consigneeList_rs)) {
            foreach ($consigneeList_rs as $k => $v) {
                if ($range) {
                    if (in_array($v['county'], $range)
                        and
                        !(
                            $v['city'] == 284
                            and
                            (
                                strpos($v['address'] , '开封路88号') !== false
                                or strpos($v['address'] , '百洋科技园') !== false
                                or strpos($v['address'] , '百洋健康科技园') !== false
                            )
                        )
                    ) {
                        $consigneeList['oto'][] = $v;
                    } else {
                        $consigneeList['other'][] = $v;
                    }
                } else {
                    $consigneeList['other'][] = $v;
                }

            }
        }
        unset($rangeRs);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeList' => $consigneeList]);
    }

    /**
     * @desc 新增或修改收货地址信息
     * @param array $param
     *      -int user_id 用户id
     *      -string action  动作(add:添加 edit:修改)
     *      -int consignee_id 收货地址主键id  (edit时必填)
     *      -string consignee 收货人姓名
     *      -string idCard 收货人身份证号码
     *      -int province 省id
     *      -int city 市id
     *      -int county 区id
     *      -string address 详细地址
     *      -string telphone 联系电话
     *      -string fix_line 固定电话
     *      -string email 电子邮件
     *      -string zipcode 邮政编码
     *      -int default_addr 是否默认地址(1:是 0:否)
     *      -int tag_id 标签id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function addOrEditConsignee($param)
    {
        // 格式化参数
        $action = isset($param['action']) ? (string)$param['action'] : '';
        $data['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $data['id'] = isset($param['consignee_id']) ? (int)$param['consignee_id'] : 0;
        $data['consignee'] = isset($param['consignee']) ? (string)$param['consignee'] : '';
        $idCard = isset($param['idCard']) ? (string)$param['idCard'] : '';
        $data['province'] = isset($param['province']) ? (int)$param['province'] : 0;
        $data['city'] = isset($param['city']) ? (int)$param['city'] : 0;
        $data['county'] = isset($param['county']) ? (int)$param['county'] : 0;
        $data['address'] = isset($param['address']) ? (string)$param['address'] : '';
        $data['telphone'] = isset($param['telphone']) ? (string)$param['telphone'] : '';
        $data['fix_line'] = isset($param['fix_line']) ? (string)$param['fix_line'] : '';
        $data['email'] = isset($param['email']) ? (string)$param['email'] : '';
        $data['zipcode'] = isset($param['zipcode']) ? (string)$param['zipcode'] : '';
        $data['default_addr'] = isset($param['default_addr']) ? (int)$param['default_addr'] : 0;
        $data['tag_id'] = isset($param['tag_id']) ? (int)$param['tag_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        $channelSubid = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $udid = isset($param['udid']) ? (string)$param['udid'] : '';
        if (!in_array($action, ['add', 'edit'])
            || empty($data['user_id'])
            || empty($data['consignee'])
            || empty($data['province'])
            || empty($data['city'])
            || empty($data['county'])
            || empty($data['address'])
            || empty($data['telphone'])
            || !$this->verifyRequiredParam($param)
        ) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if ($action == 'edit' && empty($data['id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        if ($data['city'] == 284
            and in_array($data['county'], [2345, 2343])
            and
            (
                strpos($data['address'] , '开封路88号') !== false
                or strpos($data['address'] , '百洋科技园') !== false
                or strpos($data['address'] , '百洋健康科技园') !== false
            )
        ) {
            return $this->uniteReturnResult(HttpStatus::OTO_ADDRESS_INVALID);
        }

        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $baseData = BaseData::getInstance();

        // 新增或修改收货地址
        if ($action == 'add') {
            // 用户收货地址数量
            $consigneeNumber = $userConsigneeData->getConsigneeNumber($param['user_id']);
            // 配置表的用户收货信息最大值
            $configNumber = $this->func->getConfigValue(BaiyangConfigEnum::CONSIGNEE_NUM);
            if ($consigneeNumber >= $configNumber) {
                return $this->uniteReturnResult(HttpStatus::MAX_CONSIGNEE_ADDRESS, [], [$configNumber]);
            }
            unset($data['id']);
            // 验证提交的是否正确的姓名和身份证组合(海外购)
            if ($idCard) {
                $result = $this->_eventsManager->fire('auth:idCardVerify', $this, [
                    'user_id' => $data['user_id'],
                    'username' => $data['consignee'],
                    'idCard' => $idCard,
                    'platform' => $platform,
                ]);
                if ($platform == OrderEnum::PLATFORM_PC && $result['code'] != HttpStatus::SUCCESS) {
                    return $this->uniteReturnResult($result['code']);
                }
                $data['consignee_id'] = $idCard;
                $data['identity_confirmed'] = ($result['code'] == HttpStatus::SUCCESS) ? 1 : 0;
            }
            $consigneeId = $baseData->addData([
                'table' => '\Shop\Models\BaiyangUserConsignee',
                'bind' => $data,
            ], true);
            if (!$consigneeId) {
                return $this->uniteReturnResult(HttpStatus::ADD_ERROR);
            }
            if ($consigneeNumber == 0) {
                // 修改默认收货地址
                $baseData->updateData([
                    'table' => '\Shop\Models\BaiyangUserConsignee',
                    'column' => 'default_addr = 1',
                    'where' => 'where id = :id: and user_id = :user_id:',
                    'bind' => [
                        'id' => $consigneeId,
                        'user_id' => $data['user_id'],
                    ],
                ]);
            }
        } else {
            // 验证收货地址信息
            $consigneeInfo = $userConsigneeData->getConsigneeInfo([
                'column' => 'id,consignee,consignee_id,default_addr',
                'where' => 'id = :id: and user_id = :user_id:',
                'bind' => [
                    'id' => $data['id'],
                    'user_id' => $data['user_id'],
                ],
            ]);
            if (empty($consigneeInfo)) {
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            // 修改收货人姓名时，一律把身份证验证改为0
            if ($data['consignee'] != $consigneeInfo['consignee']) {
                $data['identity_confirmed'] = 0;
            }
            $data['consignee_id'] = !empty($idCard) ? $idCard : $consigneeInfo['consignee_id'];
            $result = $this->_eventsManager->fire('auth:idCardVerify', $this, [
                'user_id' => $data['user_id'],
                'username' => $data['consignee'],
                'addressId' => $data['id'],
                'idCard' => $data['consignee_id'],
                'platform' => $platform,
            ]);
            if ($result['code'] == HttpStatus::SUCCESS) {
                $data['identity_confirmed'] = 1;
            } else {
                $data['identity_confirmed'] = 0;
                $data['consignee_id'] = '';
                if ($idCard && $platform == OrderEnum::PLATFORM_PC) {
                    return $this->uniteReturnResult($result['code']);
                }
            }
            $updateResult = $baseData->updateData([
                'table' => '\Shop\Models\BaiyangUserConsignee',
                'column' => $this->func->jointString($data, ['user_id', 'id']),
                'where' => 'where id = :id: and user_id = :user_id:',
                'bind' => $data,
            ]);
            if (!$updateResult) {
                return $this->uniteReturnResult(HttpStatus::EDIT_ERROR);
            }
        }

        // 当前收货地址主键id
        $consigneeId = ($action == 'add') ? $consigneeId : $data['id'];
        // 其他地址设为非默认
        if ($data['default_addr'] == 1) {
            $baseData->updateData([
                'table' => '\Shop\Models\BaiyangUserConsignee',
                'column' => 'default_addr = 0',
                'where' => 'where id != :id: and user_id = :user_id:',
                'bind' => [
                    'id' => $consigneeId,
                    'user_id' => $data['user_id'],
                ]
            ]);
        }
        // 用户收货地址数量
        $consigneeNumber = $userConsigneeData->getConsigneeNumber($param['user_id']);
        // 配置表的用户收货信息最大值
        $configNumber = $this->func->getConfigValue(BaiyangConfigEnum::CONSIGNEE_NUM);
        $remainNumber = ($configNumber - $consigneeNumber > 0) ? $configNumber - $consigneeNumber : 0;
        // 收货地址信息
        $consigneeList_rs = BaiyangUserConsigneeData::getInstance()->getUserConsigneeList($data['user_id']);


        //获取o2o 的配送范围
        $range = $this->getOtoDeliveryArea();
        $consigneeList = [
            'oto' => [],
            'other' => []
        ];

        if ($consigneeList_rs and is_array($consigneeList_rs)) {
            foreach ($consigneeList_rs as $k => $v) {
                if ($range) {
                    if (in_array($v['county'], $range)
                        and
                        !(
                            $v['city'] == 284
                            and
                            (
                                strpos($v['address'] , '开封路88号') !== false
                                or strpos($v['address'] , '百洋科技园') !== false
                                or strpos($v['address'] , '百洋健康科技园') !== false
                            )
                        )
                    ) {
                        $consigneeList['oto'][] = $v;
                    } else {
                        $consigneeList['other'][] = $v;
                    }
                } else {
                    $consigneeList['other'][] = $v;
                }

            }
        }
        unset($rangeRs);


        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['current_id' => $consigneeId, 'consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeList' => $consigneeList]);
    }

    /**获取o2o 的配送范围
     * @return array 返回一维数组
     *      county  配送的区域 , 如市北区, 市南区 等
     */
    public function getOtoDeliveryArea()
    {

        $rangeRs = BaiyangO2oData::getInstance()->getO2ORegionAll();
        $range = [];
        if ($rangeRs and is_array($rangeRs)) {
            $range = array_column($rangeRs, 'county');
        }
        return $range;
    }


    /**
     * @desc 获取收货地址信息
     * @param array $param
     *      -int user_id 用户id
     *      -int consignee_id 收货地址主键id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getConsigneeInfo($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['consignee_id'] = isset($param['consignee_id']) ? (int)$param['consignee_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        $channelSubid = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $udid = isset($param['udid']) ? (string)$param['udid'] : '';
        if (empty($param['user_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        if (empty($param['consignee_id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 收货地址信息
        $consigneeInfo = $userConsigneeData->getConsigneeInfo([
            'column' => '*',
            'where' => 'id = :id: and user_id = :user_id:',
            'bind' => [
                'id' => $param['consignee_id'],
                'user_id' => $param['user_id'],
            ],
        ]);
        if (empty($consigneeInfo)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        // 所有省份
        $provinceList = $userConsigneeData->getRegionList(1);
        // 用户收货地址数量
        $consigneeNumber = $userConsigneeData->getConsigneeNumber($param['user_id']);
        // 配置表的用户收货信息最大值
        $configNumber = $this->func->getConfigValue(BaiyangConfigEnum::CONSIGNEE_NUM);
        $remainNumber = ($configNumber - $consigneeNumber > 0) ? $configNumber - $consigneeNumber : 0;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeInfo' => $consigneeInfo, 'provinceList' => $provinceList]);
    }

    /**
     * @desc 获取下级地区
     * @param array $param
     *      -int pid 地区表父id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getChildZone($param)
    {
        // 格式化参数
        $pid = isset($param['pid']) ? (int)$param['pid'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        $channelSubid = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $udid = isset($param['udid']) ? (string)$param['udid'] : '';
        if (empty($pid) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 下级地区信息
        $data = BaiyangUserConsigneeData::getInstance()->getRegionList($pid);
        if (empty($data)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * @desc 删除收货地址
     * @param array $param
     *      -int user_id 用户id
     *      -int consignee_id 收货地址主键id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function deleteConsignee($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['consignee_id'] = isset($param['consignee_id']) ? (int)$param['consignee_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        $channelSubid = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $udid = isset($param['udid']) ? (string)$param['udid'] : '';
        if (empty($param['user_id']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $baseData = BaseData::getInstance();
        // 删除收货地址
        $delResult = $baseData->deleteData([
            'table' => '\Shop\Models\BaiyangUserConsignee',
            'where' => 'where id = :id: and user_id = :user_id:',
            'bind' => [
                'id' => $param['consignee_id'],
                'user_id' => $param['user_id'],
            ],
        ]);
        if (!$delResult) {
            return $this->uniteReturnResult(HttpStatus::DELETE_ERROR);
        }
        // 收货地址信息
        $consigneeList_rs = $userConsigneeData->getUserConsigneeList($param['user_id']);


        //获取o2o 的配送范围
        $range = $this->getOtoDeliveryArea();
        $consigneeList = [
            'oto' => [],
            'other' => []
        ];

        if ($consigneeList_rs and is_array($consigneeList_rs)) {
            foreach ($consigneeList_rs as $k => $v) {
                if ($range) {
                    if (in_array($v['county'], $range)
                        and
                        !(
                            $v['city'] == 284
                            and
                            (
                                strpos($v['address'] , '开封路88号') !== false
                                or strpos($v['address'] , '百洋科技园') !== false
                                or strpos($v['address'] , '百洋健康科技园') !== false
                            )
                        )
                    ) {
                        $consigneeList['oto'][] = $v;
                    } else {
                        $consigneeList['other'][] = $v;
                    }
                } else {
                    $consigneeList['other'][] = $v;
                }

            }
        }
        unset($rangeRs);


        // 用户收货地址数量
        $consigneeNumber = $userConsigneeData->getConsigneeNumber($param['user_id']);
        // 配置表的用户收货信息最大值
        $configNumber = $this->func->getConfigValue(BaiyangConfigEnum::CONSIGNEE_NUM);
        $remainNumber = ($configNumber - $consigneeNumber > 0) ? $configNumber - $consigneeNumber : 0;
        if (empty($consigneeList)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, ['consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeList' => []]);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['consigneeNumber' => $consigneeNumber, 'remainNumber' => $remainNumber, 'consigneeList' => $consigneeList]);
    }

    /**
     * @desc 获取所有地区列表
     * @param array $param
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getRegionList($param)
    {
        // 格式化参数
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        $channelSubid = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $udid = isset($param['udid']) ? (string)$param['udid'] : '';
        if (!$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        set_time_limit(600);

        // 存入redis
        $redis = $this->cache;
        $redis->selectDb(2);
        $allRegionList = $redis->getValue('region_list');
        $regionKeysList = $redis->getValue('region_keys_list');
        if (empty($allRegionList) || empty($regionKeysList)) {
            // 所有省市区信息
            $regionList = BaiyangUserConsigneeData::getInstance()->getAllRegionList();
            $proList = $cityList = $allRegionList = $regionKeysList = [];
            if (!empty($regionList)) {
                // 处理省市区
                foreach ($regionList as $key => $item) {
                    if ($item['pid'] == 1) {
                        $proList[$item['id']] = [
                            'id' => $item['id'],
                            'name' => $item['region_name'],
                        ];
                    } else {
                        $cityList[$item['pid']][] = [
                            'id' => $item['id'],
                            'name' => $item['region_name']
                        ];
                    }
                    unset($regionList);
                }
                if ($proList && $cityList) {
                    $s_k = 0;
                    foreach ($proList as $item) {
                        if (isset($cityList[$item['id']])) {
                            $c_k = 0;
                            foreach ($cityList[$item['id']] as $city) {
                                if (isset($cityList[$city['id']])) {
                                    $item['city_list'][$c_k] = $city;
                                    foreach ($cityList[$city['id']] as $key => $qu) {
                                        $item['city_list'][$c_k]['district_list'][] = $qu;
                                        $regionKeysList[$item['id']][$city['id']][$qu['id']] = "{$s_k},{$c_k},{$key}";
                                    }
                                    $c_k++;
                                }
                            }
                            $s_k++;
                            $allRegionList[] = $item;
                        }
                    }
                }
            }
            $redis->setValue('region_list', $allRegionList);
            $redis->setValue('region_keys_list', $regionKeysList);
        }
        if (empty($allRegionList) || empty($regionKeysList)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, ['region_list' => [], 'region_keys_list' => []]);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['region_list' => $allRegionList, 'region_keys_list' => $regionKeysList]);
    }

}