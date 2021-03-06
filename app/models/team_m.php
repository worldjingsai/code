<?php
/**
 * 队伍的Model
 */

class Team_m extends SB_Model{

	const IS_VALID_YES = 0;
	const IS_VALID_NO = -1;
	const IS_ENTER_YES = 1;
	const IS_ENTER_NO = 0;

	const STATUS_NORMAL = 1;
	const STATUS_DEL = -1;
	const STATUS_CANCLE = -2;

	public $tb = 'team';

	function __construct(){
		parent::__construct();
	}

	function add($data){
		if($this->db->insert($this->tb, $data)){
			return $this->db->insert_id();
		}else{
			return false;
		}
	}

	public function get($id){
		$this->db->select('*');
		$query = $this->db->where('team_id',$id)->get($this->tb);
		return $query->row_array();
	}

	public function get_by_id_status($id, $status = self::STATUS_NORMAL) {
		$this->db->select('*');
		$query = $this->db->where('team_id',intval($id))->where('status', intval($status))->get($this->tb);
		return $query->row_array();
	}

	public function get_by_team_number($team_number, $contest_id, $session){
		$this->db->select('*');
		$query = $this->db->where('team_number',$team_number)->where('contest_id', $contest_id)
		->where('session', $session)->where('status', self::STATUS_NORMAL)->get($this->tb);
		return $query->row_array();
	}

	/**
	 * 更新一个团队
	 * @param int $id
	 * @param array $data
	 */
	public function update($id, $data) {
		$this->db->where('team_id', $id);
		$this->db->update($this->tb, $data);
		return $this->db->affected_rows();
	}

	/**
	 * 根据用户得到参赛信息
	 * @param int $uid
	 * @param int $cid
	 * @param int $session
	 */
	public function get_by_user_contest_session($uid, $cid, $session)
	{
		$this->db->select('*');
		$query = $this->db->where('contest_id', $cid)->where('session', $session)->where('create_user_id', $uid)->where('status', 1)->get($this->tb);
		return $query->row_array();
	}


