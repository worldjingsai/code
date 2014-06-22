<?php
require(realpath(dirname(__FILE__)) . '/fpdf.php');

class PDF_Chinese extends FPDF
{

static $Big5_widths=array(' '=>250,'!'=>250,'"'=>408,'#'=>668,'$'=>490,'%'=>875,'&'=>698,'\''=>250,
	'('=>240,')'=>240,'*'=>417,'+'=>667,','=>250,'-'=>313,'.'=>250,'/'=>520,'0'=>500,'1'=>500,
	'2'=>500,'3'=>500,'4'=>500,'5'=>500,'6'=>500,'7'=>500,'8'=>500,'9'=>500,':'=>250,';'=>250,
	'<'=>667,'='=>667,'>'=>667,'?'=>396,'@'=>921,'A'=>677,'B'=>615,'C'=>719,'D'=>760,'E'=>625,
	'F'=>552,'G'=>771,'H'=>802,'I'=>354,'J'=>354,'K'=>781,'L'=>604,'M'=>927,'N'=>750,'O'=>823,
	'P'=>563,'Q'=>823,'R'=>729,'S'=>542,'T'=>698,'U'=>771,'V'=>729,'W'=>948,'X'=>771,'Y'=>677,
	'Z'=>635,'['=>344,'\\'=>520,']'=>344,'^'=>469,'_'=>500,'`'=>250,'a'=>469,'b'=>521,'c'=>427,
	'd'=>521,'e'=>438,'f'=>271,'g'=>469,'h'=>531,'i'=>250,'j'=>250,'k'=>458,'l'=>240,'m'=>802,
	'n'=>531,'o'=>500,'p'=>521,'q'=>521,'r'=>365,'s'=>333,'t'=>292,'u'=>521,'v'=>458,'w'=>677,
	'x'=>479,'y'=>458,'z'=>427,'{'=>480,'|'=>496,'}'=>480,'~'=>667);

static $GB_widths=array(' '=>500,'!'=>270,'"'=>342,'#'=>467,'$'=>462,'%'=>797,'&'=>710,'\''=>239,
	'('=>374,')'=>374,'*'=>423,'+'=>605,','=>238,'-'=>575,'.'=>238,'/'=>334,'0'=>462,'1'=>462,
	'2'=>462,'3'=>462,'4'=>462,'5'=>462,'6'=>462,'7'=>462,'8'=>462,'9'=>462,':'=>238,';'=>238,
	'<'=>605,'='=>605,'>'=>605,'?'=>344,'@'=>548,'A'=>550,'B'=>550,'C'=>550,'D'=>550,'E'=>550,
	'F'=>550,'G'=>550,'H'=>550,'I'=>450,'J'=>500,'K'=>550,'L'=>550,'M'=>600,'N'=>550,'O'=>550,
	'P'=>550,'Q'=>550,'R'=>550,'S'=>550,'T'=>550,'U'=>550,'V'=>550,'W'=>600,'X'=>550,'Y'=>550,
	'Z'=>550,'['=>374,'\\'=>333,']'=>374,'^'=>606,'_'=>500,'`'=>239,'a'=>500,'b'=>500,'c'=>500,
	'd'=>500,'e'=>500,'f'=>500,'g'=>500,'h'=>500,'i'=>400,'j'=>450,'k'=>500,'l'=>450,'m'=>550,
	'n'=>500,'o'=>500,'p'=>500,'q'=>500,'r'=>500,'s'=>500,'t'=>500,'u'=>500,'v'=>500,'w'=>550,
	'x'=>500,'y'=>500,'z'=>500,'{'=>370,'|'=>258,'}'=>370,'~'=>605);

public $GB_character = array();
public $GB_endchar = array();

function AddCIDFont($family,$style,$name,$cw,$CMap,$registry)
{
	$i=count($this->fonts)+1;
	$fontkey=strtolower($family).strtoupper($style);
	$this->fonts[$fontkey]=array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>-120,'ut'=>40,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry);
}

function AddBig5Font($family='Big5')
{
	$cw=self::$Big5_widths;
	$name='MSungStd-Light-Acro';
	$CMap='ETenms-B5-H';
	$registry=array('ordering'=>'CNS1','supplement'=>0);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry);
}

function AddGBFont($family='GB', $name='STSongStd-Light-Acro')
{
	$cw=self::$GB_widths;
	//$name='STSongStd-Light-Acro';
	$CMap='GBKp-EUC-H';
	$registry=array('ordering'=>'GB1','supplement'=>2);
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry);
}

