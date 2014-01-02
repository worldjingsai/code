<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CRM
 *
 * @file        sysconf.php
 * @author		lizhonghua@360.cn
 * @date		2013-3-21
 * @desc
 */


$sysconfig['smarty'] = array(
		'smarty_path' => BASEPATH.'libraries/smarty',
	    'template_dir' => BASEPATH.'../static/common/template',
        #'template_dir' => BASEPATH.'../application/static/sales/template',
		'compile_dir' =>   BASEPATH.'../data/compile_dir',
		'config_dir' =>   BASEPATH.'../data/config_dir',
		'cache_dir' =>  BASEPATH.'../data/cache_dir',
		'left_delimiter' =>  '<%',
		'right_delimiter' =>  '%>',
);
