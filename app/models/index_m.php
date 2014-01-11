<?php
/**
 * 首页的Model
 */

class Index_m extends SB_Model{
    function __construct (){
        parent::__construct();
    }

    // 获取可以展示的所有高校
    public function get_all_university(){
        $this->db->select('*')
        ->from('university')
        ->where('status',1);
        $query=$this->db->get();
        return $query->result_array();
    }

    //获取所有省份信息
    public function get_all_province(){
        $result = array();
        $this->db->select('provs_id, provs_name')
        ->from('province');
        $query = $this->db->get();
        if($query->num_rows>0){
            $res = $query->result_array();
        }
        if(is_array($res) && !empty($res)){
            foreach($res as $one){
                $result[$one['provs_id']] = $one;
            }
        }
        return $result;
    }
}