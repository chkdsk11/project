<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\Event;



include __DIR__ . '/../../../../common/config/services.php';

/**
 * 注入session，使用phalcon自带redis做session的容器，session默认使用redis的0库进行存储
 */
$di->setShared('session', function (){
    $config=$this->getConfig();
    $sessionConfig = array(
        'host' => $config->redis->baiyang->host,
        'port' => $config->redis->baiyang->port,
        'persistent' => false,
        'lifetime' => 86400,
        'prefix' =>'admin_'
    );
    if($config->redis->baiyang->auth){
        $sessionConfig['auth'] = $config->redis->baiyang->auth;
    }
    $session = new \Phalcon\Session\Adapter\Redis($sessionConfig);
    session_set_cookie_params(0,'/',$config->cookie->domain,false,1);
    session_name('gz_baiyang');
    $session->start();
    return $session;
});

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include __DIR__ . '/config.php';
});

$di->setShared('routerfilter', function () {
    return include __DIR__ . '/router_filter.php';
});

//注册默认命名空间
$di->set('dispatcher', function() {
    // 创建一个事件管理
    $eventsManager = new EventsManager();

    // 异常处理事件，控制器与方法没找到时触发该事件
    $eventsManager->attach(
        "dispatch:beforeException",
        function (Event $event, $dispatcher, Exception $exception) {
            // 处理404异常
            if ($exception instanceof DispatchException) {
                $dispatcher->forward(
                    [
                        "controller" => "errors",
                        "action"     => "show404",
                    ]
                );
                return false;
            }
            // 代替控制器或者动作不存在时的路径
            switch ($exception->getCode()) {
                case MvcDispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case MvcDispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(
                        [
                            "controller" => "errors",
                            "action"     => "show404",
                        ]
                    );
                    return false;
            }
        }
    );

    //日志处理事件
    $modelManager=new Manager();
    $eventsManager->attach( "dispatch:beforeDispatch",function(Event $event, $dispatcher)use($modelManager){

    });

    $dispatcher = new MvcDispatcher();
    $dispatcher->setDefaultNamespace('Shop\Admin\Controllers');
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});

/**
 * modelsManager管理
 */
$di->set('modelsManager',function(){
    $modelsManager=new Manager();
    return $modelsManager;
});

$di->set('collectionManager',function (){
    $collectionManager = new Phalcon\Mvc\Collection\Manager();
    return $collectionManager;
});
/**
 * Shared loader service
 */
$di->setShared('loader', function () {
    $config = $this->getConfig();
    /**
     * Include Autoloader
     */
    include __DIR__ . '/loader.php';
    return $loader;
});

/**
 * Shared loader service
 */
$di->setShared('csrf', function () {
    $csrf=new Shop\Libs\CheckSecurity();
    return $csrf;
});