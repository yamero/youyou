<?php
    class VideoController extends ControllerBase{
        /**
         * 视频列表
         * @return [type] [description]
         */
        public function indexAction()
        {
            $currentPage=(int)empty($_GET["page"])?1:$_GET["page"];
			$phql = "SELECT v.id,v.title,v.blurb,v.url,v.create_time,n.name FROM Video as v JOIN NewsType as n WHERE v.ntid = n.id";
			$Video=$this->modelsManager->executeQuery($phql);
			$paginator = new \Phalcon\Paginator\Adapter\Model(
					array(
							"data" => $Video,
							"limit"=> 1,
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
            $this->view->setVar('operation','video');
            $this->view->setVar('lastnum',$lastnum);
            $this->view->setVar('startnum',$startnum);
            $this->view->setVar('currentPage',$currentPage);
			$this->view->setVar('page',$page);
        }
        
        /**
         * 添加视频
         */
        
        public function addAction()
        {
        	if (!empty($_POST))
        	{
                $ntid=$this->request->getPost('ntid');
                $title=$this->request->getPost('title');
                $blurb=$this->request->getPost('blurb');
                $url=$this->request->getPost('url');
                if($title == '' || $url == ''){
                    echo "<script> window.alert('填写完整');location.href='' </script>";
                    exit;
                }
        		$phql="INSERT INTO Video (title,blurb,url,ntid,create_time) values (:title:,:blurb:,:url:,:ntid:,:create_time:)";
        		$res = $this->modelsManager->executeQuery($phql,
				    array(
				        'title'         => $title,
				        'blurb'         => $blurb,
                        'url'           => $url,
                        'ntid'          => $ntid,
				        'create_time'   => time()
				    )
				);
        		if ($res->success()) {
        			$this->response->redirect('Video/index');
        		}else{
        			foreach ($res->getMessages() as $message) {
        				echo $message->getMessage(), "<br/>";
        			}
        		}
        	}else{
                $phql="SELECT nt.id,nt.name FROM NewsType as nt";
                $newsType=$this->modelsManager->executeQuery($phql);
                $this->view->setVar('operation','add');
                $this->view->setVar('newsType',$newsType);
            }
        }
        /**
         * 修改视频
         * @return [type] [description]
         */
        public function editAction()
        {
            if (empty($_POST)) {
                # code...
                $phql = "SELECT v.id,v.title,v.blurb,v.url,v.create_time FROM Video as v  WHERE v.id = :d:";
                $res = $this->modelsManager->executeQuery(
                    $phql,
                    array('d' => $_GET['id'] )
                    );
                $phql="SELECT nt.id,nt.name FROM NewsType as nt";
                $newsType=$this->modelsManager->executeQuery($phql);
                $this->view->setVar('operation','edit');
                $this->view->setVar('newsType',$newsType);
                $this->view->setVar('info',$res);
            }else{
                $id=$this->request->getPost('id');
                $ntid=$this->request->getPost('ntid');
                $title=$this->request->getPost('title');
                $blurb=$this->request->getPost('blurb');
                $url=$this->request->getPost('url');
                $phql="UPDATE Video SET title=:title:,blurb=:blurb:,url=:url:,ntid=:ntid:,create_time = :create_time: WHERE id=:d:";
                $res=$this->modelsManager->executeQuery($phql,
                    array(
                    'd'           => $id,
                    'title'       => $title,
                    'blurb'       => $blurb,
                    'ntid'        => $ntid,
                    'url'         => $url,
                    'create_time' => time()
                ));
                if($res){
                    $this->response->redirect('Video/index');
                }else{
                    foreach ($res->getMessage as $message) {
                        echo $message->getMessage(),'<br/>';
                        # code...
                    }
                }
            }

        }
        /**
         * 删除视频
         * @return [type] [description]
         */
        public function delAction(){

            $phql = "DELETE FROM Video WHERE id = :id:";
            $res = $this->modelsManager->executeQuery(
                $phql,
                array(
                    'id' => $_GET['id']
                )
            );
            if ($res) {
                $this->response->redirect('Video/index');
                # code...
            }else{
                foreach ($res->getMessage as $message) {
                    echo $message->getMessage(),'<br/>';
                    # code...
                }
            }
        }
    }
?>