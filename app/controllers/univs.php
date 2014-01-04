<?php
/**
 * 高校首页
 */
class Univs extends SB_Controller{
    function __construct (){
        parent::__construct();
        $this->load->library('myclass');
    }
     
    public function index ($abc){

        $this->display("univs/index.html");
    }
}
