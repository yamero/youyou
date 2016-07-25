<?php
    class NewsController extends ControllerBase{
        public function indexAction($page){
            if(!$this->session->has('uname')){  //判断管理员是否登录，没登录就定位到登录页面
                $this->response->redirect('login/index');
            }
            $currentPage=(int)empty($page)?1:$page;
            if(empty($pageNum)){    //若第一次进入，则清空session显示全部记录
                $this->session->set('title','');
                $this->session->set('type','');
            }
            $config=Config::findFirst();//取出站点配置信息
            $newsType=NewsType::find();//取出文章所有分类
            if($this->request->isPost()){//若是通过post提交的请求就将post过来的表单值存入session
                foreach($this->request->getPost() as $k=>$v){
                    $this->session->set($k,$v);
                }
            }
            if(!empty($this->session->get('title'))){
                $where.=" and n.title like '%".$this->session->get('title')."%'";
            }
            if(!empty($this->session->get('type'))){
                $where.=" and n.type=".$this->session->get('type');
            }
            $phql="select n.id,n.title,n.member,n.content,nt.name,n.modify_time from News as n join NewsType as nt where 1=1".$where;
            $news=$this->modelsManager->executeQuery($phql);
            $paginator = new \Phalcon\Paginator\Adapter\Model(
                array(
                    "data" => $news,
                    "limit"=> $config->record_num,
                    "page" => $currentPage
                )
            );
            $page = $paginator->getPaginate();
            if($page->last<=$config->page_offset*2+1){  //若要总页数小于或等于要显示的页码数，让起始页码等于1，结束页码等于总页数
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
                'newsType'=>$newsType,
                'operation'=>'newslist'
            ));
        }
        public function addAction(){
            $newsType=NewsType::find();//查询出文章所有分类
            $this->view->setVars(array(
                'newsType'=>$newsType,
                'operation'=>'newsadd'
            ));
        }
        public function doaddAction(){
            $news=new News();
            $data=$this->request->getPost();
            $data['modify_time']=date('Y-m-d H:i:s',time());
            if(empty($data['member'])){
                $data['member']=$this->session->get('uname');
            }
            if($this->request->hasFiles()){
                $config=Config::findFirst();//取出站点配置信息
                $datestr=date('Ymd',time());
                $path='../uploads/'.$datestr.'/';
                if(!file_exists($path)){
                    mkdir($path);
                }
                foreach($this->request->getUploadedFiles() as $file){
                    $imgUrl=date('YmdHis',time()).uniqid().'.'.$file->getExtension();
                    $file->moveTo($path.$imgUrl);
                    $data['thumb']=$config->img_domain.$datestr.'/'.$imgUrl;
                }
            }
            if($news->save($data)){
                $this->response->redirect('/news/index');
            }else{
                echo "添加失败，请检查输入！";
            }
        }
        public function editAction($id){
            $newsType=NewsType::find();//查询出文章所有分类
            $news=News::findFirst($id);//查询出要编辑的文章
            $this->view->setVars(array(
                'newsType'=>$newsType,
                'news'=>$news
            ));
        }
        public function doeditAction(){
            $data=$this->request->getPost();
            $news=News::findFirst($data['id']);
            unset($data['id']);
            $data['modify_time']=date('Y-m-d H:i:s',time());
            if(empty($data['member'])){
                $data['member']=$this->session->get('uname');
            }
            if($this->request->hasFiles()){
                $config=Config::findFirst();//取出站点配置信息
                $datestr=date('Ymd',time());//当天日期字符串
                $path='../uploads/'.$datestr.'/';
                if(!file_exists($path)){    //以当天日期字符串为文件夹名创建文件夹，用来存入今天上传的图片
                    mkdir($path);
                }
                foreach($this->request->getUploadedFiles() as $file){
                    $imgUrl=date('YmdHis',time()).uniqid().'.'.$file->getExtension();//生成唯一文件名
                    $file->moveTo($path.$imgUrl);//上传文件
                    $data['thumb']=$config->img_domain.$datestr.'/'.$imgUrl;//将图片路径存入数据表
                }
            }
            if($news->save($data)){
                $this->response->redirect('/news/index');
            }else{
               echo "编辑不成功，请检查输入！";
            }
        }
        public function delAction($id){
            $news=News::findFirst($id);
            if($news->delete()){
                $this->response->redirect('/news/index');
            }else{
                echo "抱歉，无法删除！";
            }
        }
    }
?>