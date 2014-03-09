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
     * 显示一个文章
     * @param int $article_id
     */
    public function show($article_id) {
        $data = $this->_get($article_id);
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

        $cInfo = $this->contest_m->get($contest_id);
        if (empty($cInfo)){
            return show_error('不存在的竞赛');
        }

        $univs_id = $cInfo["univs_id"];

        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;
        $data['contest'] = $cInfo;

        $colums = Contest_m::$columNames;
        $data['colums'] = $colums;
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

        $cInfo = $this->contest_m->get($contest_id);
        if (empty($cInfo)){
            return show_error('不存在的竞赛');
        }

        $univs_id = $cInfo["univs_id"];

        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;
        $data['contest'] = $cInfo;

        $colums = Contest_m::$columNames;
        $data['colums'] = $colums;
        $data['col'] = 0;

        $this->load->model('contest_regist_config_m');

        if ($_POST) {

            $id = intval($this->input->post('id'));
            $session = intval($this->input->post('session'));
            $baseNumber = intval($this->input->post('base_number'));
            $minMember = intval($this->input->post('min_member'));
            $maxMember = intval($this->input->post('max_member'));

            // 团队的配置信息  t字段名 b备注  c是否有效
            $t = $this->input->post('t');
            $b = $this->input->post('b');
            $c = $this->input->post('c');

            // 成员的配置信息  u字段名 d备注  s是否有效
            $u = $this->input->post('u');
            $d = $this->input->post('d');
            $s = $this->input->post('s');

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

            $configData = array(
                    'contest_id' =>$contest_id,
                    'session' => $session,
                    'type' => Contest_regist_config_m::TYPE_REGIST,
                    'article_url' => '',
                    'base_number' => $baseNumber,
                    'min_member' => $minMember,
                    'max_member' => $maxMember,
                    'team_column' => json_encode($teamColumn),
                    'member_column' => json_encode($memberColumn),
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

        $cInfo = $this->contest_m->get($contest_id);
        if (empty($cInfo)){
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

        if ($_POST) {
            $team = $this->input->post('t', true);
            $member = $this->input->post('m', true);

            $team_number = $this->contest_regist_config_m->get_team_number($contest_id);
            $teamData = array(
                    'contest_id' => $contest_id,
                    'session' => $configs['session'],
                    'team_number' => $team_number,
                    'create_time' => date('Y-m-d H:i:s'),
                    'create_user_id' =>$this->user_info['uid'],
                    'status' => Team_m::STATUS_NORMAL,
                    );
            $team_id = $this->team_m->add($teamData);
            if ($team_id) {
                $teamColumn = $team;
                $teamColumn['team_id'] = $team_id;
                $this->team_column_m->add($teamColumn);

                foreach ($member as $memColumn) {
                    $memberColum = $memColumn;
                    $memberColum['team_id'] = $team_id;

                    $this->member_column_m->add($memberColum);
                }
            }
        }

        $univs_id = $cInfo["univs_id"];

        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;
        $data['contest'] = $cInfo;

        $colums = Contest_m::$columNames;
        $data['colums'] = $colums;
        $data['col'] = 0;

        $this->tplData = $data;
        $this->display('contest/user_apply.html');
    }

    /**
     * 显示编辑的文章界面
     * @param int $article_id
     */
    public function update($article_id){
        $data = $this->_get($article_id);
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

    public function _get($article_id){
        $aid = intval($article_id);

        if (!$aid){
            return show_error('不存在的文章');
        }
        // 引入模型
        $this->load->model('article_m');
        $this->load->model('article_content_m');
        $this->load->model('contest_m');


        $article = $this->article_m->get($aid);
        $content = $this->article_content_m->get($aid);

        if (!$article) {
            return show_error('不存在的文章');
        }
        $article['content'] = $content['content'];

        if ($article['article_type'] == article_m::TYPE_CONTEST)
        {
            $cInfo = $this->contest_m->get($article['type_id']);
        }
        if (empty($cInfo)){
            return show_error('不存在的竞赛');
        }

        $univs_id = $cInfo['univs_id'];
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;

        $data['col'] = $article['column_id'];

        $colums = Contest_m::$columNames;
        $data['article'] =$article;
        $data['contest'] = $cInfo;
        $data['colums'] = $colums;

        return $data;
    }

}