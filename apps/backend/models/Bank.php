<?php
    class Bank extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_bank";
        }
        public function initialize(){
            $this->hasMany('id', 'Exchange', 'bank_id');
            $this->hasMany('id', 'BankApply', 'bank_id');
        }
    }
?>