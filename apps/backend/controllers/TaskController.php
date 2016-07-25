<?php
class TaskController extends ControllerBase{
    public function indexAction($pageNum){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $where='';
        $currentPage=(int)empty($pageNum)?1:$pageNum;
        $config=Config::findFirst();//取出站点配置信息
        if(empty($pageNum)){    //若第一次进入，则清空session显示全部记录
            $this->session->remove('taskSN');
            $this->session->remove('releaseName');
            $where='';
        }
        if($this->request->isPost()){   //若是通过post提交的请求就将post过来的表单值存入session
            $where='';
            foreach($this->request->getPost() as $k=>$v){
                $this->session->set($k,$v);
            }
        }
        if(!empty($this->session->get('taskSN'))){
            $where.=" and t.sn='".$this->session->get('taskSN')."'";
        }
        if(!empty($this->session->get('releaseName'))){
            $where.=" and u.account='".$this->session->get('releaseName')."'";
        }
        //$phql="select t.id,t.sn,t.platform,t.type,t.total_money,t.commission,t.keywords,t.img_thumb,t.create_time,u.account,s.shop from Task as t join User as u join Shop as s where 1=1".$where;
        $phql="select t.id,t.sn,t.platform,t.type,t.total_money,t.commission,t.keywords,t.img_thumb,t.create_time,u.account,s.shop from Task t,User u,Shop s where u.id=t.user_id and s.id=t.shop_id".$where;
        $task=$this->modelsManager->executeQuery($phql);
        $paginator = new \Phalcon\Paginator\Adapter\Model(
            array(
                "data" => $task,
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
            'operation'=>'tasklist'
        ));
    }
    public function editAction($id){
        $phql="select t.id,t.sn,t.platform,t.type,t.total_money,t.commission,t.keywords,t.img_thumb,t.detail,t.add_num,t.add_title,t.confirm_type,t.confirm_time,t.comment_time,t.isaddcomment,t.false_talk,t.change_address,t.special_ask,t.remote_pay,t.isauth,t.auth_level,t.province,t.city,t.days,t.return_way,t.return_time,t.auto_cancel,t.number,t.look_way,t.look_time,t.iscollect,t.isfocus,t.ismobile,t.auth_thumb,t.mobile_buy,t.create_time,u.account,s.shop from Task t,User u,Shop s where u.id=t.user_id and s.id=t.shop_id and t.id=".$id;
        $task=$this->modelsManager->executeQuery($phql);
        $this->view->setVar('task',$task);
    }
    public function doeditAction(){
        $data=$this->request->getPost();
        $task=Task::findFirst($data['id']);
        unset($data['id']);
        if($task->save($data)){
            $this->response->redirect('/task/index');
        }else{
            echo "编辑不成功，请检查输入！";
        }
    }
    public function delAction($id){
        $shop=Shop::findFirst($id);
        if($shop->delete()){
            $this->response->redirect('/shop/index');
        }else{
            echo "抱歉，无法删除！";
        }
    }
}
?>