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

        $uid = $this->session->userdata ('uid');
        $act = $this->input->get('act', true);
        $limit = 20;

        $config = $this->pageConfig;
        $config['uri_segment'] = 4;
        $config['base_url'] = site_url('mycontest/my_team_list/');
        $this->load->model('contest_regist_config_m');
        $this->load->model('team_m');
        $conf = $this->contest_regist_config_m->get_normal($cid);
        $contest = $this->contest_m->get($cid);
        if ($uid != $contest['create_user_id']) {
            return show_error('查看错误', 404);
        }

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
            $conf['team_column'] = json_decode($conf['team_column'], true);
            $conf['member_column'] = json_decode($conf['member_column'], true);
            if ($act == 'export') {
                $start = 0;
                $limit = $config['total_rows'];
            }
            $rows = $this->team_m->get_detail_by_cid_session($cid, $conf['session'], $start, $limit);
        }

        if ($act == 'export') {
            $start = 0;
            $limit = $config['total_rows'];
            if(!empty($rows)){
                $title = "队号";
                $i=1;
                $sk = array();

                foreach($conf['team_column'] as $k=>$v) {
                    if ($v[2] > 0) {
                        if($i++ > 5) {
                            break;
                        }
                        $sk[$k] = $k;
                        $title .= ','.$v[0];
                    }
                }
                if (!empty($conf['fee'])) {
                    $title .= ',是否缴费';
                }
                $title.="\r\n";
                $content = '';
                foreach($rows as $k=>$v){
                    $content .= $v['team_number'];
                    foreach($sk as $kk) {
                        $content .= ','.$v[$kk];
                    }
                    // 是否缴费
                    if (!empty($conf['fee'])) {
                        if ($v['is_fee'] >= 1) {
                            $content .= ',是';
                        } else {
                            $content .= ',否';
                        }
                    }
                    $content.="\r\n";
                }
                return $this->exportCsv($contest['contest_name'] . '_报名表.csv' , $title.$content);
            } else {
                $this->myclass->notice('alert("没有没有报名团队");window.location.href="'.site_url("/mycontest/my_team_list/$cid").'";');
            }

        }
        $data['title'] = '我的竞赛';
        $data['rows'] = $rows;
        $data['contest'] = $contest;
        $data['conf'] = $conf;
        $this->load->view('mycontest_member', $data);
    }


    /**
     * 显示一个团队信息
     * @param int $team_id
     */
    public function team_info($team_id) {
        $uid = $this->session->userdata ('uid');

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
            if ($uid != $contest['create_user_id']) {
                return show_error('查看错误', 404);
            }
        } else {
            return show_error('团队不存在', 404);
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
     * 下载数据文件，只有竞赛创建者和团队创建者可以下载
     * @param int $team_id
     */
    public function result_file($team_id) {
        $uid = $this->session->userdata ('uid');

        $this->load->model('team_m');
        $this->load->model('contest_regist_config_m');

        $teamInfo = $this->team_m->get($team_id);
        $teamColumn = $memberColumn = array();
        if ($teamInfo) {
            $team_id = $teamInfo['team_id'];
            $contest = $this->contest_m->get($teamInfo['contest_id']);
            if (($uid != $contest['create_user_id']) && ($uid != $teamInfo['create_user_id'])) {
                return show_error('查看错误', 404);
            }
        } else {
            return show_error('团队不存在', 404);
        }

        if (empty($teamInfo['result_file'])) {
            return $this->myclass->notice('alert("未上交作品！");');
        }
        $file_dir = UPLOADPATH . '/paper/';
        $file_name = $teamInfo['result_file'];
        $show_name = $teamInfo['team_number'] . strrchr($file_name, '.');
        $file = fopen($file_dir . $teamInfo['result_file'],"r"); // 打开文件
        // 输入文件标签
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ".filesize($file_dir . $file_name));
        Header("Content-Disposition: attachment; filename=" . $show_name);
        // 输出文件内容
        echo fread($file,filesize($file_dir . $file_name));
        fclose($file);
        exit();
    }

    /**
     *
     */
    public function batch_process($cid = 0, $page=1)
    {
        $uid = $this->session->userdata ('uid');

        $contest = $this->contest_m->get($cid);
        if (empty($contest) || ($uid != $contest['create_user_id'])) {
            return show_error('查看错误', 404, '违法操作');
        }
        $tids = array_slice($this->input->post(), 0, -1);
        if(empty($tids)){
            $this->myclass->notice('alert("请选择需要操作的队伍!");window.location.href="'.site_url("mycontest/my_team_list/${cid}/${page}").'";');
        }
        if($this->input->post('batch_del')){
            if($this->db->where_in('fid',$tids)->delete('forums')){
                $this->myclass->notice('alert("批量删除团队成功！");window.location.href="'.site_url("mycontest/my_team_list/${cid}/${page}").'";');
            }
        }
        if($this->input->post('batch_fee')){
            if($this->db->where_in('team_id',$tids)->where('contest_id', $cid)->update('team', array('is_fee'=>1))){
                $this->myclass->notice('alert("批量更新缴费状态成功！");window.location.href="'.site_url("mycontest/my_team_list/${cid}/${page}").'";');
            }
        }
        if($this->input->post('batch_unfee')){
            if($this->db->where_in('team_id',$tids)->where('contest_id', $cid)->update('team', array('is_fee'=>0))){
                $this->myclass->notice('alert("批量更新缴费状态成功！");window.location.href="'.site_url("mycontest/my_team_list/${cid}/${page}").'";');
            }
        }
    }
        
    public function ajax_search_team(){
        $contest_id = intval($this->input->get('cid'));
        $session    = intval($this->input->get('session'));
        $is_fee     = intval($this->input->get('is_fee'));
        $is_upload_fee_image = intval($this->input->get('is_upload_fee_image'));
        $page       = intval($this->input->get('page'));
        $limit      = 10;
        $this->load->model('team_m');
        $result['rows'] = $this->team_m->get_detail_by_cid_session($contest_id,$session,$is_fee,$is_upload_fee_image,$page,$limit);
        $this->load->view('search_team_result', $result);
    }
}