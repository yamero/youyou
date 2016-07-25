<?php

/**
 * 前台基类，所有具有实体功能的控制器都继承此类
 */
class ControllerBase extends \Phalcon\Mvc\Controller {
    
     

    /**
     * 页面标题
     * 
     * @var string
     */
    protected $pageTitle = '右右网';

    /**
     * 布局
     * 
     * @var string
     */
    protected $layout = 'default';

    /**
     * 控制器名称
     * 
     * @var string
     */
    protected $controllerName = NULL;

    /**
     * 方法名称
     * 
     * @var string
     */
    protected $actionName = NULL;

    /**
     * 输出 json
     *
     * @param number $code 错误代码, 如果如错, 必须提供错误代码
     * @param string $msg 错误信息
     */
    protected function echoJson($code = '0', $msg = '操作成功') {
        header('Content-Type: application/json');
        $ret = [];
        if (is_array($code) && $code) {
            $sCode = isset($code['code']) ? $code['code'] : $code[0];
            $sMsg = isset($code['message']) ? $code['message'] : (count($code) >= 2 ? $code[1] : $msg);
            $ret = ['code' => $sCode, 'message' => $sMsg];
            if (count($code) > 2) {
                $ret = array_merge($ret, array_slice($code, 2));
            }
        } elseif (is_string($code)) {
            $ret = ['code' => 0, 'message' => $code];
        } else {
            $ret = ['code' => $code, 'message' => $msg];
        }
        if (isset($ret['code']) && $ret['code'] == 0) {
            $ret['url'] = '/' . $this->controllerName;
        }
        echo json_encode($ret);
        exit;
    }

    /**
     * 得到相应的模型
     *
     * @param string $modelName
     * @return object
     */
    protected function getModel($modelName = NULL) {
        if ($modelName == NULL) {
            $modelName = trim(str_replace('Controller', '', get_called_class()));
        }
        return Cache::getClass($modelName);
    }

    //18位身份证号码合法性验证函数
    protected function check_identity($id=''){
        $set = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        $ver = array('1','0','x','9','8','7','6','5','4','3','2');
        $arr = str_split($id);
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            if (!is_numeric($arr[$i])) {
                return false;
            }
            $sum += $arr[$i] * $set[$i];
        }
        $mod = $sum % 11;
        if (strcasecmp($ver[$mod],$arr[17]) != 0) {
            return false;
        }
        return true;
    }
    /**
     * 得到当前 session 对象
     *
     * @return object
     */
    protected function getSession() {
        return $this->session->get(SESS_USER);
    }
    
    //@see Controller::afterExecuteRoute()
    public function afterExecuteRoute($dispatcher) {
        if ($this->request->isGet()) {
            $this->view->setVars([
                'STATIC_URL' => STATIC_URL,
                'controllerName' => $this->controllerName,
                'actionName' => $this->actionName,
                'TITLE' => $this->pageTitle
            ]);
        }
        $this->view->setMainView('_layouts/' . $this->layout);
    }

}
