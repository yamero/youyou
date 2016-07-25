<?php
    class TaoBaoLevel extends \Phalcon\Mvc\Model{
        public function getSource(){
            return "yy_taobao_level";
        }
        public function initialize(){
            $this->belongsTo('tb_id', 'TaoBao', 'id');
            $this->belongsTo('user_id', 'User', 'id');
        }
    }
?>