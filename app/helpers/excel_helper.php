<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('cumcm_formate'))
{
	function cumcm_formate($team, $member)
	{
		$result = array();
		foreach ($team as $k => $t) {
			$result[$t['team_id']] = $t;
		}
		$mc = array();
		foreach ($member as $k => $mem) {
			$tid = $mem['team_id'];
			if (empty($mc[$tid])) {
				$mc[$tid] = 1;
			} else {
				$mc[$tid] ++;
			}

			foreach ($mem as $sk => $sv) {
				$result[$tid][$sk.'_'.($mc[$tid])] = $sv;
			}
		}

		return $result;
	}
}

if ( ! function_exists('team_formate'))
{
	function team_formate($conf, $team, $mem, $fname)
	{
		$CI =& get_instance();
		$data = array();
		// 导出团队信息
		if(!empty($team)){
			$data[0][] = '队号';
			$mk = $sk = $rk = array();

			foreach($conf['team_column'] as $k=>$v) {
				if ($v[2] > 0) {
					$sk[$k] = $k;
					$data[0][] = $v[0];
				}
			}
			if (!empty($conf['fee'])) {
				$data[0][] = '是否缴费';
				$data[0][] .= '是否上传缴费图片';
			}
			if (!empty($conf['is_checked'])) {
				$data[0][] = '是否审核通过';
			}

			foreach($conf['result_column'] as $k=>$v) {
				if ($k == 'r1' && $v[2] > 0) {
					$rk[$k] = 'team_level';
					$data[0][] = $v[0];
				} elseif ($k == 'r2' && $v[2] > 0) {
					$rk[$k] = 'problem_number';
					$data[0][] = $v[0];
				}
			}
			$data[0][] = '是否上传作品';

			$seal_num = array();
			if (!empty($conf['is_seal'])) {
				$CI->load->model('seal_number_m');
				$seals = $CI->seal_number_m->list_by_cid_session($conf['contest_id'], $conf['session']);
				if ($seals) {
					foreach ($seals as $se) {
						$seal_num[$se['team_id']] = $se['seal_number'];
					}
					$data[0][] = '密封号';
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
				foreach($team as $k=>$v){
					$mt[$v['team_id']] = $v['team_id'];
				}

				$CI->load->model('member_column_m');
				$members = $CI->member_column_m->list_by_team_id($mt);
				$showMembers = array();
				foreach ($members as $tmpm) {
					$showMembers[$tmpm['team_id']][] = $tmpm;
				}
			}

			foreach($team as $k=>$v){
				$data[$k][] .= $v['team_number'];
				foreach($sk as $kk) {
					$data[$k][] .= $v[$kk];
				}
				// 是否缴费
				if (!empty($conf['fee'])) {
					if ($v['is_fee'] >= 1) {
						$data[$k][] = '是';
					} else {
						$data[$k][] = '否';
					}
					if ($v['fee_image']) {
						$data[$k][] = '是';
					} else {
						$data[$k][] = '否';
					}
				}
				// 是否审核
				if (!empty($conf['is_checked'])) {
					if ($v['is_valid']) {
						$data[$k][] = '是';
					} else {
						$data[$k][] = '否';
					}
				}
				foreach ($rk as $kk) {
					$data[$k][] = $v[$kk] ;
				}
				if ($v['result_file']) {
					$data[$k][] = '是';
				} else {
					$data[$k][] = '否';
				}

				if ($seal_num) {
					$data[$k][] = empty($seal_num[$v['team_id']]) ? '' : $seal_num[$v['team_id']];
				}
				if ($mem) {
					foreach($showMembers[$v['team_id']] as $mv) {
						foreach ($mk as $kk) {
							$data[$k][] = $mv[$kk];
						}
					}
				}
			}
		}
	}
}

if (! function_exists('excel_export')) {
	function write_excel($data)
	{
		if($data) {
			return $this->exportCsv($fname . '.csv' , $title.$content);
		} else {
			return $this->myclass->notice('alert("没有报名团队");');
		}
	}
}

/* End of file br2nl_helper.php */
/* Location: ./system/helpers/br2nl_helper.php */