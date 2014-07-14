<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('team_formate'))
{
	function team_formate($conf, $team, $mem, $contest)
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
				$data[0][] = '是否上传缴费图片';
			}
			if (!empty($conf['is_checked'])) {
				$data[0][] = '是否审核通过';
			}

			if (!empty($conf['result_column'])) {
				foreach($conf['result_column'] as $k=>$v) {
					if ($k == 'r1' && $v[2] > 0) {
						$rk[$k] = 'team_level';
						$data[0][] = $v[0];
					} elseif ($k == 'r2' && $v[2] > 0) {
						$rk[$k] = 'problem_number';
						$data[0][] = $v[0];
					}
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
							$data[0][] = '队员'.$i.$v[0];
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

			$dk = 0;
			foreach($team as $k=>$v){
				$dk++;
				$data[$dk][] = $v['team_number'];
				foreach($sk as $kk) {
					$data[$dk][] = $v[$kk];
				}
				// 是否缴费
				if (!empty($conf['fee'])) {
					if ($v['is_fee'] >= 1) {
						$data[$dk][] = '是';
					} else {
						$data[$dk][] = '否';
					}
					if ($v['fee_image']) {
						$data[$dk][] = '是';
					} else {
						$data[$dk][] = '否';
					}
				}
				// 是否审核
				if (!empty($conf['is_checked'])) {
					if ($v['is_valid']) {
						$data[$dk][] = '是';
					} else {
						$data[$dk][] = '否';
					}
				}
				foreach ($rk as $kk) {
					$data[$dk][] = $v[$kk] ;
				}
				if ($v['result_file']) {
					$data[$dk][] = '是';
				} else {
					$data[$dk][] = '否';
				}

				if ($seal_num) {
					$data[$dk][] = empty($seal_num[$v['team_id']]) ? '' : $seal_num[$v['team_id']];
				}
				if ($mem) {
					foreach($showMembers[$v['team_id']] as $mv) {
						foreach ($mk as $kk) {
							$data[$dk][] = $mv[$kk];
						}
					}
				}
			}
			$fname = $contest['contest_name'];
			if ($mem) {
				$fname .= '_团队和队员信息表';
			} else {
				$fname .= '_团队信息表';
			}

			if (count($data) <= 60000 && count($data[0]) <= 250) {
				download_excel($data, $fname);;
			} else {
				download_csv($data, $fname);
			}
		}
	}
}



if ( ! function_exists('cumcm_formate'))
{
	function cumcm_formate($conf, $team, $mem, $contest)
	{
		$CI =& get_instance();
		$data = array();
		// 导出团队信息
		if(!empty($team)){
			$data[0][] = '题号';
			$data[0][] = '区号';
			$data[0][] = '学校编号';
			$data[0][] = '校内编号';
			$data[0][] = '学校名称';
			$mk = $tk = $rk = array();

			// 导出团队信息
			if ($mem) {
				for($i=1; $i<=$conf['max_member']; $i++) {
					foreach($conf['member_column'] as $k=>$v) {
						if ($v[2] > 0) {
							$mk[$k] = $k;
							$data[0][] = $v[0].$i;
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

			$team_column = $conf['team_column'];
			unset($team_column['t1']);
			unset($team_column['t2']);
			foreach($team_column as $k=>$v) {
				if ($v[2] > 0) {
					$tk[$k] = $k;
					$data[0][] = $v[0].$i;
				}
			}
			$data[0][] = '备注';

			$dk = 0;
			foreach($team as $k=>$v){
				$dk++;
				$data[$dk][] = $v['problem_number'];
				$data[$dk][] = substr($v['team_number'], 0, 2);
				$data[$dk][] = substr($v['team_number'], 2, 3);
				$data[$dk][] = substr($v['team_number'], 5);
				$data[$dk][] = $v['t2'];

				if ($mem) {
					foreach($showMembers[$v['team_id']] as $mv) {
						foreach ($mk as $kk) {
							$data[$dk][] = $mv[$kk];
						}
					}
				}

				foreach($tk as $kk) {
					$data[$dk][] = $v[$kk];
				}
			}
			$fname = $contest['contest_name'];
			$fname .= '_参赛信息表';

			if (count($data) <= 60000 && count($data[0]) <= 250) {
				download_excel($data, $fname);;
			} else {
				download_csv($data, $fname);
			}

		}

	}
}

// 以excel2003的格式显示
if (! function_exists('download_excel')) {
	function download_excel($data, $file_name='新建Excel文档')
	{
		$CI =& get_instance();
		$CI->load->library('PHPExcel');
		$objPHPExcel = new PHPExcel();
		if($data) {
			$objPHPExcel->getProperties()->setCreator("Worldjingsai");
			$objPHPExcel->setActiveSheetIndex(0);

			$i = $j = 0;
			foreach ($data as $key => $value) {
				$i++;
				$j = 0;
				foreach ($value as $k => $v) {
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, "\t{$v}");
					$j++;
				}
			}
			$file_name = get_download_name($file_name);
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'. $file_name .'.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		} else {
			return $CI->myclass->notice('alert("没有报名团队");');
		}
	}
}

// 以csv的格式显示
if (! function_exists('download_csv')) {
	function download_csv($data, $file_name='新建Excel文档')
	{
		$CI =& get_instance();
		$CI->load->library('PHPExcel');
		$objPHPExcel = new PHPExcel();
		if($data) {
			$contents = array();
			foreach ($data as $key => $value) {
				$contents[$key] = '"'. "\t" . join('","'."\t", $value) . '"';
			}
			unset($data);
			$file_name = get_download_name($file_name);
			$data = mb_convert_encoding(join("\r\n", $contents), 'GBK', "UTF8");
			header("Content-type:text/csv");
			header("Content-Disposition:attachment;filename=".$file_name.'.csv');
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
			echo $data;
		} else {
			return $CI->myclass->notice('alert("没有报名团队");');
		}
	}
}

/* End of file br2nl_helper.php */
/* Location: ./system/helpers/br2nl_helper.php */