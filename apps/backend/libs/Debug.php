<?php

class Debug {

    /**
     * 打印变量
     * @param mixed $arr 变量
     * @param string $exit 是否停止当前页面执行
     */
    public static function pr($arr, $exit = TRUE) {
        echo '<pre>';
        print_r(is_array($arr) ? $arr : ['Not_Array' => $arr]);
        echo '</pre>';
        if ($exit) {
            exit;
        }
    }

    /**
     * 
     * @param array $arr
     * @param string $exit
     */
    public static function dump($arr, $exit = TRUE) {
        echo '<pre>';
        var_dump($arr);
        echo '</pre>';
        if ($exit) {
            exit;
        }
    }

}
