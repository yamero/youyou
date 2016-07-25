<?php
    class UserController extends ControllerBase{
        protected $layout='sidebar';
        //会员信息
        public function indexAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $user=User::findFirst($this->session->get('uid'));
            $this->view->setVars(array(
                'user'=>$user,
                'operation'=>'userinfo'
            ));
        }
        //无刷新上传头像
        public function changeimgAction(){
            $this->layout='';
            if($this->request->hasFiles()){
                $datestr=date('Ymd',time());
                $path='../uploads/'.$datestr.'/';
                if(!file_exists($path)){
                    mkdir($path);
                }
                $config=Config::findFirst();
                foreach($this->request->getUploadedFiles() as $file){
                    if($file->getName()){
                        $type=strtolower($file->getExtension());
                        $typearr=array('jpg','png','gif');
                        if(!in_array($type,$typearr)){
                            return;
                        }
                        $imgUrl=date('YmdHis',time()).uniqid().'.'.$file->getExtension();
                        $file->moveTo($path.$imgUrl);
                        $imgName=$config->img_domain.$datestr.'/'.$imgUrl;
                        $user=User::findFirst($this->session->get('uid'));
                        $user->save(array('portrait'=>$imgName));
                    }
                }
                echo $imgName;
            }
        }
        //快速充值
        public function rechargeAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $this->view->setVars(array(
                'operation'=>'quickrecharge'
            ));
        }
        //选择充值方式
        public function selectmethodAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $data['coin']=(int)$data['coin'];
                $config=Config::findFirst();
                $data['money']=$config->exchange * $data['coin'];//充值金币换算成人民币
                if($data['coin']<=0){
                    return "<script>alert('请输入正确的金币数！');location='/user/recharge';</script>";
                }
                $this->view->setVars(array(
                    'data'=>$data,
                    'operation'=>'quickrecharge'
                ));
            }
        }
        //根据选择的支付方式显示不同的支付页
        public function showmethodAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $quickrecharge=new QuickRecharge();
                $data['user_id']=$this->session->get('uid');
                $data['create_time']=time();
                if($quickrecharge->save($data)){
                    $this->view->setVars(array(
                        'data'=>$data,
                        'operation'=>'quickrecharge'
                    ));
                }else{
                    return "<script>alert('充值申请提交失败，请稍候重试！');location='/user/recharge';</script>";
                }
            }
        }
        //保存会员提交的支付宝交易号
        public function savetradenumAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $data['trade_num']=preg_replace('/\s/','',$data['trade_num']);
                if(empty($data['trade_num'])){
                    return "<script>alert('请输入支付宝交易号！');history.go(-1);</script>";
                }
                $condition="user_id=".$this->session->get('uid');
                $quickrecharge=QuickRecharge::findFirst(array($condition,"order"=>"create_time desc"));
                if($quickrecharge->save($data)){
                    return "<script>alert('充值申请提交成功！');location='/user/index';</script>";
                }else{
                    return "<script>alert('充值申请提交失败，请重新提交！');history.go(-1);</script>";
                }
            }
        }
        //奖励明细
        public function rewardAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $rd=RewardDetail::findFirst();
            $total_coin=$rd->register_coin + $rd->recommend_coin + $rd->release_coin + $rd->recharge_coin + $rd->activity_coin;
            $total_release=$rd->register_release + $rd->recommend_release + $rd->release_release + $rd->recharge_release + $rd->activity_release;
            $total_point=$rd->register_point + $rd->recommend_point + $rd->release_point + $rd->recharge_point + $rd->activity_point;
            $this->view->setVars(array(
                'reward'=>$rd,
                'total_coin'=>$total_coin,
                'total_release'=>$total_release,
                'total_point'=>$total_point,
                'operation'=>'rewarddetail'
            ));
        }
        //返款设置
        public function returnmoneyAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $condition="id=".$this->session->get('uid');
            $user=User::findFirst(array($condition));
            if($this->request->isPost()){
                $data=$this->request->getPost();
                if(!preg_match('/^[\x{4e00}-\x{9fa5}]{1,10}$/u',$data['alipay_name'])||!preg_match('/^[\x{4e00}-\x{9fa5}]{1,10}$/u',$data['tenpay_name'])){
                    return "<script>alert('请输入正确的姓名！');location='/user/returnmoney';</script>";
                }
                if($user->save($data)){
                    return "<script>alert('修改成功！');location='/user/returnmoney';</script>";
                }else{
                    return "<script>alert('修改失败，请稍候重试！');location='/user/returnmoney';</script>";
                }
            }
            $this->view->setVars(array(
                'user'=>$user,
                'operation'=>'returnmoney'
            ));
        }
        //银行卡认证页面显示与认证申请的提交
        public function bankcertificationAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                if(empty($data['real_name'])||empty($data['bankcard_num'])||empty($data['mobile_num'])||$data['bank_id']==0){
                    return "<script>alert('请将信息填写完整！');history.go(-1);</script>";
                }
                $bankapply=new BankApply();
                $data['serial_num']='Y'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                $data['user_id']=$this->session->get('uid');
                $data['create_time']=time();
                if(!$bankapply->save($data)){
                    return "<script>alert('请检查您输入的信息！');history.go(-1);</script>";
                }
            }
            $bank=Bank::find();
            $phql="select ba.id,ba.serial_num,ba.bankcard_num,ba.mobile_num,ba.verification,ba.create_time,ba.handle_time,ba.real_name,b.bank_name from BankApply as ba join Bank as b where ba.user_id=".$this->session->get('uid');
            $bankapply=$this->modelsManager->executeQuery($phql);
            $this->view->setVars(array(
                'bank'=>$bank,
                'bankapply'=>$bankapply,
                'operation'=>'bankcertification'
            ));
        }
        //进行银行卡认证
        public function docertificationAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $condition='user_id='.$this->session->get('uid');
                $bankapply=BankApply::findFirst(array($condition));
                if($bankapply->verification==2){
                    $this->response->redirect('/user/bankcertification');
                }
                if($bankapply->fail_time!=0){
                    $time=time();
                    if($time < $bankapply->fail_time+24*3600){
                        return "<script>alert('请在24小时后重新进行认证！');history.go(-1);</script>";
                    }
                }
                $data=$this->request->getPost();
                if($bankapply->remittance==$data['remittance']){
                    if($bankapply->save(array('verification'=>2))){
                        $this->response->redirect('/user/bankcertification');
                    }
                }else{
                    if($bankapply->certification_num>=2){
                        $num=1;
                        $user=User::findFirst($this->session->get('uid'));
                        $gcoin=$user->gcoin - 1;
                        if($bankapply->save(array('certification_num'=>$num,'fail_time'=>time()))&&$user->save(array('gcoin'=>$gcoin))){
                            return "<script>alert('认证失败两次，您已被扣除1金币，请在24小时后重新进行认证！');history.go(-1);</script>";
                        }
                    }else{
                        $num=$bankapply->certification_num+1;
                        if($bankapply->save(array('certification_num'=>$num))){
                            return "<script>alert('认证失败，请重试！');history.go(-1);</script>";
                        }
                    }
                }
            }
            $this->view->setVars(array(
                'operation'=>'bankcertification'
            ));
        }
        //删除当前登录会员的银行卡认证申请记录
        public function delbankapplyAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $condition='user_id='.$this->session->get('uid');
            $bankapply=BankApply::findFirst(array($condition));
            if(!$bankapply->delete()){
                return "<script>alert('抱歉，删除失败，请重试！');history.go(-1);</script>";
            }else{
                $this->response->redirect('/user/bankcertification');
            }
        }
        //绑定店铺
        public function bindshopAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $contents=file_get_contents($data['shop_link']);
                $contents=iconv("GBK", "utf-8",$contents);
                if($data["shop_type"]==1){
                    $info['type']=1;
                    //匹配淘宝掌柜名
                    if(preg_match("/<a class=\"tb-seller-name\".*?>(.*?)<\/a>/is",$contents,$matches)){
                        $info['master']=$matches[1];
                    }else{
                        return "<script>alert('未匹配到掌柜名，请检查输入的商品链接是否正确！');location='/user/bindshop';</script>";
                    }
                    //匹配淘宝店铺名
                    if(preg_match("/<div class=\"tb-shop-name\">.*?<a.*?>(.*?)<\/a>/is",$contents,$matches)){
                        $info['shop']=$matches[1];
                    }else{
                        return "<script>alert('未匹配到店铺名，请检查输入的商品链接是否正确！');location='/user/bindshop';</script>";
                    }
                }
                if($data["shop_type"]==2){
                    $info['type']=2;
                    //匹配京东店铺名
                    if(preg_match("/<div class=\"popbox-inner\".*?<a.*?>(.*?)<\/a>/is",$contents,$matches)){
                        $info['shop']=$matches[1];
                    }else{
                        return "<script>alert('未匹配到店铺名，请检查输入的商品链接是否正确！');location='/user/bindshop';</script>";
                    }
                }
                $info['user_id']=$this->session->get('uid');
                $info['create_time']=time();
                $m_shop=new Shop();
                if($m_shop->save($info)){
                    return "<script>alert('绑定店铺成功！');location='/user/bindshop';</script>";
                }else{
                    return "<script>alert('绑定店铺失败，请稍候重试！');location='/user/bindshop';</script>";
                }
            }
            //随机码生成
            $string="AB0C2D1E3FG4H5IJKL6MNOP7QR8STU9VWXYZ";
            $randnum="";
            for($i=0;$i<5;$i++){
                $randnum.=$string[rand(0,strlen($string)-1)];
            }
            //取出当前绑定的店铺信息
            $phql="select s.* from Shop as s where user_id=".$this->session->get('uid');
            $shop=$this->modelsManager->executeQuery($phql);
            $this->view->setVars(array(
                'shop'=>$shop,
                'randnum'=>$randnum,
                'operation'=>'bindshop'
            ));
        }
        //删除绑定的商铺
        public function delshopAction($id){
            $shop=Shop::findFirst($id);
            if($shop->delete()){
                return "<script>alert('店铺删除成功！');location='/user/bindshop';</script>";
            }else{
                return "<script>alert('店铺删除失败，请稍候重试！');location='/user/bindshop';</script>";
            }
        }
        //绑定淘宝号
        public function bindtbAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $data['alipay']=preg_replace('/\s/','',$data['alipay']);
                $data['user_id']=$this->session->get('uid');
                $data['level']=1;
                $data['create_time']=time();
                $data['update_time']=$data['create_time'];
                if(!preg_match('/^[a-zA-Z0-9\x{4e00}-\x{9fa5}][a-zA-Z0-9\x{4e00}-\x{9fa5}_]*$/u',$data['name'])){
                    return "<script>alert('请输入正确的淘宝会员号！');location='/user/bindtb';</script>";
                }
                if(empty($data['alipay'])){
                    return "<script>alert('请输入正确的支付宝账户！');location='/user/bindtb';</script>";
                }
                if(!preg_match('/^[\x{4e00}-\x{9fa5}][\x{4e00}-\x{9fa5}]*$/u',$data['real_name'])){
                    return "<script>alert('请输入正确的淘宝实名姓名！');location='/user/bindtb';</script>";
                }
                if(!$this->check_identity($data['idcard'])){
                    return "<script>alert('请输入正确的身份证号码！');location='/user/bindtb';</script>";
                }
                $tb=new TaoBao();
                if($tb->save($data)){
                    return "<script>alert('淘宝号添加成功！');location='/user/bindtb';</script>";
                }else{
                    return "<script>alert('淘宝号添加失败，请稍候重试！');location='/user/bindtb';</script>";
                }
            }
            $condition='user_id='.$this->session->get('uid');
            $tb=TaoBao::find(array($condition));
            $num=array();
            foreach($tb as $t){
                $condition="tb_id=".$t->id." and complete_status=2 and complete_time+24*3600>".time();
                $snatchTask=SnatchTask::find(array($condition));
                $num['day'][$t->id]=$snatchTask->count();
                $condition="tb_id=".$t->id." and complete_status=2 and complete_time+7*24*3600>".time();
                $snatchTask=SnatchTask::find(array($condition));
                $num['week'][$t->id]=$snatchTask->count();
                $condition="tb_id=".$t->id." and complete_status=2 and complete_time+30*24*3600>".time();
                $snatchTask=SnatchTask::find(array($condition));
                $num['month'][$t->id]=$snatchTask->count();
            }
            $this->view->setVars(array(
                'tb'=>$tb,
                'num'=>$num,
                'operation'=>'bindtb'
            ));
        }
        //更新淘宝号信誉页面显示
        public function updatecreditAction($id){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $tb=TaoBao::findFirst($id);
            $this->view->setVars(array(
                'tb'=>$tb,
                'operation'=>'bindtb'
            ));
        }
        //ajax更新淘宝号信誉
        public function updatetbAction(){
            if($this->request->isPost()){
                if($this->request->isAjax()){
                    $data=$this->request->getPost();
                    $this->db->begin();
                    $tb=TaoBao::findFirst($data['id']);
                    if($tb->update_time > $tb->create_time){
                        if(time() < $tb->update_time + 24*3600){
                            return json_encode(array("msg"=>"申请提交失败，一天只能提交一次更新申请！"));
                        }
                    }
                    if($tb->verify_status==1){
                        return json_encode(array("msg"=>"申请提交失败，此号还未审核！"));
                    }
                    if($tb->verify_status==2){
                        return json_encode(array("msg"=>"申请提交失败，此号未通过审核！"));
                    }
                    if($tb->use_status==2){
                        return json_encode(array("msg"=>"申请提交失败，此号已被禁用！"));
                    }
                    $tblevel=new TaoBaoLevel();
                    $info['user_id']=$this->session->get('uid');
                    $info['tb_id']=$data['id'];
                    $info['create_time']=time();
                    if(!$tblevel->save($info)){
                        $this->db->rollback();
                        return json_encode(array("msg"=>"申请提交失败，请稍候重试！"));
                    }
                    if(!$tb->save(array("update_time"=>time()))){
                        $this->db->rollback();
                        return json_encode(array("msg"=>"申请提交失败，请稍候重试！"));
                    }
                    $this->db->commit();
                    return json_encode(array("msg"=>"申请提交成功，请耐心等待审核！"));
                }
            }
        }
        //禁用已绑定的淘宝号
        public function tbdisabledAction($id){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $tb=TaoBao::findFirst($id);
            if($tb->save(array("use_status"=>2))){
                return "<script>alert('此淘宝号已禁用！');location='/user/bindtb';</script>";
            }else{
                return "<script>alert('禁用失败，请稍候重试！');location='/user/bindtb';</script>";
            }
        }
        //启用已绑定的淘宝号
        public function tbenabledAction($id){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $tb=TaoBao::findFirst($id);
            if($tb->save(array("use_status"=>1))){
                return "<script>alert('此淘宝号已启用！');location='/user/bindtb';</script>";
            }else{
                return "<script>alert('启用失败，请稍候重试！');location='/user/bindtb';</script>";
            }
        }
        //添加备注
        public function addremarkAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $data['remark']=preg_replace('/\s/','',$data['remark']);
                if(empty($data['remark'])){
                    return "<script>alert('请填写备注！');location='/user/bindtb';</script>";
                }
                $tb=TaoBao::findFirst($data['id']);
                unset($data['id']);
                if($tb->save($data)){
                    return "<script>alert('备注添加成功！');location='/user/bindtb';</script>";
                }else{
                    return "<script>alert('备注添加失败，请稍候重试！');location='/user/bindtb';</script>";
                }
            }
        }
        //删除已绑定的淘宝号
        public function deltbAction($id){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $tb=TaoBao::findFirst($id);
            if($tb->delete()){
                return "<script>alert('删除成功！');location='/user/bindtb';</script>";
            }else{
                return "<script>alert('删除失败，请稍候重试！');location='/user/bindtb';</script>";
            }
        }
        //升级淘宝号申请
        public function upgradetbAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                if(!preg_match('/^[\x{4e00}-\x{9fa5}]{1,10}$/u',$data['real_name'])){
                    return "<script>alert('请输入正确的姓名！');location='/user/upgradetb';</script>";
                }
                if(!$this->check_identity($data['idcard'])){
                    return "<script>alert('请输入正确的身份证号码！');location='/user/upgradetb';</script>";
                }
                if(empty($data['idcard_front'])||empty($data['idcard_back'])){
                    return "<script>alert('请上传身份证照片！');location='/user/upgradetb';</script>";
                }
                $data['user_id']=$this->session->get('uid');
                $data['create_time']=time();
                $upgradetb=new Upgradetb();
                if($upgradetb->save($data)){
                    return "<script>alert('申请提交成功！');location='/user/upgradetb';</script>";
                }else{
                    return "<script>alert('申请提交失败，请稍候重试！');location='/user/upgradetb';</script>";
                }
            }
            $condition='user_id='.$this->session->get('uid');
            $upgradetb=Upgradetb::find(array($condition));
            $this->view->setVars(array(
                'upgradetb'=>$upgradetb,
                'operation'=>'bindtb'
            ));
        }
        //删除升级淘宝号申请
        public function delupgradetbAction($id){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $upgradetb=Upgradetb::findFirst($id);
            if($upgradetb->delete()){
                return "<script>alert('删除成功！');location='/user/upgradetb';</script>";
            }else{
                return "<script>alert('删除失败，请稍候重试！');location='/user/upgradetb';</script>";
            }
        }
        //升级淘宝号V2申请
        public function upgradetbv2Action(){
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $condition="tb_id=".$data['tb_id']." and user_id=".$this->session->get('uid');
                $upgradetbv2=Upgradetbv2::findFirst(array($condition));
                if($upgradetbv2->id){
                    return "<script>alert('请不要提交重复的数据！');location='/user/upgradetbv2';</script>";
                }
                $data['user_id']=$this->session->get('uid');
                $data['create_time']=time();
                $upgradetbv2=new Upgradetbv2();
                if($upgradetbv2->save($data)){
                    return "<script>alert('申请提交成功！');location='/user/upgradetbv2';</script>";
                }else{
                    return "<script>alert('申请提交失败，请稍候重试！');location='/user/upgradetbv2';</script>";
                }
            }
            $condition="user_id=".$this->session->get('uid')." and verify_status=3 and use_status=1";
            $tb=TaoBao::find(array($condition));
            $phql="select ut2.id,ut2.verify_status,ut2.create_time,tb.name from Upgradetbv2 as ut2 join TaoBao as tb where ut2.user_id=".$this->session->get('uid');
            $upgradetbv2=$this->modelsManager->executeQuery($phql);
            $this->view->setVars(array(
                'tb'=>$tb,
                'upgradetbv2'=>$upgradetbv2,
                'operation'=>'bindtb'
            ));
        }
        //删除升级淘宝号V2申请
        public function delupgradetbv2Action($id){
            $upgradetbv2=Upgradetbv2::findFirst($id);
            if($upgradetbv2->delete()){
                return "<script>alert('删除成功！');location='/user/upgradetbv2';</script>";
            }else{
                return "<script>alert('删除失败，请稍候重试！');location='/user/upgradetbv2';</script>";
            }
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
        //金币兑现
        public function coinexchangeAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $phql="select ba.id,ba.bankcard_num,ba.mobile_num,ba.verification,ba.create_time,ba.handle_time,ba.real_name,ba.bank_id,b.bank_name from BankApply as ba join Bank as b where verification=2 and ba.user_id=" . $this->session->get('uid');
            $bankapply=$this->modelsManager->executeQuery($phql);//取出当前登录会员的已认证的银行卡信息
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $coin=(int)$data['coin_num'];
                if(empty($coin)||$coin<100){
                    return "<script>alert('100金币起兑换！');history.go(-1);</script>";
                }
                $config=Config::findFirst();
                foreach($bankapply as $ba){
                    $data['bank_id']=$ba->bank_id;
                    $data['bankcard_id']=$ba->bankcard_num;
                    $data['real_name']=$ba->real_name;
                    $data['mobile']=$ba->mobile_num;
                }
                $data['user_id']=$this->session->get('uid');
                $data['serial_num']='Y'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                $data['create_time']=time();
                $data['money_num']=$config->exchange * $data['coin_num'];
                $exchange=new Exchange();
                if(!$exchange->save($data)){//保存金币兑现申请
                    return "<script>alert('请检查您输入的信息！');history.go(-1);</script>";
                }
            }
            $condition='user_id='.$this->session->get('uid');
            $exchangerecord=Exchange::find(array($condition));//取出当前登录会员提交过的金币兑现申请记录
            $this->view->setVars(array(
                'bankapply'=>$bankapply,
                'exchangerecord'=>$exchangerecord,
                'operation'=>'coinexchange'
            ));
        }
        //购买发布点
        public function buyreleaseAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $user=User::findFirst($this->session->get('uid'));
            $this->view->setVars(array(
                'user'=>$user,
                'operation'=>'buyrelease'
            ));
        }
        //以金币形式购买
        public function bycoinAction(){
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $data['point_num']=(int)$data['point_num'];
                if($data['point_num']<=0){
                    return "<script>alert('请输入正确的发布点数！');location='/user/buyrelease';</script>";
                }
                $config=Config::findFirst();
                $coin=ceil($data['point_num'] / $config->release_rate);//计算购买发布点所应支付的金币数
                $ucondition="id=".$this->session->get('uid');
                $user=User::findFirst(array($ucondition));//取出当前登录会员信息
                if($user->gcoin < $coin){//判断会员金币余额是否足以支付当前发布点数
                    return "<script>alert('您的金币余额不足，请充值！');location='/user/buyrelease';</script>";
                }
                $manager = new Phalcon\Mvc\Model\Transaction\Manager();//创建一个事务管理器
                $transaction = $manager->get();//发起事务
                $user->setTransaction($transaction);
                $coin_balance=$user->gcoin - $coin;//计算会员扣除金币后的金币余额
                if(!$user->save(array("gcoin"=>$coin_balance))){
                    $transaction->rollback("购买发布点失败，请稍候重试！");//若会员信息更新失败，则回滚事务
                }
                $release_balance=$user->release_num + $data['point_num'];//计算会员购买发布点后的总发布点数
                if(!$user->save(array("release_num"=>$release_balance))){
                    $transaction->rollback("购买发布点失败，请稍候重试！");//若会员信息更新失败，则回滚事务
                }
                $transaction->commit();//提交事务，进行会员信息的更新
                $info['user_id']=$this->session->get('uid');
                $info['point_num']=$data['point_num'];
                $info['coin_num']=$coin;
                $info['buy_time']=time();
                $buyreleasebycoin=new BuyReleaseBycoin();
                if($buyreleasebycoin->save($info)){
                    return "<script>alert('发布点购买成功');location='/user/index';</script>";
                }
            }
        }
        //以充值卡形式购买
        public function bycardAction(){
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $condition="card_num='".$data['card_num']."' and isuse=1";
                $rechargecard=RechargeCard::findFirst(array($condition));
                if($rechargecard->id){
                    $ucondition="id=".$this->session->get('uid');
                    $user=User::findFirst(array($ucondition));
                    $config=Config::findFirst();
                    $point=$rechargecard->gcoin_num * $config->release_rate;//根据站点配置信息计算出要充值的发布点
                    $total_point=$point + $user->release_num;//充值后会员的总发布点数
                    if($user->save(array("release_num"=>$total_point))){
                        if($rechargecard->save(array("isuse"=>2))){
                            $info['user_id']=$this->session->get('uid');
                            $info['card_num']=$data['card_num'];
                            $info['point_num']=$point;
                            $info['buy_time']=time();
                            $buyrelease=new BuyReleaseBycard();
                            $buyrelease->save($info);//将购买记录保存
                            return "<script>alert('发布点购买成功');location='/user/index';</script>";
                        }else{
                            return "<script>alert('发布点购买失败，请重试！');location='/user/buyrelease';</script>";
                        }
                    }else{
                        return "<script>alert('发布点购买失败，请重试！');location='/user/buyrelease';</script>";
                    }
                }else{
                    return "<script>alert('发布点购买失败，请检查您输入的卡号是否正确或者此充值卡已被使用！');location='/user/buyrelease';</script>";
                }
            }
        }
        //计算购买发布点所需的金币数，并将计算结果返回
        public function countnumAction(){
            $this->layout='';
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $config=Config::findFirst();
                $data['num']=(int)$data['num'];
                if($data['num'] >= 1){
                    $coin=ceil($data['num'] / $config->release_rate);
                    if($coin >= 1){
                        return json_encode(array("status"=>1,"n"=>$coin));
                    }else{
                        return json_encode(array("status"=>2,"info"=>"1金币起购买！"));
                    }
                }else{
                    return json_encode(array("status"=>2,"info"=>"请输入正确发布点数！"));
                }
            }
        }
        //安全设置
        public function safesettingAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $user=User::findFirst($this->session->get('uid'));
            $this->view->setVars(array(
                'user'=>$user,
                'operation'=>'safesetting'
            ));
        }
        //修改密码
        public function changepasswordAction(){
            if($this->request->isPost()){
                $data=$this->request->getPost();
                if(empty($data['oldpassword'])||empty($data['newpassword'])){
                    return "<script>alert('请将信息填写完整！');location='/user/safesetting';</script>";
                }
                if($data['newpassword']!=$data['repassword']){
                    return "<script>alert('两次新密码输入不一致，请重新输入！');location='/user/safesetting';</script>";
                }
                $password=sha1($data['oldpassword']);
                $newpassword=sha1($data['newpassword']);
                $uid=$this->session->get('uid');
                $condition="id=?1 and password=?2";
                $params=array(1=>$uid,2=>$password);
                $user=User::findFirst(array($condition,"bind"=>$params));
                if($user->id){
                    if($user->save(array("password"=>$newpassword))){
                        return "<script>alert('密码修改成功，请使用新密码重新登录！');location='/index/logout';</script>";
                    }else{
                        return "<script>alert('密码修改失败，请稍候重试！');location='/user/safesetting';</script>";
                    }
                }else{
                    return "<script>alert('原密码输入有误，请重新输入！');location='/user/safesetting';</script>";
                }
            }
        }
        //修改密保问题
        public function changequestionAction(){
            if($this->request->isPost()){
                $data=$this->request->getPost();
                if($data['question1']!=0){
                    $data['answer1']=preg_replace('/\s/','',$data['answer1']);
                    if(empty($data['answer1'])){
                        return "<script>alert('请输入第一问题答案！');location='/user/safesetting';</script>";
                    }
                }
                if($data['question2']!=0){
                    $data['answer2']=preg_replace('/\s/','',$data['answer2']);
                    if(empty($data['answer2'])){
                        return "<script>alert('请输入第二问题答案！');location='/user/safesetting';</script>";
                    }
                }
                $user=User::findFirst($this->session->get('uid'));
                if($user->save($data)){
                    return "<script>alert('密保问题修改成功！');location='/user/safesetting';</script>";
                }else{
                    return "<script>alert('密保问题修改失败，请稍候重试！');location='/user/safesetting';</script>";
                }
            }
        }
       //交易记录
        public function transactionrecordAction(){
            $this->view->setVars(array(
                'operation'=>'transactionrecord'
            ));
        }
    }
?>