<?php
/**
 * 队伍的Model
 */

class Member_column_m extends SB_Model{

    const IS_VALID_YES = 1;
    const IS_VALID_NO = 0;
    const IS_ENTER_YES = 1;
    const IS_ENTER_NO = 0;

    const STATUS_NORMAL = 1;
    const STATUS_DEL = -1;

    public $tb = 'member_column';

    function __construct(){
        parent::__construct();
    }

    function add($data)
    {
        if($this->db->insert($this->tb, $data)){
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

    public function get($id)
    {
        $this->db->select('*');
        $query = $this->db->where('member_id',$id)->get($this->tb);
        return $query->row_array();
    }

    /**
     * 根据团队id获取报名信息
     *
     * @param unknown $team_id
     */
    public function list_by_team_id($team_id)
    {
        $this->db->select('*');
        $this->db->order_by('member_id','asc');
        if (is_array($team_id)) {
        	$this->db->where_in('team_id',$team_id);
        } else {
        	$this->db->where('team_id',$team_id);
        }
        $query = $this->db->get($this->tb);
        return $query->result_array();
    }


    /**
     * 更新一个团队
     * @param int $id
     * @param array $data
     */
    public function update($id, $data)
    {
        $this->db->where('member_id', $id);
        $this->db->update($this->tb, $data);
        return $this->db->affected_rows();
    }

    /**
     * 根据团队ID删除数据
     * @param int $team_id
     */
    public function delete_ty_team_id($team_id)
    {
        $this->db->where('team_id', $team_id);
        $this->db->delete($this->tb);
        return $this->db->affected_rows();
    }

}