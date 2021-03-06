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
        if($bol){
            echo "false";
        }else{
            echo "true";
        }
        return ;
    }


    /**
     * 检测竞赛的Number是否被占用
     */
    public function ajax_chknumber(){
        $cid = intval($this->input->post('contest_id'));
        $session  = intval($this->input->post('session'));
        $team_number = $this->input->post('team_number', true);
        $uid = $this->user_info['uid'];
        $team_id = intval($this->input->post('team_id'));

        if (empty($cid) || empty($session) || empty($team_number)) {
            echo "false";
            return;
        }
        
        $return = false;

        $this->load->model('team_m');
        // 如果是更新竞赛，并且都没有变化就返回成功
        if ($team_number) {
            $tInfo = $this->team_m->get_by_team_number($team_number, $cid, $session);
            if (empty($tInfo)) {
                $return = true;
            } elseif ($tInfo['team_id'] == $team_id) {
                $return = true;
            }
        }
        if ($return) {
            echo "true";
        } else {
            echo "false";
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
        }else{
            $contest_url = $uri;
        }

        $cInfo = $this->contest_m->get_contest_by_short_name($univs_id, $contest_url);
        // 如果是更新竞赛，并且都没有变化就返回成功
        if(empty($cInfo)) {
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
        if (!$this->is_login) {
            $this->myclass->notice('alert("请登录后再操作");window.location.href="/user/login";');
            return 0;
        }
        if(!$contest_id){
            return show_error('竞赛不存在');
        }
        $data = $this->_get_contest($contest_id);
        if(empty($data)){
            return show_error('不存在的竞赛');
        }
        if($this->user_info['uid'] != $data['contest']['create_user_id'] && !$this->auth->is_admin()){
            return show_error('非法操作，无权限编辑此赛事!');
        }
        $data['col'] = 0;
        $data['show_more'] = $this->input->get('show_more', true);
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
        if($this->user_info['uid'] != $data['contest']['create_user_id'] && !$this->auth->is_admin()){
            return show_error('非法操作，无权限编辑此赛事!');
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
    public function user_apply($contest_id, $tid = 0) {
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
            $ct = json_decode($configs['team_column'], true);
            foreach ($ct as &$t) {
                if (!empty($t[1]) && (strpos($t[1], '|') !== false)) {
                    $t[1] = explode('|', $t[1]);
                }
            }
            unset($t);
            $mt = json_decode($configs['member_column'], true);
            foreach ($mt as &$t) {
                if (!empty($t[1]) && (strpos($t[1], '|') !== false)) {
                    $t[1] = explode('|', $t[1]);
                }
            }

            $configs['t'] = $ct;
            $configs['m'] = $mt;

            if ($configs['is_defined_number']) {
                if(!empty($configs['number_width']) );
                {
                    $configs['base_number'] = str_pad($configs['base_number'], $configs['number_width'], '0', STR_PAD_LEFT);
                }
            }
        } else {
            return $this->myclass->notice('alert("竞赛没有报名系统");window.location.href="history.back();";');
        }


        $data['reconf'] = $configs;

        // 竞赛的直接创建者可以修改团队信息
        if ($tid) {
            $teamInfo = $this->team_m->get_by_id_status($tid);
            if ($teamInfo['contest_id'] != $contest_id) {
                return show_error('没有权限');
            }
            $see = false;
            if ($teamInfo['create_user_id'] == $this->user_info['uid']) {
                $see = true;
            }
            if (!$see) {
                $see = $this->_cheak_uid_by_cid($contest_id);
            }
            
            if (!$see) {
                return show_error('没有权限');
            }
            
        // 本人是否有报名信息
        } else {
            $teamInfo = $this->team_m->get_by_user_contest_session($this->user_info['uid'], $contest_id, $configs['session']);
        }
        
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
                    'status' => Team_m::STATUS_NORMAL,
            );
            if ($configs['is_defined_number']) {
                $team_number = $this->input->post('team_number', true);
                if (empty($team_number)) {
                    return show_json(100, '参赛队号不能为空', array('return_url' => '/contest/user_apply/'.$contest_id, 'show_time'=>1000));
                } else {
                    $tInfo = $this->team_m->get_by_team_number($team_number, $contest_id, $configs['session']);
                    if ($tInfo && $tInfo['team_id'] != $tid) {
                        return show_json(200, '参赛队号重复请重新填写', array('return_url' => '/contest/user_apply/'.$contest_id, 'show_time'=>1000));
                    }
                }
                $teamData['team_number'] = $team_number;
            }
            $teamColumn = $team;
            $teamColumn['team_id'] = $team_id;

            // 如果存在则更新
            if ($team_id && $teamInfo['team_number']) {
                $this->team_m->update($team_id, $teamData);
                $this->team_column_m->update($team_id, $teamColumn);
            } else {
                $teamData['create_time'] = date('Y-m-d H:i:s');
                $teamData['create_user_id'] = $this->user_info['uid'];

                // 是否需要自定义队号
                if (empty($configs['is_defined_number'])) {
                    $team_number = $this->contest_regist_config_m->get_team_number($contest_id);
                }

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
            $teamInfo = $this->team_m->get_by_id_status($team_id);

            $str = '您已成功报名参赛<br/>参数队号：'.$teamInfo['team_number'].'<br/>在右上角您的个人账户”我的竞赛”中可以查看到完整的报名信息<br/>';
            if ($configs['fee'] > 0) {
                $str .= '请尽快缴纳参赛费,并在右上角您的个人账户”我的竞赛”中上传缴费的付款证明图片及查看是否已缴费的状态';
            }

            return show_json(0, $str, array('return_url' => '/contest/user_apply/'.$contest_id . '/'.$tid, 'show_time'=>10000));
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
     * 取消报名信息
     */
    public function user_cancle($team_id = 0) {
        $team_id = intval($team_id);
        if (!$team_id) {
            $code = 0;
        }
        $message = '';
        $data = array();
        if ($team_id) {
            $this->load->model('team_m');
            $uid=$this->session->userdata('uid');
            $t = $this->team_m->get_by_id_status($team_id);
            
            if(empty($t)) {
                return show_json(404, '团队不存在');
            }
            $see = false;
            if ($t['create_user_id'] == $uid) {
                $see = true;
            }
            if (!$see) {
                $see = $this->_cheak_uid_by_cid($t['contest_id']);
            }
            
            if ($see){
                $res = $this->team_m->update($team_id, array('status' => team_m::STATUS_CANCLE));
                if ($res) {
                    $code = 0;
                } else {
                    $code = 500;
                    $message = '更新错误';
                }
            } else {
                $code = 500;
                $message = '没有权限';
            }
            
            $contest = $this->_get_contest($t['contest_id']);
            if (isset($contest['contest_url'])) {
                $data['return_url'] = '/'.$contest['contest_url'];
            }
        }
        
        return show_json($code, $message, $data);
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