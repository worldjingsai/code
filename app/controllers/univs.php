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
    public function create($univs_id){
        $step = 1;
        if(isset($_GET['step']))
        {
            $step = intval($_GET['step']);
        }
        
        $univs_id = intval($univs_id);
        $this->load->model('univs_m');
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;
        $data['step'] = $step;
        $this->tplData = $data;
        if ($step == 1)
        {
            $this->display("contest/create_1.html");
        } else {
            $this->display("contest/create_2.html");
        }
        
    }
}