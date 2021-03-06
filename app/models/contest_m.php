<?php
/**
 * 竞赛相关操作的Model
 */

class Contest_m extends SB_Model{

    const COLUM_NOTICE  = 1;
    const COLUM_ABOUT   = 2;
    const COLUM_PROBLEM = 3;
    const COLUM_WINNER  = 4;
    static $columNames = array(
    self::COLUM_NOTICE => '竞赛通知',
    self::COLUM_ABOUT => '竞赛简介',
    self::COLUM_PROBLEM => '赛题发布',
    self::COLUM_WINNER => '获奖名单',
    );

    const TYPE_SCHOOL = 1;
    const TYPE_PUBLIC = 2;

    const LEVEL_SCHOOL = 1;
    const LEVEL_PROVINCE = 2;
    const LEVEL_NATION = 3;
    const LEVEL_INTERNATION = 4;
    static $leverNames = array(
    self::LEVEL_SCHOOL => '校内竞赛',
    self::LEVEL_PROVINCE => '省级竞赛',
    self::LEVEL_NATION => '全国竞赛',
    self::LEVEL_INTERNATION => '国际竞赛'
    );

    static $typeNames = array(
        1 => '数学建模',
        2=>'ACM程序设计',
        3=>'创新创业',
        4=>'工科类竞赛',
        5=>'理科类竞赛',
        6=>'文体类竞赛',
        7=>'艺术类竞赛'
    );

    const STATUS_NORMAL = 1;
    const STATUS_DEL = -6;

    public $tb = 'contest';

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

    public function get($cid){
        $this->db->select('*');
        $query = $this->db->where('contest_id',$cid)->where('status',1)->get($this->tb);
        return $query->row_array();
    }

    public function get_contest_by_short_name($univs_id, $short_name){
        $this->db->select('*');

        if ($univs_id) {
            $query = $this->db->where('univs_id', $univs_id)->where('contest_url',$short_name)->where('status',1)->get($this->tb);;
        } else {
            $col = self::$leverNames;
            unset($col[self::LEVEL_SCHOOL]);
            $query = $this->db->where('contest_url',$short_name)->where('status',1)->where_in('contest_level', array_keys($col))->get($this->tb);
        }
        return $query->row_array();
    }

    /**
     * 更新一个竞赛
     * @param int $contest_id
     * @param array $data
     */
    public function update($contest_id, $data) {
        $this->db->where('contest_id',$contest_id);
        $this->db->update($this->tb, $data);
        return $this->db->affected_rows();
    }

    /**
     * 根据contest_id获取竞赛明细
     */
    public function listByCid($cid){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where_in('contest_id', $cid)->where('status',1);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * 根据contest_id获取竞赛明细
     */
    public function listPublic($offset, $limit){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $col = self::$leverNames;
        unset($col[self::LEVEL_SCHOOL]);

        $this->db->where_in('contest_level', array_keys($col))->where('status',1);

        $this->db->offset($offset);
        $this->db->limit($limit);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        }else{
            return false;
        }
    }

    /*
     * 获取所有的竞赛明细
     */
    public function get_all_contest($univs_id = 0, $offset =0, $limit = 20){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        if ($univs_id) {
            $this->db->where('univs_id',$univs_id);
        }
        $this->db->where('status',1);
        $this->db->offset($offset);
        $this->db->limit($limit);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /*
     * 获取所有的竞赛明细
    */
    public function list_all($offset =0, $limit = 20){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('status',1);
        $this->db->offset($offset);
        $this->db->limit($limit);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /*
     * 获取所有的竞赛明细
    */
    public function count_all(){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('status',1);

        $query = $this->db->get();
        if ($query->result()) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    /**
     * 获取创建的竞赛
     * @param unknown $page
     * @param unknown $limit
     */
    public function get_create_by_uid($uid, $page, $limit)
    {
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('create_user_id',$uid)->where('status',1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * 获取参加的竞赛
     * @param unknown $page
     * @param unknown $limit
     */
    public function get_enter_by_uid($uid, $page, $limit)
    {
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('create_user_id',$uid)->where('status',1);
        $this->db->limit($limit,$page);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * 检测某项竞赛在某个高校是否存在
     * @param  string $uri 输入的竞赛的URI
     * @param  int    $univs_id 高校ID
     */
    public function check_contest_exist_in_univs($uri, $univs_id){
       $query = $this->db->get_where($this->tb, array('univs_id'=>$univs_id, 'contest_url'=>$uri, 'contest_level'=> 1));
       $data  = $query->row_array();
       if(!empty($data)){
           return true;
       }
       return false;
    }

    /**
     * 检测某项竞赛在全国是否存在
     * @param  string $uri 输入的竞赛的URI
     * @param  int    $univs_id 高校ID
     */
    public function check_contest_exist_in_nation($uri){
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->where('contest_url',$uri)->where_in('contest_level',array(2,3,4));
        $query = $this->db->get();
        $data  = $query->row_array();
        if(!empty($data)){
            return true;
        }
        return false;
    }


    /**
     * 获取栏目条目
     */
    public function count_contest($univs_id = 0, $cid = 0)
    {
        $this->db->select('contest_id');
        $this->db->where('status', self::STATUS_NORMAL);
        if($cid){
            $this->db->where('create_user_id', $cid);
        }
        if($univs_id) {
            $this->db->where('univs_id', $univs_id);
        }
        $query = $this->db->get($this->tb);
        if ($query->result()) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    /**
     * 获取子竞赛数
     */
    public function count_subcontest($cid = 0)
    {
        $this->db->select('contest_id');
        $this->db->where('status', self::STATUS_NORMAL);
        if($cid){
            $this->db->where('parent_id', $cid);
        }
        $query = $this->db->get($this->tb);
        if ($query->result()) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    /**
     * 获取子竞赛数
     */
    public function list_subcontest($cid = 0)
    {
        $this->db->select('a.*, b.*');
        $this->db->from($this->tb .' a');
        $this->db->join('university b', 'b.univs_id = a.univs_id');
        $this->db->order_by('a.univs_id','asc');
        $this->db->where('a.parent_id',$cid)->where('a.status',1);

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
    public function get_by_parentid($pid, $page, $limit)
    {
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->order_by('create_time','desc');
        $this->db->where('parent_id',$pid)->where('status',1);
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
     */
    public function get_ids_by_parentid($pid)
    {
        $this->db->select('*');
        $this->db->from($this->tb);
        $this->db->where('parent_id',$pid)->where('status',1);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            $result = $query->result_array();
            $ids = array();
            foreach ($result as $r) {
                $ids[$r['contest_id']] = $r['contest_id'];
            }
            return $ids;
        } else {
            return false;
        }
    }

    /**
     * 根据cid递归获取所有的子竞赛id
     */
    public function get_all_son_ids($cid, &$ids = array())
    {
        if (!$cid) {
            return array();
        }

        if ($this->count_subcontest($cid)) {
            $sonids = $this->get_ids_by_parentid($cid);
            $sons = $sonids;
            $ids = array_merge($ids, $sonids);

            foreach ($sons as $i)
            {
                $this->get_all_son_ids($i, $ids);
            }
        }
        return $ids ? $ids : array();
    }
}