<?php
/**
 * 竞赛相关操作的Model
 */

class Univs_contest_m extends SB_Model{

    public $tb = 'university_contest';
    
    function __construct(){
        parent::__construct();
    }
    
    function add($data){
        if($this->db->insert($this->tb, $data)){
            return $this->db->insert_id();
        }else{
            return false;
        }
    }
}