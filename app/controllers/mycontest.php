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
        $this->load->model('upload_m');
        if(!$this->auth->is_login ()){
            redirect('user/login');
        }
    }

    public function index(){
        $this->my();
    }

    public function my()
    {
        $uid = $this->session->userdata ('uid');
        $data = $this->user_m->get_user_by_id($uid);
        
        $data['title'] = '我的竞赛';
        $data['topics'][] = array('contest_id' => 1, 'contest_name' => '测试竞赛', 'contest_url' => 'cumcm', 'create_time'=>'2014-03-21', 
                'level_name' => '学校竞赛', 'type_name' => '数学建模', 'enter_numbers' => 5,
        );
        $this->load->view('mycontest', $data);
    
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