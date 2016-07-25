<?php
    class Shop extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_shop";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
        }
    }
?>