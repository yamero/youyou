<?php
    class UserController extends ControllerBase{
        public function indexAction($pageNum){
            if(!$this->session->has('uname')){  //判断管理员是否登录，没登录就定位到登录页面
                $this->response->redirect('/login');
            }
            $config=Config::findFirst();//取出站点配置信息
            $currentPage=(int)empty($pageNum)?1:$pageNum;
            if(empty($pageNum)){    //若第一次进入，则清空session显示全部记录
                $this->session->remove('identity');
                $this->session->remove('utype');
                $this->session->remove('username');
                $where='';
            }
            if($this->request->isPost()){   //若是通过post提交的请求就将post过来的表单值存入session
                foreach($this->request->getPost() as $k=>$v){
                    $this->session->set($k,$v);
                }
            }
            if(!empty($this->session->get('identity'))){
                $where.=" and u.identity=".$this->session->get('identity');
            }
            if(!empty($this->session->get('utype'))){
                $where.=" and u.type=".$this->session->get('utype');
            }
            if(!empty($this->session->get('username'))){
                $where.=" and u.account like '%".$this->session->get('username')."%'";
            }
            $phql="select u.* from User as u where 1=1".$where;
            $user=$this->modelsManager->executeQuery($phql);
            $paginator = new \Phalcon\Paginator\Adapter\Model(
                array(
                    "data" => $user,
                    "limit"=> $config->record_num,
                    "page" => $currentPage
                )
            );
            $page = $paginator->getPaginate();
            if($page->last<=$config->page_offset*2+1){  //若总页数小于或等于要显示的页码数，让起始页码等于1，结束页码等于总页数
                $startnum=1;
                $lastnum=$page->last;
            }else{  //若总页数大于要显示的页码数
                if($currentPage-$config->page_offset<=1){   //若当前页码减去偏移量小于或等于1，让起始页码等于1，结束页码等于要显示的页码数
                    $startnum=1;
                    $lastnum=$config->page_offset*2+1;
                }else{  //若当面页码减去偏移量大于1
                    $startnum=$currentPage-$config->page_offset <= 1 ? 1 : $currentPage-$config->page_offset;   //若当前页码减去偏移量小于或等于1，则让起始页码等于1，否则就让起始页码等于当前页码减去偏移量
                    $startnum=$currentPage+$config->page_offset >= $page->last ? $page->last-$config->page_offset*2 : $currentPage-$config->page_offset; //若当前页码加上偏移量大于或等于总页数，则让起始页码等于总页数减去偏移量的2倍，否则就让起始页码等于当前页码减去偏移量
                    $lastnum=$currentPage+$config->page_offset >= $page->last ? $page->last:$currentPage+$config->page_offset;  //若当前页码加上偏移量大于或等于总页数，则让结束页码等于总页数，否则就让结束页码等于当前页码加上偏移量
                }
            }
            $this->view->setVars(array(
                'lastnum'=>$lastnum,
                'startnum'=>$startnum,
                'currentPage'=>$currentPage,
                'page'=>$page,
                'operation'=>'userlist'
            ));
        }
        public function addAction(){
            $this->view->setVar('operation',"useradd");
        }
        public function doaddAction(){
            $user=new User();
            $data=$this->request->getPost();
            $data['reg_time']=time();
            $data['password']=sha1($data['password']);
            if($user->save($data)){
                $this->response->redirect('/user/index');
            }else{
                echo "添加失败，请检查输入！";
            }
        }
        public function editAction($id){
            $province=China::find(array("Pid=0 and Id!=0"));//取出省份信息
            $bank=Bank::find();//取出银行信息
            $userOne=User::findFirst($id);
            if($userOne->province){
                $city=China::find(array("Pid=".$userOne->province));//取出市信息
                if($userOne->city==$userOne->area){
                    $area=$city;
                }else{
                    $area=China::find(array("Pid=".$userOne->city));//取出区域信息
                }
            }
            $this->view->setVars(array(
                'u'=>$userOne,
                'province'=>$province,
                'city'=>$city,
                'area'=>$area,
                'bank'=>$bank
            ));
        }
        public function doeditAction(){
            $data=$this->request->getPost();
            $user=User::findFirst($data['id']);
            unset($data['id']);
            if(empty($data['password'])){
                unset($data['password']);
            }else{
                $data['password']=sha1($data['password']);
            }
			if($this->request->hasFiles()){
                $config=Config::findFirst();//取出站点配置信息
				$imgName=array();
                $datestr=date('Ymd',time());
                $path='../uploads/'.$datestr.'/';
                if(!file_exists($path)){
                    mkdir($path);
                }
				foreach($this->request->getUploadedFiles() as $file){
                    if($file->getName()){
                        $imgUrl=date('YmdHis',time()).uniqid().'.'.$file->getExtension();
                        $file->moveTo($path.$imgUrl);
                        $imgName[]=$config->img_domain.$datestr.'/'.$imgUrl;
                    }else{
                        $imgName[]='';
                    }
				}
				$data['idcard_front']=empty($imgName[0])?$user->idcard_front:$imgName[0];
				$data['idcard_back']=empty($imgName[1])?$user->idcard_back:$imgName[1];
                $data['idcard_gesture']=empty($imgName[2])?$user->idcard_gesture:$imgName[2];
                $data['gesture']=empty($imgName[3])?$user->gesture:$imgName[3];
                $data['shop_pic']=empty($imgName[4])?$user->shop_pic:$imgName[4];
			}
            if($user->save($data)){
                $this->response->redirect('/user/index');
            }else{
                echo "编辑失败，请检查输入！";
            }
        }
        public function delAction($id){
            $user=User::findFirst($id);
            if($user->delete()){
                $this->response->redirect('/user/index');
            }else{
                echo "对不起，无法删除！";
            }
        }
        //根据传入的省id获取市
        public function getcityAction(){
            if($this->request->isPost()){
                $str="<option value='0' selected='selected'>--请选择--</option>";
                $id=$this->request->getPost('id');
                if($id){
                    $condition="Pid=".$id;
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
    }
?>