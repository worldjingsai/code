<?php
/**
 * 高校首页
 */
class Univs extends SB_Controller{

    public $limit = 15;
    function __construct (){
        parent::__construct();
        $this->load->model('univs_m');
        $this->load->model('contest_m');
        $this->load->model('index_m');
    }

    /**
     * 根据高校昵称获取高校信息
     * 如果高校昵称不存在则查询竞赛短地址
     */
    public function index(){
        $args = func_get_args();
        $short_name = $args[0];
        $contest_url = isset($args[1]) ? $args[1] : '';

        if($this->auth->is_login()){
            $data['is_login'] = true;
        }
        $univs_info = $this->univs_m->get_univs_info_by_univs_short_name($short_name);

        $univs_id = isset($univs_info['univs_id']) ? $univs_info['univs_id'] : 0;

        // 如果有竞赛url或者不是一个学校，查看是否有这个是竞赛
        if(empty($univs_info)){
            // 查询是否有竞赛存在
            $contestInfo = $this->contest_m->get_contest_by_short_name($univs_id, $short_name);
            array_shift($args);

            if ($contestInfo) {
                return $this->_show_contest($contestInfo, $args);
            }
            $this->myclass->notice('alert("该高校或者竞赛不存在");window.location.href="'.site_url('/').'";');
        }

        if ($contest_url) {
            $args = array_slice($args, 2);
            // 查询是否有竞赛存在
            $contestInfo = $this->contest_m->get_contest_by_short_name($univs_id, $contest_url);
            if ($contestInfo) {
                return $this->_show_contest($contestInfo, $args);
            }
            $this->myclass->notice('alert("该高校竞赛不存在");window.location.href="'.site_url('/').'";');
        }

        // 显示学校信息
        $data['university'] = $univs_info;
        $schooleContests = $this->_schoolcList(intval($univs_info['univs_id']), 0);
        $publicContests = $this->_cList(0);
        $data['schooleContests'] = $schooleContests;
        $data['publicContests'] = $publicContests;
        $data['action'] = 'index';
        $this->tplData = $data;
        $this->display("univs/index.html");
    }

    /**
     * 学校竞赛列表
     */
    public function clist($univs_short_name_type_page){
        $params     = explode('_', $univs_short_name_type_page);
        if(empty($params) || !is_array($params)){
            $this->myclass->notice('alert("该高校不存在");window.location.href="'.site_url('/').'";');
        }
        $univs_short_name = $params[0];
        $type             = $params[1];
        $offset           = intval($params[2]);
        $univs_info = $this->univs_m->get_univs_info_by_univs_short_name($univs_short_name);
        $data['university'] = $univs_info;
        $univs_id           = $univs_info['univs_id'];
        if($type == 'inner'){
            $list = $this->_schoolcList($univs_id,$offset);
            $data['action'] = 'schoolContest';
            $subTitle = '校内竞赛';
        }elseif($type == 'outer'){
            $data['action'] = 'allContest';
            $list = $this->_cList($offset);
            $subTitle = '全部竞赛';
        }
        $data['subTitle'] = $subTitle;
        $data['list'] = $list;
        $this->tplData = $data;
        $this->display("contest/univs_contest_list.html");
    }


