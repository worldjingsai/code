<?php
/**
 * 静态页面的Controller
 */
class Other extends SB_Controller{
    
    public function about(){
       $this->display("other/about.html");
    }
}