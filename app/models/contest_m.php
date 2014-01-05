<?php
/**
 * 竞赛相关操作的Model
 */

class Contest_m extends SB_Model{

    const COLUM_ABOUT = 1;
    const COLUM_NOTICE = 2;
    const COLUM_PROBLEM = 3;
    const COLUM_WINNER =4;
    static $columNames = array(
        self::COLUM_ABOUT => '竞赛简介',
        self::COLUM_NOTICE => '竞赛通知',
        self::COLUM_PROBLEM => '赛题发布',
        self::COLUM_WINNER => '获奖名单',
    );
    
    public $tb = 'contest';
    function __construct(){
        parent::__construct();
    }
    function add($data){
        return $this->db->insert($this->tb, $data);
    }
    function check_url($univs_id, $url){
        $query = $this->db->get_where($this->tb, array('univs_id'=>$univs_id, 'url'=>$url));
        return $query->row_array();
    }
    
    /*
     * 获取所有的竞赛明细
     */
    public function get_all_contest($univs_id, $page, $limit){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('univs_id',$univs_id)->where('status',1)
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }
}