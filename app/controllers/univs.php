<?php
/**
 * 高校首页
 */
class Univs extends SB_Controller{
    function __construct (){
        parent::__construct();
        $this->load->model('univs_m');
        $this->load->library('myclass');
    }
     
    public function index($univs_id){
        $univs_id = intval($univs_id);
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;
        $this->tplData = $data;
        $this->display("univs/index.html");
    }

    public function create($univs_id){
        $step = 1;
        $contest_id = 0;
        $univs_id = intval($univs_id);
        if(isset($_GET['step']))
        {
            $step = intval($this->input->get('step'));
        }
        $this->load->model('contest_m');
        $this->load->model('univs_m');

        $data = array();
        if ($step > 5)
        {
            return false;
        }
        
        if ($step == 2)
        {
            $data['contest_name'] = $this->input->post('contest_name', true);
            $data['contest_url'] = $this->input->post('contest_url', true);
            $data['contest_type'] = intval($this->input->post('contest_type'));
            $data['regist_start_time'] = $this->input->post('regist_start_time', true);
            $data['regist_end_time'] = $this->input->post('regist_end_time', true);
            $data['contest_start_time'] = $this->input->post('contest_start_time', true);
            $data['contest_end_time'] = $this->input->post('contest_end_time', true);
            
            $data['univs_id'] = $univs_id;
            $data['create_time'] = date('Y-m-d H:i:s'); 
            // TODO
            $data['create_user_id'] = 0;
            $contest_id = $this->contest_m->add($data);
            unset($data);
        } else {
            $data['contest_id'] = intval($this->input->post('contest_id'));
            $data['title'] = $this->input->post('title', true);
            $data['content'] = $this->input->post('content', true);
        }
        
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $show_data['contest_id'] = $contest_id;
        $show_data['university'] = $univs_info;
        $show_data['step'] = $step;
        $this->tplData = $show_data;
        if ($step == 1)
        {
            $this->display("contest/create_1.html");
        } else {
            $this->display("contest/create_2.html");
        }
    }
}