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
        $this->db->select('*');
        $query = $this->db->where('univs_id',$univs_id)->where('status',1)->get('university');
        return $query->row_array();
    }

    /**
     * 根据高校昵称获取高校基本信息
     */
    public function get_univs_info_by_univs_short_name($short_name){
        $this->db->select('*');
        $query = $this->db->where('short_name',$short_name)->where('status',1)->get('university');
        return $query->row_array();
    }
}