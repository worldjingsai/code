<?php
/**
 * The base controller which is used by the Front and the Admin controllers
 */
class Base_Controller extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->loadSmarty();

    }
    protected function loadSmarty(){
        $this->load->library('smarty');
    }

    /**
     * 展示模板
     * @param string $template 模板的相对路径
     */
    public function display($template){
        if(!empty($this->user_info)){
            $this->tplData['user_info'] = $this->user_info;
        }
        $this->tplData['is_login'] = $this->is_login;
        $this->smarty->assign('tplData', $this->tplData);
        $this->smarty->display($template);
    }

    /**
     * 展示模板
     * @param string $template 模板的相对路径
     */
    public function fetch($template){
        if(!empty($this->user_info)){
            $this->tplData['user_info'] = $this->user_info;
        }
        $this->tplData['is_login'] = $this->is_login;
        $this->smarty->assign('tplData', $this->tplData);
        return $this->smarty->fetch($template);
    }

    /**
     * 已Json格式返回数据
     * @param array  $data 要返回给前端的数据
     */
    public function show_json($data){
        if(!isset($data['error_code'])){
            $data['err_code'] = Constants::$success;
        }
        if(!isset($data['err_msg'])){
            $data['err_msg']  = Constants::$err_message[$data['err_code']];
        }
        echo json_encode($data);
        return ;
    }

}

class SB_Controller extends Base_Controller{
    public $pageConfig = array(
            'uri_segment' => 3,
            'use_page_numbers' => TRUE,
            'base_url' => '',
            'total_rows' => 0,
            'per_page' => 20,
            'prev_link' => '&larr;',
            'prev_tag_open' => '<li class=\'prev\'>',
            'prev_tag_close' => '</li',
            'cur_tag_open' => '<li class=\'active\'><span>',
            'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'next_link' => '&rarr;',
            'next_tag_open' => '<li class=\'next\'>',
            'next_tag_close' => '</li>',
            'first_link' => '首页',
            'first_tag_open' => '<li class=\'first\'>',
            'first_tag_close' => '</li>',
            'last_link' => '尾页',
            'last_tag_open' => '<li class=\'last\'>',
            'last_tag_close' => '</li>',
            'num_links' => 10,
    );
    function __construct(){
        parent::__construct();
        //判断关闭
        if($this->config->item('site_close')=='off'){
            show_error($this->config->item('site_close_msg'),500,'网站关闭');
        }
        //载入前台模板
        $this->load->set_front_theme($this->config->item('themes'));
        $this->load->database();
        //网站设定
        $data['items']=$this->db->get('settings')->result_array();
        $data['settings']=array(
            'site_name'=>$data['items'][0]['value'],
            'welcome_tip'=>$data['items'][1]['value'],
            'short_intro'=>$data['items'][2]['value'],
            'show_captcha'=>$data['items'][3]['value'],
            'site_run'=>$data['items'][4]['value'],
            'site_stats'=>$data['items'][5]['value'],
            'site_keywords'=>$data['items'][6]['value'],
            'site_description'=>$data['items'][7]['value'],
            'money_title'=>$data['items'][8]['value'],
            'per_page_num'=>$data['items'][9]['value'],
            'logo'=>$this->config->item('logo')
        );

        if($this->auth->is_login()){
            $this->is_login = true;
            $this->user_info = $this->db->select('uid,username,avatar,univs_id')->where('uid',$this->session->userdata('uid'))->get('users')->row_array();
            $univs = $this->db->select('short_name')->get_where('university',array('univs_id'=>$this->user_info['univs_id']))->row_array();
            if (!empty($univs['short_name'])) {
                $this->user_info['univs_name'] = $univs['short_name'];
            }
            $data['user'] = $this->user_info;
        }else{
            $this->is_login = false;
        }
        //一个用户的用户组
        $data['group'] = $this->db->select('group_name')->get_where('user_groups',array('gid'=>$this->session->userdata('gid')))->row_array();
        $data['group']['group_name']=($data['group'])?$data['group']['group_name']:'普通会员';
        //获取二级目录
        $data['base_folder'] = $this->config->item('base_folder');
        //获取头像
        $this->load->model('upload_m');
        $data['user']['big_avatar']=$this->upload_m->get_avatar_url($this->session->userdata('uid'), 'big');
        $data['user']['big_avatar']=(file_exists($data['user']['big_avatar']))?$data['user']['big_avatar']:'uploads/avatar/avatar_large.jpg';
        $data['user']['middle_avatar']=$this->upload_m->get_avatar_url($this->session->userdata('uid'), 'middle');
        $data['user']['middle_avatar']=(file_exists($data['user']['middle_avatar']))?$data['user']['middle_avatar']:'uploads/avatar/default.jpg';
        //获取分类
        $this->load->model('cate_m');
        $data['catelist'] =$this->cate_m->get_all_cates();

        //右侧登录调用收藏贴子数
        $favorites=$this->db->select('favorites')->where('uid',$this->session->userdata('uid'))->get('favorites')->row_array();
        if(!@$favorites['favorites']){
                @$favorites['favorites'] =0;
        }

        //右侧登录处调用提醒数
        $notices= $this->db->select('notices')->where('uid',$this->session->userdata('uid'))->get('users')->row_array();
        $data['users'] = array('favorites'=>@$favorites['favorites'],'notices'=>@$notices['notices']);

        //右侧调用关注数
        $follows= $this->db->select('follows')->where('uid',$this->session->userdata('uid'))->get('users')->row_array();
        $data['users']['follows'] = @$follows['follows'];

        //底部菜单(单页面)
        $this->load->model('page_m');
        $data['page_links'] = $this->page_m->get_page_menu(10,0);

        // 渲染模板 兼容两种方式
        $this->load->vars($data);
        foreach ($data as $key=>$value) {
            $this->smarty->assign($key, $value);
        }
    }


