<?php
/**
 * 高校首页
 */
class Univs extends SB_Controller{
    function __construct (){
        parent::__construct();
        $this->load->library('myclass');
    }
     
    public function index($univs_id){
        $univs_id = intval($univs_id);
        $this->load->model('univs_m');
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;
        $this->tplData = $data;
        $this->display("univs/index.html");
    }
}