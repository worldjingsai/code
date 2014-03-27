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


    /**
     * 根据contestid和sessionid获取参数总数
     */
    public function count_team($cid = 0, $session = 0){
        $this->db->select('team_id');
        $this->db->where('status', self::STATUS_NORMAL);
        if($cid){
            $this->db->where('contest_id', $cid);
        }
        if($session) {
            $this->db->where('session', $session);
        }
        $query = $this->db->get($this->tb);
        if($query->result()){
            return $query->num_rows();
        } else {
            return '0';
        }
    }

    /**
     * 根据contestid和sessionid获取参数总数
     */
    public function count_by_uid($uid = 0){
        $this->db->select('team_id');
        $this->db->where('status', self::STATUS_NORMAL);
        if($uid){
            $this->db->where('create_user_id', $uid);
        }

        $query = $this->db->get($this->tb);
        if($query->result()){
            return $query->num_rows();
        } else {
            return '0';
        }
    }


    /**
     * 根据uid获取参与的竞赛
     */
    public function list_by_uid($uid = 0, $page, $limit){
        $this->db->select('a.*, b.*');
        $this->db->from($this->tb .' a');
        $this->db->join('contest b', 'b.contest_id = a.contest_id');
        $this->db->order_by('a.create_time','desc');
        $this->db->where('a.create_user_id',$uid)->where('a.status',1)->where('b.status', 1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }


    /**
     * 获取创建的竞赛
     * @param unknown $page
     * @param unknown $limit
     */
    public function get_by_cid_session($cid, $session, $page, $limit)
    {
        $this->db->select('a.*, b.username, b.uid');
        $this->db->from($this->tb .' a');
        $this->db->join('users b', 'b.uid = a.create_user_id');
        $this->db->order_by('create_time','desc');
        $this->db->where('a.contest_id',$cid)->where('a.session', $session)->where('a.status',1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * 获取详细的参赛列表
     * @param unknown $page
     * @param unknown $limit
     */
    public function get_detail_by_cid_session($cid, $session, $page, $limit)
    {
        $this->db->select('a.*, b.*');
        $this->db->from($this->tb .' a');
        $this->db->join('team_column b', 'b.team_id = a.team_id');
        $this->db->order_by('create_time','desc');
        $this->db->where('a.contest_id',$cid)->where('a.session', $session)->where('a.status',1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }
}