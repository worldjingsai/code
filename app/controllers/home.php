<?php
/**
 * 环球竞赛网首页
 * @copyright (c) 2014, Kevin
 */

class Home extends SB_Controller{
    function __construct (){
        parent::__construct();
        $this->load->library('myclass');
    }
    public function index (){
        $data['date'] = date("m月d日");
        $data['last_user']=$this->db->select('username',1)->order_by('uid','desc')->get('users')->row_array();
        $this->load->model('index_m');
        $data['taglist'] = $this->index_m->get_latest_tags(15);
        $this->tplData = $data;
        $this->display("index/index.html");
    }
}