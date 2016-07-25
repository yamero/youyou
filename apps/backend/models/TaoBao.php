<?php
    class TaoBao extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_taobao";
        }
        public function initialize(){
            $this->belongsTo('user_id', 'User', 'id');
            $this->hasMany('id', 'Upgradetbv2', 'tb_id');
            $this->hasMany('id', 'TaoBaoLevel', 'tb_id');
        }
    }
?>