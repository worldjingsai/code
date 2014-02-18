<?php
/**
 * 静态页面的Controller
 */
class Other extends SB_Controller{

    public function about(){
       $this->display("other/about.html");
    }

    public function apply(){
        $this->display("other/apply.html");
    }

    public function connect(){
        $this->display("other/connect.html");
    }

    public function frands(){
        $this->display("other/frands.html");
    }
}