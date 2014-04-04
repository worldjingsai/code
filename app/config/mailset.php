<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


//mailset
$config = array (
  'protocol' => 'smtp',//邮件方式
  'smtp_host' => 'smtp.exmail.qq.com',//设置 SMTP 服务器的地址
  'smtp_port' => '25',//设置 SMTP 服务器的端口，默认为 25
  'smtp_user' => 'service@worldjingsai.com',//发信人邮件地址。
  'smtp_pass' => 'liqinwei!112',//SMTP 身份验证密码
  'smtp_crypto' => ''
);

/* End of file mailset.php */
/* Location: ./application/config/mailset.php */