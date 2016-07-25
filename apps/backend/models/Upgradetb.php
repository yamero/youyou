<?php
    class Upgradetb extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_upgradetb";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
        }
    }
?>