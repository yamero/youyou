<?php
    class Task extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_task";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
            $this->belongsTo('city', 'China', 'Id');
        }
    }
?>