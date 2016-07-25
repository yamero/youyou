<?php
    class News extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_news";
        }
        public function initialize(){
            $this->belongsTo('type', 'NewsType', 'id');
        }
    }
?>