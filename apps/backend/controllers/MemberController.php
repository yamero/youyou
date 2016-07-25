<?php
	class MemberController extends ControllerBase{
		public function indexAction(){
			if(!$this->session->has('uname')){
				$this->response->redirect('/login');
			}
			$currentPage=(int)empty($_GET["page"])?1:$_GET["page"];
			$phql="select m.id,m.name,m.password,m.create_time from Member as m";
			$members=$this->modelsManager->executeQuery($phql);
			$paginator = new \Phalcon\Paginator\Adapter\Model(
					array(
							"data" => $members,
							"limit"=> 10,
							"page" => $currentPage
					)
			);
			$page = $paginator->getPaginate();
			if($page->last<=5){
				$startnum=1;
				$lastnum=$page->last;
			}else{
				if($currentPage-2<=1){
					$startnum=1;
					$lastnum=5;
				}else{
					$startnum=$currentPage-2<=1?1:$currentPage-2;
					$startnum=$currentPage-2>=$page->last-4?$page->last-4:$currentPage-2;
					$lastnum=$currentPage+2>=$page->last?$page->last:$currentPage+2;
				}
			}
			$this->view->setVar('lastnum',$lastnum);
			$this->view->setVar('startnum',$startnum);
			$this->view->setVar('currentPage',$currentPage);
			$this->view->setVar('page',$page);
			$this->view->setVar('operation','memberlist');
		}
		public function addAction(){
			$this->view->setVar('operation','memberadd');
		}
		public function doaddAction(){
			$member=new Member();
			$data=$this->request->getPost();
			$data['password']=sha1($data['password']);
			$data['create_time']=date('Y-m-d H:i:s',time());
			if($member->save($data)){
				$this->response->redirect('member/');
			}else{
				echo "添加不成功，请检查输入！";
			}
		}
		public function editAction(){
			$id=$_GET['id'];
			$memberone=Member::findFirst($id);
			$this->view->setVar('memberone',$memberone);
		}
		public function doeditAction(){
			$data=$this->request->getPost();
			$memberone=Member::findFirst($data['id']);
			unset($data['id']);
			if(empty($data['password'])){
				unset($data['password']);
			}else{
				$data['password']=sha1($data['password']);
			}
			if($memberone->save($data)){
				$this->response->redirect('member/');
			}else{
				echo "编辑不成功，请检查输入！";
			}
		}
		public function delAction(){
			$id=$_GET['id'];
			$phql="delete from Member where id=:id:";
			$data=array(
					'id'=>$id
			);
			$result=$this->modelsManager->executeQuery($phql,$data);
			if($result->success()){
				$this->response->redirect('/member');
			}else{
				foreach ($result->getMessages() as $message) {
					echo $message->getMessage(), "<br/>";
				}
			}
		}
	}
?>