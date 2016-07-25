<?php
    class China extends \Phalcon\Mvc\Model{
        public function initialize(){
            $this->hasMany('Id', 'Task', 'city');
        }
    }
?>