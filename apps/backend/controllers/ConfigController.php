<?php
    class ConfigController extends ControllerBase{
        public function editAction(){
            $config=Config::findFirst();
            $this->view->setVars(array(
                'config'=>$config,
                'operation'=>'config'
            ));
        }
        public function doeditAction(){
            $data=$this->request->getPost();
            $config=Config::findFirst($data['id']);
            unset($data['id']);
            if($config->save($data)){
                $this->response->redirect('/config/edit');
            }else{
                echo "配置失败，请检查输入！";
            }
        }
    }
?>