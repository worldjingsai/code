<?php
/**
 * 文章相关操作的Model
 */

class Article_content_m extends SB_Model{

    const TYPE_UNIVS = 1; // 学校类型
    const TYPE_CONTEST = 2; // 竞赛类型
    
    
    public $tb = 'article_content';
    function __construct(){
        parent::__construct();
    }
    function add($data){
        return $this->db->insert($this->tb, $data);
    }

    // article_id获取文章内容
    public function get($article_id){
        $this->db->select('*');
        $query = $this->db->where('article_id',$article_id)->get($this->tb);
        return $query->row_array();
    }
}