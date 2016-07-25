<?php
    class BankApply extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_bank_apply";
        }
        public function initialize(){
            $this->belongsTo('bank_id', 'Bank', 'id');
        }
    }
?>