	/**
	 * 根据contest_id获取竞赛和届数获取列表
	 */
	public function list_all_by_cid_session($cid, $session){
		$this->db->select('*');
		$this->db->from($this->tb);
		$this->db->order_by('team_id','asc');
		$this->db->where('contest_id', $cid)->where('session', $session)->where('status',1);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 * 根据contestid和sessionid获取参数总数
	 */
	public function count_team($cid = 0, $session = 0){
		$this->db->select('team_id');
		$this->db->where('status', self::STATUS_NORMAL);
		if($cid){
			$this->db->where('contest_id', $cid);
		}
		if($session) {
			$this->db->where('session', $session);
		}
		$query = $this->db->get($this->tb);
		if($query->result()){
			return $query->num_rows();
		} else {
			return '0';
		}
	}

	/**
	 * 根据contestid和sessionid获取参数总数
	 */
	public function count_by_uid($uid = 0){
		$this->db->select('team_id');
		$this->db->where('status', self::STATUS_NORMAL);
		if($uid){
			$this->db->where('create_user_id', $uid);
		}

		$query = $this->db->get($this->tb);
		if($query->result()){
			return $query->num_rows();
		} else {
			return '0';
		}
	}


	/**
	 * 根据uid获取参与的竞赛
	 */
	public function list_by_uid($uid = 0, $page, $limit){
		$this->db->select('a.*, b.*');
		$this->db->from($this->tb .' a');
		$this->db->join('contest b', 'b.contest_id = a.contest_id');
		$this->db->order_by('a.create_time','desc');
		$this->db->where('a.create_user_id',$uid)->where('a.status',1)->where('b.status', 1);
		$this->db->limit($limit,$page);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 * 获取创建的竞赛
	 * @param unknown $page
	 * @param unknown $limit
	 */
	public function get_by_cid_session($cid, $session, $page, $limit)
	{
		$this->db->select('a.*, b.username, b.uid');
		$this->db->from($this->tb .' a');
		$this->db->join('users b', 'b.uid = a.create_user_id');
		$this->db->order_by('create_time','desc');
		$this->db->where('a.contest_id',$cid)->where('a.session', $session)->where('a.status',1);
		$this->db->limit($limit,$page);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 * 批量获取team信息
	 */
	public function get_by_team_id_cid($tids, $cid)
	{
		if (!is_array($tids)) {
			$tids = array($tids);
		}
		$this->db->select('*');
		$this->db->from($this->tb);
		$this->db->where_in('team_id', $tids)->where('contest_id', $cid)->where('status',1);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 * 获取详细的参赛列表
	 * @param  int $cid 竞赛的ID
	 * @param  int $session 竞赛是第几届
	 * @param  int $is_fee 是否已经付费
	 * @param  int $is_upload_fee_image 是否上传缴费证明
	 */
	public function get_detail_by_cid_session($cid, $session, $page, $limit, $is_fee=-1, $is_upfee_image=-1, $is_result=-1, $tkey='', $tvalue = '', $status = 1, $is_valid=-1)
	{
		$this->db->select('a.*, b.*');
		$this->db->from($this->tb .' a');
		$this->db->join('team_column b', 'b.team_id = a.team_id');
		$this->db->order_by('team_number', 'ASC');
		if (!is_array($cid)) {
			$cid = array($cid);
		}
		$this->db->where_in('a.contest_id',$cid)->where('a.session', $session)->where('a.status',$status);
		if($is_fee != -1){
			$this->db->where('a.is_fee',$is_fee);
		}
		if($is_upfee_image != -1){
			if ($is_upfee_image) {
				$this->db->where('a.fee_image!=""',null,false);
			} else {
				$this->db->where('a.fee_image','');
			}
		}
		if($is_result != -1){
			if ($is_result) {
				$this->db->where('a.result_file!=""',null, false);
			} else {
				$this->db->where('a.result_file','');
			}
		}
		if ($is_valid != -1) {
			$this->db->where('a.is_valid',$is_valid);
		}
		if ($tvalue !== '') {
			if ($tkey == 'team_number') {
				$this->db->where('a.team_number', $tvalue);
			} else {
				$this->db->where('b.'.$tkey, $tvalue);
			}
		}
		$this->db->limit($limit,$page);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 * 根据contestid和sessionid获取参数总数
	 */
	public function count_detail_by_cid_session($cid, $session, $is_fee=-1, $is_upfee_image=-1, $is_result=-1, $tkey='', $tvalue = '', $status=1, $is_valid=-1){
		$this->db->select('a.team_id');
		$this->db->from($this->tb .' a');
		$this->db->join('team_column b', 'b.team_id = a.team_id');

		if (!is_array($cid)) {
			$cid = array($cid);
		}
		$this->db->where_in('a.contest_id',$cid)->where('a.session', $session)->where('a.status',$status);
		if($is_fee != -1){
			$this->db->where('a.is_fee',$is_fee);
		}
		if($is_upfee_image != -1){
			if ($is_upfee_image) {
				$this->db->where('a.fee_image!=""', null, false);
			} else {
				$this->db->where('a.fee_image', '');
			}
		}
		if($is_result != -1){
			if ($is_result) {
				$this->db->where('a.result_file!=""',null, false);
			} else {
				$this->db->where('a.result_file','');
			}
		}
		if ($is_valid != -1) {
			$this->db->where('a.is_valid',$is_valid);
		}
		if ($tvalue !== '') {
			if ($tkey == 'team_number') {
				$this->db->where('a.team_number', $tvalue);
			} else {
				$this->db->where('b.'.$tkey, $tvalue);
			}
		}

		$query = $this->db->get();
		if($query->result()){
			return $query->num_rows();
		} else {
			return '0';
		}
	}
}