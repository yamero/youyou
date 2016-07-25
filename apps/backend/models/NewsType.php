<?php
    class NewsType extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_news_type";
        }
        public function initialize(){
            $this->hasMany('id', 'News', 'type');
        }
    }
?>