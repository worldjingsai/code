<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Contests extends Admin_Controller{
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
        $limit = 50;
        $config = $this->pageConfig;
        $config['uri_segment'] = 3;
        $config['base_url'] = site_url('admin/contest/my/');
        $config['total_rows'] = $this->contest_m->count_contest(0, $uid);
        $config['per_page'] = $limit;

        $this->load->library('pagination');
        $this->pagination->initialize($config);

        $start = ($page-1)*$limit;
        $data['pagination'] = $this->pagination->create_links();
        $this->load->model('contest_regist_config_m');
        $this->load->model('team_m');

        $rows = $this->contest_m->list_all($start, $limit);

        if ($rows) {
            foreach ($rows as &$row) {
                $cid = $row['contest_id'];

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
     * 我创建的竞赛
     */
    public function contest($cid, $page = 1)
    {
        $this->load->model('contest_m');
        $contest = $this->contest_m->get($cid);
        $this->load->model('contest_regist_config_m');

        $rows = $this->contest_regist_config_m->listByCid($cid);

        $this->load->model('team_m');
        if ($rows) {
            foreach ($rows as &$row) {
                $cid = $row['contest_id'];
                $sid = $row['session'];

                $number = $this->team_m->count_team($cid, $sid);
                $row['enter_members'] = $number;
            }
        }
        $data['title'] = '我的竞赛';
        $data['rows'] = $rows;
        $data['contest'] = $contest;
        $this->load->view('contest_one', $data);
    }


    /**
     * 根据cid获取参赛的队列表
     * @param intval $cid
     * @param number $page
     */
    public function team_list($cid, $session, $page = 1) {

        $gets = $this->input->get(null, true);

        $uid = $this->session->userdata ('uid');
        $act = $this->input->get('act', true);
        $mem = $this->input->get('mem', true);
        $limit = 50;

        $config = $this->pageConfig;
        $config['per_page'] = $limit;
        $config['uri_segment'] = 6;
        $config['base_url'] = site_url('admin/contests/team_list/' . $cid . '/'.$session);
        $config['url_arguments'] = $gets;

        $this->load->model('team_m');
        $this->load->model('contest_regist_config_m');
        $conf = $this->contest_regist_config_m->get_by_cid_session($cid, $session);
        $contest = $this->contest_m->get($cid);

        $is_fee = isset($gets['is_fee']) ? $gets['is_fee'] : '-1';
        $is_up_imag = isset($gets['fee_image']) ? $gets['fee_image'] : '-1';
        $is_result = isset($gets['is_result']) ? $gets['is_result'] :'-1';
        $tk = isset($gets['select']) ? $gets['select'] : '';
        $tv = isset($gets['keywords']) ? $gets['keywords'] :'';
        $config['total_rows'] = 0;
        if ($conf) {
            $config['total_rows'] = $this->team_m->count_detail_by_cid_session($conf['contest_id'], $conf['session'], $is_fee, $is_up_imag, $is_result, $tk, $tv);
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
            $rows = $this->team_m->get_detail_by_cid_session($cid, $conf['session'], $start, $limit, $is_fee, $is_up_imag, $is_result, $tk, $tv);
        }

        // 导出团队信息
        if ($act == 'export') {
            $start = 0;
            $limit = $config['total_rows'];
            if(!empty($rows)){
                $title = '"队号"';
                $mk = $sk = array();

                foreach($conf['team_column'] as $k=>$v) {
                    if ($v[2] > 0) {

                        $sk[$k] = $k;
                        $title .= ',"'.$v[0].'"';
                    }
                }
                if (!empty($conf['fee'])) {
                    $title .= ',"是否缴费"';
                    $title .= ',"是否上传缴费图片"';
                }
                $title .= ',"团队组别","团队选题","是否上传作品"';

                // 导出团队信息
                if ($mem) {
                    for($i=1; $i<=$conf['max_member']; $i++) {
                        foreach($conf['member_column'] as $k=>$v) {
                            if ($v[2] > 0) {
                                $mk[$k] = $k;
                                $title .= ',"队员'.$i.$v[0].'"';
                            }
                        }
                    }
                    $mt = array();
                    foreach($rows as $k=>$v){
                        $mt[$v['team_id']] = $v['team_id'];
                    }

                    $this->load->model('member_column_m');
                    $members = $this->member_column_m->listByTeamIds($mt);
                    $showMembers = array();
                    foreach ($members as $tmpm) {
                        $showMembers[$tmpm['team_id']][] = $tmpm;
                    }
                }
                $title.="\r\n";
                $content = '';
                foreach($rows as $k=>$v){
                    $content .= '"'.$v['team_number'].'"';
                    foreach($sk as $kk) {
                        $content .= ',"'.$v[$kk].'"';
                    }
                    // 是否缴费
                    if (!empty($conf['fee'])) {
                        if ($v['is_fee'] >= 1) {
                            $content .= ',"是"';
                        } else {
                            $content .= ',"否"';
                        }
                        if ($v['fee_image']) {
                            $content .= ',"是"';
                        } else {
                            $content .= ',"否"';
                        }
                    }
                    $content.= ',"'.$v['team_level'] .'","'.$v['problem_number'].'"';
                    if ($v['result_file']) {
                        $content .= ',"是"';
                    } else {
                        $content .= ',"否"';
                    }
                    if ($mem) {
                        foreach($showMembers[$v['team_id']] as $mv) {
                            foreach ($mk as $kk) {
                                $content .= ',"'.$mv[$kk].'"';
                            }
                        }
                    }
                    $content.="\r\n";
                }
                $fname = $contest['contest_name'];
                if ($mem) {
                    $fname .= '_团队和队员信息表';
                } else {
                    $fname .= '_团队信息表';
                }
                return $this->exportCsv($fname . '.csv' , $title.$content);
            } else {
                $this->myclass->notice('alert("没有报名团队");window.location.href="'.site_url("/mycontest/my_team_list/$cid").'";');
            }
        }
        $data['gets'] = $gets;
        $data['url_query'] = http_build_query((array)$gets);
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

        $this->load->model('team_m');
        $this->load->model('contest_regist_config_m');

        $teamInfo = $this->team_m->get($team_id);
        $teamColumn = $memberColumn = array();
        if ($teamInfo) {
            $team_id = $teamInfo['team_id'];
            $contest = $this->contest_m->get($teamInfo['contest_id']);
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
        $refer = $this->input->server('HTTP_REFERER', true);

        $refer = str_replace(';', '', $refer);
        if (preg_match('/.*batch_process.*/', $refer)) {
            $refer = site_url('admin/contests/my/'.$page);
        }

        $cids = array_slice($this->input->post(), 0, -1);
        if(empty($cids)){
            $this->myclass->notice('alert("请选择需要操作的竞赛!");window.location.href="'.$refer.'";');
        }
        if($this->input->post('batch_del')){
            if($this->db->where_in('contest_id',$cids)->update('contest', array('status' => 0))){
                $this->db->where_in('contest_id',$cids)->update('university_contest', array('status' => 0));
                $this->myclass->notice('alert("批量删除竞赛成功！");window.location.href="'.$refer.'";');
            }
        }
    }

}