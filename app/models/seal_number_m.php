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
	public function make_number($contest_id, $base_number = 10000001)
	{
		$CI =& get_instance();
		$CI->load->model('contest_m');
		$CI->load->model('contest_regist_config_m');
		$CI->load->model('team_m');
		$son_ids = $CI->contest_m->get_all_son_ids($contest_id);
		$son_ids[] = $contest_id;
		
		$parent_conf = $CI->contest_regist_config_m->get_normal($contest_id);
		if (empty($parent_conf)) {
			return TRUE;
		} else {
			$s_session = $parent_conf['session'];
		}
		
		$all_tids = $all_tinfo = array();

		$this->db->delete($this->tb, array('contest_id' => $contest_id, 'session' => $s_session));
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
				$all_tinfo[$team['team_id']] = array('team_id' => $team['team_id'], 'contest_id' => $contest_id, 'session' => $s_session);
			}
			shuffle($tids);
			$all_tids = array_merge($all_tids, $tids);
		}
		
		if (empty($all_tinfo)) {
			return TRUE;
		}
		// 对半截开保证两列的竞赛ID不同
		$sum = count($all_tids);
		$half = ceil($sum / 2);
		$onelist = array_slice($all_tids, 0, $half);
		$twolist = array_slice($all_tids, $half);
		unset($all_tids);
		// 再次打乱
		shuffle($onelist);
		shuffle($twolist);
		$i = 0;
		while ($i < $half) {
			$all_tinfo[$onelist[$i]]['seal_number'] = $base_number++;
			if (isset($twolist[$i])) {
				$all_tinfo[$twolist[$i]]['seal_number'] = $base_number++;
			}
			$i++;
		}

		$this->db->insert_batch($this->tb, $all_tinfo);
		return $this->db->affected_rows();
	}


	/**
	 * 根据竞赛ID和届数删除数据
	 * @param unknown $cid
	 * @param unknown $session
	 */
	function delete_by_cid_session($cid, $session) {
		$this->db->where('contest_id', $cid)->where('session', $session)->delete($this->tb);
		return $this->db->affected_rows();
	}
	
	/**
	 * 根据生成的竞赛的ID获取密封号
	 * @param unknown $scid
	 * @return multitype:
	 */
	function list_by_cid_session($scid, $session) {
		if (empty($scid) || empty($session)) {
			return array();
		}
		$query = $this->db->where('contest_id', $scid)->where('session', $session)->get($this->tb);
		return $query->result_array();
	}
}
