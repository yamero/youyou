<?php

/**
 * 打印变量
 * @param mixed $arr 变量
 * @param string $exit 是否停止当前页面执行
 */
function pr($arr, $exit = TRUE) {
    echo '<pre>';
    print_r(is_array($arr) ? $arr : ['Not_Array' => $arr]);
    echo '</pre>';
    if ($exit) {
        exit;
    }
}

/*
 *根据腾讯IP分享计划的地址获取IP所在地，比较精确
 */
function getIPLoc_QQ($queryIP){
    $url = 'http://ip.qq.com/cgi-bin/searchip?searchip1='.$queryIP;
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_ENCODING ,'gb2312');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
    $result = curl_exec($ch);
    $result = mb_convert_encoding($result, "utf-8", "gb2312"); // 编码转换，否则乱码
    curl_close($ch);
    preg_match("@<span>(.*)</span></p>@iU",$result,$ipArray);
    $loc = $ipArray[1];
    return $loc;
}

/**
 * 
 * @param array $arr
 * @param string $exit
 */
function dump($arr, $exit = TRUE) {
    echo '<pre>';
    var_dump($arr);
    echo '</pre>';
    if ($exit) {
        exit;
    }
}

/**
 * 依据年月日创建文件目录
 * 
 * @param string $dir 在哪个目录下创建目录
 */
function mk_date_dirs($dir = UPLOAD_DIR) {
    if (!is_dir($dir)) {
        echo '要上传文件的路径, 不存在此目录!';
        return;
    }

    $mkdir = function($year, $month, $day) use ($dir) {
        $yearDir = $dir . '/' . $year;
        if (!is_dir($yearDir)) {
            mkdir($yearDir);
        }
        $monthDir = $yearDir . '/' . $month;
        if (!is_dir($monthDir)) {
            mkdir($monthDir);
        }
        $dayDir = $monthDir . '/' . $day;
        if (!is_dir($dayDir)) {
            mkdir($dayDir);
        }
    };

    $date = explode('-', date('Y-m-d-H-i')); //取当前的
    $mkdir($date[0], $date[1], $date[2]);

    $hour = intval($date[3]);
    $minute = intval($date[4]);
    if ($hour == 23 && $minute >= 55) { //如果是23时, 59分, 则创建第二天的目录
        $date = explode('-', date('Y-m-d-H-i', strtotime('+1 day')));
        $mkdir($date[0], $date[1], $date[2]);
    }
}

/**
 * 简单实现的写日志功能
 * 
 * @param string $str 日志内容
 * @param string $level 日志级别: GENERAL, WARNING, ERROR, HALT
 */
function L($str, $level = 'GENERAL', $cat = NULL) {
    file_put_contents(LOG_DIR . '/' . APP_NAME . '/log_' . date('Y-m-d') . ($cat ? '_' . $cat : '') . '.log', '[' . $level . '][' . date('H:i:s') . '] ' . $str . "\n", FILE_APPEND);
}

/**
 * 模拟 post 登录
 * 
 * @param string $url 登录地址
 * @param string $cookie 将cookie 保存到指定的文件中
 * @param array $post 提交的数组
 */
function http_login($url, $cookie, $post) {
    $curl = curl_init(); //初始化curl模块 
    curl_setopt($curl, CURLOPT_URL, $url); //登录提交的地址 
    curl_setopt($curl, CURLOPT_HEADER, 0); //是否显示头信息 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0); //是否自动显示返回的信息 
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中 
    curl_setopt($curl, CURLOPT_POST, 1); //post方式提交 
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); //要提交的信息 
    curl_exec($curl); //执行cURL 
    curl_close($curl); //关闭cURL资源，并且释放系统资源 
}

/**
 * 获取登录后的内容
 * 
 * @param string $url
 * @param string $cookie
 * @return string
 */
function http_get_content($url, $cookie) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie 
    $rs = curl_exec($ch); //执行cURL抓取页面内容 
    curl_close($ch);
    return $rs;
}

/**
 * 获取页码数
 * 
 * @param int $cur 当前页
 * @param int $max 最大页
 * @return array
 */
function get_pages($cur, $max) {
    if ($max <= 10) {
        return range(1, $max);
    }
    if ($cur == 1) {
        return range(1, ($max >= 10 ? 10 : $max));
    }
    if ($cur == $max) {
        return range(($max >= 10 ? $max - 10 : 1), $max);
    }
    if ($cur >= $max - 5) {
        return range($cur, $max);
    }
    $sArr = [];
    if ($cur >= 10) {
        $sArr[] = 1;
        $sArr[] = 2;
        $sArr[] = 3;
    }
    $lArr = [];
    for ($i = $cur, $j = 7; $i >= 1 && $j >= 1; $i--, $j--) {
        $lArr[] = $i;
    }
    sort($lArr);
    $arr = array_merge($sArr, $lArr);

    for ($i = $cur + 1, $j = 1; $i <= $max && $j <= 6; $i++, $j++) {
        $arr[] = $i;
    }
    if ($cur < $max - 10) {
        $arr[] = $max - 2;
        $arr[] = $max - 1;
        $arr[] = $max;
    }

    return $arr;
}

/**
 * 获取用户头像, 如果不存在, 刚取默认值
 * 
 * @param object $user
 * @return string
 */
/*
  function photo($user) {
  if ($user->id && !$user->photo) {
  $idx = $user->id % 34;
  return '/face/' . $idx . '.' . [
  0 => 'png', 1 => 'jpg', 2 => 'gif', 3 => 'png', 4 => 'png', 5 => 'png', 6 => 'jpg', 7 => 'gif', 8 => 'png', 9 => 'png',
  10 => 'png', 11 => 'png', 12 => 'png', 13 => 'png', 14 => 'jpg', 15 => 'png', 16 => 'png', 17 => 'png', 18 => 'png', 19 => 'gif',
  20 => 'png', 21 => 'png', 22 => 'png', 23 => 'jpeg', 24 => 'png', 25 => 'gif', 26 => 'gif', 27 => 'gif', 28 => 'gif', 29 => 'png',
  30 => 'jpeg', 31 => 'png', 32 => 'png', 33 => 'jpg'
  ][$idx];
  }
  return $user->photo;
  }
 */

/**
 * 对金额进行着色
 * 
 * @param float $amount
 * @return string
 */
function moneyColor($amount) {
    if ($amount > 0) {
        return '<strong style="color: green">+ ' . number_format($amount, 2) . '</strong>';
    } else if ($amount < 0) {
        return '<strong style="color: red">- ' . number_format($amount, 2) . '</strong>';
    } else {
        return '<strong>' . number_format($amount, 2) . '</strong>';
    }
}

function url($controller, $action) {
    return '/' . $controller . ($action == 'index' ? '' : '/' . $action);
}

/**
 * 调用文件上传方法
 * @return [type] [description]
 */
function upAction(){
            // 定义上传路径
            $file_savepath = '../uploads/';
            // 定义上传文件的大小
            $file_size = 2 * 1024 * 1024;
            // 定义上传文件的允许类型
            $file_type = array('jpg','png','gif');
            // 上传配置
            $upload = new Upload($this->request , $file_savepath  , $file_size , $file_type);
            // 开始上传
            $upload->uploadfile();
            // 判断上传状态  true标识没有上传成功  false 标识上传成功
            if(!$upload->errState()){
                // 返回文件保存真实路径
                return array(1 , $upload->getFileRealPath());
            }else{
                // 打印错误信息
                return array(0 , $upload->errInfo());

            }

        }