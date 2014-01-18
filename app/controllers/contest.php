<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Contest extends SB_controller{
    function __construct (){
        parent::__construct();
        $this->load->model('forum_m');
        $this->load->model('cate_m');
        $this->load->library('myclass');
    }
    
    public function index($cid, $page=1){
        
    }
    
    /**
     * 检测竞赛的URL是否被占用
     */
    public function ajax_chkuri(){
        $type = $this->input->post('contest_type');
        $uri  = $this->input->post('contest_url');
        if($type == 1){ // 校内级别
            $univs_id = intval($this->input->post('univs_id'));
            $bol      = $this->contest_m->check_contest_exist_in_univs($uri, $univs_id);
        }elseif($type == 2 || $type == 3 || $type == 4){ // 全国级别
            $bol = $this->contest_m->check_contest_exist_in_nation($uri);
        }else{
            $bol = false;
        }
        return $bol;
    }
}