<?php

class Cache {

    /**
     * 缓存过的类对象
     *
     * @var object
     */
    public static $classes;

    /**
     * 缓存对象
     * @var object
     */
    public static $cache = NULL;

    /**
     * 得到类型对象/单例模式
     *
     * @param string $className 类名称
     * @return object
     */
    public static function getClass($className) {
        if (!self::$classes[$className]) {
            self::$classes[$className] = new $className();
        }
        return self::$classes[$className];
    }

    /**
     * 初始化缓存
     */
    public static function init() {
        if (!self::$cache) {
            $redis = new \Phalcon\Config(include (APP_DIR . '/config/redis.php'));
            self::$cache = new Redis();
            self::$cache->connect($redis->host, $redis->port);
            self::$cache->auth($redis->auth);
        }
    }

    /**
     * 关闭缓存
     */
    public static function closeCache() {
        if (self::$cache !== NULL) {
            self::$cache->close();
            self::$cache = NULL;
        }
    }

    /**
     * 获取缓存
     * 
     * @param string $key 键名
     * @return mixed
     */
    public static function get($key) {
        return unserialize(self::$cache->get($key));
    }

    /**
     * 保存缓存
     * 
     * @param string $key 键
     * @param mixed $val 值
     * @param int $timeout
     */
    public static function set($key, $val, $timeout = 0) {
        if ($timeout > 0) {
            self::$cache->setex($key, $timeout, serialize($val));
        } else {
            self::$cache->set($key, serialize($val));
        }
    }

    /**
     * 清理所有缓存
     *
     * @return null
     */
    public static function clear() {
        if (self::$cache !== NULL) {
            self::$cache->flushAll();
        }
    }

}
