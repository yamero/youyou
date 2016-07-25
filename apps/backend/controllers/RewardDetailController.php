<?php
    class RewardDetailController extends ControllerBase{
        public function editAction(){
            $rewarddetail=RewardDetail::findFirst();
            $this->view->setVars(array(
                'rewarddetail'=>$rewarddetail,
                'operation'=>'rewarddetail'
            ));
        }
        public function doeditAction(){
            $data=$this->request->getPost();
            $rewarddetail=RewardDetail::findFirst($data['id']);
            unset($data['id']);
            if($rewarddetail->save($data)){
                $this->response->redirect('/rewarddetail/edit');
            }else{
                echo "编辑失败，请检查输入！";
            }
        }
    }
?>