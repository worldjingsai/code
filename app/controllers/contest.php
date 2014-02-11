<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Contest extends SB_controller{
    function __construct (){
        parent::__construct();
        $this->load->model('contest_m');
        $this->load->model('univs_m');
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