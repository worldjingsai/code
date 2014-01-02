<?php
/**
 * 环球竞赛网首页
 * @copyright (c) 2014, Kevin
 */

class Home extends SB_Controller{
    function __construct (){
        parent::__construct();
        $this->load->model('forum_m');
        $this->load->model('cate_m');
        $this->load->library('myclass');
        $this->load->model('link_m');
        $this->home_page_num=($this->config->item('home_page_num'))?$this->config->item('home_page_num'):20;
    }
    public function index (){
        $data['date'] = date("m月d日");
        $data['last_user']=$this->db->select('username',1)->order_by('uid','desc')->get('users')->row_array();
        //tags
        $this->load->model('tag_m');
        $data['taglist'] = $this->tag_m->get_latest_tags(15);
        $this->tplData = $data;
        $this->display("index/index.html");
    }
}