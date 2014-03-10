<?php
/**
 * 队伍的Model
 */

class Team_m extends SB_Model{

    const IS_VALID_YES = 1;
    const IS_VALID_NO = 0;
    const IS_ENTER_YES = 1;
    const IS_ENTER_NO = 0;

    const STATUS_NORMAL = 1;
    const STATUS_DEL = -1;

    public $tb = 'team';

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

    public function get($id){
        $this->db->select('*');
        $query = $this->db->where('team_id',$id)->where('status',1)->get($this->tb);
        return $query->row_array();
    }

    /**
     * 更新一个团队
     * @param int $id
     * @param array $data
     */
    public function update($id, $data) {
        $this->db->where('team_id', $id);
        $this->db->update($this->tb, $data);
        return $this->db->affected_rows();
    }

    /**
     * 根据用户得到参赛信息
     * @param int $uid
     * @param int $cid
     * @param int $session
     */
    public function get_by_user_contest_session($uid, $cid, $session)
    {
        $this->db->select('*');
        $query = $this->db->where('contest_id', $cid)->where('session', $session)->where('create_user_id', $uid)->where('status', 1)->get($this->tb);
        return $query->row_array();
    }
    
    
    /**
     * 根据contest_id获取竞赛和届数获取列表
     */
    public function listByCidAndSession($cid, $session){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('team_id','asc');
        $this->db->where('contest_id', $cid)->where('session', $session)->where('status',1);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }
}