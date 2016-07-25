<?php
    class Upgradetbv2 extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_upgradetbv2";
        }
        public function initialize(){
            $this->belongsTo('tb_id', 'TaoBao', 'id');
        }
    }
?>