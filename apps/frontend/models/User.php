<?php
    class User extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_user";
        }
        public function initialize(){
            $this->hasMany('id', 'BuyReleaseBycard', 'user_id');
            $this->hasMany('id', 'BuyReleaseBycoin', 'user_id');
            $this->hasMany('id', 'Task', 'user_id');
        }
    }
?>