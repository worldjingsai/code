<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CRM
 *
 * @file        Smarty.php
 * @author		lizhonghua@360.cn
 * @date		2013-3-21
 * @desc
 */


class CI_Smarty {
	
	private $objSmarty;
	
	public function __construct()
	{
		$this->_initSmarty();
	}
	
	private function _initSmarty()
	{
		require_once BASEPATH.'conf/sysconf.php';
		global $CFG;
		$smartyConfig = $sysconfig['smarty'];
		if (!empty($CFG->config['smarty'])) {
			$smartyConfig = $CFG->config['smarty'];
		}
		require_once $smartyConfig['smarty_path'].'/Smarty.class.php';
		$this->objSmarty = new Smarty();
		$this->objSmarty->template_dir = $smartyConfig['template_dir'];
		$this->objSmarty->compile_dir = $smartyConfig['compile_dir'];
		$this->objSmarty->config_dir = $smartyConfig['config_dir'];
		$this->objSmarty->cache_dir = $smartyConfig['cache_dir'];
		$this->objSmarty->left_delimiter = $smartyConfig['left_delimiter'];
		$this->objSmarty->right_delimiter = $smartyConfig['right_delimiter'];
		$this->objSmarty->auto_literal = false;//允许模板中的分隔符旁边有空格
	}
	
	public function assign($strVar, $mixedValue)
	{
		$this->objSmarty->assign($strVar, $mixedValue);
	}
	
	public function display($strTpl)
	{
		$this->objSmarty->display($strTpl);
	}
	
	public function fetch($strTpl)
	{
	    return $this->objSmarty->fetch($strTpl);
	}
}