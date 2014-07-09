<?php

/**
 * 密封号模型
 * @author renlifeng
 *
 */

class Seal_number_m extends SB_Model
{

	function __construct ()
	{
		parent::__construct();
	}

	public $tb = 'seal_number';

	/**
	 * 随机生成8位密封号保证同一个竞赛没有相邻的号码
	 * @param int $contest_id
	 * 
	 * 算法：
	 *     取出一个竞赛打算团队ID，然后按照步长增加随机数
	 *     同一个竞赛的混排一次，然后总队分为两半，没半一个添加成一个队列
	 */
	public function make_number($contest_id, $base_number = 10000000)
	{
		$CI =& get_instance();
		$CI->load->model('contest_m');
		$CI->load->model('contest_regist_config_m');
		$CI->load->model('team_m');
		$son_ids = $CI->contest_m->get_all_son_ids($contest_id);
		if (empty($son_ids)) {
			return TRUE;
		}
		
		$all_tids = $all_tinfo = array();
		
		foreach ($son_ids as $cid) {
			$conf = $CI->contest_regist_config_m->get_normal($cid);
			if (empty($conf)) {
				continue;
			}
			$session = $conf['session'];
			$teams = $CI->team_m->list_all_by_cid_session($cid, $session);
			if (empty($teams)) {
				continue;
			}
			$tids = array();
			foreach ($teams as $team) {
				$tids[] = $team['team_id'];
				$all_tinfo[$team['team_id']] = array('contest_id' => $cid);
			}
			$tids = array_shift($tids);
			$all_tids = array_merge($all_tids, $tids);
		}
		$sum = count($all_tids);
		$half = ceil($sum / 2);

		$i = 0;
		while ($i < $half) {
			$all_tinfo[$all_tids[$i]]['seal_number'] = $base_number++;
			if (isset($all_tids[$i+$half])) {
				$all_tinfo[$all_tids[$i+$half]]['seal_number'] = $base_number++;
			}
			$i++;
		}
	}
	
	/**
	 * 根据竞赛ID和届数删除数据
	 * @param unknown $cid
	 * @param unknown $session
	 */
	function delete_by_cid_session($cid, $session) {
		$this->db->where('contest_id', $cid)->where('session', $session)->delete($this->tb);
		return mysql_affected_rows();
	}
	
	
	function get_comment($page,$limit,$fid,$order='desc'){
		$this->db->select('comments.*, u.uid, u.username, u.avatar, u.signature');
		$query=$this->db->from('comments')
		->where('fid',$fid)
		->join ( 'users u', "u.uid=comments.uid" )
		->order_by('comments.replytime',$order)
		->limit($limit,$page)
		->get();
		return $query->result_array();
	}
	
}
