<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Mycontest extends SB_controller{
	function __construct (){
		parent::__construct();
		$this->load->model('contest_m');
		$this->load->model('univs_m');
		$this->load->library('myclass');
		$this->load->model('user_m');
		if (!$this->is_login) {
			$this->myclass->notice('alert("您还未登录请登录后再操作");window.location.href="/user/login";');
			return 0;
		}
	}

	public function index($page = 1){
		$this->my($page);
	}

	/**
	 * 我创建的竞赛
	 */
	public function my($page = 1)
	{
		$uid = $this->session->userdata ('uid');

		//分页
		$limit = 20;
		$config = $this->pageConfig;
		$config['uri_segment'] = 3;
		$config['base_url'] = site_url('mycontest/my/');
		$config['total_rows'] = $this->contest_m->count_contest(0, $uid);
		$config['per_page'] = $limit;

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$start = ($page-1)*$limit;
		$data['pagination'] = $this->pagination->create_links();
		$this->load->model('contest_regist_config_m');
		$this->load->model('team_m');

		$rows = $this->contest_m->get_create_by_uid($uid, $start, $limit);

		if ($rows) {
			foreach ($rows as &$row) {
				$cid = $row['contest_id'];

				$conf = $this->contest_regist_config_m->get_normal($cid);
				if (!$conf) {
					$row['enter_members'] = 0;
				} else {
					// 需要获取到所有的子竞赛的竞赛id
					$cids = array($cid);
					$this->contest_m->get_all_son_ids($cid, $cids);

					$number = $this->team_m->count_detail_by_cid_session($cids, $conf['session']);
					$row['enter_members'] = $number;
				}
				$row['type_name'] = Contest_m::$typeNames[$row['contest_type']];
				$row['level_name'] = Contest_m::$leverNames[$row['contest_level']];

				if ($row['contest_level'] <= Contest_m::LEVEL_SCHOOL) {
					$univs = $this->univs_m->get_univs_info_by_univs_id($row['univs_id']);
					$row['contest_url'] = $univs['short_name'] . '/' . $row['contest_url'];
				}
				// 计算子竞赛的数量
				$row['sons'] = $this->contest_m->count_subcontest($cid);
			}
		}
		$data['title'] = '我的竞赛';
		$data['rows'] = $rows;
		$this->load->view('mycontest', $data);
	}


	/**
	 * 我创建的竞赛的子竞赛
	 * $cid是一条链路 id_sid_ssid...
	 */
	public function sons($cid, $page = 1)
	{

		if (empty($cid)  || !preg_match('/^(\d|_)*$/', $cid)) {
			$this->myclass->notice('alert("没有选择竞赛");window.location.href="/mycontest/my";');
			return 0;
		}
		$uid = $this->session->userdata('uid');

		// 验证权限
		$cids = array();
		if (strpos($cid, '_') !== false) {
			$cids = explode('_', $cid);
			$ancestorcid = $cids[0];
		} else {
			$ancestorcid = $cid;
		}

		$cinfo = $this->contest_m->get($ancestorcid);
		if ($cinfo['create_user_id'] != $uid) {
			$this->myclass->notice('alert("没有权限");window.location.href="/mycontest/my";');
			return 0;
		}

		// 逐层判断是否是父id
		$isFather = true;
		$old = $ancestorcid;
		if (!empty($cids)) {

			$son = next($cids);
			while ($son) {
				$scinfo = $this->contest_m->get($son);
				if ($scinfo['parent_id'] != $old) {
					$isFather = false;
					break;
				}
				$old = $son;
				$son = next($cids);
			}
		}
		if (!$isFather) {
			$this->myclass->notice('alert("没有权限");window.location.href="/mycontest/my";');
			return 0;
		}
		//分页
		$limit = 20;
		$config = $this->pageConfig;
		$config['uri_segment'] = 4;
		$config['base_url'] = site_url('mycontest/sons/'.$cid);
		$config['total_rows'] = $this->contest_m->count_subcontest($old);
		$config['per_page'] = $limit;

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$start = ($page-1)*$limit;
		$data['pagination'] = $this->pagination->create_links();
		$this->load->model('contest_regist_config_m');
		$this->load->model('team_m');

		$rows = $this->contest_m->get_by_parentid($old, $start, $limit);

		if ($rows) {
			foreach ($rows as &$row) {
				$tcid = $row['contest_id'];
				$conf = $this->contest_regist_config_m->get_normal($tcid);
				if (!$conf) {
					$row['enter_members'] = 0;
				} else {
					$number = $this->team_m->count_team($tcid, $conf['session']);
					$row['enter_members'] = $number;
				}
				$row['type_name'] = Contest_m::$typeNames[$row['contest_type']];
				$row['level_name'] = Contest_m::$leverNames[$row['contest_level']];

				if ($row['contest_level'] <= Contest_m::LEVEL_SCHOOL) {
					$univs = $this->univs_m->get_univs_info_by_univs_id($row['univs_id']);
					$row['contest_url'] = $univs['short_name'] . '/' . $row['contest_url'];
				}
				// 计算子竞赛的数量
				$row['sons'] = $this->contest_m->count_subcontest($tcid);
			}
		}

		$pos = strrpos($cid, '_');
		$data['parent_cid_str'] = substr($cid, 0, $pos);
		$data['current_cid_str'] = $cid;
		$data['cinfo'] = $cinfo;
		$data['title'] = '我的竞赛';
		$data['rows'] = $rows;
		$this->load->view('mycontest_son', $data);
	}


	/**
	 * 根据cid获取参赛的队列表
	 * @param unknown $cid
	 * @param number $page
	 */
	public function my_team_list($cid, $page = 1) {

		$gets = $this->input->get(null, true);

		$uid = $this->session->userdata('uid');
		$act = $this->input->get('act', true);
		$mem = $this->input->get('mem', true);
		$limit = 100;

		$config = $this->pageConfig;
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['base_url'] = site_url('mycontest/my_team_list/' . $cid . '/');
		$config['url_arguments'] = $gets;
		$this->load->model('contest_regist_config_m');
		$this->load->model('team_m');
		$conf = $this->contest_regist_config_m->get_normal($cid);
		$contest = $this->contest_m->get($cid);
		if ($uid != $contest['create_user_id']) {
			return header('location:/mycontest/my');
		}

		// 需要获取到所有的子竞赛的竞赛id
		$cids = array($cid);

		$is_fee = isset($gets['is_fee']) ? intval($gets['is_fee']) : '-1';
		$is_up_imag = isset($gets['fee_image']) ? intval($gets['fee_image']) : '-1';
		$is_result = isset($gets['is_result']) ? intval($gets['is_result']) :'-1';
		$is_valid = isset($gets['is_checked']) ? intval($gets['is_checked']) : '-1';
		$tk = isset($gets['select']) ? $gets['select'] : '';
		$tv = isset($gets['keywords']) ? $gets['keywords'] :'';
		$config['total_rows'] = 0;
		if ($conf) {
			$this->contest_m->get_all_son_ids($cid, $cids);
			$config['total_rows'] = $this->team_m->count_detail_by_cid_session($cids, $conf['session'], $is_fee, $is_up_imag, $is_result, $tk, $tv, 1, $is_valid);
		}

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$start = ($page-1)*$limit;
		$data['pagination'] = $this->pagination->create_links();

		// 获取数据
		$rows = array();
		if($conf) {
			$conf['team_column'] = json_decode($conf['team_column'], true);
			$conf['member_column'] = json_decode($conf['member_column'], true);
			$conf['result_column'] = json_decode($conf['result_column'], true);
			if ($act == 'export') {
				$start = 0;
				$limit = $config['total_rows'];
			}
			$rows = $this->team_m->get_detail_by_cid_session($cids, $conf['session'], $start, $limit, $is_fee, $is_up_imag, $is_result, $tk, $tv, 1, $is_valid);
		}

		// 导出团队信息
		if ($act == 'export') {
			return $this->_export($rows, $conf, $mem, $contest);
		} else {
			$tids = $show_rows = $show_field = $members = array();
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$tids[$row['team_id']] = $row['team_id'];
				}
				$this->load->model('member_column_m');

				$members = $this->member_column_m->list_by_team_id($tids);
				$this->load->helper('team_helper');
				$show_rows = con_team_member($rows, $members);
				$show_field = get_show_field($conf);
			}
		}
		$data['gets'] = $gets;
		$data['url_query'] = http_build_query((array)$gets);
		$data['title'] = '我的竞赛';
		$data['rows'] = $show_rows;
		$data['field'] = $show_field;
		$data['contest'] = $contest;
		$data['conf'] = $conf;
		$this->load->view('mycontest_member', $data);
	}

	/**
	 * 根据cid获取放弃参赛的队列表
	 * @param unknown $cid
	 * @param number $page
	 */
	public function my_team_list_delete($cid, $page = 1) {

		$gets = $this->input->get(null, true);

		$uid = $this->session->userdata ('uid');
		$act = $this->input->get('act', true);
		$mem = $this->input->get('mem', true);
		$limit = 100;

		$config = $this->pageConfig;
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['base_url'] = site_url('mycontest/my_team_list_delete/' . $cid . '/');
		$config['url_arguments'] = $gets;
		$this->load->model('contest_regist_config_m');
		$this->load->model('team_m');
		$conf = $this->contest_regist_config_m->get_normal($cid);
		$contest = $this->contest_m->get($cid);
		if ($uid != $contest['create_user_id']) {
			return header('location:/mycontest/my');
		}

		$is_fee = isset($gets['is_fee']) ? $gets['is_fee'] : '-1';
		$is_up_imag = isset($gets['fee_image']) ? $gets['fee_image'] : '-1';
		$is_result = isset($gets['is_result']) ? $gets['is_result'] :'-1';
		$tk = isset($gets['select']) ? $gets['select'] : '';
		$tv = isset($gets['keywords']) ? $gets['keywords'] :'';
		$config['total_rows'] = 0;
		$status = team_m::STATUS_CANCLE;
		if ($conf) {
			$config['total_rows'] = $this->team_m->count_detail_by_cid_session($conf['contest_id'], $conf['session'], $is_fee, $is_up_imag, $is_result, $tk, $tv, $status);
		}

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$start = ($page-1)*$limit;
		$data['pagination'] = $this->pagination->create_links();

		// 获取数据
		$rows = array();
		if($conf) {
			$conf['team_column'] = json_decode($conf['team_column'], true);
			$conf['member_column'] = json_decode($conf['member_column'], true);
			$conf['result_column'] = json_decode($conf['result_column'], true);
			if ($act == 'export') {
				$start = 0;
				$limit = $config['total_rows'];
			}
			$rows = $this->team_m->get_detail_by_cid_session($cid, $conf['session'], $start, $limit, $is_fee, $is_up_imag, $is_result, $tk, $tv, $status);
		}

		// 导出团队信息
		if ($act == 'export') {
			return $this->_export($rows, $conf, $mem, $contest);
		} else {
			$tids = $show_rows = $show_field = $members = array();
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$tids[$row['team_id']] = $row['team_id'];
				}
				$this->load->model('member_column_m');

				$members = $this->member_column_m->list_by_team_id($tids);
				$this->load->helper('team_helper');
				$show_rows = con_team_member($rows, $members);
				$show_field = get_show_field($conf);
			}
		}
		$data['gets'] = $gets;
		$data['url_query'] = http_build_query((array)$gets);
		$data['title'] = '我的竞赛';
		$data['rows'] = $rows;
		$data['contest'] = $contest;
		$data['conf'] = $conf;
		$this->load->view('mycontest_member_delete', $data);
	}


	/**
	 * 根据cid获取参赛的队列表
	 * @param unknown $cid
	 * @param number $page
	 */
	public function sons_team_list($cid, $page = 1)
	{

		if (empty($cid)  || !preg_match('/^(\d|_)*$/', $cid)) {
			$this->myclass->notice('alert("没有选择竞赛");window.location.href="/mycontest/my";');
			return 0;
		}
		$uid = $this->session->userdata('uid');

		// 验证权限
		$cids = array();
		if (strpos($cid, '_') !== false) {
			$cids = explode('_', $cid);
			$ancestorcid = $cids[0];
		} else {
			$ancestorcid = $cid;
		}

		$cinfo = $this->contest_m->get($ancestorcid);
		if ($cinfo['create_user_id'] != $uid) {
			$this->myclass->notice('alert("没有权限");window.location.href="/mycontest/my";');
			return 0;
		}
		$data['cinfo'] = $cinfo;
		// 逐层判断是否是父id
		$isFather = true;
		$old = $ancestorcid;
		if (!empty($cids)) {

			$son = next($cids);
			while ($son) {
				$scinfo = $this->contest_m->get($son);
				if ($scinfo['parent_id'] != $old) {
					$isFather = false;
					break;
				}
				$old = $son;
				$son = next($cids);
			}
		}
		if (!$isFather) {
			$this->myclass->notice('alert("没有权限");window.location.href="/mycontest/my";');
			return 0;
		}

		$gets = $this->input->get(null, true);

		$act = $this->input->get('act', true);
		$mem = $this->input->get('mem', true);
		$limit = 100;

		$data['current_cid_str'] = $cid;
		$pos = strrpos($cid, '_');
		$data['parent_cid_str'] = substr($cid, 0, $pos);
		$cid = $old;
		$config = $this->pageConfig;
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['base_url'] = site_url('mycontest/my_team_list/' . $cid . '/');
		$config['url_arguments'] = $gets;
		$this->load->model('contest_regist_config_m');
		$this->load->model('team_m');
		$conf = $this->contest_regist_config_m->get_normal($cid);
		$contest = $this->contest_m->get($cid);

		$is_fee = isset($gets['is_fee']) ? $gets['is_fee'] : '-1';
		$is_up_imag = isset($gets['fee_image']) ? $gets['fee_image'] : '-1';
		$is_result = isset($gets['is_result']) ? $gets['is_result'] :'-1';
		$is_valid = isset($gets['is_checked']) ? intval($gets['is_checked']) : '-1';
		$tk = isset($gets['select']) ? $gets['select'] : '';
		$tv = isset($gets['keywords']) ? $gets['keywords'] :'';
		$config['total_rows'] = 0;
		if ($conf) {
			$config['total_rows'] = $this->team_m->count_detail_by_cid_session($conf['contest_id'], $conf['session'], $is_fee, $is_up_imag, $is_result, $tk, $tv, 1, $is_valid);
		}

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$start = ($page-1)*$limit;
		$data['pagination'] = $this->pagination->create_links();

		// 获取数据
		$rows = array();
		if($conf) {
			$conf['team_column'] = json_decode($conf['team_column'], true);
			$conf['member_column'] = json_decode($conf['member_column'], true);
			if ($act == 'export') {
				$start = 0;
				$limit = $config['total_rows'];
			}
			$rows = $this->team_m->get_detail_by_cid_session($cid, $conf['session'], $start, $limit, $is_fee, $is_up_imag, $is_result, $tk, $tv, 1, $is_valid);
		}

		// 导出团队信息
		if ($act == 'export') {
			return $this->_export($rows, $conf, $mem, $contest);
		} else {
			$tids = $show_rows = $show_field = $members = array();
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$tids[$row['team_id']] = $row['team_id'];
				}
				$this->load->model('member_column_m');

				$members = $this->member_column_m->list_by_team_id($tids);
				$this->load->helper('team_helper');
				$show_rows = con_team_member($rows, $members);
				$show_field = get_show_field($conf);
			}
		}
		$data['gets'] = $gets;
		$data['url_query'] = http_build_query((array)$gets);
		$data['title'] = '我的竞赛';
		$data['rows'] = $show_rows;
		$data['field'] = $show_field;
		$data['contest'] = $contest;
		$data['conf'] = $conf;
		$this->load->view('mycontest_sons_member', $data);
	}

	/**
	 * 显示一个团队信息
	 * @param int $team_id
	 */
	public function team_info($team_id) {
		$uid = $this->session->userdata ('uid');

		$this->load->model('team_m');
		$this->load->model('team_column_m');
		$this->load->model('member_column_m');
		$this->load->model('contest_regist_config_m');

		$teamInfo = $this->team_m->get($team_id);
		$teamColumn = $memberColumn = array();
		if ($teamInfo) {
			$team_id = $teamInfo['team_id'];
			$teamColumn = $this->team_column_m->get($team_id);
			$memberColumn = $this->member_column_m->list_by_team_id($team_id);
			$contest = $this->contest_m->get($teamInfo['contest_id']);
			if ($uid != $contest['create_user_id']) {
				$see = $this->_cheak_uid_by_cid($teamInfo['contest_id']);
				if (!$see) {
					return header('location:/mycontest/my');
				}
			}
		} else {
			return header('location:/mycontest/my');
		}
		$configs = $this->contest_regist_config_m->get_normal($teamInfo['contest_id']);
		if ($configs) {
			$configs['t'] = json_decode($configs['team_column'], true);
			$configs['m'] = json_decode($configs['member_column'], true);
		}
		$data['title'] = '团队信息';
		$data['team'] = $teamInfo;
		$data['t'] = $teamColumn;
		$data['m'] = $memberColumn;
		$data['conf'] = $configs;
		$data['contest'] = $contest;
		$this->load->view('show_team_info', $data);
	}

	/**
	 * 下载数据文件，只有竞赛创建者和团队创建者可以下载
	 * @param int $team_id
	 */
	public function result_file($team_id) {
		$uid = $this->session->userdata ('uid');

		$this->load->model('team_m');
		$this->load->model('contest_regist_config_m');

		$teamInfo = $this->team_m->get($team_id);
		$teamColumn = $memberColumn = array();
		if ($teamInfo) {
			$see = false;
			$team_id = $teamInfo['team_id'];
			$contest = $this->contest_m->get($teamInfo['contest_id']);
			if (($uid == $contest['create_user_id']) || ($uid = $teamInfo['create_user_id'])) {
				$see = true;
			}
			if (!$see) {
				$tmpPid = $contest['parent_id'];
				while($tmpPid) {
					$tmpc = $this->contest_m->get($tmpPid);
					if ($tmpc['create_user_id'] == $uid) {
						$see = true;
						break;
					}
					$tmpPid = $tmpc['parent_id'];
				}
			}
			if (!$see) {
				return header('location:/mycontest/my');
			}

		} else {
			return header('location:/mycontest/my');
		}

		if (empty($teamInfo['result_file'])) {
			return $this->myclass->notice('alert("未上交作品！");');
		}
		$file_dir = UPLOADPATH . 'paper/';
		$file_name = $teamInfo['result_file'];
		$show_name = $teamInfo['team_number'] . strrchr($file_name, '.');

		$fileName = $file_dir . $teamInfo['result_file'];

		// 本地文件
		if (file_exists($fileName)) {
			$file = fopen($file_dir . $teamInfo['result_file'],"r"); // 打开文件

			// 输入文件标签
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file_dir . $file_name));
			Header("Content-Disposition: attachment; filename=" . $show_name);

			echo fread($file,filesize($file_dir . $file_name));
			fclose($file);
			exit();

		// 七牛文件
		} else {
			$isQiniu = $this->config->item('is_use_qiniu');
			$qiniu = array('is_used' => $isQiniu);
			if ($isQiniu) {
				$this->config->load('qiniu');

				$params =array(
						'accesskey'=>$this->config->item('accesskey'),
						'secretkey'=>$this->config->item('secretkey'),
						'bucket'=>$this->config->item('bucket'),
						'file_domain'=>$this->config->item('file_domain'),
				);
				$this->load->library('qiniu_lib',$params);
				$url = $this->qiniu_lib->getDownUrl($file_name, $show_name);

				header('location:'.$url);
			}
		}
	}

	/**
	 *
	 */
	public function batch_process($cid = 0, $page=1)
	{
		$uid = $this->session->userdata ('uid');

		$refer = $this->input->server('HTTP_REFERER', true);

		$refer = str_replace(';', '', $refer);
		if (preg_match('/.*batch_process.*/', $refer)) {
			$refer = site_url('/mycontest/my_team_list/'.$cid.'/'.$page);
		}
		$contest = $this->contest_m->get($cid);
		if (empty($contest) ) {
			return show_error('查看错误', 404, '违法操作');
		}

		$see = false;
		if (($uid == $contest['create_user_id']) || $this->auth->is_admin()) {
			$see = true;
		}
		if (!$see) {
			$see = $this->_cheak_uid_by_cid($cid);
		}
		if (!$see) {
			return show_error('没有查看的权限', 404);
		}

		// 批量下载论文
		if ($this->input->post('batch_down')) {
			set_time_limit(0);
			@ini_set('memory_limit', "10240M");

			$this->load->model('contest_regist_config_m');

			// 批量下载论文
			$conf = $this->contest_regist_config_m->get_normal($cid);
			if (empty($conf) ) {
				echo "没有报名信息" . $cid;
				return false;
			}
			$session = $conf['session'];

			$file_dir = UPLOADPATH . 'paper/'.$cid.'/';
			$file_name = $cid.'_'.$session.'.zip';
			$zipFile = $file_dir.$file_name;

			if (FALSE !== ($data = @file_get_contents($zipFile))) {
				// 输入文件标签
				Header("Content-type: application/octet-stream");
				Header("Accept-Ranges: bytes");
				Header("Accept-Length: ".filesize($zipFile));
				Header("Content-Disposition: attachment; filename=" . $file_name);
				echo $data;
				exit();
			} else {
				return show_error('文件不存在', 409);
			}
		}

		// 批量生成密封号
		if ($this->input->post('seal_number')) {
			set_time_limit(0);
			@ini_set('memory_limit', "1024M");
			$this->load->model('seal_number_m');
			$res = $this->seal_number_m->make_number($cid);
			$message = '';
			if ($res) {
				$code = 0;
			} else {
				$code = 500;
				$message = '服务端生成错误！';
			}
			return show_json($code, $message);
		}

		$tids = array_slice($this->input->post(), 0, -1);
		$tids = array_map('intval', $tids);
		if(empty($tids)){
			$this->myclass->notice('alert("请选择需要操作的队伍!");window.location.href="'.$refer.'";');
		}
		if($this->input->post('batch_del')){
			if($this->db->where_in('fid',$tids)->delete('forums')){
				$this->myclass->notice('alert("批量删除团队成功！");window.location.href="'.$refer.'";');
			}
		}
		if($this->input->post('batch_fee')){
			if($this->db->where_in('team_id',$tids)->where('contest_id', $cid)->update('team', array('is_fee'=>1))){
				$this->myclass->notice('alert("批量更新缴费状态成功！");window.location.href="'.$refer.'";');
			}
		}
		if($this->input->post('batch_unfee')){
			if($this->db->where_in('team_id',$tids)->where('contest_id', $cid)->update('team', array('is_fee'=>0))){
				$this->myclass->notice('alert("批量更新缴费状态成功！");window.location.href="'.$refer.'";');
			}
		}

		// 审核通过
		if($this->input->post('batch_check')){
			if($this->db->where_in('team_id',$tids)->where('contest_id', $cid)->update('team', array('is_valid'=>1))){
				$this->myclass->notice('alert("批量更新审核状态成功！");window.location.href="'.$refer.'";');
			}
		}
		// 审核不通过
		if($this->input->post('batch_uncheck')){
			if($this->db->where_in('team_id',$tids)->where('contest_id', $cid)->update('team', array('is_valid'=>0))){
				$this->myclass->notice('alert("批量更新审核状态成功！");window.location.href="'.$refer.'";');
			}
		}


	}

	public function ajax_search_team(){
		$contest_id = intval($this->input->get('cid'));
		$session	= intval($this->input->get('session'));
		$is_fee	 = intval($this->input->get('is_fee'));
		$is_upload_fee_image = intval($this->input->get('is_upload_fee_image'));
		$page	   = intval($this->input->get('page'));
		$limit	  = 10;
		$this->load->model('team_m');
		$result['rows'] = $this->team_m->get_detail_by_cid_session($contest_id,$session,$is_fee,$is_upload_fee_image,$page,$limit);
		$this->load->view('search_team_result', $result);
	}


	/**
	 * 专门导出参赛队信息
	 * @param 数据列 $rows
	 * @param 报名配置文件 $conf
	 * @param 是否导出参赛者信息 $mem
	 * @param 竞赛信息 $contest
	 */
	protected function _export($rows, $conf, $mem, $contest)
	{
		// 导出团队信息
		if(!empty($rows)){
			$title = '"队号"';
			$mk = $sk = array();

			foreach($conf['team_column'] as $k=>$v) {
				if ($v[2] > 0) {

					$sk[$k] = $k;
					$title .= ',"'.$v[0].'"';
				}
			}
			if (!empty($conf['fee'])) {
				$title .= ',"是否缴费"';
				$title .= ',"是否上传缴费图片"';
			}
			if (!empty($conf['is_checked'])) {
				$title .= ',"是否审核通过"';
			}
			$title .= ',"团队组别","团队选题","是否上传作品"';

			$seal_num = array();
			if (!empty($conf['is_seal'])) {
				$this->load->model('seal_number_m');
				$seals = $this->seal_number_m->list_by_cid_session($conf['contest_id'], $conf['session']);
				if ($seals) {
					foreach ($seals as $se) {
						$seal_num[$se['team_id']] = $se['seal_number'];
					}
					$title .= ',"密封号"';
				}
			}

			// 导出团队信息
			if ($mem) {
				for($i=1; $i<=$conf['max_member']; $i++) {
					foreach($conf['member_column'] as $k=>$v) {
						if ($v[2] > 0) {
							$mk[$k] = $k;
							$title .= ',"队员'.$i.$v[0].'"';
						}
					}
				}
				$mt = array();
				foreach($rows as $k=>$v){
					$mt[$v['team_id']] = $v['team_id'];
				}

				$this->load->model('member_column_m');
				$members = $this->member_column_m->list_by_team_id($mt);
				$showMembers = array();
				foreach ($members as $tmpm) {
					$showMembers[$tmpm['team_id']][] = $tmpm;
				}
			}
			$title.="\r\n";
			$content = '';
			foreach($rows as $k=>$v){
				$content .= '"'.$v['team_number'].'"';
				foreach($sk as $kk) {
					$content .= ',"'.$v[$kk].'"';
				}
				// 是否缴费
				if (!empty($conf['fee'])) {
					if ($v['is_fee'] >= 1) {
						$content .= ',"是"';
					} else {
						$content .= ',"否"';
					}
					if ($v['fee_image']) {
						$content .= ',"是"';
					} else {
						$content .= ',"否"';
					}
				}
				// 是否审核
				if (!empty($conf['is_checked'])) {
					if ($v['is_valid']) {
						$content .= ',"是"';
					} else {
						$content .= ',"否"';
					}
				}
				$content.= ',"'.$v['team_level'] .'","'.$v['problem_number'].'"';
				if ($v['result_file']) {
					$content .= ',"是"';
				} else {
					$content .= ',"否"';
				}

				if ($seal_num) {
					$content .= ',"'. (empty($seal_num[$v['team_id']]) ? '' : $seal_num[$v['team_id']]) .'"';
				}
				if ($mem) {
					foreach($showMembers[$v['team_id']] as $mv) {
						foreach ($mk as $kk) {
							$content .= ',"'.$mv[$kk].'"';
						}
					}
				}
				$content.="\r\n";
			}
			$fname = $contest['contest_name'];
			if ($mem) {
				$fname .= '_团队和队员信息表';
			} else {
				$fname .= '_团队信息表';
			}
			return $this->exportCsv($fname . '.csv' , $title.$content);
		} else {
			return $this->myclass->notice('alert("没有报名团队");');
		}
	}
}