function GetStringWidth($s)
{
	if($this->CurrentFont['type']=='Type0')
		return $this->GetMBStringWidth($s);
	else
		return parent::GetStringWidth($s);
}

function GetMBStringWidth($s)
{
	//Multi-byte version of GetStringWidth()
	$l=0;
	$cw=&$this->CurrentFont['cw'];
	$nb=strlen($s);
	$i=0;
	while($i<$nb)
	{
		$c=$s[$i];
		if(ord($c)<128)
		{
			$l+=(isset($cw[$c])?$cw[$c]:100);
			$i++;
		}
		else
		{
			$l+=1000;
			$i+=2;
		}
	}
	return $l*$this->FontSize/1000;
}

function MultiCell($w,$h,$txt,$border=0,$align='J',$fill=0)
{
	if($this->CurrentFont['type']=='Type0')
		$this->MBMultiCell($w,$h,$txt,$border,$align,$fill);
	else
		parent::MultiCell($w,$h,$txt,$border,$align,$fill);
}

function MBMultiCell($w,$h,$txt,$border=0,$align='J',$fill=0)
{
	//Multi-byte version of MultiCell()
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
	$b=0;
	if($border)
	{
		if($border==1)
		{
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else
		{
			$b2='';
			if(is_int(strpos($border,'L')))
				$b2.='L';
			if(is_int(strpos($border,'R')))
				$b2.='R';
			$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$ns=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		//Check if ASCII or MB
		$ascii=(ord($c)<128);
		if($c=="\n")
		{
			//Explicit line break
			if($this->ws>0)
			{
				$this->ws=0;
				$this->_out('0 Tw');
			}
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2)
				$b=$b2;
			continue;
		}
		if(!$ascii)
		{
			$sep=$i;
			$ls=$l;
		}
		elseif($c==' ')
		{
			$sep=$i;
			$ls=$l;
			$ns++;
		}
		$l+=$ascii ? (isset($cw[$c]) ? $cw[$c] : 1000) : 1000;
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				if($i==$j)
					$i+=$ascii ? 1 : 2;
				if($this->ws>0)
				{
					$this->ws=0;
					$this->_out('0 Tw');
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}
			else
			{
				if($align=='J')
				{
					if($s[$sep]==' ')
						$ns--;
					if($s[$i-1]==' ')
					{
						$ns--;
						$ls-=$cw[' '];
					}
                    // 添加如果下一个是标点符号也需要把这个值加进来
                    while(in_array(substr($s,$sep,2), $this->GB_character))
                    {
                        $ns++;
                        $sep+=2;
                    }

                    // 如果是左边的括号需要把括号换到下一行
                    while(in_array(substr($s,$sep-2,2), $this->GB_endchar))
                    {
                        $ns--;
                        $sep-=2;
                    }

					$this->ws=($ns>0) ? ($wmax-$ls)/1000*$this->FontSize/$ns : 0;
					$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2)
				$b=$b2;
		}
		else
			$i+=$ascii ? 1 : 2;
	}
	//Last chunk
	if($this->ws>0)
	{
		$this->ws=0;
		$this->_out('0 Tw');
	}
	if($border and is_int(strpos($border,'B')))
		$b.='B';
	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
	$this->x=$this->lMargin;
}

function Write($h,$txt,$link='')
{
	if($this->CurrentFont['type']=='Type0')
		$this->MBWrite($h,$txt,$link);
	else
		parent::Write($h,$txt,$link);
}

function MBWrite($h,$txt,$link)
{
	//Multi-byte version of Write()
	$cw=&$this->CurrentFont['cw'];
	$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		//Check if ASCII or MB
		$ascii=(ord($c)<128);
		if($c=="\n")
		{
			//Explicit line break
			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
			continue;
		}
		if(!$ascii or $c==' ')
			$sep=$i;
		$l+=$ascii ? $cw[$c] : 1000;
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				if($this->x>$this->lMargin)
				{
					//Move to next line
					$this->x=$this->lMargin;
					$this->y+=$h;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
					$i++;
					$nl++;
					continue;
				}
				if($i==$j)
					$i+=$ascii ? 1 : 2;
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
		}
		else
			$i+=$ascii ? 1 : 2;
	}
	//Last chunk
	if($i!=$j)
		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j,$i-$j),0,0,'',0,$link);
}

