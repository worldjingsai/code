<?php
/**
 * 文件路径权限
 */
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
 *  These modes are used when working with fopen()/popen()
 */

define('FOPEN_READ',                          'rb');
define('FOPEN_READ_WRITE',		      'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',      'wb');  // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',		      'ab');
define('FOPEN_READ_WRITE_CREATE',	      'a+b');
define('FOPEN_WRITE_CREATE_STRICT',	      'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',      'x+b');

class Constants{
    public static $success  =   0;
    public static $failured =   -1;
    
    public static $err_message = array(
        0    => '成功',
        -1   => '失败',
    );
}