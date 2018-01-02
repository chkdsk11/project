<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Hprose\ResultMode;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangAdmin;
use Shop\Models\BaiyangAdminAuthGroupAccess;
use Shop\Models\BaiyangAdministratorLog;
use Shop\Models\BaiyangSite;
use Phalcon\Paginator\Adapter\Model as PagerModel;
use Shop\Models\CacheKey;
use Shop\Models\BaseModel;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class BaiyangFrontCateData extends BaseData
{
    protected static $instance=null;
	
	public function getCateNums ()
	{
		$phql  = "SELECT b.category_id,COUNT(1) as nums FROM \Shop\Models\BaiyangGoods as a LEFT JOIN \Shop\Models\BaiyangSpu as b on a.spu_id = b.spu_id
				WHERE b.category_id <> '' GROUP BY a.spu_id";
		$result = $this->modelsManager->executeQuery($phql);

		if(count($result) > 0){
			$result = $result->toArray();
			return $result;
		}
		return false;
	}
}