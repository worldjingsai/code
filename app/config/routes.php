<?php  if ( ! defined('BASEPATH')){ exit('No direct script access allowed');}

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['admin']='/admin';
$route['add.html']='forum/add';
$route['qq_login'] = 'oauth/qqlogin';
$route['qq_callback'] = 'oauth/qqcallback';
$route['forum/flist/(:num)'] = 'forum/flist/$1';
$route['forum/view/(:num)'] = 'forum/view/$1';
$route['tag/index/(:any)'] = 'tag/index/$1';
$route['([a-z]+)'] = 'univs/index/$1';
$route['([a-z]+)/(outer|inner)/(\d+)'] = 'univs/clist/$1_$2_$3';
$route['([a-z]+)/([a-z|\d]+)'] = 'contest/index/$1';