    protected function _get_article($article_id){
        $aid = intval($article_id);

        if (!$aid){
            return show_error('不存在的文章');
        }
        // 引入模型
        $this->load->model('article_m');
        $this->load->model('article_content_m');

        $article = $this->article_m->get($aid);
        $content = $this->article_content_m->get($aid);

        if (!$article) {
            return show_error('不存在的文章');
        }
        $article['content'] = $content['content'];

        if ($article['article_type'] == article_m::TYPE_CONTEST)
        {
            $data = $this->_get_contest($article['type_id']);
        }
        if (empty($data)){
            return show_error('不存在的竞赛');
        }

        $data['col'] = $article['column_id'];
        $data['article'] =$article;

        $this->load->model('comment_m');
        $query = $this->comment_m->get_comment(0,20,$article_id,$order='desc');
        $data['comment'] = $query;
        return $data;
    }


    /**
     * 获取竞赛的基本信息
     * @param int $contest_id
     * @return multitype:string
     */
    protected function _get_contest($contest_id){
        $cid = intval($contest_id);

        if (!$cid){
            return show_error('不存在的竞赛');
        }
        // 引入模型
        $this->load->model('univs_m');
        $this->load->model('contest_m');
        $this->load->model('contest_regist_config_m');
        $this->load->model('contest_menu_m');

        $cInfo = $this->contest_m->get($cid);

        if (empty($cInfo)){
            return show_error('不存在的竞赛');
        }
        $config = $this->contest_regist_config_m->get_normal($cid);

        if (!empty($config['type']) && $config['type']==2) {
            $t = date('Y-m-d H:i:s');
            if ($t >= $cInfo['regist_start_time'] && $t <= $cInfo['regist_end_time']) {
                $data['show_enter'] = true;
            }

            $et = date('Y-m-d H:i:s', strtotime('-2 hours'));
            if ($t >= $cInfo['contest_start_time'] && $et <= $cInfo['contest_end_time']) {
                $data['show_result'] = true;
            }

        }
        $data['reconf'] = $config;

        $univs_id = $cInfo['univs_id'];
        if (!empty($cInfo['parent_id'])){
            $pInfo = $this->contest_m->get($cInfo['parent_id']);
            if ($pInfo) {
                $cInfo['parent_url'] = $pInfo['contest_url'];
            }
        }
        $cInfo['sons_num'] = 0;
        $sons = $this->contest_m->count_subcontest($cid);
        if ($sons) {
            $cInfo['sons_num'] = $sons;
        }
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;

        $colums = $this->contest_menu_m->list_colume_by_contestid($contest_id);
        if (empty($colums)) {
            $colums = Contest_m::$columNames;
        }
        $data['contest'] = $cInfo;
        $data['colums'] = $colums;

        if ($cInfo['contest_level'] > Contest_m::LEVEL_SCHOOL) {
            $contest_short = $cInfo['contest_url'];
        } else {
            $contest_short = $univs_info['short_name'] . '/' . $cInfo['contest_url'];
        }
        $data['contest_url'] = $contest_short;

        if ($this->is_login && isset($config['session']) && $config['type'] == 2) {
            // 本人是否有报名信息
            $this->load->model('team_m');
            $teamInfo = $this->team_m->get_by_user_contest_session($this->user_info['uid'], $contest_id, $config['session']);

            $data['teamInfo'] = $teamInfo;
        }

        return $data;
    }


