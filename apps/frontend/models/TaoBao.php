<?php
    class TaoBao extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_taobao";
        }
        public function initialize(){
            $this->hasMany('id', 'Upgradetbv2', 'tb_id');
        }
    }
?>