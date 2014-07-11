<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('con_team_member'))
{
	function con_team_member($team, $member)
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

if (! function_exists('get_show_field')) {
	function get_show_field($conf)
	{
		$show_col = array();
		$team_col = $conf['team_column'] ;
		$mem_col = $conf['member_column'];
		foreach ($team_col as $k => $t) {
			if (empty($t['2'])) {
				unset($team_col[$k]);
			}
		}
		foreach ($mem_col as $k => $m) {
			if (empty($m['2'])) {
				unset($mem_col[$k]);
			}
		}
	
		$t_n = count($team_col);
		$m_n = count($mem_col);
	
		// 如果团队大于三个就显示三个
		if ($m_n * $conf['max_member'] >= 3) {
			$m_c = 3;
		} else {
			$m_c = $m_n * $conf['max_member'];
		}
		$t_c = 5 - $m_c;
	
	
		foreach ($team_col as $k => $t) {
			if (count($show_col) > $t_c) {
				break;
			}
			$show_col[$k] = $t['0'];
		}
	
		foreach ($mem_col as $k => $m) {
				
			for ($i = 1; $i <= $conf['max_member']; $i ++) {
				if (count($show_col) > 5) {
					break;
				}
				$show_col[$k.'_'.$i] = $m['0'] . '_' . $i;
			}
		}
	
		return $show_col;
	}
}

/* End of file br2nl_helper.php */
/* Location: ./system/helpers/br2nl_helper.php */