    /**
     * 显示一个竞赛
     * args[0],栏目
     * args[1],分页
     */
    protected function _show_contest($cInfo, $args)
    {
        // 引入模型
        $this->load->model('article_m');
        $this->load->model('article_content_m');

        $cid = $cInfo['contest_id'];

        $pageStr = '';
        if (isset($args[1])) {
            $pageStr = $args[1];
        }

        // 是否是分支赛事
        if (isset($args[0]) && $args[0] == 'branch') {
            $data = $this->_get_contest($cid);
            $subContest = $this->contest_m->list_subcontest($cid);
            $data['son_contest'] = $subContest;
            $tpl = 'contest/son_contest_list.html';
            $data['col'] = 0;

        } elseif (strpos($pageStr, '.html') !== FALSE) {
            $article_id = intval(str_replace('.html', '', $pageStr));
            $data = $this->_get_article($article_id);
            $tpl = 'contest/contest_article.html';
        } else {
            $data = $this->_get_contest($cid);
            $page = $pageStr ? intval($pageStr) : 1;
            $limit = $this->limit;

            $colums = Contest_m::$columNames;

            $col = isset($args[0]) ? $args[0] : Contest_m::COLUM_NOTICE;
            if (!$col || !isset($colums[$col])){
                $col = Contest_m::COLUM_NOTICE;
            }

            $start = ($page - 1) * $limit;

            $article = $this->article_m->get_all_contest(Article_content_m::TYPE_CONTEST, $cid, $col, $start, $limit);

            $data['col'] = $col;
            $data['article'] = $article;
            $tpl = 'contest/univs_contest_col_list.html';
        }

        $this->tplData = $data;
        $this->display($tpl);
    }

    /**
     * 创建竞赛
     * @param int $univs_id
     * @return boolean
     */
    public function create($univs_id){
        if (!$this->is_login) {
            $this->myclass->notice('alert("请登录后再操作");window.location.href="/user/login";');
            return 0;
        }
        $contest_id = 0;
        $univs_id = intval($univs_id);
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        if (empty($univs_info)) {
            $this->myclass->notice('alert("学校不存在");window.location.href="/";');
            return 0;
        }
        $show_data['action'] = 'create';
        $show_data['university'] = $univs_info;

        $data = array();
        if ($_POST) {
            $data['contest_name'] = $this->input->post('contest_name', true);
            $data['contest_url'] = $this->input->post('contest_url', true);
            $data['contest_type'] = intval($this->input->post('contest_type'));
            $data['contest_level'] = intval($this->input->post('contest_level'));
            $data['regist_start_time'] = $this->input->post('regist_start_time', true);
            $data['regist_end_time'] = $this->input->post('regist_end_time', true);
            $data['contest_start_time'] = $this->input->post('contest_start_time', true);
            $data['contest_end_time'] = $this->input->post('contest_end_time', true);
            $data['contest_bbs'] = $this->input->post('contest_bbs', true);

            $data['old_url'] = $this->input->post('old_url', true);
            $data['remark'] = $this->input->post('remark', true);

            $parent_url = $this->input->post('parent_url', true);
            $parentInfo = $this->_get_contest_by_uri($parent_url);
            if (!empty($parentInfo['contest_id'])) {
                $data['parent_id'] = $parentInfo['contest_id'];
            }

            $data['univs_id'] = $univs_id;
            $data['create_time'] = date('Y-m-d H:i:s');

            $data['create_user_id'] = $this->user_info['uid'];

            $contest_id = $this->input->post('contest_id', true);
            if ($contest_id) {
                $this->contest_m->update($contest_id, $data);
            } else {
                $contest_id = $this->contest_m->add($data);
                $this->load->model('univs_contest_m');
                if($contest_id){
                    $addData = array(
                        'univs_id' => $univs_id,
                        'contest_id' => $contest_id,
                    );
                    $this->univs_contest_m->add($addData);
                }
            }
            if ($data['contest_level'] > Contest_m::LEVEL_SCHOOL) {
                $contest_short = $data['contest_url'];
            } else {
                $contest_short = $univs_info['short_name'] . '/' . $data['contest_url'];
            }

            redirect($contest_short);
        }

        $show_data['show_more'] = $this->input->get('show_more', true);
        $show_data['contest'] = array();
        $this->tplData = $show_data;
        $this->display("contest/create_1.html");
    }


