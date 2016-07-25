<?php
    class BuyReleaseBycoin extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_buyrelease_bycoin";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
        }
    }
?>