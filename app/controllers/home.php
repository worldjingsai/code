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
        $this->load->model('index_m');
        $data['provices'] = $this->index_m->get_all_province();
        $this->tplData = $data;
        $this->display("index/index.html");
    }
}