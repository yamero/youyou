<?php
class RegisterController extends ControllerBase{
    protected $layout='';
    //注册实名认证
    public function nextregisteAction(){
        $this->layout='default';
        $data=$this->request->getPost();
        if($data['question1']==0||$data['question2']==0){
            return "<script>alert('请选择密保问题！');location='/index/reg';</script>";
        }
        if($data['question1']!=0){
            $data['answer1']=preg_replace('/\s/','',$data['answer1']);
            if(empty($data['answer1'])){
                return "<script>alert('请输入第一问题答案！');location='/index/reg';</script>";
            }
        }
        if($data['question2']!=0){
            $data['answer2']=preg_replace('/\s/','',$data['answer2']);
            if(empty($data['answer2'])){
                return "<script>alert('请输入第二问题答案！');location='/index/reg';</script>";
            }
        }
        $data['password']=sha1($data['password']);
        $province=China::find(array("Pid=0 and Id!=0"));
        $this->view->setVars(array(
            'data'=>$data,
            'province'=>$province
        ));
    }
    //ajax无刷上传图片
    public function uploadthumbAction(){
        $this->layout='';
        if($this->request->hasFiles()){
            $datestr=date('Ymd',time());
            $path='../uploads/'.$datestr.'/';
            if(!file_exists($path)){
                mkdir($path);
            }
            $config=Config::findFirst();
            foreach ($this->request->getUploadedFiles() as $file){
                if($file->getName()){
                    $type=strtolower($file->getExtension());
                    $size=$file->getSize();
                    $typearr=array('jpg','png','gif');
                    if(!in_array($type,$typearr)){//若图片不是jpg，png或gif格式，则禁止上传
                        return json_encode(array('status'=>0,'info'=>'无法上传此类型的图片！'));
                    }
                    if($size>2*1024*1024){//若图片大小大于2MB，禁止上传
                        return json_encode(array('status'=>0,'info'=>'您上传的图片过大！'));
                    }
                    $imgUrl=date('YmdHis',time()).uniqid().'.'.$type;
                    $file->moveTo($path.$imgUrl);
                    $imgName=$config->img_domain.$datestr.'/'.$imgUrl;
                }
            }
            return json_encode(array('status'=>1,'info'=>$imgName));
        }else{
            return json_encode(array('status'=>0,'info'=>'您还没有选择图片！'));
        }
    }
    //根据传入的省id获取市
    public function getcityAction(){
        $this->layout='';
        if($this->request->isPost()){
            $str="<option value='0' selected='selected'>--请选择--</option>";
            $id=$this->request->getPost('id');
            if($id){
                if($id==110000||$id==120000||$id==310000||$id==500000){
                    $condition="Id=".$id;
                }else{
                    $condition="Pid=".$id;
                }
                $city=China::find(array($condition));
                foreach($city as $c){
                    $str.="<option value='".$c->Id."'>".$c->Name."</option>";
                }
                return $str;
            }else{
                return '';
            }
        }
    }
    //根据传入的市id获取区域
    public function getareaAction(){
        $this->layout='';
        if($this->request->isPost()){
            $str="<option value='0' selected='selected'>--请选择--</option>";
            $id=$this->request->getPost('id');
            if($id){
                $condition="Pid=".$id;
                $area=China::find(array($condition));
                if($area->count()>0){
                    foreach($area as $a){
                        $str.="<option value='".$a->Id."'>".$a->Name."</option>";
                    }
                }else{
                    $area=China::findFirst($id);
                    $str="<option value='".$area->Id."'>".$area->Name."</option>";
                }
                return $str;
            }else{
                return '';
            }
        }
    }
    //接收用户输入，进行会员注册
    public function doregisteAction(){
        if($this->request->isPost()){
            $data=$this->request->getPost();
            $data['alipay']=preg_replace('/\s/','',$data['alipay']);
            $data['tenpay']=preg_replace('/\s/','',$data['tenpay']);
            if(!preg_match('/^[\x{4e00}-\x{9fa5}]{1,10}$/u',$data['real_name'])){
                return "<script>alert('请输入正确的姓名！');history.go(-1);</script>";
            }
            if(!$this->check_identity($data['idcard_number'])){
                return "<script>alert('请输入正确的身份证号码！');history.go(-1);</script>";
            }
            if(!preg_match('/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]{3,50}$/u',$data['address'])){
                return "<script>alert('请输入正确的联系地址！');history.go(-1);</script>";
            }
            /*
            if(empty($data['alipay'])||empty($data['tenpay'])||empty($data['shop_pic'])||empty($data['idcard_back'])||empty($data['idcard_front'])||empty($data['gesture'])||empty($data['idcard_gesture'])||$data['province']==0||$data['city']==0||$data['area']==0){
                return "<script>alert('请将信息填写完整！');history.go(-1);</script>";
            }*/
            $data['reg_time']=time();
            $data['promote']=substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
            if($this->session->has('puid')){
                $data['master_id']=$this->session->get('puid');
            }
            $this->db->begin();
            $user=new User();
            //注册
            if(!$user->save($data)){
                $this->db->rollback();
                return "<script>alert('注册失败，请稍候重试！');history.go(-1);</script>";
            }
            //当会员注册成功后，若有推荐人，则对推荐人进行金币奖励
            if($this->session->has('puid')){
                $condition="id=".$this->session->get('puid');
                $promoteUser=User::findFirst(array($condition));
                $gcoin=$promoteUser->gcoin + 50;
                if(!$promoteUser->save(array("gcoin"=>$gcoin))){
                    $this->db->rollback();
                    return "<script>alert('注册失败，请稍候重试！');history.go(-1);</script>";
                }
                $this->session->remove('puid');
            }
            //记录IP以及登录时间
            $datestr=time();
            $ip=$_SERVER['REMOTE_ADDR'];
            $user=User::findFirst(array("order"=>"id desc"));
            if(!$user->save(array('login_time'=>$datestr,'ip'=>$ip))){
                $this->db->rollback();
                return "<script>alert('注册失败，请稍候重试！');history.go(-1);</script>";
            }
            $this->session->set('uid',$user->id);
            $this->session->set('uname',$user->account);
            $this->session->set('utype',$user->type);
            $this->session->set('uidentity',$user->identity);
            $this->session->set('ip',$ip);
            $this->session->set('login_time',$datestr);
            $this->db->commit();
            return "<script>alert('注册成功！');location='/';</script>";
        }
    }
    //验证用户名
    public function checkNameAction(){
        $pattern='/^[a-zA-Z\x{4e00}-\x{9fa5}][0-9a-zA-Z\x{4e00}-\x{9fa5}_]{1,15}$/u';
        $account=$_POST["param"];
        if(preg_match($pattern,$account)){
            $conditions="account=:account:";
            $parameters=array("account"=>$account);
            $user=User::findFirst(array(
                $conditions,
                "bind"=>$parameters
            ));
            if($user->id){
                return json_encode(array(
                    "info"=>"此用户名已存在",
                    "status"=>"n"
                ));
            }else{
                return json_encode(array(
                    "info"=>"这用户名真棒",
                    "status"=>"y"
                ));
            }
        }else{
            return json_encode(array(
                "info"=>"用户名可不能这么起哦",
                "status"=>"n"
            ));
        }
    }
    //验证密码
    public function checkPasswordAction(){
        $pattern='/[\w]{6,}/';
        $password=$_POST["param"];
        if(preg_match($pattern,$password)){
            return json_encode(array(
                "info"=>"密码格式正确",
                "status"=>"y"
            ));
        }else{
            return json_encode(array(
                "info"=>"密码格式不正确",
                "status"=>"n"
            ));
        }
    }
    //验证QQ号
    public function checkQQAction(){
        $pattern='/^[1-9]*[1-9][0-9]*$/';
        $qq=$_POST["param"];
        if(preg_match($pattern,$qq)){
            return json_encode(array(
                "info"=>"QQ格式正确",
                "status"=>"y"
            ));
        }else{
            return json_encode(array(
                "info"=>"QQ格式不正确",
                "status"=>"n"
            ));
        }
    }
    //验证Email
    public function checkEmailAction(){
        $pattern="/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
        $email=$_POST["param"];
        if(preg_match($pattern,$email)){
            return json_encode(array(
                "info"=>"邮箱格式正确",
                "status"=>"y"
            ));
        }else{
            return json_encode(array(
                "info"=>"邮箱格式有问题哦",
                "status"=>"n"
            ));
        }
    }
}
?>