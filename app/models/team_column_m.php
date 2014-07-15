<?php
/**
 * 队伍的Model
 */

class Team_column_m extends SB_Model{

    const IS_VALID_YES = 1;
    const IS_VALID_NO = 0;
    const IS_ENTER_YES = 1;
    const IS_ENTER_NO = 0;

    const STATUS_NORMAL = 1;
    const STATUS_DEL = -1;

    public $tb = 'team_column';

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
        $query = $this->db->where('team_id',$id)->get($this->tb);
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
}