    /**
     * 根据contestid查看是否有权限
     */
    protected function _cheak_uid_by_cid($cid) {
        if (!$cid) {
            return false;
        }
        $contest = $this->contest_m->get($cid);
        $uid = $this->session->userdata('uid');
        $see = false;
        if (($uid == $contest['create_user_id']) || $this->auth->is_admin()) {
            $see = true;
        }
        if (!$see) {
            $tmpPid = $contest['parent_id'];
            while($tmpPid) {
                $tmpc = $this->contest_m->get($tmpPid);
                if ($tmpc && $tmpc['create_user_id'] == $uid) {
                    $see = true;
                    break;
                }
                $tmpPid = isset($tmpc['parent_id']) ? $tmpc['parent_id'] : 0;
            }
        }
        return $see;
    }

}


class Admin_Controller extends Base_Controller{

    public $pageConfig = array(
            'uri_segment' => 4,
            'use_page_numbers' => TRUE,
            'base_url' => '',
            'total_rows' => 0,
            'per_page' => 20,
            'prev_link' => '&larr;',
            'prev_tag_open' => '<li class=\'prev\'>',
            'prev_tag_close' => '</li',
            'cur_tag_open' => '<li class=\'active\'><span>',
            'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'next_link' => '&rarr;',
            'next_tag_open' => '<li class=\'next\'>',
            'next_tag_close' => '</li>',
            'first_link' => '首页',
            'first_tag_open' => '<li class=\'first\'>',
            'first_tag_close' => '</li>',
            'last_link' => '尾页',
            'last_tag_open' => '<li class=\'last\'>',
            'last_tag_close' => '</li>',
            'num_links' => 10,
    );
    function __construct(){
        parent::__construct();
        $this->load->database();
        //载入后台模板
        $this->load->set_admin_theme();
        //网站设定
        $data['items']=$this->db->get('settings')->result_array();
        $data['settings']=array(
                'site_name'=>$data['items'][0]['value'],
                'welcome_tip'=>$data['items'][1]['value'],
                'short_intro'=>$data['items'][2]['value'],
                'show_captcha'=>$data['items'][3]['value'],
                'site_run'=>$data['items'][4]['value'],
                'site_stats'=>$data['items'][5]['value'],
                'site_keywords'=>$data['items'][6]['value'],
                'site_description'=>$data['items'][7]['value'],
                'money_title'=>$data['items'][8]['value'],
                'per_page_num'=>$data['items'][9]['value']
         );
        $this->load->vars($data);
        /** 加载验证库 */
        $this->load->library('auth');
        /** 检查登陆 */
        $group_type = $this->session->userdata('group_type');
        $this->load->library('myclass');
        if(!$this->auth->is_login()){
            $this->myclass->notice('alert("管理员未登录或非管理员");window.location.href="'.site_url('user/login').'";');
            exit;
        }
        if(!$this->auth->is_admin()){
            $this->myclass->notice('alert("无权访问此页");window.location.href="/";');
            exit;
        }
    }
}

class Install_Controller extends Base_Controller {
    function __construct(){
        parent::__construct();
        //载入前台模板
        $this->load->set_front_theme('default');
    }
}


class Other_Controller extends Base_Controller {
    function __construct(){
        parent::__construct();
        $this->load->database();
        //载入前台模板
        $this->load->set_front_theme('default');
    }
}