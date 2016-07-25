<?php
    class Exchange extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_exchange";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
            $this->belongsTo('bank_id', 'Bank', 'id');
        }
    }
?>