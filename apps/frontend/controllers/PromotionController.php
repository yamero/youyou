<?php
    class PromotionController extends ControllerBase{
        protected $layout='sidebarp';
        //推荐说明
        public function indexAction(){
            $this->view->setVars(array(
                'operation'=>'explain'
            ));
        }
        //奖励排行
        public function rewardrankAction(){
            $this->view->setVars(array(
                'operation'=>'rewardrank'
            ));
        }
        //我要推荐
        public function promoteAction(){
            $condition="id=".$this->session->get('uid');
            $user=User::findFirst(array($condition));
            $link="http://you.phalcon.com/index/index/".$user->promote;
            $this->view->setVars(array(
                'link'=>$link,
                'operation'=>'promote'
            ));
        }
        //推荐记录
        public function promoterecordAction(){
            //我
            $condition="id=".$this->session->get('uid');
            $user=User::findFirst(array($condition));
            //我推荐的人
            $condition="master_id=".$this->session->get('uid');
            $recommendUser=User::find(array($condition));
            $personNum=$recommendUser->count();
            //我的推荐人
            $condition="id=".$user->master_id;
            $promoteUser=User::findFirst(array($condition));
            $this->view->setVars(array(
                'puser'=>$promoteUser,
                'ruser'=>$recommendUser,
                'rcount'=>$personNum,
                'operation'=>'promoterecord'
            ));
        }
    }