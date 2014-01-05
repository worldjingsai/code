<?php
/**
 * 竞赛相关操作的Model
 */

class University_contest_m extends SB_Model{

    public $tb = 'university_contest';
    function __construct(){
        parent::__construct();
    }
    function add($data){
        return $this->db->insert($this->tb, $data);
    }

    /*
     * 获取所有的竞赛明细
     */
    public function lists($univs_id, $page = 1, $limit = 15){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('univs_id',$univs_id)->where('status',1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }
}