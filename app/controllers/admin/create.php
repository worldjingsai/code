<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Create extends Admin_Controller{
    function __construct (){
        parent::__construct();
        $this->load->model('contest_m');
        $this->load->model('univs_m');
        $this->load->library('myclass');
        $this->load->model('user_m');

        if(!$this->auth->is_login ()){
            redirect('user/login');
        }
    }

    public function index($page = 1){
        $this->my($page);
    }

    /**
     * 创建省赛cumcm
     */
    public function cumcm($pid = '5')
    {

        $cid = 0;
        $this->db->select('*');
        $query = $this->db->where('provs_id', $pid)->get('province');
        $prov = $query->row_array();
        if(empty($prov)){
            echo '没有这个省份';
            return false;
        }

        if (empty($prov['short_pinyin'])) {
            echo '省份还没有简称请添加';
            return false;
        }
        // 创建一个管理员
        // cumcm+省简称字母
        $uInfo = array(
                'username' => cumcm.$prov['short_pinyin']
        );
        $uid = $this->_reg($uInfo);
        if (!$uid) {
            echo '注册错误';
            return false;
        }

        // 创建一个总竞赛
        $provName = $prov['provs_name'];
        $data = array();
        $data['contest_name'] = "2014年“高教社杯”全国大学生数学建模竞赛${provName}赛区";
        // 省赛区域名: cumcm+省简称字母
        $data['contest_url'] = 'cumcm'.$prov['short_pinyin'];
        $data['contest_level'] = 2;

        $this->db->select('*');
        $this->db->order_by('univs_id','asc');
        $this->db->limit(1, 0);
        $query = $this->db->where('provs_id', $pid)->get('university');
        $tunivs = $query->row_array();

        $data['parent_id'] = 0;
        $data['univs_id'] = $tunivs['univs_id'];
        $data['create_user_id'] = $uid;

        $cid = $this->_create_contest($data);

        // 第一步查出这个省下面的学校 cumcmid字段不为空的
        // 如果结果为空 查询这个省下面的前20所学校

        $this->load->model('univs_m');
        $this->db->select('*');
        $query = $this->db->where('provs_id', 5)->where('cumcmid!=""',null, false)->get('university');
        if($query->num_rows() > 0){

        } else {
            $this->db->select('*');
            $this->db->order_by('univs_id','asc');
            $this->db->limit(30, 0);
            $query = $this->db->where('provs_id', 5)->get('university');
        }
        $schools = $query->result_array();

        foreach ($schools as $s) {
            // 创建一个管理员
            // 校赛区账户名：cumcm+学校简称
            $uInfo = array(
                    'username' => cumcm.$s['short_name']
            );
            $uid = $this->_reg($uInfo);
            // 创建一个总竞赛
            $data = array();
            $univsName = $s['univs_name'];
            $data['contest_name'] = "2014年“高教社杯”全国大学生数学建模竞赛${univsName}报名官网";
            // 省赛区域名: cumcm+省简称字母
            $data['contest_url'] = 'cumcm';
            $data['contest_level'] = 1;
            $data['parent_id'] = $cid;
            $data['univs_id'] = $s['univs_id'];
            $data['create_user_id'] = $uid;

            $scid = $this->_create_contest($data);

        }
    }

    protected function _create_contest($data = array())
    {
        $data['contest_type'] = 1;
        $data['regist_start_time'] = '2014-5-11 00:00:00';
        $data['regist_end_time'] = '2014-9-12 08:00:00';
        $data['contest_start_time'] = '2014-9-12 08:00:00';
        $data['contest_end_time'] = '2014-9-15 08:00:00';
        $data['contest_bbs'] = '';

        $data['old_url'] = '';
        $data['remark'] = '';

        $data['create_time'] = date('Y-m-d H:i:s');

        $this->load->model('contest_m');
        // 检查是否存在
        $type = $data['contest_level'];
        $uri = $data['contest_url'];
        $bol = true;
        if($type == 1){ // 校内级别
            $univs_id = $data['univs_id'];
            $bol      = $this->contest_m->check_contest_exist_in_univs($uri, $univs_id);
        }elseif($type == 2 || $type == 3 || $type == 4){ // 全国级别
            $bol = $this->contest_m->check_contest_exist_in_nation($uri);
        } else {
            return false;
        }
        if ($bol) {
            return '竞赛已经存在';
        }

            $contest_id = $this->contest_m->add($data);
            $this->load->model('univs_contest_m');
            if($contest_id){
                $addData = array(
                        'univs_id' => $univs_id,
                        'contest_id' => $contest_id,
                );
                $this->univs_contest_m->add($addData);
            }
            return $contest_id;
    }

    /**
     * 注册一个用户
     */
    protected function _reg($data)
    {
        $username = $data['username'] ;
        $password = $username.'123';
        $data['password'] = md5($password);
        $data['email']='admin@worldjingsai.com';
        $data['tel']   = '13000000000';
        $data['ip'] = '';
        $data['group_type'] = 2;
        $data['gid'] = 3;
        $data['regtime'] = time();
        $data['is_active'] = 1;

        $check_username = $this->user_m->check_username($data['username']);
        if(!empty($check_username)){
            return false;
        }
        $uid = 0;
        if($this->user_m->reg($data)){
            $uid = $this->db->insert_id();
        }
        return $uid;
    }
}