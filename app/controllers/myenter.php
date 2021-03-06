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
        if (!$this->is_login) {
            $this->myclass->notice('alert("您还未登录请登录后再操作");window.location.href="/user/login";');
            return 0;
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
    public function result($team_id = '') {

        $isQiniu = $this->config->item('is_use_qiniu');
        $qiniu = array('is_used' => $isQiniu);
        if ($isQiniu) {
            $this->config->load('qiniu');

            $params =array(
                    'accesskey'=>$this->config->item('accesskey'),
                    'secretkey'=>$this->config->item('secretkey'),
                    'bucket'=>$this->config->item('bucket'),
                    'file_domain'=>$this->config->item('file_domain').'/',
            );
            $this->load->library('qiniu_lib',$params);
            $qiniu['up_token'] = $this->qiniu_lib->upToken;
        }

        $uid = $this->session->userdata ('uid');

        $data = $this->_get_team($team_id);

        if (!$data || $data['team']['create_user_id'] != $uid) {
            return $this->myclass->notice('alert("没有权限!");window.location.href="'.site_url("/").'";');
        }

        // 增加两小时过后不可更改

        $data['qiniu'] = $qiniu;
        list($usec, $sec) = explode(" ", microtime());
        $dir = $data['contest']['contest_id'] . '/' . $team_id % 60;
        $data['qiniu_key'] = $dir .'/'. $team_id . md5(mt_rand() . $sec);

        if($_POST){
            if(empty($data['show_result'])) {
                $msg="竞赛已经结束，不可更改作品!";
                if ($isQiniu) {
                    return show_json(500, $msg);
                } else {
                    return $this->myclass->notice('alert("'. $msg .'");window.location.href="'.site_url("myenter/result/${team_id}").'";');
                }
            }
            $problem_number = $team_level = '';
            $confr = $data['conf']['r'];
            if (!empty($confr['r1'][2])) {
                $team_level = $this->input->post('team_level', true);
                if (empty($team_level)) {
                    return $this->myclass->notice('alert("团队组别不能为空!");window.location.href="'.site_url("myenter/result/${team_id}").'";');;
                }
            }
            if(!empty($confr['r2'][2])) {
                $problem_number = $this->input->post('problem_number', true);
                if (empty($problem_number)) {
                    return $this->myclass->notice('alert("题目类型不能为空!");window.location.href="'.site_url("myenter/result/${team_id}").'";');;
                }
            }

            // 七牛上传，直接保存文件
            if ($isQiniu) {
                $file = $this->input->post('upload_file', true);
                $filearray = array('result_file' => $file, 'problem_number' => $problem_number, 'team_level' => $team_level, 'result_time' => date('Y-m-d H:i:s'));
                $this->db->where('team_id',$team_id)->update('team', $filearray);
                $data['msg'] = '文件上传成功!';
                show_json(0, '恭喜你作品上传成功');
                exit;
            // 本地上传
            } else {
                $config = array(
                        'allowed_types' => '*',
                        'upload_path' => UPLOADPATH.'paper/',
                        'encrypt_name' => true,
                        'max_size' => '10240'
                );
                if (empty($_FILES['userfile']['tmp_name'])) {
                    return $this->myclass->notice('alert("文件不能为空!");window.location.href="'.site_url("myenter/result/${team_id}").'";');;
                }

                // 分成60份
                $dir = $data['contest']['contest_id'] . '/' . ($team_id % 60);
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
                    return $this->myclass->notice('alert("恭喜你作品上传成功!");window.location.href="'.site_url("myenter/result/${team_id}").'";');
                    exit();
                } else {
                    return $this->myclass->notice('alert("文件不存在或者不符合要求请修改!");window.location.href="'.site_url("myenter/result/${team_id}").'";');
                }
            }
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
                $row['uid'] = $uid;
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

            if (!empty($data['conf']['type']) && $data['conf']['type']==2) {
                $t = date('Y-m-d H:i:s');
                if ($t >= $contest['regist_start_time'] && $t <= $contest['regist_end_time']) {
                    $data['show_enter'] = true;
                }

                $et = date('Y-m-d H:i:s', strtotime('-2 hours'));
                if ($t >= $contest['contest_start_time'] && $et <= $contest['contest_end_time']) {
                    $data['show_result'] = true;
                }
            }
            return $data;
        } else {
            return show_error('团队不存在', 404);
        }
    }

    /**
     * 导出报名队伍信息
     */
    public function ajax_export_team(){
        $uid = $this->session->userdata ('uid');
        if(empty($uid) ) {
            $this->myclass->notice('alert("下载失败！");window.location.href="'.site_url('myenter/enter').'";');
            exit;
        }
        // 输出Excel文件头，可把user.csv换成你要的文件名 http://yige.org
        $team_id = intval($this->input->get('team_id'));

        $this->load->model('team_m');
        $this->load->model('team_column_m');
        $this->load->model('member_column_m');
        $this->load->model('contest_regist_config_m');
        $data = $this->team_m->get($team_id);

        if(empty($data) || ($data['create_user_id'] != $uid)){
            $this->myclass->notice('alert("无权限下载！");window.location.href="'.site_url('myenter/enter').'";');
            return 0;
        }
        $cid = $data['contest_id'];
        $session = $data['session'];
        $conf = $this->contest_regist_config_m->get_by_cid_session($cid, $session);

        if($conf) {
            $conf['team_column'] = json_decode($conf['team_column'], true);
            $conf['member_column'] = json_decode($conf['member_column'], true);
            $conf['result_column'] = json_decode($conf['result_column'], true);
        }

        $t = $this->team_column_m->get($team_id);

        if(!empty($data)){

        	$this->load->helper('excel_helper');
        	$rows[0] = array_merge($data, $t);
        	$conf['is_seal'] = 0;
        	return team_formate($conf, $rows, 1, array('contest_name' => $data['team_number']));
        } else {
        	return $this->myclass->notice('alert("没有报名信息！");window.location.href="'.site_url('myenter/enter').'";');
        }

    }
}