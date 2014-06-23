<?php

require_once BASEPATH . 'libraries/fpdf/fpdf.php';
class CI_Contract extends FPDF
{
    public $number;
    public $footer_left;
    public $footer_right;
    public $pic_path;
    public $maxY = 0;
    public function SetNumber($number)
    {
        $this->number = $number;
    }

    public function SetFooter($footer_left, $footer_right)
    {
        $this->footer_left = $footer_left;
        $this->footer_right = $footer_right;
    }

    public function SetPicPath($path)
    {
        $this->pic_path = $path;
    }

function Header()
{
	if (!$this->pic_path)
	{
		return true;
	}

	$this->Image($this->pic_path,0,0,$this->w,$this->h); 

    // Arial bold 15
    /*$this->SetFont('simsun','',9);
    // Calculate width of title and position
    //$w = $this->GetStringWidth($title)+6;
    //$this->SetX(100);
    // Colors of frame, background and text
    //$this->SetDrawColor(0,80,180);
    //$this->SetFillColor(230,230,0);
    //$this->SetTextColor(220,50,50);
    // Thickness of frame (1 mm)
    //$this->SetLineWidth(1);
    // Title
    //$this->Cell($w,9,$title,1,1,'C',true);
    // Line break
	//$this->SetXY(130,15.8);
	$this->SetY(16);
	$this->Cell(0,13,$rightheader,0, 0, 'R'); 
	//$this->Write(10,$rightheader); 
	$this->Ln(10);
	$this->Cell(0,1,'','B');
    $this->Ln(3);*/
}

function SetChar()
{
    $char = array('��', '��', '��', '��', '��', '��', '��', '��', '��', '��');
    $endchar = array('��', '��', '��', '��', '��');
    $this->GB_character = $char;
    $this->GB_endchar = $endchar;
}
function Footer()
{
	if (!$this->footer_left)
	{
		return true;
	}
    // Position at 1.5 cm from bottom
    $this->SetY(-25);
    // Arial italic 8
    $this->SetFont('xihei','',8);
    // Text color in gray
    //$this->SetTextColor(128);
    // Page number
    if ($this->footer_right != 'PageNo') {
        $this->Cell(0,10,$this->PageNo(),0,0,'C');
    }
	$this->SetY(-25);
	$this->Cell(0,10,$this->footer_left,0,0,'L');
	$this->SetY(-25);
    if ($this->footer_right == 'PageNo') {
	    $this->Cell(0,10,$this->PageNo(),0,0,'R');
    } else {
	    $this->Cell(0,10,$this->footer_right,0,0,'R');
    }
}

function ChapterBody($file, $param, $fs=10.5, $tdlh=7)
{
    $this->SetChar();
	$lh=7;     // ��ͨ�ļ����и�
	$thlh=6;    // ��ͷ���и�
	$tdlh=$tdlh;     // ���ĸ߶�
	$iw=10;    // item�Ŀ��
	$fs=$fs;  //�����С
    // Read text file

    // Times 12
    $this->SetFont('xihei','',$fs);
    // Output justified text

    $haddle = fopen($file, 'r');
    while(($line = fgets($haddle)))
    {
        if(preg_match_all('/\{foreach\(([^\)]*)\)\}/', $line, $wd))
        {
            foreach($wd[1] as $wk=>$w)
            {
                $nline = '';
                if(preg_match_all('/\{\$([^\{]*)\}/', $w, $sa))
                {
                    // ���ҳ���Ҫ�滻������
                    $replaceArray = array();

                    foreach($sa[1] as $k => $v)
                    {
                        if (isset($param[$v]) && is_array($param[$v]))
                        {
                            foreach ($param[$v] as $key=>$value)
                            {
                                $replaceArray[$key][$v] = $value;
                            }
                        }
                    }

                    foreach($replaceArray as $ra)
                    {
                        $nline.=str_replace($sa[0], $ra, $w);
                    }
                }
                $line = str_replace($wd[0][$wk], $nline, $line);
            }
        }

        // �ȶԱ��������滻
        $re = array();
        if(preg_match_all('/\{\$([^\{]*)\}/', $line, $sa))
        {
            foreach($sa[1] as $k => $v)
            {
                // �鿴�Ƿ��зֺ��߼��ָ��
                if (strpos($v, ':') !== false)
                {
                    list($var, $value) = explode(':', $v);

                    // ture false �ؼ��ֵ��ж�
                    if ($value === 'true')
                    {
                        if ( !empty($param[$var]) )
                        {
                            $line = str_replace($sa[0][$k], '', $line);
                        } else
                        {
                            $line = '';
                        }
                    } elseif ($value === 'false')
                    {
                        if (empty($param[$var]))
                        {
                            $line = str_replace($sa[0][$k], '', $line);
                        } else
                        {
                            $line = '';
                        }
                    } elseif ($value === 'value')
                    {
                        if ( isset($param[$var]) && ($param[$var] !== '') )
                        {
                            $line = str_replace($sa[0][$k], $param[$var], $line);
                        } else
                        {
                            $line = '';
                        }
                    } else
                    {
                        if ( isset($param[$var]) && $param[$var] == $value)
                        {
                            $line = str_replace($sa[0][$k], '', $line);
                        } else
                        {
                            $line = '';
                        }
                    }
                }

                // �鿴�Ƿ��������߼��ָ��
                if (strpos($v, '|') !== false)
                {
                    list($var, $value) = explode('|', $v);

                    $subValue = explode('-', $param[$var]);
                    $line = str_replace($sa[0][$k], $subValue[$value], $line);
                }

                if (strpos($line, $sa[0][$k]) !== false)
                {
                    $line=str_replace($sa[0][$k], (isset($param[$v]) ? $param[$v]: ''), $line);
                }

            }
        }

        // �Ը�ʽ�������
		if(strpos($line,'<include>') !== false)
		{
			$line=trim(str_replace('<include>', '', $line));
            if ($line)
            {
                $this->PrintChapter(dirname($file) .'/'. $line, $param);
            }
            continue;
        }

        // �Ը�ʽ�������
		if(strpos($line,'<newpage>') !== false)
		{
			$this->AddPage();
            continue;
        }

        // �Ը�ʽ�������
		if(strpos($line,'<table_header>') !== false)
		{
			$line=str_replace('<table_header>', '', $line);
			$this->PrintTable($line);
            continue;
        }

        // �Ը�ʽ�������
		if(strpos($line,'<title>') !== false)
		{
			$line=str_replace('<title>', '', $line);
			$this->PrintTitle($line);
            continue;
        }

		if(strpos($line,'<subitem>') !== false)
		{
			$line=str_replace('<subitem>', '', $line);
			$this->PrintSubItem($line);
            continue;
        }

        if (strpos($line,'<bitem>') !== false)
		{
			$line=str_replace('<bitem>', '', $line);
			$this->PrintBItem($line);
            continue;
        }

        if (strpos($line,'<item>') !== false)
		{
			$line=str_replace('<item>', '', $line);
			$this->PrintItem($line);
            continue;
		}

        if (strpos($line,'<tail>') !== false)
		{
			$this->SetTail();
            continue;
		}

        if (strpos($line,'<table_th>') !== false)
		{
			$line=str_replace('<table_th>', '', $line);
			$this->PrintTh($line, $fs);
            continue;
		}

        if (strpos($line,'<table_td>') !== false)
		{
			$line=str_replace('<table_td>', '', $line);
			$this->PrintTd($line, $fs, $tdlh);
            continue;
		}

        if ($line !== ''){
            if (preg_match('/<font_style=([^>]*)>/', $line, $match))
            {
                $fontstyle=$match[1];
                $line=str_replace($match[0], '', $line);
                $match=array();
                $this->SetFont('xihei',$fontstyle,$fs);
            }
            $this->MultiCell(0,$lh,$line);
            $this->SetFont('xihei','',$fs);
        }

    }

}

function PrintTitle($title)
{
	$lh=7; //������б�����¼��
	$fs=10.5;//�����С
    // Arial 12
    $this->SetFont('xihei','B',16);
    // Background color
    //$this->SetFillColor(200,220,255);
    // Title
    $this->Ln(4);
    $this->Cell(0,8,$title,0,0,'C');
	$this->SetFont('xihei','',$fs);
    // Line break
    $this->Ln(20);
}

function PrintTable($item)
{
	$fs=10.5;//�����С
	$this->SetFont('xihei','',$fs);
	$thlh=6;    // ��ͷ���и�
    $this->MultiCell(0,$thlh,$item);
}

function PrintBItem($item)
{
	$lh=7; //������б�����¼��
	$fs=10.5;//�����С
	$iw=10;// item�Ŀ��
	$font='xihei';
	$this->Ln(6);
	$this->SetFont($font,'B',$fs);
	if (strpos($item,"\t") !== false)
	{
		list($nu, $item) = explode("\t",$item);
		$this->Cell($iw,$lh,$nu);
	}
	$this->MultiCell(0,$lh,$item);
	$this->SetFont('xihei','',$fs);
	$this->Ln(6);
}

function PrintItem($item)
{
	$lh=7; //������б�����¼��
	$fs=10.5;//�����С
	$iw=10;// item�Ŀ��

	if (strpos($item,"\t") !== false)
	{
		list($nu, $item) = explode("\t",$item);
		$this->Cell($iw,$lh,$nu);
    } else
    {
		$this->Cell($iw,$lh,'');
    }
	$this->MultiCell(0,$lh,$item);

}

function PrintSubItem($item)
{
	$lh=7; //������б�����¼��
	$fs=10.5;//�����С
	$iw=10;// item�Ŀ��

    $this->Cell($iw,$lh,'');
	if (strpos($item,"\t") !== false)
	{
		list($nu, $item) = explode("\t",$item);
		$this->Cell($iw,$lh,$nu);
    } else
    {
		$this->Cell($iw,$lh,'');
    }
	$this->MultiCell(0,$lh,$item);
}


function PrintTd($item, $fs=10.5, $lh=7)
{
    // table_end �������ʽĬ���ǿղ��Ӵ�
    $table_end = 0;
    if (preg_match('/<table_end=([^>]*)>/', $item, $match))
	{
		$table_end=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();
	}

    // font style �������ʽĬ���ǿղ��Ӵ�
    $fontstyle = '';
    if (preg_match('/<font_style=([^>]*)>/', $item, $match))
	{
		$fontstyle=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();
	}

    // table length ���Ŀ�� Ĭ����������
    // file length �����ļ�������ʾ�Ŀ��
    $fl = $this->w-$this->lMargin-$this->rMargin;
    $tl = $fl;
    if (preg_match('/<table_width=([^>]*)>/', $item, $match))
	{
		$tl=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();
	}

    // border width �߿�Ŀ��Ĭ����0
    $bw=0;
    if (preg_match('/<border=([^>]*)>/', $item, $match))
	{
		$bw=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();
	}

    // aline ��ͷ�����λ�ã�Ĭ���Ǿ���
    $aline = 'L';
    if (preg_match('/<aline=([^>]*)>/', $item, $match))
	{
		$aline=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();
	}

    $lh=$lh; //�еĸ߶�
    if (preg_match('/<height=([^>]*)>/', $item, $match))
	{
		$lh=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();
	}

    $fs=$fs;//�����С
    $this->SetFont('xihei',$fontstyle,$fs);

    $lists = explode("\t", $item);
    $col = count($lists);

    // Ĭ�ϵı������ƽ������
    $tw = $tl/$col;

    $x = $this->lMargin + ($fl - $tl)/2;
    //$x=$this->GetX();
    $y=$this->GetY();
    $xs = array($x);
    $nx = $x;
    foreach($lists as $i => $list)
    {
        $px=$nx;
        if (preg_match('/<width=([^>]*)>/', $list, $match))
        {
            $tw=$match[1];
            $list=str_replace($match[0], '', $list);
            $match=array();
            $this->tablewidth[$i] = $tw;
        }
		$border_top = $bw;
        if (preg_match('/<border_top=([^>]*)>/', $list, $match))
        {
            $border_top=$match[1];
            $list=str_replace($match[0], '', $list);
        }
        if (!empty($this->tablewidth[$i]))
        {
            $tw=$this->tablewidth[$i];
        }
        $nx=$nx+$tw;

        if ($list && $bw && $border_top)
        {
            $this->Line($px, $y, $nx, $y);
        }
        $this->SetXY($px, $y);

        $this->MultiCell($tw,$lh, $list, 0,$aline);

        $tmpy=$this->GetY();
        if ($tmpy > $this->maxY)
        {
            $this->maxY = $tmpy;
        }
        $xs[] = $nx;
    }

    if ($bw)
    {
        foreach($xs as $xx)
        {
            $this->Line($xx,$y,$xx,$this->maxY);
        }
    }

    if ($bw && $table_end)
    {
        $this->Line($x, $this->maxY, $x+$tl, $this->maxY);
    }
    $this->SetFont('xihei','',$fs);
    $this->SetY($this->maxY);
}

function PrintTh($item, $fs=10.5)
{
    $this->tablewidth = array();
    $y=$this->GetY();

    if (preg_match('/<table_height=([^>]*)>/', $item, $match))
	{
		$lh=$match[1];
		$item=str_replace($match[0], '', $item);
        $match=array();

        if ( ($this->h - $this->bMargin - $y) < $lh)
        {
            $this->addPage();
        }
	}

    $y=$this->GetY();
    $this->maxY = $y;
    if ($item)
    {
        $this->PrintTd($item, $fs);
    }
}

function SetTail()
{
    $y=$this->GetY();
    // ��С��Ͳ��ܳ���220,��������Ҫ��ҳ
    // ���С��200������Ϊ200����������Ϊy
    if ($y>220)
    {
        $this->addPage();
    }
    if ($y<210)
    {
        $this->SetY(210);
    }
}

function PrintChapter($file, $param)
{
    $this->AddPage();
    $this->ChapterBody($file, $param);
}

function PrintSchedule($file, $param)
{
    $this->AddPage();
    $this->ChapterBody($file, $param, 8.5, 5);
}

}
/*
$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetMargins(25.4, 35.8);
$pdf->AddGBFont('simsun','����'); 
$pdf->AddGBFont('simhei','����'); 
$pdf->AddGBFont('simkai','����_GB2312'); 
$pdf->AddGBFont('sinfang','����_GB2312'); 
$pdf->AddGBFont('xihei','����ϸ��'); 

$title = '20000 Leagues Under the Seas';
$pdf->SetTitle($title);
$pdf->SetAutoPageBreak(true, 25.4);

$pdf->SetAuthor('Jules Verne');
$pdf->PrintChapter('template.txt');
//$pdf->PrintChapter(2,'THE PROS AND CONS','20k_c2.txt');
$pdf->Output();
 */
?>
