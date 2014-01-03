<?php
/**
 * 首页的Model
 */

class Index_m extends SB_Model{
    function __construct (){
        parent::__construct();
    }
    //tag贴子列表 
    public function get_tag_forums_list($page,$limit,$tag_title){
            $tag = $this->db->select('tag_id')->where('tag_title',$tag_title)->get('tags')->row_array();
            if($tag){
                    $this->db->select('a.fid, a.title, a.comments, a.updatetime, b.uid, b.username, b.avatar')
                    ->from('forums a')
                    ->join('users b','a.uid=b.uid')
                    ->join('tags_relation c','a.fid=c.fid')
                    ->join('tags d','c.tag_id=d.tag_id')
                    ->where('d.tag_id',$tag['tag_id'])
                    ->limit($limit,$page);
                    $query=$this->db->get();
                    return $query->result_array();
            } else {
                    return false;
            }
    }
    //获取所有省份信息
    public function get_all_province(){
        $this->db->select('provs_id, provs_name')
        ->from('province');
        $query = $this->db->get();
        if($query->num_rows>0){
            return $query->result_array();
        }
    }
}