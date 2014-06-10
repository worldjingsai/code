<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Mcm extends Admin_Controller{
    function __construct (){
        parent::__construct();
        $this->load->model('contest_m');
        $this->load->model('univs_m');
        $this->load->library('myclass');
        $this->load->model('user_m');

        if(!$this->auth->is_login ()){
            redirect('user/login');
        }
    }

    /**
     * 创建省赛cumcm
     */
    public function cumcm($pid = '', $cumcmsq = '')
    {

        if (empty($pid) || empty($cumcmsq)) {
            echo "赛区编号不能为空！";
            return false;
        }
        $cid = 0;
        $this->db->select('*');
        $query = $this->db->where('provs_id', $pid)->get('province');
        $prov = $query->row_array();
        if(empty($prov)){
            echo '没有这个省份';
            return false;
        }

        if (empty($prov['short_pinyin'])) {
            echo '省份还没有简称请添加';
            return false;
        }

        // 创建一个总竞赛
        $provName = $prov['provs_name'];
        $data = array();
        $data['contest_name'] = "2014年宁夏回族自治区大学生数学建模竞赛";

        // 省赛区域名: cumcm+省简称字母
        $data['contest_url'] = $prov['short_pinyin'].'mcm';
        $data['contest_level'] = 2;

        $this->db->select('*');
        $this->db->order_by('univs_id','asc');
        $this->db->limit(1, 0);
        $query = $this->db->where('provs_id', $pid)->get('university');
        $tunivs = $query->row_array();

        $data['parent_id'] = 0;
        $data['univs_id'] = $tunivs['univs_id'];

        // 创建一个管理员
        // cumcm+省简称字母
        $uInfo = array(
                'username' => $prov['short_pinyin'].'mcm',
                'univs_id' => $tunivs['univs_id']
        );
        $uid = $this->_reg($uInfo);
        if (!$uid) {
            echo '注册错误';
            return false;
        }

        $data['create_user_id'] = $uid;
        $cid = $this->_create_contest($data);

        $content = mb_convert_encoding($prov['provs_name'], 'GBK', 'UTF8') . ',' . $cumcmsq . ',www.worldjingsai.com/'.$data['contest_url'] . ','.$uInfo['username'] . ','.$uInfo['username'] . '123' . "\n";

        // 第一步查出这个省下面的学校 cumcmid字段不为空的
        // 如果结果为空 查询这个省下面的前30所学校

        $this->load->model('univs_m');
        $this->db->select('*');
        $query = $this->db->where('provs_id', $pid)->where('cumcmid!=0',null, false)->get('university');

        if($query->num_rows() > 0){

        } else {
            $this->db->select('*');
            $this->db->order_by('univs_id','asc');
            //$this->db->limit(1, 0);
            $query = $this->db->where('provs_id', $pid)->get('university');
        }
        $schools = $query->result_array();

        // 开始创建学校的竞赛
        foreach ($schools as $s) {
            // 创建一个管理员
            // 校赛区账户名：cumcm+学校简称
            $uInfo = array(
                    'username' => substr($s['short_name'].'mcm', 0, 20),
                    'univs_id' => $s['univs_id']
            );
            if (empty($s['short_name'])) {
                $content .= mb_convert_encoding($s['univs_name'] . ',' . '没有简称跳过', 'GBK', 'UTF8') ."\n";
                continue;
            }
            $uid = $this->_reg($uInfo);
            // 创建一个学校竞赛
            $data = array();
            $univsName = $s['univs_name'];
            $data['contest_name'] = "2014年宁夏回族自治区大学生数学建模竞赛${univsName}报名官网";

            // 省赛区域名: cumcm+省简称字母
            $data['contest_url'] = 'mcm';
            $data['contest_level'] = 1;
            $data['parent_id'] = $cid;
            $data['univs_id'] = $s['univs_id'];
            $data['create_user_id'] = $uid;

            $scid = $this->_create_contest($data);
            if ($scid) {
                $sqremark = $xxremark = '';
                if ($cumcmsq) {
                    $sqremark = $provName . '赛区编号:' . $cumcmsq;
                }
                if ($s['cumcmid']) {
                    $scumcmid = $s['cumcmid'];
                } else {
                    $scumcmid = $s['univs_id'] - $pid*1000;
                }
                $xxremark = $univsName . '编号:' . $scumcmid;
                // 队号基础就是省id和学校id然后000
                $baseNumber = $cumcmsq*1000000+$scumcmid*1000;
                $this->_createRegConf($scid, $uid, $sqremark, $xxremark, $univsName, $baseNumber);
                // 创建报名信息
                $content .= mb_convert_encoding($s['univs_name'], 'GBK', 'UTF8') . ',' .$scumcmid. ',www.worldjingsai.com/'.$s['short_name'] . '/mcm,' . $uInfo['username'] . ',' . $uInfo['username'] . '123' . "\n";
            }
        }

        // 输入文件标签
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ".strlen($content));
        Header("Content-Disposition: attachment; filename=" . $prov['short_pinyin'].'mcm.csv');

        echo $content;
        exit();
    }

    protected function _create_contest($data = array())
    {
        $data['contest_type'] = 1;
        $data['regist_start_time'] = '2014-6-09 00:00:00';
        $data['regist_end_time'] = '2014-6-22 08:00:00';
        $data['contest_start_time'] = '2014-6-22 08:00:00';
        $data['contest_end_time'] = '2014-6-25 08:00:00';
        $data['contest_bbs'] = '';

        $data['old_url'] = '';
        $data['remark'] = '';

        $data['create_time'] = date('Y-m-d H:i:s');

        $this->load->model('contest_m');
        // 检查是否存在
        $type = $data['contest_level'];
        $uri = $data['contest_url'];
        $univs_id = $data['univs_id'];
        if($type == 2 || $type == 3 || $type == 4) {
            $univs_id = '';
        }
        $contest = $this->contest_m->get_contest_by_short_name($univs_id, $uri);

        if ($contest) {
            $contest_id = $contest['contest_id'];
            $this->contest_m->update($contest_id, $data);
        } else {
            $contest_id = $this->contest_m->add($data);
            $this->load->model('univs_contest_m');
            if($contest_id){
                $addData = array(
                        'univs_id' => $univs_id,
                        'contest_id' => $contest_id,
                );
                $this->univs_contest_m->add($addData);
            }
        }

        return $contest_id;
    }

    /**
     * 注册一个用户
     */
    protected function _reg($data)
    {
        $username = $data['username'] ;
        $password = $username.'123';
        $data['password'] = md5($password);
        $data['email']='';
        $data['tel']   = '';
        $data['ip'] = '';
        $data['group_type'] = 2;
        $data['gid'] = 3;
        $data['regtime'] = time();
        $data['is_active'] = 1;

        $check_username = $this->user_m->check_username($data['username']);
        if(!empty($check_username)){
            return $check_username['uid'];
        }
        $uid = 0;
        if($this->user_m->reg($data)){
            $uid = $this->db->insert_id();
        }
        return $uid;
    }

    /**
     * 创建一个报名信息
     */
    protected function _createRegConf($cid, $uid, $sqremark = '', $xxremark='', $xxmc = '', $baseNumber = 0)
    {
        $this->load->model('contest_regist_config_m');

            // 团队的配置信息  t字段名 b备注  c是否有效
            $t = array('参数组别', '学校名称', '教师姓名', '教师性别', '教师职称', '教师电话', '教师Email', '', '', '');
            $b = array('本科组|专科组', $xxmc, '', '男|女', '', '', '', '', '', '');
            $c = array(1, 1, 1, 1, 1, 1, 1, 0, 0, 0);

            // 成员的配置信息  u字段名 d备注  s是否有效
            $u = array('姓名', '性别', '专业', '入学年份', '电话', 'Email', '', '', '', '');
            $d = array('', '男|女', '', '例:2012', '', '', '', '', '', '', '');
            $s = array(1, 1, 1, 1, 1, 1, '', '', '', '');

            // 结果配置信息  o选项信息  i是否有效
            $o = array('本科组|专科组', 'A|B|C|D');
            $ii = array(1, 1);

            $i = 1;
            $teamColumn = array();
            foreach($t as $k => $v) {
                if (!empty($c[$k]) && !empty($v)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $teamColumn["t$i"] = array($v, $b[$k], $isValid);
                $i++;
            }

            $i = 1;
            $memberColumn = array();
            foreach($u as $k => $v) {
                if (!empty($s[$k]) && !empty($v)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $memberColumn["m$i"] = array($v, $d[$k], $isValid);
                $i++;
            }

            $resultColumn = array();
            if (isset($o[0])) {
                if (!empty($ii[0])) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $resultColumn["r1"] = array('团队组别', $o[0], $isValid);
            }

            if (isset($o[1])) {
                if (!empty($ii[1])) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                $resultColumn["r2"] = array('题目选择', $o[1], $isValid);
            }
            $id = $cid;
            $session = '1';
            $minMember = 1;
            $maxMember = 3;
            $fee = 0;
            $configData = array(
                    'contest_id' =>$cid,
                    'session' => $session,
                    'type' => Contest_regist_config_m::TYPE_REGIST,
                    'article_url' => '',
                    'base_number' => $baseNumber,
                    'number_width' => 8,
                    'min_member' => $minMember,
                    'max_member' => $maxMember,
                    'fee' => $fee,
                    'team_column' => json_encode($teamColumn),
                    'member_column' => json_encode($memberColumn),
                    'result_column' => json_encode($resultColumn),
                    'create_time' => date('Y-m-d H:i:s'),
                    'create_user_id' => $uid,
                    'status' => Contest_regist_config_m::STATUS_NORMAL
            );

            // 如果有是更新
            $oldConfig = array();
            if ($id) {
                $oldConfig = $this->contest_regist_config_m->get_by_cid_session($id, $session);
            }
            if ($oldConfig && $oldConfig['contest_id'] == $id && $oldConfig['session'] == $session) {
                if ($oldConfig['status'] != Contest_regist_config_m::STATUS_NORMAL) {
                    $this->contest_regist_config_m->updateExpire($id);
                }
                if ($oldConfig['current_number'] < $configData['base_number']) {
                    $configData['current_number'] = $configData['base_number'];
                }
                //var_dump($configData['result_column'])
                $this->contest_regist_config_m->update($oldConfig['id'], $configData);
                // 新增
            } else {
                $configData['current_number'] = $configData['base_number'];
                $this->contest_regist_config_m->updateExpire($id);
                $this->contest_regist_config_m->add($configData);
            }

    }
}