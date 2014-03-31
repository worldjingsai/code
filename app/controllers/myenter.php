<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Myenter extends SB_controller{
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
        $this->enter($page);
    }


    /**
     * 显示一个团队信息
     * @param int $team_id
     */
    public function team($team_id) {
        $uid = $this->session->userdata ('uid');

        $data = $this->_get_team($team_id);
        if (!$data) {
            return ;
        }
        $this->load->model('team_column_m');
        $this->load->model('member_column_m');

        $teamColumn = $memberColumn = array();

        $teamColumn = $this->team_column_m->get($team_id);
        $memberColumn = $this->member_column_m->list_by_team_id($team_id);

        $data['title'] = '团队信息';
        $data['t'] = $teamColumn;
        $data['m'] = $memberColumn;
        $this->load->view('myenter_team', $data);
    }


    /**
     * 上传缴费图片
     * @param int $team_id
     */
    public function fee($team_id) {
        $uid = $this->session->userdata ('uid');

        $data = $this->_get_team($team_id);
        if (!$data) {
            return ;
        }
        if($_POST){
            $config = array(
                    'allowed_types' => 'jpg|jpeg|gif|png',
                    'upload_path' => FCPATH.'uploads/fee_images/',
                    'encrypt_name' => true,
                    'max_size' => '1024'
            );

            if (empty($_FILES['userfile'])) {
                return $this->myclass->notice('alert("请选择图片!");window.location.href="'.site_url("myenter/fee/${team_id}").'";');
            }
            $date = date('Ymd');
            $config['upload_path'] = $config['upload_path'] . $date . '/';
            if(!is_dir($config['upload_path'])){
                mkdir($config['upload_path'],0777,true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload()) {

                $image_data_temp = $this->upload->data();
                $fee_image = $date . '/' . $image_data_temp['file_name'];
                $this->db->where('team_id',$team_id)->update('team', array('fee_image'=>$fee_image));
                $data['msg'] = '头像上传成功!';
                header("location:/myenter/fee/${team_id}");
                exit();
            } else {

                return $this->myclass->notice('alert("图片不存在或者不符合要求请修改!");window.location.href="'.site_url("myenter/fee/${team_id}").'";');

            }
            //header("location:".$_SERVER["PHP_SELF"]);

        }

        if ($data['team']['fee_image']) {
            $data['team']['fee_image'] = '/uploads/fee_images/' . $data['team']['fee_image'];
        }
        $data['title'] = '缴费信息';
        $this->load->view('myenter_fee', $data);
    }


    /**
     * 上传作品
     * @param int $team_id
     */
    public function result($team_id) {
        $uid = $this->session->userdata ('uid');

        $data = $this->_get_team($team_id);
        if (!$data) {
            return ;
        }
        if($_POST){
            $config = array(
                    'allowed_types' => '*',
                    'upload_path' => FCPATH.'uploads/result_file/',
                    'encrypt_name' => true,
                    'max_size' => '10240'
            );

            $team_level = $this->input->post('team_level', true);
            $problem_number = $this->input->post('problem_number', true);
            if (empty($team_level) || empty($problem_number) || empty($_FILES['userfile']['tmp_name'])) {
                return $this->myclass->notice('alert("内容填写不完整!");window.location.href="'.site_url("myenter/result/${team_id}").'";');
            }

            // 分成10份
            $dir = $team_id % 10;
            $config['upload_path'] = $config['upload_path'] . $dir . '/';
            if(!is_dir($config['upload_path'])){
                mkdir($config['upload_path'],0777,true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload()) {

                $image_data_temp = $this->upload->data();
                $file = $dir . '/' . $image_data_temp['file_name'];
                $filearray = array('result_file' => $file, 'problem_number' => $problem_number, 'team_level' => $team_level, 'result_time' => date('Y-m-d H:i:s'));
                $this->db->where('team_id',$team_id)->update('team', $filearray);
                $data['msg'] = '文件上传成功!';
                header("location:/myenter/result/${team_id}");
                exit();
            } else {
                return $this->myclass->notice('alert("文件不存在或者不符合要求请修改!");window.location.href="'.site_url("myenter/result/${team_id}").'";');
            }
        }
        if ($data['team']['result_file']) {
            $data['team']['result_file'] = '/uploads/result_file/' . $data['team']['result_file'];
        }
        $data['title'] = '作品信息';
        $this->load->view('myenter_result', $data);
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
        if ($rows) {
            foreach ($rows as &$row) {
                if ($row['contest_level'] == Contest_m::LEVEL_SCHOOL) {
                    $univs = $this->univs_m->get_univs_info_by_univs_id($row['univs_id']);
                    $row['contest_url'] = $univs['short_name'] . '/' . $row['contest_url'];
                }
            }
        }

        $data['title'] = '我的参加的竞赛';
        $data['rows'] = $rows;
        $this->load->view('myenter', $data);
    }


    /**
     * 显示一个团队信息
     * @param int $team_id
     */
    protected function _get_team($team_id) {
        $uid = $this->session->userdata ('uid');

        $this->load->model('team_m');
        $this->load->model('contest_m');
        $this->load->model('contest_regist_config_m');

        $teamInfo = $this->team_m->get($team_id);
        $data = array();
        if ($teamInfo) {
            if ($uid != $teamInfo['create_user_id']) {
                return show_error('查看错误', 404);
            }

            $contest = $this->contest_m->get($teamInfo['contest_id']);

            $configs = $this->contest_regist_config_m->get_by_cid_session($teamInfo['contest_id'], $teamInfo['session']);
            if ($configs) {
                $configs['t'] = json_decode($configs['team_column'], true);
                $configs['m'] = json_decode($configs['member_column'], true);
                $configs['r'] = json_decode($configs['result_column'], true);
            }
            $data['team'] = $teamInfo;
            $data['conf'] = $configs;
            $data['contest'] = $contest;
            return $data;
        } else {
            return show_error('团队不存在', 404);
        }
    }
}