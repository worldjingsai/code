<?php
/**
 * 文章相关操作的Model
 */

class Contest_menu_m extends SB_Model{

    const STATUS_NORMAL = 1; // 正常状态

    public $tb = 'contest_menu';
    function __construct(){
        parent::__construct();
    }
    function add($data){
        if($this->db->insert($this->tb, $data))
        {
            return $this->db->insert_id();
        } else
        {
            return false;
        }
    }

    /**
     * 根据ID获取菜单
     * @param int $id
     */
    public function get($id){
        $this->db->select('*');
        $query = $this->db->where('id',$id)->get($this->tb);
        return $query->row_array();
    }

    /**
     * 根据竞赛ID获取菜单
     * @param int $contest_id
     */
    public function list_colume_by_contestid($contest_id)
    {
        $colums = array();
        $this->db->select('*');
        $query = $this->db->where('contest_id',$contest_id)->where('status', self::STATUS_NORMAL)->get($this->tb);
        if ($query->num_rows() > 0) {
            $menu = $query->result_array();
            foreach ($menu as $m) {
                $colums[$m['menu_id']] = $m['menu_name'];
            }
        }
        return $colums;
    }

    /**
     * 根据竞赛ID和显示的MENUID获取菜单
     * @param int $contest_id
     * @param int $menu_id
     */
    public function list_by_contestid_menuid($contest_id, $menu_id)
    {
        $this->db->select('*');
        $query = $this->db->where('contest_id',$contest_id)->where('menu_id', $menu_id)->where('status', self::STATUS_NORMAL)->get($this->tb);
        return $query->row_array();
    }

    /**
     * 更新一个菜单
     * @param int $id
     * @param array $data
     */
    public function update($id, $data){
            $this->db->where('id',$id);
            $this->db->update($this->tb, $data);
            return $this->db->affected_rows();
    }

    /**
     * 删除一个菜单
     * @param int $id
     */
    public function del($id) {
        $this->db->where('id',$id);
        $this->db->delete($this->tb);
        return $this->db->affected_rows();
    }
}