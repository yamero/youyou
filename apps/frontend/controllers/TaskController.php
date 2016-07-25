<?php
    class TaskController extends ControllerBase{
        protected $layout='sidebart';
        //任务大厅
        public function indexAction($pageNum){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $where="";
            if($this->session->get('utype')==2){
                //若登录的是刷手，则取出刷手绑定的买号
                $condition="user_id=".$this->session->get('uid')." and use_status=1 and verify_status=3";
                $tb=TaoBao::find(array($condition));
                $this->view->setVar('tb',$tb);
                //在重复接任务限制天数内的店铺任务不显示
                $condition="user_id1=".$this->session->get('uid');
                $snatchTask=SnatchTask::find(array($condition));
                foreach($snatchTask as $st){
                    if($st->day_num>0&&time()<$st->create_time+3600*24*$st->day_num){
                        $where.=" and t.shop_id!=".$st->shop_id;
                    }
                }
            }
            if(empty($pageNum)){
                $this->session->remove('splatform');
                $this->session->remove('stype');
                $this->session->remove('level1');
                $this->session->remove('level2');
                $this->session->remove('remote');
                $this->session->remove('price');
                $this->session->remove('searchKey');
                $this->session->remove('searchWords');
            }
            $pageNum=(int)$pageNum;
            $currentPage=$pageNum < 1 ? 1 : $pageNum;
            if($this->request->isPost()){
                $where="";
                $data=$this->request->getPost();
                foreach($data as $k=>$v){
                    $this->session->set($k,$v);
                }
            }
            if($this->session->get('splatform')!=0){
                $where.=" and t.platform=".$this->session->get('splatform');
            }
            if($this->session->get('stype')!=0){
                $where.=" and t.type=".$this->session->get('stype');
            }
            if($this->session->get('level1')=='1'){
                $where.=" and u.points>=4 and u.points<=10";
            }
            if($this->session->get('level1')=='2'){
                $where.=" and u.points>=11 and u.points<=40";
            }
            if($this->session->get('level1')=='3'){
                $where.=" and u.points>=41 and u.points<=90";
            }
            if($this->session->get('level1')=='4'){
                $where.=" and u.points>=91 and u.points<=150";
            }
            if($this->session->get('level1')=='5'){
                $where.=" and u.points>=151 and u.points<=250";
            }
            if($this->session->get('level1')=='6'){
                $where.=" and u.points>=251 and u.points<=500";
            }
            if($this->session->get('level2')!=0){
                $authLevel=ltrim($this->session->get('level2'),'0');
                $where.=" and t.auth_level=".$authLevel;
            }
            if($this->session->get('remote')!=0){
                $where.=" and t.remote_pay=".$this->session->get('remote');
            }
            if($this->session->get('price')!=0){
                $price=explode('-',$this->session->get('price'),2);
                $where.=" and t.total_money>=".$price[0]." and t.total_money<=".$price[1];
            }
            $searchWords=trim($this->session->get('searchWords'));
            if($this->session->get('searchKey')!=0&&!empty($searchWords)){
                if($this->session->get('searchKey')==1){
                    $where.=" and t.sn='".$searchWords."'";
                }elseif($this->session->get('searchKey')==2){
                    $where.=" and u.account='".$searchWords."'";
                }
            }
            $phql="select t.id,t.sn,t.commission,t.total_money,t.false_talk,t.return_time,t.change_address,t.type,t.isauth,t.auth_level,t.special_ask,t.number,t.remote_pay,t.city,u.account,u.points,c.Name from Task as t join User as u join China as c where 1=1".$where." order by t.create_time desc";
            $task=$this->modelsManager->executeQuery($phql);
            $paginator = new \Phalcon\Paginator\Adapter\Model(
                array(
                    "data" => $task,
                    "limit"=> 1,
                    "page" => $currentPage
                )
            );
            $page = $paginator->getPaginate();
            $pageOffset=5;
            if($page->last<=$pageOffset*2+1){  //若要总页数小于或等于要显示的页码数，让起始页码等于1，结束页码等于总页数
                $startnum=1;
                $lastnum=$page->last;
            }else{  //若总页数大于要显示的页码数
                if($currentPage-$pageOffset<=1){   //若当前页码减去偏移量小于或等于1，让起始页码等于1，结束页码等于要显示的页码数
                    $startnum=1;
                    $lastnum=$pageOffset*2+1;
                }else{  //若当面页码减去偏移量大于1
                    $startnum=$currentPage-$pageOffset <= 1 ? 1 : $currentPage-$pageOffset;   //若当前页码减去偏移量小于或等于1，则让起始页码等于1，否则就让起始页码等于当前页码减去偏移量
                    $startnum=$currentPage+$pageOffset >= $page->last ? $page->last-$pageOffset*2 : $currentPage-$pageOffset; //若当前页码加上偏移量大于或等于总页数，则让起始页码等于总页数减去偏移量的2倍，否则就让起始页码等于当前页码减去偏移量
                    $lastnum=$currentPage+$pageOffset >= $page->last ? $page->last:$currentPage+$pageOffset;  //若当前页码加上偏移量大于或等于总页数，则让结束页码等于总页数，否则就让结束页码等于当前页码加上偏移量
                }
            }
            $this->view->setVars(array(
                'startnum'=>$startnum,
                'lastnum'=>$lastnum,
                'currentPage'=>$currentPage,
                'page'=>$page
            ));
        }
        //任务详情
        public function taskdetailAction($id){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $snatchTask=SnatchTask::findFirst($id);
            $task=Task::findFirst($snatchTask->task_id);
            $shop=Shop::findFirst($snatchTask->shop_id);
            $user1=User::findFirst($snatchTask->user_id1);
            $user2=User::findFirst($snatchTask->user_id2);
            $tb=TaoBao::findFirst($snatchTask->tb_id);
            $this->view->setVars(array(
                'st'=>$snatchTask,
                'task'=>$task,
                'shop'=>$shop,
                'user1'=>$user1,
                'user2'=>$user2,
                'tb'=>$tb
            ));
        }
        //刷手抢任务
        public function snatchAction(){
            if($this->request->isPost()){
                if($this->request->isAjax()){
                    $data=$this->request->getPost();
                    $condition="id=".$this->session->get('uid');
                    $this->db->begin();
                    $user=User::findFirst(array($condition));
                    $task=Task::findFirst($data['taskid']);
                    $tb=TaoBao::findFirst($data['tbid']);
                    if($task->number==0){
                        return json_encode(array("status"=>0,"msg"=>"抢任务失败，此任务已被抢完！"));
                    }
                    if($task->isauth==1){
                        if($tb->heart_level < $task->auth_level){
                            return json_encode(array("status"=>0,"msg"=>"抢任务失败，买号信誉等级不足！"));
                        }
                    }
                    if($task->city!=0){
                        if($task->city == $user->city){
                            return json_encode(array("status"=>0,"msg"=>"抢任务失败，此任务有地域限制！"));
                        }
                    }
                    $info['sn']='D'.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 10);
                    $info['user_id1']=$this->session->get('uid');
                    $info['user_id2']=$task->user_id;
                    $info['task_id']=$data['taskid'];
                    $info['tb_id']=$data['tbid'];
                    $info['shop_id']=$task->shop_id;
                    $info['day_num']=$task->days;
                    $info['create_time']=time();
                    $snatchtask=new SnatchTask();
                    if(!$snatchtask->save($info)){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"抢任务失败，请稍候再试！"));
                    }
                    $taskNum=$task->number - 1;
                    if(!$task->save(array("number"=>$taskNum))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"抢任务失败，请稍候再试！"));
                    }
                    $this->db->commit();
                    return json_encode(array("status"=>1,"msg"=>"抢任务成功，请耐心等待商家审核！"));
                }
            }
        }
        //商家我的任务
        public function shoppertaskAction($opt){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $condition="id=".$this->session->get('uid');
            $user=User::findFirst(array($condition));
            $where='';
            if($opt==1){
                $where=' and st.verify_status=1';
            }
            if($opt==2){
                $where=' and st.verify_status=3 and st.pay_status=1';
            }
            if($opt==3){
                $where=' and st.verify_status=3 and st.pay_status=2 and st.return_status=1';
            }
            if($opt==4){
                $where=' and st.verify_status=3 and st.pay_status=2 and st.return_status=2 and st.receive_status=1';
            }
            if($opt==5){
                $where=' and st.verify_status=3 and st.pay_status=2 and st.return_status=2 and st.receive_status=2 and complete_status=2';
            }
            $phql="select st.id,st.sn,st.verify_status,st.pay_status,st.return_status,st.receive_status,st.complete_status,st.order_sn,st.create_time,t.platform,t.type,t.total_money,t.commission,t.remote_pay,t.isauth,u.account,u.qq,u.tenpay,u.tenpay_name,u.real_name,s.shop,s.master,tb.name,tb.heart_level from SnatchTask st,Task t,User u,Shop s,TaoBao tb where t.id=st.task_id and u.id=st.user_id1 and s.id=st.shop_id and tb.id=st.tb_id and st.user_id2=".$this->session->get('uid').$where;
            $mytask=$this->modelsManager->executeQuery($phql);
            $this->view->setVars(array(
                'user'=>$user,
                'mytask'=>$mytask,
                'opt'=>$opt,
                'operation'=>'mytask'
            ));
        }
        //刷手我的任务
        public function usertaskAction($opt){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $where='';
            if($opt==1){
                $where=' and st.verify_status=1';
            }
            if($opt==2){
                $where=' and st.verify_status=3 and st.pay_status=1';
            }
            if($opt==3){
                $where=' and st.verify_status=3 and st.pay_status=2 and st.return_status=1';
            }
            if($opt==4){
                $where=' and st.verify_status=3 and st.pay_status=2 and st.return_status=2 and st.receive_status=1';
            }
            if($opt==5){
                $where=' and st.verify_status=3 and st.pay_status=2 and st.return_status=2 and st.receive_status=2 and complete_status=2';
            }
            $phql="select st.id,st.sn,st.verify_status,st.pay_status,st.return_status,st.receive_status,st.complete_status,st.order_sn,st.create_time,t.platform,t.type,t.total_money,t.commission,t.remote_pay,t.isauth,u.account,u.qq,u.tenpay,u.tenpay_name,u.real_name,s.shop,s.master,tb.name,tb.heart_level from SnatchTask st,Task t,User u,Shop s,TaoBao tb where t.id=st.task_id and u.id=st.user_id2 and s.id=st.shop_id and tb.id=st.tb_id and st.user_id1=".$this->session->get('uid').$where;
            $mytask=$this->modelsManager->executeQuery($phql);
            $this->view->setVars(array(
                'mytask'=>$mytask,
                'opt'=>$opt,
                'operation'=>'mytask'
            ));
        }
        //刷手付款后提交订单号
        public function commitorderAction(){
            if($this->request->isPost()) {
                if ($this->request->isAjax()) {
                    $data=$this->request->getPost();
                    $this->db->begin();
                    $snatchTask=SnatchTask::findFirst($data['id']);
                    if(!$snatchTask->save(array("order_sn"=>$data['sn']))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"提交失败，请稍候重试！"));
                    }
                    if(!$snatchTask->save(array("pay_status"=>2))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"提交失败，请稍候重试！"));
                    }
                    $this->db->commit();
                    return json_encode(array("status"=>1,"msg"=>"提交成功，等待商家返款！"));
                }
            }
        }
        //商家审核任务
        public function verifyAction(){
            if($this->request->isPost()) {
                if ($this->request->isAjax()) {
                    $data=$this->request->getPost();
                    $snatchTask=SnatchTask::findFirst($data['id']);
                    if($snatchTask->save(array("verify_status"=>$data['val']))){
                        return json_encode(array("status"=>1,"msg"=>"操作成功！"));
                    }else{
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                }
            }
        }
        //商家确认返款
        public function returnmoneyAction(){
            if($this->request->isPost()) {
                if ($this->request->isAjax()) {
                    $data=$this->request->getPost();
                    $snatchTask=SnatchTask::findFirst($data['id']);
                    if($snatchTask->save(array("return_status"=>2))){
                        return json_encode(array("status"=>1,"msg"=>"操作成功！"));
                    }else{
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                }
            }
        }
        //商家终结任务
        public function completetaskAction(){
            if($this->request->isPost()) {
                if ($this->request->isAjax()) {
                    $data=$this->request->getPost();
                    $this->db->begin();
                    $snatchTask=SnatchTask::findFirst($data['id']);
                    $user1=User::findFirst($snatchTask->user_id1);
                    $user2=User::findFirst($snatchTask->user_id2);
                    //任务完成，对推荐人进行金币奖励
                    if($user1->master_id>0){
                        $condition="id=".$user1->master_id;
                        $promoteUser1=User::findFirst(array($condition));
                        $gcoin=$promoteUser1->gcoin + 10;
                        if(!$promoteUser1->save(array("gcoin"=>$gcoin))){
                            $this->db->rollback();
                            return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                        }
                    }
                    if($user2->master_id>0){
                        $condition="id=".$user2->master_id;
                        $promoteUser2=User::findFirst(array($condition));
                        $gcoin=$promoteUser2->gcoin + 10;
                        if(!$promoteUser2->save(array("gcoin"=>$gcoin))){
                            $this->db->rollback();
                            return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                        }
                    }
                    //任务标识为已完成
                    if(!$snatchTask->save(array("complete_status"=>2))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                    //任务完成，刷手积分加1
                    $points=$user1->points + 1;
                    if(!$user1->save(array("points"=>$points))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                    //任务完成，商家积分加1
                    $points=$user2->points + 1;
                    if(!$user2->save(array("points"=>$points))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                    //记录任务完成时间
                    if(!$snatchTask->save(array("complete_time"=>time()))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                    $this->db->commit();
                    return json_encode(array("status"=>1,"msg"=>"操作成功！"));
                }
            }
        }
        //刷手取消任务
        function canceltaskAction(){
            if($this->request->isPost()) {
                if ($this->request->isAjax()) {
                    $data=$this->request->getPost();
                    $this->db->begin();
                    $mytask=SnatchTask::findFirst($data['id']);
                    $task=Task::findFirst($mytask->task_id);
                    if(!$mytask->delete()){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"任务取消失败，请稍候重试！"));
                    }
                    $taskNum=$task->number + 1;
                    if(!$task->save(array("number"=>$taskNum))){
                        $this->db->rollback();
                        return json_encode(array("status"=>0,"msg"=>"任务取消失败，请稍候重试！"));
                    }
                    $this->db->commit();
                    return json_encode(array("status"=>1,"msg"=>"任务取消成功！"));
                }
            }
        }
        //刷手确认收货
        public function receiveyesAction(){
            if($this->request->isPost()) {
                if ($this->request->isAjax()) {
                    $data=$this->request->getPost();
                    $snatchTask=SnatchTask::findFirst($data['id']);
                    if($snatchTask->save(array("receive_status"=>2))){
                        return json_encode(array("status"=>1,"msg"=>"操作成功！"));
                    }else{
                        return json_encode(array("status"=>0,"msg"=>"操作失败，请稍候重试！"));
                    }
                }
            }
        }
        //选择任务平台和类型
        public function selectplatformAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $this->view->setVars(array(
                'operation'=>'releasetask'
            ));
        }
        //选择模板
        public function selecttplAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $condition="user_id=".$this->session->get('uid')." and platform=".$data['platform']." and type=".$data['type']." and issave=1";
                $tpl=Task::find(array($condition));
                $this->view->setVars(array(
                    'data'=>$data,
                    'tpl'=>$tpl,
                    'operation'=>'releasetask'
                ));
            }
        }
        //根据选择项显示不同的任务发布页
        public function showtplAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $this->session->set('platform',$data['platform']);
                $this->session->set('type',$data['type']);
                $this->session->set('tpl',$data['tpl']);
                //淘宝阿里京东电脑手机单秒拍单
                if(($data['platform']==1||$data['platform']==2)&&($data['type']==1||$data['type']==2||$data['type']==4)){
                    $this->response->redirect('/task/tbalids');
                }
                //淘宝阿里京东浏览单
                if(($data['platform']==1||$data['platform']==2)&&($data['type']==3)){
                    $this->response->redirect('/task/tbalill');
                }
            }
        }
        //淘宝阿里京东电脑手机任务发布页
        public function tbalidsAction(){
            if(!$this->session->has('uid')){//判断会员是否登录，没登录就定位到登录页面
                header('Location: /');
            }
            $platform=$this->session->get('platform');
            $type=$this->session->get('type');
            $tpl=$this->session->get('tpl');
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $condition="id=".$this->session->get('uid');
                $data['user_id']=$this->session->get('uid');
                $data['platform']=$platform;
                $data['type']=$type;
                if($data['confirm_type']==1){
                    $data['confirm_time']=$data['txtyear'].'-'.$data['txtmonth'].'-'.$data['txtday'];
                }
                unset($data['txtyear'],$data['txtmonth'],$data['txtday']);
                $n=0;
                $keyArr=array();
                for($i=0;$i<=20;$i++){
                    $str="keyword_".$i;
                    if(!empty($data[$str])){
                        $keyArr[]=$str;
                        $n++;
                    }
                }
                $m=0;
                $detailArr=array();
                for($i=0;$i<=20;$i++){
                    $str="ask_".$i;
                    if(!empty($data[$str])){
                        $detailArr[]=$str;
                        $m++;
                    }
                }
                if($n==0){
                    return "<script>alert('任务发布失败，请至少输入一个关键词！');history.go(-1);</script>";
                }
                if($n!=$m){
                    return "<script>alert('任务发布失败，关键搜索词数量与任务详细要求数量必须保持一致！');history.go(-1);</script>";
                }
                if($data['number'] % $n != 0){
                    return "<script>alert('任务发布失败，发布任务数量只能与关键搜索词数量相等或是关键搜索词数量的倍数！');history.go(-1);</script>";
                }
                $data['number']=$data['number'] / $n;
                for($j=0;$j<$n;$j++){
                    $data['sn']='TK'.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 10);
                    $data['create_time']=time();
                    $data['keywords']=$data[$keyArr[$j]];
                    $data['detail']=$data[$detailArr[$j]];
                    unset($data[$keyArr[$j]],$data[$detailArr[$j]]);
                    $this->db->begin();
                    $task=new Task();
                    if(!$task->save($data)){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，请检查您的输入！');history.go(-1);</script>";
                    }
                    $shop=Shop::findFirst($data['shop_id']);
                    $taskNum=$shop->task_num + $data['number'];
                    if(!$shop->save(array("task_num"=>$taskNum))){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，请稍候重试！');history.go(-1);</script>";
                    }
                    $user=User::findFirst(array($condition));
                    $releaseNum=$user->release_num - $data['number'];
                    if($releaseNum < 0){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，您的发布点数不足，请及时充值！');location='/user/buyrelease';</script>";
                    }
                    if(!$user->save(array("release_num"=>$releaseNum))){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，请稍候重试！');location='/user/buyrelease';</script>";
                    }
                    $this->db->commit();
                }
                return "<script>alert('任务发布成功！');location='/task/index';</script>";
            }
            $province=China::find(array("Pid=0 and Id!=0"));//取出省
            $condition="user_id=".$this->session->get('uid')." and type=".$platform." and status=1";
            $shop=Shop::find(array($condition));//取出店铺
            $this->view->setVars(array(
                'province'=>$province,
                'shop'=>$shop,
                'operation'=>'releasetask'
            ));
        }
        //淘宝阿里浏览任务发布页
        public function tbalillAction(){
            $platform=$this->session->get('platform');
            $type=$this->session->get('type');
            $tpl=$this->session->get('tpl');
            if($this->request->isPost()){
                $data=$this->request->getPost();
                $condition="id=".$this->session->get('uid');
                $data['user_id']=$this->session->get('uid');
                $data['platform']=$platform;
                $data['type']=$type;
                $n=0;
                $keyArr=array();
                for($i=0;$i<=20;$i++){
                    $str="keyword_".$i;
                    if(!empty($data[$str])){
                        $keyArr[]=$str;
                        $n++;
                    }
                }
                $m=0;
                $detailArr=array();
                for($i=0;$i<=20;$i++){
                    $str="ask_".$i;
                    if(!empty($data[$str])){
                        $detailArr[]=$str;
                        $m++;
                    }
                }
                if($n==0){
                    return "<script>alert('任务发布失败，请至少输入一个关键词！');history.go(-1);</script>";
                }
                if($n!=$m){
                    return "<script>alert('任务发布失败，关键搜索词数量与任务详细要求数量必须保持一致！');history.go(-1);</script>";
                }
                if($data['number'] % $n != 0){
                    return "<script>alert('任务发布失败，发布任务数量只能与关键搜索词数量相等或是关键搜索词数量的倍数！');history.go(-1);</script>";
                }
                $data['number']=$data['number'] / $n;
                for($j=0;$j<$n;$j++){
                    $data['sn']='TK'.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 10);
                    $data['create_time']=time();
                    $data['keywords']=$data[$keyArr[$j]];
                    $data['detail']=$data[$detailArr[$j]];
                    unset($data[$keyArr[$j]],$data[$detailArr[$j]]);
                    $this->db->begin();
                    $task=new Task();
                    if(!$task->save($data)){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，请检查您的输入！');history.go(-1);</script>";
                    }
                    $shop=Shop::findFirst($data['shop_id']);
                    $taskNum=$shop->task_num + $data['number'];
                    if(!$shop->save(array("task_num"=>$taskNum))){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，请稍候重试！');history.go(-1);</script>";
                    }
                    $user=User::findFirst(array($condition));
                    $releaseNum=$user->release_num - $data['number'];
                    if($releaseNum < 0){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，您的发布点数不足，请及时充值！');location='/user/buyrelease';</script>";
                    }
                    if(!$user->save(array("release_num"=>$releaseNum))){
                        $this->db->rollback();
                        return "<script>alert('任务发布失败，请稍候重试！');location='/user/buyrelease';</script>";
                    }
                    $this->db->commit();
                }
                return "<script>alert('任务发布成功！');location='/task/index';</script>";
            }
            $condition="user_id=".$this->session->get('uid')." and type=".$platform." and status=1";
            $shop=Shop::find(array($condition));//取出店铺
            $this->view->setVars(array(
                'shop'=>$shop,
                'operation'=>'releasetask'
            ));
        }
        //根据传入的省id获取市
        public function getcityAction(){
            if($this->request->isPost()){
                $str='';
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
        //ajax无刷上传任务图片
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
                        if(!in_array($type,$typearr)){
                            return json_encode(array('status'=>0,'info'=>'无法上传此类型的图片！'));
                        }
                        if($size>5*1024*1024){
                            return json_encode(array('status'=>0,'info'=>'您上传的图片过大！'));
                        }
                        $imgUrl=date('YmdHis',time()).uniqid().'.'.$file->getExtension();
                        $file->moveTo($path.$imgUrl);
                        $imgName=$config->img_domain.$datestr.'/'.$imgUrl;
                    }
                }
                return json_encode(array('status'=>1,'info'=>$imgName));
            }else{
                return json_encode(array('status'=>0,'info'=>'请选择图片后再点击上传！'));
            }
        }
    }