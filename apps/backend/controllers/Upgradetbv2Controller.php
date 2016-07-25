<?php
class Upgradetbv2Controller extends ControllerBase{
    public function indexAction($pageNum){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $currentPage=(int)empty($pageNum)?1:$pageNum;
        $config=Config::findFirst();//取出站点配置信息
        $phql="select ut2.id,ut2.verify_status,ut2.create_time,u.account,tb.name from Upgradetbv2 as ut2 join User as u join TaoBao as tb";
        $upgradetbv2=$this->modelsManager->executeQuery($phql);
        $paginator = new \Phalcon\Paginator\Adapter\Model(
            array(
                "data" => $upgradetbv2,
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
            'operation'=>'upgradetbv2list'
        ));
    }
    public function editAction($id){
        $upgradetbv2=Upgradetbv2::findFirst($id);
        $this->view->setVars(array(
            'upgradetbv2'=>$upgradetbv2
        ));
    }
    public function doeditAction(){
        $data=$this->request->getPost();
        $upgradetbv2=Upgradetbv2::findFirst($data['id']);
        unset($data['id']);
        if($data['verify_status']==3){
            $tb=TaoBao::findFirst($upgradetbv2->tb_id);
            if($tb->save(array("level"=>3))){
                if($upgradetbv2->save($data)){
                    $this->response->redirect('/upgradetbv2/index');
                }else{
                    echo "编辑不成功，请检查输入！";
                }
            }else{
                echo "审核不成功，请稍候重试！";
            }
        }
    }
    public function delAction($id){
        $upgradetbv2=Upgradetbv2::findFirst($id);
        if($upgradetbv2->delete()){
            $this->response->redirect('/upgradetbv2/index');
        }else{
            echo "抱歉，无法删除！";
        }
    }
}
?>