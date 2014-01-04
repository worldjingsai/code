<?php
/**
 * 高校相关信息Model
 */
class Univs_m extends SB_Model{
    function __construct(){
        parent::__construct();
        $this->load->library('myclass');
    }
    
    // 根据高校ID获取高校基本信息
    public function get_univs_info_by_univs_id($univs_id){
        $query = $this->db->select('*')
        ->from('university')
        ->where('status',1)
        ->where('univs_id', intval($univs_id));
        return $query->row_array();
    }
}