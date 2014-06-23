<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 竞赛内容首页
 */

class Zhengshu extends Admin_Controller{
    function __construct (){
        parent::__construct();

        $this->load->library('PHPExcel');
        $this->load->library('Contract');
        if(!$this->auth->is_login ()){
            redirect('user/login');
        }
    }

    public static $_grades = array(
            '一等奖' => 'First Prize',
            '二等奖' => 'Second Prize',
            '三等奖' => 'Third Prize'
    );
    
    /**
     * 读取文件制作证书
     */
    public function make()
    {
        set_time_limit(0);
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        
        $file = UPLOADPATH . 'zhengshu.xlsx';
        $objPHPExcel = $objReader->load($file);
        
        $gradeCol = 'F';
        $memberCol = array('I', 'M', 'Q');
        $univsCol = array('K', 'O', 'S');
        $teacherCol = 'V';
        $teamCol = 'A';
        $i = 1;
        while ($teamNumber = $objPHPExcel->getActiveSheet()->getCell($teamCol.$i)->getValue())
        {
            if ($teamNumber == '队号') {
                $i++;
                continue;
            }
            $members = array();
            foreach ($memberCol as $k=>$c) {
                $member = $objPHPExcel->getActiveSheet()->getCell($c.$i)->getValue();
                if ($member) {
                    $univs = $objPHPExcel->getActiveSheet()->getCell($univsCol[$k].$i)->getValue();
                    $members[$k] = array('name'=>$member, 'univs'=>$univs);
                }
            }
            $teacher = $objPHPExcel->getActiveSheet()->getCell($teacherCol.$i)->getValue();
            
            $grade = $objPHPExcel->getActiveSheet()->getCell($gradeCol.$i)->getValue();
            if (!isset(self::$_grades[$grade])) {
                echo '没有这个等级' . "<br/>";
                $i++;
                continue;
            }
            $grade = self::$_grades[$grade];
            
            $times = count($members);
            for($j=1; $j<=$times; $j++) {
                $this->_makePdf($members, $teacher, $grade, $teamNumber.'_'.$j);
                $first = array_shift($members);
                array_push($members, $first);
            }
            
            $i++;
        }
    }
    
    protected function _makePdf($members, $teacher, $grade, $fname)
    {
        
        $pdf = $this->contract;

        // 设置pdf纸张 l横向  mm单位是毫米  A4纸张是A4的
        $pdf->FPDF('l', 'mm', 'A4');
        
        // 设置pdf纸张边距 左右30 上35.8
        $pdf->SetMargins(30, 35);
        
        // 设置可以用的字体
        
        // 设置自动分页 距离下边距25.4mm时分页 也是设置下边距
        $pdf->SetAutoPageBreak(false);
        
        // 设置作者
        $pdf->SetAuthor('MathorCup');
        
        $pdf->SetPicPath(UPLOADPATH.'template.jpg');
        
        $pdf->AddPage();
        $pdf->SetFont('times','',21);
        
        // Background color
        //$this->SetFillColor(200,220,255);
        // Title
        $pdf->Cell(0,0,'2014', 0, 0, 'C');
        $pdf->Ln(1);
        $pdf->Cell(0,25,'MathorCup Global Collegiate Mathematical Contest in Modeling',0,0,'C');
        $pdf->Ln(1);
        $pdf->Cell(0,50,'Certificate of Achievement',0,0,'C');
        
        $pdf->SetFont('times','',16);
        $pdf->Ln(4);
        //$pdf->SetX(140);
        $pdf->Cell(0,68,'Awarded to',0,0,'C');
        $pdf->Ln(1);
        $l = 70;
        
        $pdf->SetFont('times','',16);
        foreach ($members as $i=>$member) {
            
            $l += 12;
            $pdf->Ln(1);
            //$pdf->SetX(140);
            $pdf->Cell(0,$l,$member['name'],0,0,'C');
        }
        
        $pdf->Ln(1);
        //$pdf->SetX(140);
        if ($teacher) {
            $pdf->Cell(0,123,$teacher.', Adviser',0,0,'C');
        }
        $pdf->Ln(1);
        $pdf->SetFont('times','',24);
        $pdf->Cell(0,145,$grade,0,0,'C');
        
        $pdf->Ln(1);
        $pdf->SetFont('times','',16);
        $pdf->Cell(0,165,'from '.$members[0]['univs'],0,0,'C');
        
        $pdf->Output(UPLOADPATH.$fname.'.pdf','F');
    }
}