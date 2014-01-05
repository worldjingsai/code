<?php
/**
 * 高校首页
 */
class Univs extends SB_Controller{

    public $limit = 15;
    function __construct (){
        parent::__construct();
        $this->load->model('univs_m');
        $this->load->library('myclass');
    }

    public function index($univs_id){
        $univs_id = intval($univs_id);
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;

        $schooleContests = $this->_schoolcList($univs_id);
        $publicContests = $this->_cList();
        $data['schooleContests'] = $schooleContests;
        $data['publicContests'] = $publicContests;
        $this->tplData = $data;

        $this->display("univs/index.html");
    }

    /**
     * 学校竞赛列表
     */
    public function clist($univs_id)
    {
        $univs_id = intval($univs_id);
        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $data['university'] = $univs_info;

        $cat = 1;
        if ($this->input->get('cat') == 2)
        {
            $cat = 2;
        }
        if ($cat == 1)
        {
            $list = $this->_schoolcList($univs_id);
            $subTitle = '校内竞赛';
        } elseif ($cat == 2)
        {
            $list = $this->_cList();
            $subTitle = '全部竞赛';
        }
        $data['subTitle'] = $subTitle;
        $data['list'] = $list;
        $this->tplData = $data;
        $this->display("contest/univs_contest_list.html");
    }

    /**
     * 所有竞赛列表
     */
    public function aclist($univs_id)
    {

    }

    public function create($univs_id){
        $step = 1;
        $contest_id = 0;
        $univs_id = intval($univs_id);
        if(isset($_GET['step']))
        {
            $step = intval($this->input->get('step'));
        }
        $this->load->model('contest_m');
        $this->load->model('univs_m');

        $data = array();
        if ($step > 5)
        {
            return false;
        }

        if ($step == 2)
        {
            $data['contest_name'] = $this->input->post('contest_name', true);
            $data['contest_url'] = $this->input->post('contest_url', true);
            $data['contest_type'] = intval($this->input->post('contest_type'));
            $data['regist_start_time'] = $this->input->post('regist_start_time', true);
            $data['regist_end_time'] = $this->input->post('regist_end_time', true);
            $data['contest_start_time'] = $this->input->post('contest_start_time', true);
            $data['contest_end_time'] = $this->input->post('contest_end_time', true);

            $data['univs_id'] = $univs_id;
            $data['create_time'] = date('Y-m-d H:i:s');
            // TODO
            $data['create_user_id'] = 0;
            $contest_id = $this->contest_m->add($data);
            if($contest_id)
            {
               $this->load->model('university_contest_m');
               $addData = array(
                       'univs_id' => $univs_id,
                       'contest_id' => $contest_id,
                       );
               $this->university_contest_m->add($addData);
            }
            unset($data);
        } elseif($step > 2) {
            if ($step == 3)
            {
                $column_id = Contest_m::COLUM_ABOUT;
            } elseif ($step == 4)
            {
                $column_id = Contest_m::COLUM_NOTICE;
            } elseif ($step == 5)
            {
                $column_id = Contest_m::COLUM_PROBLEM;
            }

            $this->load->model('article_m');
            $this->load->model('article_content_m');
            $data['article_type'] = Article_m::TYPE_CONTEST;
            $data['column_id'] = $column_id;
            $contest_id = intval($this->input->post('contest_id'));
            $data['type_id'] = $contest_id;
            $data['title'] = $this->input->post('title', true);
            $contentdata['content'] = $this->input->post('content', true);
            $article_id = $this->article_m->add($data);
            if($article_id)
            {
                $contentdata['article_id'] = $article_id;
                $this->article_content_m->add($contentdata);
            }
            if ($step == 5)
            {
                return $this->index($univs_id);
            }
        }

        $univs_info = $this->univs_m->get_univs_info_by_univs_id($univs_id);
        $show_data['contest_id'] = $contest_id;
        $show_data['university'] = $univs_info;
        $show_data['step'] = $step;
        $this->tplData = $show_data;
        if ($step == 1)
        {
            $this->display("contest/create_1.html");
        } else {
            $this->display("contest/create_2.html");
        }
    }


    /**
     * 显示学校的竞赛列表
     */
    protected function _schoolcList($univs_id)
    {
        if(!$univs_id)
        {
            return show_error('错误的学校ID');
        }
        $this->load->model('university_contest_m');
        $this->load->model('contest_m');

        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        if (!$page)
        {
            $page = 0;
        }
        if (!$limit)
        {
            $limit = $this->limit;
        }
        $cids = $this->university_contest_m->lists($univs_id, $page, $limit);
        $cid = array();
        $cList = array();
        if ($cids)
        {
            foreach ($cids as $key=>$value)
            {
                $cid[$value['contest_id']] = $value['contest_id'];
            }
            $cList = $this->contest_m->listByCid($cid);
        }
        return $cList;
    }


    /**
     * 显示所有的竞赛列表
     */
    protected function _cList()
    {
        $this->load->model('contest_m');

        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        if (!$page)
        {
            $page = 0;
        }
        if (!$limit)
        {
            $limit = $this->limit;
        }

        $cList = array();
        $cList = $this->contest_m->listPublic($page, $limit);
        return $cList;
    }
}