<?php
    class LoginController extends \Phalcon\Mvc\Controller{
        public function indexAction(){

        }
        public function dologinAction(){
            if($this->request->isPost()==true){
                if($this->request->isAjax()==true){
                    $name=$this->request->getPost('name');
                    $pwd=sha1($this->request->getPost('pwd'));
                    $code=strtolower($this->request->getPost('code'));
                    $sessioncode=strtolower($this->session->get('captcha_code'));
                    $conditions="name=?1 and password=?2";
                    $parameters=array(1=>$name,2=>$pwd);
                    $member=Member::findFirst(array(
                        $conditions,
                        "bind"=>$parameters
                    ));
                    if($code==$sessioncode&&$member->id){
                        $this->session->set('uid',$member->id);
                        $this->session->set('uname',$member->name);
                        echo "ok";
                    }else{
                        echo "notok";
                    }
                }
            }
        }
    }
?>