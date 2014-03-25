<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Mycontest extends SB_controller{
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
     * 我创建的竞赛
     */
    public function my($page = 1)
    {
        $uid = $this->session->userdata ('uid');
        
        //分页
        $limit = 20;
        $config = $this->pageConfig;
        $config['uri_segment'] = 3;
        $config['base_url'] = site_url('mycontest/my/');
        $config['total_rows'] = $this->contest_m->count_contest(0, $uid);
        $config['per_page'] = $limit;
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $start = ($page-1)*$limit;
        $data['pagination'] = $this->pagination->create_links();
        $this->load->model('contest_regist_config_m');
        $this->load->model('team_m');
        
        $rows = $this->contest_m->get_create_by_uid($uid, $start, $limit);
        
        if ($rows) {
            foreach ($rows as &$row) {
                $cid = $row['contest_id'];
                $conf = $this->contest_regist_config_m->get_normal($cid);
                if (!$conf) {
                    $row['enter_members'] = 0;
                } else {
                    $number = $this->team_m->count_team($cid, $conf['session']);
                    $row['enter_members'] = $number;
                }
                $row['type_name'] = Contest_m::$typeNames[$row['contest_type']];
                $row['level_name'] = Contest_m::$leverNames[$row['contest_level']];
                
                if ($row['contest_level'] <= Contest_m::LEVEL_SCHOOL) {
                    $univs = $this->univs_m->get_univs_info_by_univs_id($row['univs_id']);
                    $row['contest_url'] = $univs['short_name'] . '/' . $row['contest_url'];
                }
            }
        }
        $data['title'] = '我的竞赛';
        $data['rows'] = $rows;
        $this->load->view('mycontest', $data);
    }
    
    /**
     * 根据cid获取参赛的队列表
     * @param unknown $cid
     * @param number $page
     */
    public function my_team_list($cid, $page = 1) {
        $limit = 20;
        $config = $this->pageConfig;
        $config['uri_segment'] = 4;
        $config['base_url'] = site_url('mycontest/my_team_list/');
        $this->load->model('contest_regist_config_m');
        $this->load->model('team_m');
        $conf = $this->contest_regist_config_m->get_normal($cid);
        $contest = $this->contest_m->get($cid);
        $config['total_rows'] = 0;
        if ($conf) {
            $config['total_rows'] = $this->team_m->count_team($conf['contest_id'], $conf['session']);
        }
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $start = ($page-1)*$limit;
        $data['pagination'] = $this->pagination->create_links();
        // 获取数据
        $rows = array();
        if($conf) {
            $rows = $this->team_m->get_by_cid_session($cid, $conf['session'], $start, $limit);
        }
        
        $data['title'] = '我的竞赛';
        $data['rows'] = $rows;
        $data['contest'] = $contest;
        $this->load->view('mycontest_member', $data);
    }
    
    
    /**
     * 显示一个团队信息
     * @param int $team_id
     */
    public function team_info($team_id) {
        $this->load->model('team_m');
        $this->load->model('team_column_m');
        $this->load->model('member_column_m');
        $this->load->model('contest_regist_config_m');
        
        $teamInfo = $this->team_m->get($team_id);
        $teamColumn = $memberColumn = array();
        if ($teamInfo) {
            $team_id = $teamInfo['team_id'];
            $teamColumn = $this->team_column_m->get($team_id);
            $memberColumn = $this->member_column_m->list_by_team_id($team_id);
            $contest = $this->contest_m->get($teamInfo['contest_id']);
        }
        $configs = $this->contest_regist_config_m->get_normal($teamInfo['contest_id']);
        if ($configs) {
            $configs['t'] = json_decode($configs['team_column'], true);
            $configs['m'] = json_decode($configs['member_column'], true);
        }
        $data['title'] = '团队信息';
        $data['team'] = $teamInfo;
        $data['t'] = $teamColumn;
        $data['m'] = $memberColumn;
        $data['conf'] = $configs;
        $data['contest'] = $contest;
        $this->load->view('show_team_info', $data);
    }
    
    /**
     * 展示我报名的竞赛
     */
    public function enter($page = 1) {
        $uid = $this->session->userdata ('uid');
        $this->load->model('team_m');
        $this->load->model('team_column_m');
        $this->load->model('member_column_m');
        $this->load->model('contest_regist_config_m');
        
        //分页
        $limit = 20;
        $config = $this->pageConfig;
        $config['uri_segment'] = 3;
        $config['base_url'] = site_url('mycontest/enter/');
        $config['total_rows'] = $this->team_m->count_by_uid($uid);
        $config['per_page'] = $limit;
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $start = ($page-1)*$limit;
        $data['pagination'] = $this->pagination->create_links();
        
        $rows = $this->team_m->list_by_uid($uid, $start, $limit);
        
        $data['title'] = '我的竞赛';
        $data['rows'] = $rows;
        $this->load->view('mycontest_enter', $data);
    }

}