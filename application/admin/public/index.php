<?php
define('BASE_PATH', realpath('..'));
error_reporting(1);

if (isset($_GET['_url'])) {
    $_GET['_url'] = strtolower($_GET['_url']);
}



try {

    /**
     * Read services
     */
    include BASE_PATH . "/app/config/services.php";

    /**
     * Call the autoloader service.  We don't need to keep the results.
     */
    $di->getLoader();

    /**
     * Handle the request
     */

    $application = new \Phalcon\Mvc\Application($di);

    $config = $di->getConfig();
    if(in_array($config['environment'],['dev','stg'])  ){
        require_once '../../../vendor/autoload.php';
        $di['app'] = $application; //将应用实例保存到$di的app服务中
        #根据debugbar.php存放的路径，适当的调整引入的相对路径
        $provider = new Snowair\Debugbar\ServiceProvider(BASE_PATH.'/app/config/debugbar.php');
        $provider -> register();//注册
        $provider -> boot(); //启动
    }

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
