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
}