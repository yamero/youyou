<?php

class Conf {

    /**
     * 得到自动注册类路径
     * 
     * @return array
     */
    private static function getRegisterDirs() {
        return [
            APP_DIR . '/controllers/',
            APP_DIR . '/models/',
            //APP_DIR . '/rows/',
            APP_DIR . '/validations/',
            LIB_DIR,
            APP_DIR . '/traits/'
        ];
    }

    /**
     * 得到依赖注入
     *
     * @return function
     */
    private static function getDI() {
        $di = new \Phalcon\DI\FactoryDefault();
        $di->set('view', self::getViewService());
        $di->set('db', self::getDbServices());
        $di->set('flash',self::getFlash());
        $di->setShared('session', self::getSessionService());
        $di->setShared('transactions', self::getTransactionService());
        return $di;
    }

    private static function getFlash(){
        return function(){
            return new \Phalcon\Flash\Direct();
        };
    }

    /**
     * 视图服务
     *
     * @return function
     */
    private static function getViewService() {
        return function() { //视图 
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir(APP_DIR . '/views');
            // $view->registerEngines(array(
            //     ".phtml" => 'Phalcon\Mvc\View\Engine\Volt'
            // ));
            return $view;
        };
    }

    /**
     * 数据库服务
     *
     * @return function
     */
    private static function getDbServices() {
        return function() { //数据库
            $db = new \Phalcon\Config(include(APP_DIR . '/config/db.php'));
            return new \Phalcon\Db\Adapter\Pdo\Mysql([
                'host' => $db->host,
                'username' => $db->user,
                'password' => $db->password,
                'dbname' => $db->name,
                'charset' => 'utf8'
            ]);
        };
    }

    /**
     * session服务
     *
     * @return function
     */
    private static function getSessionService() {
        return function() {
            $session = new \Phalcon\Session\Adapter\Files();
            if (!isset($_SESSION)) {
                $session->start();
            }
            return $session;
        };
    }

    /**
     * 事务处理服务
     *
     * @return function
     */
    private static function getTransactionService() {
        return function() {
            return new \Phalcon\Mvc\Model\Transaction\Manager();
        };
    }

    /**
     * 运行主程序
     */
    public static function runMvc() {
        try {
            (new \Phalcon\Loader())->registerDirs(self::getRegisterDirs())->register();
            echo @(new \Phalcon\Mvc\Application(self::getDI()))->handle()->getContent();
        } catch (Phalcon\Exception $e) { //出错处理
            die($e->getMessage());
            if (DEBUG_MODE) {
                die('Error: ' . $e->getMessage());
            }
            die('Error!');
        }
    }

}
