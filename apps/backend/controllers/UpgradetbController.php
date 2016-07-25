<?php
class UpgradetbController extends ControllerBase{
    public function indexAction($pageNum){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $currentPage=(int)empty($pageNum)?1:$pageNum;
        $config=Config::findFirst();//取出站点配置信息
        $phql="select ut.id,ut.real_name,ut.idcard,ut.verify_status,ut.create_time,u.account from Upgradetb as ut join User as u";
        $upgradetb=$this->modelsManager->executeQuery($phql);
        $paginator = new \Phalcon\Paginator\Adapter\Model(
            array(
                "data" => $upgradetb,
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
            'operation'=>'upgradetblist'
        ));
    }
    public function editAction($id){
        $upgradetb=Upgradetb::findFirst($id);
        $this->view->setVar('upgradetb',$upgradetb);
    }
    public function doeditAction(){
        $data=$this->request->getPost();
        $upgradetb=Upgradetb::findFirst($data['id']);
        unset($data['id']);
        if($upgradetb->save($data)){
            $this->response->redirect('/upgradetb/index');
        }else{
           echo "编辑不成功，请检查输入！";
        }
    }
    public function delAction($id){
        $upgradetb=Upgradetb::findFirst($id);
        if($upgradetb->delete()){
            $this->response->redirect('/upgradetb/index');
        }else{
            echo "抱歉，无法删除！";
        }
    }
}
?>