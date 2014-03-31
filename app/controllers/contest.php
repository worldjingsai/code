<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Contest extends SB_controller{
    function __construct (){
        parent::__construct();
        $this->load->model('contest_m');
        $this->load->model('univs_m');
        $this->load->library('myclass');
    }

    public function index($cid, $page=1){

    }

    /**
     * 检测竞赛的URL是否被占用
     */
    public function ajax_chkuri(){
        $type = $this->input->post('contest_level');
        $uri  = $this->input->post('contest_url');
        $contest_id = intval($this->input->post('contest_id'));

        // 如果是更新竞赛，并且都没有变化就返回成功
        if ($contest_id) {
            $cInfo = $this->contest_m->get($contest_id);
            if (($cInfo['contest_url'] === $uri) && ($type == $cInfo['contest_level'])) {
                echo "true";
                return ;
            }
        }
        if($type == 1){ // 校内级别
            $univs_id = intval($this->input->post('univs_id'));
            $bol      = $this->contest_m->check_contest_exist_in_univs($uri, $univs_id);
        }elseif($type == 2 || $type == 3 || $type == 4){ // 全国级别
            $bol = $this->contest_m->check_contest_exist_in_nation($uri);
        }else{
            $bol = true;
        }
        if($bol)
        {
            echo "false";
        } else {
            echo "true";
        }
        return ;
    }

    /**
     * 检测父URL是否存在
     */
    public function ajax_parenturi(){
        $uri  = $this->input->post('parent_url');

        $short_name = $contest_url = '';
        $univs_id = 0;
        if (strpos($uri, '/')) {
            list($short_name, $contest_url) = explode('/', $uri);
            $univsInfo = $this->univs_m->get_univs_info_by_univs_short_name($short_name);
            if (empty($univsInfo['univs_id'])) {
                echo "false";
                return 0;
            }
            $univs_id = $univsInfo['univs_id'];
        } else {
            $contest_url = $uri;
        }

        $cInfo = $this->contest_m->get_contest_by_short_name($univs_id, $contest_url);
        // 如果是更新竞赛，并且都没有变化就返回成功
        if (empty($cInfo)) {
            echo "false";
        } else {
            echo "true";
        }
        return ;
    }


    /**
     * 显示一个文章
     * @param int $article_id
     */
    public function show($article_id) {
        $data = $this->_get_article($article_id);
        $this->tplData = $data;
        $this->display('contest/contest_article.html');
    }


    /**
     * 更新一个竞赛
     * @param intval $contest_id
     */
    public function upContest($contest_id) {
        if (!$contest_id) {
            return show_error('竞赛不存在');
        }

        $data = $this->_get_contest($contest_id);
        if (empty($data)){
            return show_error('不存在的竞赛');
        }

        $data['col'] = 0;

        $this->tplData = $data;
        $this->display('contest/create_contest.html');

    }

    /**
     * 创建报名系统
     */
    public function create_apply($contest_id) {
        if (!$this->is_login) {
            $this->myclass->notice('alert("请登录后再操作");window.location.href="/user/login";');
            return 0;
        }

        if (!$contest_id) {
            return show_error('竞赛不存在');
        }

        $data = $this->_get_contest($contest_id);
        if (empty($data)){
            return show_error('不存在的竞赛');
        }

        $data['col'] = 0;

        $this->load->model('contest_regist_config_m');

        if ($_POST) {

            $id = intval($this->input->post('id'));
            $session = intval($this->input->post('session'));
            $baseNumber = intval($this->input->post('base_number'));
            $minMember = intval($this->input->post('min_member'));
            $maxMember = intval($this->input->post('max_member'));
            $fee = intval($this->input->post('fee'));

            // 团队的配置信息  t字段名 b备注  c是否有效
            $t = $this->input->post('t');
            $b = $this->input->post('b');
            $c = $this->input->post('c');

            // 成员的配置信息  u字段名 d备注  s是否有效
            $u = $this->input->post('u');
            $d = $this->input->post('d');
            $s = $this->input->post('s');

            // 结果配置信息  o选项信息  i是否有效
            $o = $this->input->post('o');
            $ii = $this->input->post('i');

            $i = 1;
            $teamColumn = array();
            foreach($t as $k => $v) {
                if (!empty($c[$k]) && !empty($v)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $teamColumn["t$i"] = array($v, $b[$k], $isValid);
                $i++;
            }

            $i = 1;
            $memberColumn = array();
            foreach($u as $k => $v) {
                if (!empty($s[$k]) && !empty($v)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $memberColumn["m$i"] = array($v, $d[$k], $isValid);
                $i++;
            }

            $resultColumn = array();
            if (isset($o[0])) {
                if (!empty($ii[0])) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $resultColumn["r1"] = array('团队组别', $o[0], $isValid);
            }

            if (isset($o[1])) {
                if (!empty($ii[1])) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $resultColumn["r2"] = array('题目选择', $o[1], $isValid);
            }

            $configData = array(
                    'contest_id' =>$contest_id,
                    'session' => $session,
                    'type' => Contest_regist_config_m::TYPE_REGIST,
                    'article_url' => '',
                    'base_number' => $baseNumber,
                    'min_member' => $minMember,
                    'max_member' => $maxMember,
                    'fee' => $fee,
                    'team_column' => json_encode($teamColumn),
                    'member_column' => json_encode($memberColumn),
                    'result_column' => json_encode($resultColumn),
                    'create_time' => date('Y-m-d H:i:s'),
                    'create_user_id' => $this->user_info['uid'],
                    'status' => Contest_regist_config_m::STATUS_NORMAL
            );

            // 如果有是更新
            $oldConfig = array();
            if ($id) {
                $oldConfig = $this->contest_regist_config_m->get($id);
            }
            if ($oldConfig && $oldConfig['contest_id'] == $contest_id && $oldConfig['session'] == $session) {
                if ($oldConfig['status'] != Contest_regist_config_m::STATUS_NORMAL) {
                    $this->contest_regist_config_m->updateExpire($contest_id);
                }
                if ($oldConfig['current_number'] < $configData['base_number']) {
                    $configData['current_number'] = $configData['base_number'];
                }
                $this->contest_regist_config_m->update($id, $configData);
            // 新增
            } else {
                $configData['current_number'] = $configData['base_number'];
                $this->contest_regist_config_m->updateExpire($contest_id);
                $this->contest_regist_config_m->add($configData);
            }

            return show_json(0, '更新成功', array('return_url' => '/contest/create_apply/'.$contest_id));
        }

        $configs = $this->contest_regist_config_m->get_normal($contest_id);
        if ($configs) {
            $configs['t'] = json_decode($configs['team_column'], true);
            $configs['m'] = json_decode($configs['member_column'], true);
            $configs['r'] = json_decode($configs['result_column'], true);
            if (empty($configs['r'])) {
                $configs['r'] = Contest_regist_config_m::$default_r;
            }
        } else {
            // 设置默认值
            $configs= array(
                    'id' =>'',
                    'session' => 1,
                    'base_number' => 1000,
                    'min_member' => 1,
                    'max_member' => 3,
                    't' => Contest_regist_config_m::$default_t,
                    'm' => Contest_regist_config_m::$default_m,
                    'r' => Contest_regist_config_m::$default_r,
            );
        }
        $data['reconf'] = $configs;

        $this->tplData = $data;
        $this->display('contest/create_apply.html');
    }

    /**
     * 创建报名系统
     */
    public function user_apply($contest_id) {
        if (!$this->is_login) {
            $this->myclass->notice('alert("请登录后再操作");window.location.href="/user/login";');
            return 0;
        }

        if (!$contest_id) {
            return show_error('竞赛不存在');
        }

        $data = $this->_get_contest($contest_id);
        if (empty($data)){
            return show_error('不存在的竞赛');
        }

        $this->load->model('contest_regist_config_m');
        $this->load->model('team_m');
        $this->load->model('team_column_m');
        $this->load->model('member_column_m');

        $configs = $this->contest_regist_config_m->get_normal($contest_id);
        if ($configs) {
            $configs['t'] = json_decode($configs['team_column'], true);
            $configs['m'] = json_decode($configs['member_column'], true);
        } else {
            return $this->myclass->notice('alert("竞赛没有报名系统");window.location.href="history.back();";');
        }
        $data['reconf'] = $configs;

        // 本人是否有报名信息
        $teamInfo = $this->team_m->get_by_user_contest_session($this->user_info['uid'], $contest_id, $configs['session']);

        $team_id = 0;
        $teamColumn = $memberColumn = array();
        if ($teamInfo) {
            $team_id = $teamInfo['team_id'];
            $teamColumn = $this->team_column_m->get($team_id);
            $memberColumn = $this->member_column_m->list_by_team_id($team_id);
        }

        if ($_POST) {
            $team = $this->input->post('t', true);
            $member = $this->input->post('m', true);

            $teamData = array(
                    'create_time' => date('Y-m-d H:i:s'),
                    'create_user_id' =>$this->user_info['uid'],
                    'status' => Team_m::STATUS_NORMAL,
            );
            $teamColumn = $team;
            $teamColumn['team_id'] = $team_id;

            // 如果存在则更新
            if ($team_id && $teamInfo['team_number']) {
                $this->team_m->update($team_id, $teamData);
                $this->team_column_m->update($team_id, $teamColumn);
            } else {
                $team_number = $this->contest_regist_config_m->get_team_number($contest_id);
                $teamData['contest_id'] = $contest_id;
                $teamData['session'] = $configs['session'];
                $teamData['team_number'] = $team_number;

                $team_id = $this->team_m->add($teamData);
                $teamColumn['team_id'] = $team_id;
                $this->team_column_m->add($teamColumn);
            }

            if ($team_id) {
                $this->member_column_m->delete_ty_team_id($team_id);
                foreach ($member as $memColumn) {
                    $memberColumn = $memColumn;
                    $memberColumn['team_id'] = $team_id;
                    $this->member_column_m->add($memberColumn);
                }
            }

            return show_json(0, '更新成功', array('return_url' => '/contest/user_apply/'.$contest_id));
        }
        $data['teamInfo'] = $teamInfo;
        $data['teamColumn'] = $teamColumn;
        $data['memberColumn'] = $memberColumn;

        // 模板中生成会员的个数
        $data['mem_num'] = $configs['min_member'];
        if ($memberColumn && (count($memberColumn) > $configs['min_member'])) {
            $data['mem_num'] = count($memberColumn);
        }

        $data['col'] = 0;

        $this->tplData = $data;
        $this->display('contest/user_apply.html');
    }

    /**
     * 显示图片
     */
    public function user_pay_img($team_id) {
        $data['title'] = '头像设置';
        $uid=$this->session->userdata('uid');
        $data['my_avatar'] = $this->upload_m->get_avatar_url($uid, 'middle');
        if($_POST){
            //print_r($this->input->post('avatar_file'));
            if($this->upload_m->do_avatar()){
                $this->db->where('uid',$uid)->update('users', array('avatar'=>$data['my_avatar']));
                $data['msg'] = '头像上传成功!';
                header("location:/settings/avatar");
                exit();
            } else {
                $data['msg'] = $this->upload->display_errors();
            }
            //header("location:".$_SERVER["PHP_SELF"]);

        }
        $data['avatars']['big'] = $this->upload_m->get_avatar_url($uid, 'big');
        $data['avatars']['middle'] = $this->upload_m->get_avatar_url($uid, 'middle');
        $data['avatars']['small'] = $this->upload_m->get_avatar_url($uid, 'small');
        $this->load->view('settings_avatar', $data);
    }


    /**
     * 显示编辑的文章界面
     * @param int $article_id
     */
    public function update($article_id){
        $data = $this->_get_article($article_id);
        $this->tplData = $data;
        $this->display('contest/create_2.html');
    }

    /**
     * 删除一个文章
     * @param int $article_id
     */
    public function del($article_id){
        $aid = intval($article_id);

        if (!$aid){
            return show_json(404, '不存在的文章');
        }
        // 引入模型
        $this->load->model('article_m');
        $this->load->model('article_content_m');

        $article = $this->article_m->get($aid);
        if (!$article) {
            return show_json(404, '不存在的文章');
        }

        $this->article_m->del($article_id);
        $this->article_content_m->del($article_id);

        return show_json(0, '删除成功');
    }


}