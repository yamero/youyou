<?php

use \Phalcon\Text;

/**
 * 首页 - 栏目
 */
class IndexController extends ControllerBase {



    protected $layout = 'default';

    /**
     * 登录
     */
    public function indexAction($linkid) {
        if($this->request->isPost()){
            $data=$this->request->getPost();
            $code=strtolower($this->session->get('code'));
            $data['code']=strtolower($data['code']);
            if(empty($data['name'])||empty($data['password'])){
                return "<script>alert('请将信息填写完整！');location='/';</script>";
            }
            if($code!=$data['code']){
                return "<script>alert('验证码输入不有误，请重新输入！');location='/';</script>";
            }
            $conditions='account=?1 and password=?2';
            $password=sha1($data['password']);
            $params=array(1=>$data['name'],2=>$password);
            $user=User::findFirst(array($conditions,'bind'=>$params));
            if($user->id){
                $datestr=time();
                $ip=$_SERVER['REMOTE_ADDR'];
                if($user->save(array('login_time'=>$datestr,'ip'=>$ip))){
                    $this->session->set('uid',$user->id);
                    $this->session->set('uname',$data['name']);
                    $this->session->set('utype',$user->type);
                    $this->session->set('uidentity',$user->identity);
                    $this->session->set('ip',$user->ip);
                    $this->session->set('login_time',$user->login_time);
                    $this->view->setVar('user',$user);
                    return "<script>alert('登录成功！');location='/';</script>";
                }else{
                    return "<script>alert('登录失败，请稍候重试！');location='/';</script>";
                }
            }else{
                return "<script>alert('您输入的用户名或密码有误，请重新输入！');location='/';</script>";
            }
        }else{
            $linkid=preg_replace("/\s/","",$linkid);
            $condition="promote='".$linkid."'";
            $promoteUser=User::findFirst(array($condition));
            if($promoteUser->id){
                $this->session->set('puid',$promoteUser->id);
            }
            if($this->session->has('uid')){
                $user=User::findFirst($this->session->get('uid'));
                $this->view->setVar('user',$user);
            }
        }
    }



    /**
     * 注册
     */
    public function regAction() {

    }

    /**
     * 忘记密码
     */
    public function lostpasswordAction() {

    }
    public function confirmquestionAction(){
        $account=$this->request->getPost('account');
        $condition="account=?1";
        $params=array(1=>$account);
        $user=User::findFirst(array($condition,"bind"=>$params));
        $this->view->setVars(array(
            'user'=>$user,
            'account'=>$account
        ));
    }
    public function findpasswordAction(){
        $data=$this->request->getPost();
        $condition="account=?1 and answer1=?2 and answer2=?3";
        $params=array(1=>$data['account'],2=>$data['answer1'],3=>$data['answer2']);
        $user=User::findFirst(array($condition,"bind"=>$params));
        if($user->id){
            $this->view->setVars(array(
                'account'=>$data['account'],
                'answer1'=>$data['answer1'],
                'answer2'=>$data['answer2']
            ));
        }else{
            return "<script>alert('答案输入有误，请重新输入！');history.go(-1);</script>";
        }
    }
    public function changepasswordAction(){
        $data=$this->request->getPost();
        if(!preg_match('/^[\w]{6,32}$/',$data['password'])){
            return "<script>alert('密码格式有误，请重新输入！');history.go(-1);</script>";
        }
        if($data['password']!=$data['repassword']){
            return "<script>alert('两次密码输入不一致，请重新输入！');history.go(-1);</script>";
        }
        $data['password']=sha1($data['password']);
        $condition="account=?1 and answer1=?2 and answer2=?3";
        $params=array(1=>$data['account'],2=>$data['answer1'],3=>$data['answer2']);
        $user=User::findFirst(array($condition,"bind"=>$params));
        if($user->id){
            if($user->save(array("password"=>$data['password']))){
                return "<script>alert('密码修改成功，请登录！');location='/';</script>";
            }else{
                return "<script>alert('密码修改失败，请稍候重试！');history.go(-1);</script>";
            }
        }else{
            return "<script>alert('操作出错，请稍候重试！');history.go(-1);</script>";
        }
    }
    /**
     * 注销登录
     */
    public function logoutAction() {
        $this->session->destroy();
        header('Location: /');
    }

}
