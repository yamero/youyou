<?php
    class User extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_user";
        }
        public function initialize(){
            $this->hasMany('id', 'Exchange', 'user_id');
            $this->hasMany('id', 'BankApply', 'user_id');
            $this->hasMany('id', 'BuyReleaseBycard', 'user_id');
            $this->hasMany('id', 'BuyReleaseBycoin', 'user_id');
            $this->hasMany('id', 'Shop', 'user_id');
            $this->hasMany('id', 'TaoBao', 'user_id');
            $this->hasMany('id', 'Upgradetb', 'user_id');
            $this->hasMany('id', 'Upgradetbv2', 'user_id');
            $this->hasMany('id', 'QuickRecharge', 'user_id');
            $this->hasMany('id', 'TaoBaoLevel', 'user_id');
        }
    }
?>