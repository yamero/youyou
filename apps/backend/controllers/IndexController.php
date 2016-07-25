<?php

use \Phalcon\Text;

/**
 * 首页 - 栏目
 */
class IndexController extends ControllerBase {

    protected $layout = '';

    /**
     * 语言选择
     */
    public function indexAction() {
        if(!$this->session->has('uname')){
            $this->response->redirect('login/index');
        }
    }

    public function showcodeAction(){
        $code=new ValidateCode();
        $code->config('90','40');
        $code->create();
    }
    /**
     * 主界面
     */
    public function mainAction() {
        echo "主界面";
    }

    /**
     * 注册
     */
    public function regAction() {
        
    }

    /**
     * 忘记密码
     */
    public function lostAction() {
        
    }

    /**
     * 注销登录
     */
    public function logoutAction() {
        $this->session->destroy();
        header('Location:/');
    }

}
