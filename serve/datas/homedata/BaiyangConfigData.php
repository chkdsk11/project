<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 9:01
 */

namespace Shop\Home\Datas;

use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Shop\Models\BaiyangConfig;

/**
 * 拼团方法集合
 * @package Shop\Home\Datas
 */
class BaiyangConfigData extends BaseData
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
    public function getOne($config_sign)
    {

        $result = BaiyangConfig::findFirst([
            'config_sign = :config_sign:',
            'bind' => [
                'config_sign' => $config_sign
            ]
        ]);

        if(empty($result)){
            return 0;
        }
        return $result->toArray()['config_value'];

    }


}

