function _putfonts()
{
	$nf=$this->n;
	foreach($this->diffs as $diff)
	{
		//Encodings
		$this->_newobj();
		$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
		$this->_out('endobj');
	}
	$mqr=get_magic_quotes_runtime();
	//set_magic_quotes_runtime(0);
	ini_set("magic_quotes_runtime",0);
	foreach($this->FontFiles as $file=>$info)
	{
		//Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n']=$this->n;
		if(defined('FPDF_FONTPATH'))
			$file=FPDF_FONTPATH.$file;
		$size=filesize($file);
		if(!$size)
			$this->Error('Font file not found');
		$this->_out('<</Length '.$size);
		if(substr($file,-2)=='.z')
			$this->_out('/Filter /FlateDecode');
		$this->_out('/Length1 '.$info['length1']);
		if(isset($info['length2']))
			$this->_out('/Length2 '.$info['length2'].' /Length3 0');
		$this->_out('>>');
		$f=fopen($file,'rb');
		$this->_putstream(fread($f,$size));
		fclose($f);
		$this->_out('endobj');
	}
	//set_magic_quotes_runtime($mqr);
	ini_set("magic_quotes_runtime",$mqr);
	foreach($this->fonts as $k=>$font)
	{
		//Font objects
		$this->_newobj();
		$this->fonts[$k]['n']=$this->n;
		$this->_out('<</Type /Font');
		if($font['type']=='Type0')
			$this->_putType0($font);
		else
		{
			$name=$font['name'];
			$this->_out('/BaseFont /'.$name);
			if($font['type']=='core')
			{
				//Standard font
				$this->_out('/Subtype /Type1');
				if($name!='Symbol' and $name!='ZapfDingbats')
					$this->_out('/Encoding /WinAnsiEncoding');
			}
			else
			{
				//Additional font
				$this->_out('/Subtype /'.$font['type']);
				$this->_out('/FirstChar 32');
				$this->_out('/LastChar 255');
				$this->_out('/Widths '.($this->n+1).' 0 R');
				$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
				if($font['enc'])
				{
					if(isset($font['diff']))
						$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
					else
						$this->_out('/Encoding /WinAnsiEncoding');
				}
			}
			$this->_out('>>');
			$this->_out('endobj');
			if($font['type']!='core')
			{
				//Widths
				$this->_newobj();
				$cw=&$font['cw'];
				$s='[';
				for($i=32;$i<=255;$i++)
					$s.=$cw[chr($i)].' ';
				$this->_out($s.']');
				$this->_out('endobj');
				//Descriptor
				$this->_newobj();
				$s='<</Type /FontDescriptor /FontName /'.$name;
				foreach($font['desc'] as $k=>$v)
					$s.=' /'.$k.' '.$v;
				$file=$font['file'];
				if($file)
					$s.=' /FontFile'.($font['type']=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
				$this->_out($s.'>>');
				$this->_out('endobj');
			}
		}
	}
}

function _putType0($font)
{
	//Type0
	$this->_out('/Subtype /Type0');
	$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
	$this->_out('/Encoding /'.$font['CMap']);
	$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
	$this->_out('>>');
	$this->_out('endobj');
	//CIDFont
	$this->_newobj();
	$this->_out('<</Type /Font');
	$this->_out('/Subtype /CIDFontType0');
	$this->_out('/BaseFont /'.$font['name']);
	$this->_out('/CIDSystemInfo <</Registry (Adobe) /Ordering ('.$font['registry']['ordering'].') /Supplement '.$font['registry']['supplement'].'>>');
	$this->_out('/FontDescriptor '.($this->n+1).' 0 R');
	$W='/W [1 [';
	foreach($font['cw'] as $w)
		$W.=$w.' ';
	$this->_out($W.']]');
	$this->_out('>>');
	$this->_out('endobj');
	//Font descriptor
	$this->_newobj();
	$this->_out('<</Type /FontDescriptor');
	$this->_out('/FontName /'.$font['name']);
	$this->_out('/Flags 6');
	$this->_out('/FontBBox [0 0 1000 1000]');
	$this->_out('/ItalicAngle 0');
	$this->_out('/Ascent 1000');
	$this->_out('/Descent 0');
	$this->_out('/CapHeight 1000');
	$this->_out('/StemV 10');
	$this->_out('>>');
	$this->_out('endobj');
}
}
?>
