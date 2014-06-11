<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Result extends SB_controller{
    function __construct (){
        parent::__construct();
        $this->load->model('contest_m');
        $this->load->model('univs_m');
        $this->load->model('contest_regist_config_m');
    }

    /**
     *
     */
    public function batch_down($cid = 1)
    {
        @ini_set('memory_limit', "10240M");
        // 批量下载论文
        $conf = $this->contest_regist_config_m->get_normal($cid);
        if (empty($conf) ) {
            echo "没有报名信息" . $cid;
            return false;
        }

        $this->load->library('zip');

        $session = $conf['session'];
        $this->load->model('team_m');
        $num = $this->team_m->count_detail_by_cid_session($cid, $session);
        if ($num) {
            $tInfos = $this->team_m->get_detail_by_cid_session($cid, $session, 1, $num);
            foreach ($tInfos as $t) {
                if (empty($t['result_file'])) {
                    continue;
                }

                $file_dir = UPLOADPATH . 'paper/';
                $file_name = $t['result_file'];
                $show_name = $t['team_number'] . strrchr($file_name, '.');

                $realPath = $file_dir . $t['result_file'];

                // 转存到本地
                if (!file_exists($realPath)) {
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

                        $puts = @file_get_contents($url);
                        if ($puts) {

                            // 分成60份
                            $pos = strrpos($t['result_file'], '/');
                            $tdir = substr($t['result_file'], 0, $pos);
                            $dir = $file_dir . $tdir;

                            if(!is_dir($dir)){
                                mkdir($dir,0777,true);
                            }
                            @file_put_contents($realPath, $puts);
                        }
                    }
                }
                if (FALSE !== ($data = @file_get_contents($realPath)))
                {
                    $this->zip->add_data($show_name, $data);
                }
            }

            if ($this->zip->zipdata) {
                // 保存为zip文件
                $zipFile = $file_dir.$cid.'/'.$cid.'_'.$session.'.zip';
                $this->zip->archive($zipFile);
            }
            echo $zipFile;
            $this->zip->clear_data();
        }
    }
}