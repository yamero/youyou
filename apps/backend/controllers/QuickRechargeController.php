<?php
class QuickRechargeController extends ControllerBase{
    public function indexAction($pageNum){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $currentPage=(int)empty($pageNum)?1:$pageNum;
        $config=Config::findFirst();//取出站点配置信息
        $phql="select qr.id,qr.rechargeway,qr.coin,qr.money,qr.trade_num,qr.pay_status,qr.create_time,u.account from QuickRecharge as qr join User as u";
        $quickrecharge=$this->modelsManager->executeQuery($phql);
        $paginator = new \Phalcon\Paginator\Adapter\Model(
            array(
                "data" => $quickrecharge,
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
            'operation'=>'quickrechargelist'
        ));
    }
    public function editAction($id){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $quickrecharge=QuickRecharge::findFirst($id);
        $this->view->setVars(array(
            'quickrecharge'=>$quickrecharge
        ));
    }
    public function doeditAction(){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $data=$this->request->getPost();
        $this->db->begin();
        $quickrecharge=QuickRecharge::findFirst($data['id']);
        $user=User::findFirst($quickrecharge->user_id);
        unset($data['id']);
        if(!$quickrecharge->save($data)){
            $this->db->rollback();
            return "<script>alert('操作失败，请重新操作！');history.go(-1);</script>";
        }
        if($data['pay_status']==2){//若为已支付状态，就更新会员金币数
            $coin=$user->gcoin + $quickrecharge->coin;
            if(!$user->save(array("gcoin"=>$coin))){
                $this->db->rollback();
                return "<script>alert('操作失败，请重新操作！');history.go(-1);</script>";
            }
        }
        $this->db->commit();
        $this->response->redirect('/quickrecharge/index');
    }
    public function delAction($id){
        if(!$this->session->has('uname')){//判断管理员是否登录，没登录就定位到登录页面
            $this->response->redirect('/login');
        }
        $quickrecharge=QuickRecharge::findFirst($id);
        if($quickrecharge->delete()){
            $this->response->redirect('/quickrecharge/index');
        }else{
            return "<script>alert('删除失败，请重新删除！');history.go(-1);</script>";
        }
    }
}
?>