<?php
    class BuyReleaseBycard extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_buyrelease_bycard";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
        }
    }
?>