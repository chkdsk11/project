<?php
/**
 * @author 邓永军
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


class BaiyangOrderOperationLogData extends BaseData
{
    protected static $instance=null;

    
    
}