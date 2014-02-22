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
        $query = $this->db->where('univs_id',$univs_id)->get('university');
        return $query->row_array();
    }

    /**
     * 根据高校昵称获取高校基本信息
     */
    public function get_univs_info_by_univs_short_name($short_name){
        $this->db->select('*');
        $query = $this->db->where('short_name',$short_name)->get('university');
        return $query->row_array();
    }

    /**
     * 获取所有高校
     */
    public function get_all_univs_info(){
        $univs = $this->db->select('univs_id, univs_name,short_name,provs_id')->from('university')->where('status', 1)->group_by('provs_id')->get()->row_array();
        return $univs;
    }
}