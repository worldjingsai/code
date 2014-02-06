<?php
/**
 * 文章相关操作的Model
 */

class Article_m extends SB_Model{

    const TYPE_UNIVS = 1; // 学校类型
    const TYPE_CONTEST = 2; // 竞赛类型


    public $tb = 'article';
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
     * 得到一个
     * @param int $cid
     */
    public function get($aid){
        $this->db->select('*');
        $query = $this->db->where('article_id',$aid)->where('status',1)->get($this->tb);
        return $query->row_array();
    }

    /**
     *
     * @param int $article_id
     * @param array $data
     */
    public function update($article_id, $data){
        $this->db->where('article_id',$article_id);
        $this->db->update($this->tb, $data);
        return $this->db->affected_rows();
    }
    /*
     * 获取所有的文章
     */
    public function get_all_contest($article_type, $type_id, $column_id, $page, $limit){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('article_type',$article_type)->where('type_id', $type_id)->where('column_id', $column_id)->where('status',1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * 获取最后一个
     */
    public function getLast($article_type, $type_id, $column_id)
    {
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('article_type',$article_type)->where('type_id', $type_id)->where('column_id', $column_id)->where('status',1);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }
}