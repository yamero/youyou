<?php
    class QuickRecharge extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_quick_recharge";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
        }
    }
?>