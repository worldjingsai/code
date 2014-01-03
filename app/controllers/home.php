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
        $this->tplData = $data;
        $this->display("index/index.html");
    }
}