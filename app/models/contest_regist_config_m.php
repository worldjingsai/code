<?php
/**
 * 竞赛相关操作的Model
 */

class Contest_regist_config_m extends SB_Model{

    const TYPE_ARTICLE  = 1;  // 文章形式
    const TYPE_REGIST   = 2;  // 报名系统方式
    const TYPE_TEMPLATE = 3; // 模板报名方式，子竞赛需要继承此模板
    const STATUS_NORMAL = 1;  // 正常
    const STATUS_EXPIRE = -1;  // 失效

    static $default_t =array(
        't1' => array('团队名称', '', 1),
        't2' => array('指导老师', '', 1),
        't3' => array('队长姓名', '', 1),
        't4' => array('队长联系电话', '', 1),
        't5' => array('团队级别', '请填写：专科组、本科组、研究生组', 1),
        't6' => array('', '', 0),
        't7' => array('', '', 0),
        't8' => array('', '', 0),
        't9' => array('', '', 0),
        't10' => array('', '', 0)
        );

    static $default_m =array(
        'm1' => array('姓名', '' ,1),
        'm2' => array('性别', '填写：男、女' ,1),
        'm3' => array('学校名称', '' ,1),
        'm4' => array('专业', '' ,1),
        'm5' => array('年龄', '' ,1),
        'm6' => array('联系电话', '' ,1),
        'm7' => array('电子邮件', '' ,1),
        'm8' => array('QQ', '' ,1),
        'm9' => array('', '' ,0),
        'm10' => array('', '' ,0),
        'm11' => array('', '' ,0),
        'm12' => array('', '' ,0),
        'm13' => array('', '' ,0),
        'm14' => array('', '' ,0),
        'm15' => array('', '' ,0),
        'm16' => array('', '' ,0),
        'm17' => array('', '' ,0),
        'm18' => array('', '' ,0),
        'm19' => array('', '' ,0),
        'm20' => array('', '' ,0),
        );

    static $default_r =array(
        'r1' => array('团队组别', '大专组|本科组|研究生组' ,0),
        'r2' => array('题目选择', 'A题|B题|C题' ,0)
    );

    public $tb = 'contest_regist_config';

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

    /**
     * 根据ID得到正常的一个竞赛正常的配置
     * @param int $id
     */
    public function get_normal($contest_id){
        $this->db->select('*');
        $query = $this->db->where('contest_id', $contest_id)->where('status',1)->get($this->tb);
        return $query->row_array();
    }


    /**
     * 根据ID得到正常的一个竞赛正常的配置
     * @param int $id
     */
    public function get_by_cid_session($contest_id, $session){
        $this->db->select('*');
        $query = $this->db->where('contest_id', $contest_id)->where('session', $session)->where('status',1)->get($this->tb);
        return $query->row_array();
    }


    /**
     * 根据ID得到正常的一个竞赛正常的配置
     * @param int $id
     */
    public function get_team_number($contest_id){
        do {
            $conf = $this->get_normal($contest_id);
            if (!$conf) {
                return false;
                break;
            }
            $nextNumber = $conf['current_number'] + 1;
            $data = array('current_number' => $nextNumber);

            $this->db->where('id',$conf['id'])->where('current_number', $conf['current_number']);
            $this->db->update($this->tb, $data);
            $res =  $this->db->affected_rows();
        } while(!$res);

        $number = $nextNumber;
        if(!empty($conf['number_width']) && strlen($number)<$conf['number_width']);
        {
            $number = str_pad($number, $conf['number_width'], '0', STR_PAD_LEFT);
        }
        return $number;
    }

    /**
     * 根据id得到一个
     * @param int $id
     */
    public function get($id){
        $this->db->select('*');
        $query = $this->db->where('id',$id)->get($this->tb);
        return $query->row_array();
    }


    /**
     * 更新一个竞赛
     * @param int $contest_id
     * @param array $data
     */
    public function update($id, $data) {
        $this->db->where('id',$id);
        $this->db->update($this->tb, $data);
        return $this->db->affected_rows();
    }

    /**
     * 更新一个竞赛为过期
     * @param int $contest_id
     * @param array $data
     */
    public function updateExpire($contest_id) {
        $this->db->where('contest_id',$contest_id);
        $this->db->update($this->tb, array('status' => self::STATUS_EXPIRE));
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

}