    /**
     * 获取parentID
     */
    protected function _get_contest_by_uri($uri)
    {
        $short_name = $contest_url = '';
        $univs_id = 0;
        if (strpos($uri, '/')) {
            list($short_name, $contest_url) = explode('/', $uri);
            $univsInfo = $this->univs_m->get_univs_info_by_univs_short_name($short_name);
            if (empty($univsInfo['univs_id'])) {
                return 0;
            }
            $univs_id = $univsInfo['univs_id'];
        } else {
            $contest_url = $uri;
        }

        $cInfo = $this->contest_m->get_contest_by_short_name($univs_id, $contest_url);
        return $cInfo;
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
     * 添加文章和编辑文章
     * @param int $contest_id
     */
    public function content($contest_id){
        if (!$this->is_login) {
            $this->myclass->notice('alert("请登录后再操作");window.location.href="/user/login";');
            return 0;
        }
        $cid = intval($contest_id);

        $data = $this->_get_contest($cid);
        if (empty($data)){
            return show_error('不存在的竞赛');
        }

        $this->load->model('article_m');
        $this->load->model('article_content_m');

        // 只有创建者可以编辑文章
        if ($_POST) {
            $col = intval($this->input->post('col'));
            if ($col == 1){
                $column_id = Contest_m::COLUM_NOTICE;
            } elseif ($col== 2){
                $column_id = Contest_m::COLUM_ABOUT;
            } elseif ($col == 3){
                $column_id = Contest_m::COLUM_PROBLEM;
            } elseif ($col == 4){
                $column_id = Contest_m::COLUM_WINNER;
            }

            $article_id = intval($this->input->post('article_id'));

            $articleData = array();
            $articleData['article_type'] = Article_m::TYPE_CONTEST;
            $articleData['column_id'] = $column_id;
            $contest_id = intval($this->input->post('contest_id'));
            $articleData['type_id'] = $contest_id;
            $articleData['title'] = $this->input->post('title', true);
            $articleData['create_time'] = date('Y-m-d H:i:s');

            $articleData['create_user_id'] = $this->user_info['uid'];;

            $contentdata['content'] = $this->input->post('content');
            // 如果是更新
            if ($article_id) {
                $this->article_m->update($article_id, $articleData);
                $this->article_content_m->update($article_id, $contentdata);
                return show_json(0, '更新成功', array('return_url' => '/'.$data['contest_url'].'/2/'.$article_id.'.html'));
            } else {
                $article_id = $this->article_m->add($articleData);
                $contentdata['article_id'] = $article_id;
                $this->article_content_m->add($contentdata);
                return show_json(0, '添加成功', array('return_url' => '/'.$data['contest_url'].'/2/'.$article_id.'.html'));
            }
        }

        $col = $this->input->get('col');
        if (!$col || !isset($colums[$col])){
            $col = Contest_m::COLUM_NOTICE;
        }
        $data['col'] = $col;
        $this->tplData = $data;
        $this->display('contest/create_2.html');
    }

    /**
     * 显示学校的竞赛列表
     */
    protected function _schoolcList($univs_id,$offset){
        if(!$univs_id){
            return show_error('错误的学校ID');
        }
        $offset = $this->input->get('offset');
        $limit = 20;
        if(empty($offset)){
            $offset = 0;
        }
        $cids = $this->contest_m->get_all_contest($univs_id, $offset, $limit);
        $cid = array();
        $cList = array();
        if($cids){
            foreach ($cids as $key=>$value){
                $cid[$value['contest_id']] = $value['contest_id'];
            }
            $cList = $this->contest_m->listByCid($cid);
        }
        return $cList;
    }

    /**
     * 显示所有的竞赛列表
     */
    protected function _cList($offset){
        $limit = 20;
        $offset = $this->input->get('page');
        if(empty($offset)){
            $offset = 0;
        }
        $cList = $this->contest_m->listPublic($offset, $limit);
        return $cList;
    }

    /**
     * 异步获取所有高校
     */
    public function ajax_all_univs(){
        $provinces = $this->index_m->get_all_province();
        $universities = $this->index_m->get_all_university();
        if(is_array($universities) && !empty($universities)){
            foreach($universities as $university){
                if(array_key_exists($university['provs_id'], $provinces)){
                    $provinces[$university['provs_id']]['universities'][] = $university;
                }
            }
        }
        $data['provincs'] = $provinces;
        $this->show_json($data);
    }
}