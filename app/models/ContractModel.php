<?php
/**
 *
 * 生成合同页面
 *
 *  @file      ContractModel.php
 *  @author    renlifeng
 *  @date      2013-09-26 17:02:18
 **/

class ContractModel
{
    const CONTRACT_PATH = '/data/ka_contract/';

    public static $contract;
    public static function getInstance($p = 'P', $margin_left=30, $margin_top=35.8)
    {
        $CI =& get_instance();
        $CI->load->library('contract');
        self::$contract = $CI->contract;

        $pdf = self::$contract;

        // 设置pdf纸张 P 竖向 mm 单位是毫米 A4 纸张是A4的
        $pdf->FPDF($p, 'mm', 'A4');

        // 设置pdf纸张边距 左右30 上35.8
        $pdf->SetMargins($margin_left, $margin_top);

        // 设置可以用的字体
        $pdf->AddGBFont('xihei','华文细黑');
        $pdf->AddGBFont('simsun','宋体');
        $pdf->AddGBFont('simhei','黑体');
        $pdf->AddGBFont('simkai','楷体_GB2312');
        $pdf->AddGBFont('sinfang','仿宋_GB2312');

        // 设置自动分页 距离下边距25.4mm时分页 也是设置下边距
        $pdf->SetAutoPageBreak(true, 25.4);

        // 设置作者
        $pdf->SetAuthor('360 CRM');
    }

    /**
     * 根据参数生成pdf
     */
    public static function agency($param, $save = false)
    {

        $param['agency_append_local'] = 'agency_append_local.txt';
        $param['agency_append_abroad'] = 'agency_append_abroad.txt';
        $param['agency_append_nav'] = 'agency_append_nav.txt';

        if (isset(self::$regaddrs[$param['partb']]))
        {
            $param['partb_address'] = self::$regaddrs[$param['partb']];
        } else {
            $param['partb_address'] = '';
        }
        if (isset($param['copy_sum'])) {
            $param['copy_sum'] = self::numToBig($param['copy_sum']);
        }

        // 处理中文的附件一二三
        $i = 1;

        // 本地搜索的处理
        if (! empty($param['search_local']))
        {
            // 处理附件几
            $param['search_local_number'] = '一';
            $i ++;

            // 处理折扣
            $param['search_local_total_discount'] = $param['search_local_fix_discount'] + $param['search_local_float_discount'];
            //$param['discount_nopre_amount_50'] = 0.5 * $param['search_local_total_discount'];
            /*if ($param['search_local_pay_type'] == AgencyAgreementPreferModel::PAY_TYPE_BEFORE )
            {
                // 处理是否可以显示预付的优惠条件
                $param['search_local_prepay_policy'] = 1;
                $param['search_local_total_discount'] = $param['search_local_total_discount'] + $param['search_local_prepay_discount'];
            }
            // 处理计算折扣后的金额
            $param['discount_amount_100'] = 1 * $param['search_local_total_discount'];
            $param['discount_amount_50'] = 0.5 * $param['search_local_total_discount'];
             */
        }

        // 海外搜索的处理
        if (! empty($param['search_abroad']))
        {
            $param['search_abroad_number'] = self::numToCN($i);
            $i ++;
        }

        // 导航资源的处理
        if (! empty($param['nav']))
        {
            $param['nav_number'] = self::numToCN($i);

            // 处理折扣
            $param['nav_total_discount'] = $param['nav_fix_discount'] + $param['nav_float_discount'];
            //$param['nav_discount_nopre_amount_600'] = 6 * ($param['nav_total_discount'] + 5);
            /*if ($param['nav_pay_type'] == AgencyAgreementPreferModel::PAY_TYPE_BEFORE )
            {
                // 处理是否可以显示预付的优惠条件
                $param['nav_prepay_policy'] = 1;
                $param['nav_total_discount'] = $param['nav_total_discount'] + $param['nav_prepay_discount'];
            }
            // 处理计算折扣后的金额
            $param['nav_discount_amount_1000'] = 10 * ($param['nav_total_discount'] + 8);
             */
        }

        self::getInstance();

        $pdf = self::$contract;

        // 设置合同编号，显示在右上角
        $pdf->SetNumber(self::getGBCode('合同编号：' . $param['number']));

        // 设置图片路径，会取出左上角的图片360head.png和背景图片360bg.jpg
        $pdf->SetPicPath(APPPATH . 'views/pdf');
        // 设置页脚左下和右下
        $footer_left = self::getGBCode('2014年度网络信息推广服务代理协议');

        // 右下角改成页码
        $footer_right = 'PageNo';

        $pdf->SetFooter($footer_left, $footer_right);

        // 文件模板路径
        $file = APPPATH. 'views/pdf/' . $param['contract_version'] . '/agency.txt';

        $pdfName = $param['pdfName'];
        $savePdfName = $param['savePdfName'];

        // 转换字符为GBK字符，这样才可以正常在windows下显示
        foreach ($param as $key => $value)
        {
            $param[$key] = self::getGBCode($value);
        }

        // 打印字符串到pdf上
        if (!file_exists($file))
        {
            show_error('模板文件不存在' . $file);
            return false;
        }
        $pdf->PrintChapter($file, $param);


        if ($save)
        {
            $pdf->Output($savePdfName, 'F');
        } else
        {
            $pdf->Output(Common::getDownName($pdfName), 'D');
        }
    }


    /**
     * 生成排期表格
     * $schedule = array(      // 二维数据排期表格
     *      array(
     *          'position' => '360导航游戏频道游戏推荐图片位第四行',       // 位置
     *          'style' => '导航二级页推荐图1（上必须带有游戏名字）',      // 广告形式
     *          'amount' =>  '232500',                                     // 此位置的总金额
     *          'discount' => 50,                                          // 此位置的折扣
     *          'real_amount' =>  '116250',                                // 此位置的最后金额
     *          'schedule' => array(                                       // 排期数组，以月份为键值，完整日期为值
     *              '2013-07' => '2013-07-01,2013-07-02,2013-07-03,2013-07-04',
     *              '2013-08' => '2013-08-01,2013-08-02,2013-08-03,2013-08-04',
     *          ),
     *      ),
     *  );
     *
     *  $info = array(
     *      'customer' => '上海悟瀚信息技术有限公司',          // 客户
     *      'product' => '星际争霸2',                          // 推广产品
     *      'amount' => '324000',                              // 本次排期实际支付金额为【{$amount}】元，
     *      'times' => 3,                                      // 分【{$times}】次支付
     *      'before_date' => '2013-06-08',                     // 乙方于【】年【】月【】日前支付【{$before_amount}】元
     *      'before_amount' => '3333',
     *      'account' => '北京可以计算有限公司',               // 账户
     *      'bank' => '北京招商银行',                          // 开户行
     *      'account_number' => '111111133444444',             // 账号
     *      'email' => 'ruowutouto@360.cn',                    // 下单确认后请尽快提供盖章排期并扫描邮件至【{$email}】
     *      'party_a' => '北京天地在线',                       // 甲方
     *      'party_b' => '奇虎360北京有限科技公司',            // 乙方
     *  );
     */
    public static function schedule($schedule, $info)
    {

        $total_amount = 0;
        // 计算单价，分别以月份开头存储数据
        foreach($schedule as $key => $sd)
        {
            $total_amount += $sd['real_amount'];
            $countDays = 0;
            $dd = array();
            foreach($sd['schedule'] as $month => $daystr)
            {
                $days = explode(',', $daystr);
                $countDays += count($days);
                $dd[$month] = $days;
            }

            if ($countDays)
            {
                $price = $sd['amount'] / $countDays;
            } else
            {
                $price = $sd['amount'];
            }

            foreach($sd['schedule'] as $month => $daystr)
            {
                $tparam = $sd;
                unset($tparam['schedule']);
                unset($tparam['amount']);
                unset($tparam['real_amount']);
                $tparam['days'] = $dd[$month];
                $tparam['price'] = $price;
                $param[$month][] = $tparam;
            }
        }

        $data = self::getMonth($param);

        $CI =& get_instance();
        $CI->load->library('PHPExcel');
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');

        $file = APPPATH . 'views/excel/schedule.xlsx';
        $objPHPExcel = $objReader->load($file);

        $replaceCell = array('A2', 'C2', 'A9', 'A10', 'A11', 'A15', 'F15');

        // 先对变量进行替换
        foreach($replaceCell as $cell)
        {

            $line = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();

            if(preg_match_all('/\{foreach\(([^\)]*)\)\}/', $line, $wd))
            {
                foreach($wd[1] as $wk=>$w)
                {
                    $nline = '';
                    if(preg_match_all('/\{\$([^\{]*)\}/', $w, $sa))
                    {
                        // 先找出需要替换的数组
                        $replaceArray = array();

                        foreach($sa[1] as $k => $v)
                        {
                            if (is_array($info[$v]))
                            {
                                foreach ($info[$v] as $key=>$value)
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

            if(preg_match_all('/\{\$([^\{]*)\}/', $line, $sa))
            {
                foreach($sa[1] as $k => $v)
                {
                    // 查看是否有竖线逻辑分割符
                    if (strpos($v, '|') !== false)
                    {
                        list($var, $value) = explode('|', $v);

                        $subValue = explode('-', $info[$var]);
                        $line = str_replace($sa[0][$k], $subValue[$value], $line);
                    }

                    if (strpos($line, $sa[0][$k]) !== false)
                    {
                        $line=str_replace($sa[0][$k], $info[$v], $line);
                    }

                }
            }

            $objPHPExcel->getActiveSheet()->setCellValue($cell, $line);
        };

        $table_week = array('C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG');
        $head_copy_col = array('A', 'B', 'AH', 'AI', 'AJ', 'AK', 'AL');

        // 填充表格内容
        $row = 6;
        $isFirst = true;
        foreach($data as $key => $da)
        {
            $tableHead = $da['table_head'];
            // 填写表头
            if ($isFirst)
            {
                $headRow = 3;
                $isFirst = false;
            } else
            {
                // 复制一份样式
                $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 3);
                $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':AG'.$row);

                foreach($head_copy_col as $col)
                {
                    $objPHPExcel->getActiveSheet()->mergeCells($col.$row.':'.$col.($row+2));
                    $objPHPExcel->getActiveSheet()->setCellValue($col.$row, $objPHPExcel->getActiveSheet()->getCell($col.'3')->getValue());
                }
                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($objPHPExcel->getActiveSheet()->getRowDimension('3')->getRowHeight());
                $objPHPExcel->getActiveSheet()->getRowDimension($row+1)->setRowHeight($objPHPExcel->getActiveSheet()->getRowDimension('4')->getRowHeight());
                $objPHPExcel->getActiveSheet()->getRowDimension($row+2)->setRowHeight($objPHPExcel->getActiveSheet()->getRowDimension('5')->getRowHeight());

                $objPHPExcel->getActiveSheet()->duplicateStyle( $objPHPExcel->getActiveSheet()->getStyle('A3:AL5'), 'A'.$row.':AL'.($row+2) );
                $headRow = $row;
                $row += 3;
            }

            // 给表头赋值
            $objPHPExcel->getActiveSheet()->setCellValue('C'. $headRow, $tableHead['month']);
            foreach($tableHead['week'] as $d=>$w)
            {
                $objPHPExcel->getActiveSheet()->setCellValue(current($table_week).($headRow + 1), $w);
                if ($w)
                {
                    $objPHPExcel->getActiveSheet()->setCellValue(current($table_week).($headRow + 2), $d);
                } else
                {
                    $objPHPExcel->getActiveSheet()->setCellValue(current($table_week).($headRow + 2), '');
                }
                next($table_week);
            }
            reset($table_week);

            // 填写数据
            $tableData = $da['data'];

            foreach($tableData as $td)
            {
                $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
                // 插入数据
                foreach($td['checked'] as $d=>$c)
                {
                    $objPHPExcel->getActiveSheet()->setCellValue(current($table_week).($row), $c);
                    next($table_week);
                }
                reset($table_week);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $td['position'])
                    ->setCellValue('B'.$row, $td['style'])
                    ->setCellValue('AH'.$row, $td['price'])
                    ->setCellValue('AI'.$row, '=SUM(C'.$row.':AG'.$row.')')
                    ->setCellValue('AJ'.$row, '=AH'.$row.'*AI'.$row)
                    ->setCellValue('AK'.$row, $td['discount'] . '%')
                    ->setCellValue('AL'.$row, '=AJ'.$row.'*AK'.$row);
                $row += 1;
            }
        }

        // 删除空余的行
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($objPHPExcel->getActiveSheet()->getRowDimension($row+1)->getRowHeight());
        $objPHPExcel->getActiveSheet()->removeRow($row, 1);
        $objPHPExcel->getActiveSheet()->setCellValue('AL'.$row, $total_amount);


        $pdfName = Common::getDownName('排期表格.xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$pdfName.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 根据排期日期得到年份月份的数组
     */
    public static function getMonth($param)
    {
        $result = array();
        ksort($param);
        foreach ($param as $month => $data)
        {
            $orimonth = array(
                'week' => array(),
            );

            $td = date('t', strtotime($month . '-01'));  // 总共的天数

            $checked = array();
            // 先计算每个月的表头
            for ($i=1; $i<=31; $i++)
            {
                $checked[$i] = '';
                $d = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date = $month . '-' . $d;
                $w = date('N', strtotime($date)); // 星期几

                if ($i <= $td)
                {
                    $orimonth['week'][$i] = self::numToCN($w);
                } else {
                    $orimonth['week'][$i] = '';
                }
            }
            $orimonth['month'] = substr($month, 0, 4) . '年' . intval(substr($month, 5, 2)) . '月';

            $result[$month]['table_head'] = $orimonth;


            foreach($data as $d)
            {
                $check = $checked;
                foreach($d['days'] as $daystr)
                {
                    $ck = intval(substr($daystr, -2));
                    $check[$ck] = 1;
                }

                unset($d['days']);
                $d['checked'] = $check;
                $result[$month]['data'][] = $d;
            }
        }

        return $result;
    }


    /**
     * 下载已经存在的pdf文件
     */
    public static function outPdf($savePdfName, $pdfName)
    {
        $pdfName = Common::getDownName($pdfName);
        if (file_exists($savePdfName))
        {
            header('Content-Type: application/x-download');
            header('Content-Disposition: attachment; filename="'.$pdfName.'"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            $out = file_get_contents($savePdfName);
            echo $out;
            return true;
        }
        return false;
    }



    public static function getGBCode($char)
    {
        return mb_convert_encoding($char, 'GBK', 'UTF-8');
    }


    public static function getArrayGBCode(&$char)
    {
        if (is_array($char))
        {
            foreach($char as &$c)
            {
                self::getArrayGBCode($c);
            }
        } else
        {
            $char = mb_convert_encoding($char, 'GBK', 'UTF-8');
            return $char;
        }
    }

    /**
     * 人民币小写转大写
     *
     * @param string $number 数值
     * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆"
     * @param bool $is_round 是否对小数进行四舍五入
     * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30，
     *             有的系统要求输出"壹仟玖佰陆拾元零叁角"，实际上"壹仟玖佰陆拾元叁角"也是对的
     * @return string
     */
    public static function numToUpper($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE)
    {
        // 将数字切分成两段
        $parts = explode('.', $number, 2);
        $int = isset($parts[0]) ? strval($parts[0]) : '0';
        $dec = isset($parts[1]) ? strval($parts[1]) : '';

        // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
        $dec_len = strlen($dec);
        if (isset($parts[1]) && $dec_len > 2)
        {
            $dec = $is_round
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1)
                : substr($parts[1], 0, 2);
        }

        // 当number为0.001时，小数点后的金额为0元
        if(empty($int) && empty($dec))
        {
            return '零';
        }

        // 定义
        $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖');
        $uni = array('','拾','佰','仟');
        $dec_uni = array('角', '分');
        $exp = array('', '万');
        $res = '';

        // 整数部分从右向左找
        for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++)
        {
            $str = '';
            // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
            for($j = 0; $j < 4 && $i >= 0; $j++, $i--)
            {
                $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位
                $str = $chs[$int{$i}] . $u . $str;
            }
            //echo $str."|".($k - 2)."<br>";
            $str = rtrim($str, '0');// 去掉末尾的0
            $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0
            if(!isset($exp[$k]))
            {
                $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位
            }
            $u2 = $str != '' ? $exp[$k] : '';
            $res = $str . $u2 . $res;
        }

        // 如果小数部分处理完之后是00，需要处理下
        $dec = rtrim($dec, '0');

        // 小数部分从左向右找
        if(!empty($dec))
        {
            $res .= $int_unit;

            // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
            if ($is_extra_zero)
            {
                if (substr($int, -1) === '0')
                {
                    $res.= '零';
                }
            }

            for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++)
            {
                $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位
                $res .= $chs[$dec{$i}] . $u;
            }
            $res = rtrim($res, '0');// 去掉末尾的0
            $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0
        }
        else
        {
            $res .= $int_unit . '整';
        }
        return $res;
    }


    /**
     * 将数字金额转成大写 没有金额单位
     *
     */
    public static function numToBig($number = 0, $int_unit = '', $is_round = FALSE, $is_extra_zero = FALSE)
    {
        // 将数字切分成两段
        $parts = explode('.', $number, 2);
        $int = isset($parts[0]) ? strval($parts[0]) : '0';
        $dec = isset($parts[1]) ? strval($parts[1]) : '';

        // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
        $dec_len = strlen($dec);
        if (isset($parts[1]) && $dec_len > 2)
        {
            $dec = $is_round
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1)
                : $parts[1];
        }

        // 当number为0.001时，小数点后的金额为0元
        if(empty($int) && empty($dec))
        {
            return '零';
        }

        // 定义
        $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖');
        $uni = array('','拾','佰','仟');
        //$dec_uni = array('角', '分');
        $exp = array('', '万');
        $res = '';

        // 整数部分从右向左找
        for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++)
        {
            $str = '';
            // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
            for($j = 0; $j < 4 && $i >= 0; $j++, $i--)
            {
                $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位
                $str = $chs[$int{$i}] . $u . $str;
            }
            //echo $str."|".($k - 2)."<br>";
            $str = rtrim($str, '0');// 去掉末尾的0
            $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0
            if(!isset($exp[$k]))
            {
                $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位
            }
            $u2 = $str != '' ? $exp[$k] : '';
            $res = $str . $u2 . $res;
        }

        // 如果小数部分处理完之后是00，需要处理下
        $dec = rtrim($dec, '0');

        // 小数部分从左向右找
        if(!empty($dec))
        {
            $res .= $int_unit;

            // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
            if ($is_extra_zero)
            {
                if (substr($int, -1) === '0')
                {
                    $res.= '零';
                }
            }
            $res .= '点';

            for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++)
            {
                $res .= $chs[$dec{$i}];
            }
            $res = rtrim($res, '0');// 去掉末尾的0
            $res = str_replace("0", "零", $res); // 替换多个连续的0
        }
        else
        {
            $res .= $int_unit;
        }
        return $res;
    }


    /**
     * numToCN
     * 将数据转换成中文
     */
    public static function numToCN($number = 0, $int_unit = '', $is_round = TRUE, $is_extra_zero = FALSE)
    {
        // 将数字切分成两段
        $parts = explode('.', $number, 2);
        $int = isset($parts[0]) ? strval($parts[0]) : '0';
        $dec = isset($parts[1]) ? strval($parts[1]) : '';

        // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
        $dec_len = strlen($dec);
        if (isset($parts[1]) && $dec_len > 2)
        {
            $dec = $is_round
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1)
                : $parts[1];
        }

        // 当number为0.001时，小数点后的金额为0元
        if(empty($int) && empty($dec))
        {
            return '零';
        }

        // 定义
        $chs = array('0','一','二','三','四','五','六','七','八','九');
        $uni = array('','十','百','千');
        //$dec_uni = array('角', '分');
        $exp = array('', '万');
        $res = '';

        // 整数部分从右向左找
        for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++)
        {
            $str = '';
            // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
            for($j = 0; $j < 4 && $i >= 0; $j++, $i--)
            {
                $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位
                $str = $chs[$int{$i}] . $u . $str;
            }
            //echo $str."|".($k - 2)."<br>";
            $str = rtrim($str, '0');// 去掉末尾的0
            $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0
            if(!isset($exp[$k]))
            {
                $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位
            }
            $u2 = $str != '' ? $exp[$k] : '';
            $res = $str . $u2 . $res;
        }

        // 如果小数部分处理完之后是00，需要处理下
        $dec = rtrim($dec, '0');

        // 小数部分从左向右找
        if(!empty($dec))
        {
            $res .= $int_unit;

            // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
            if ($is_extra_zero)
            {
                if (substr($int, -1) === '0')
                {
                    $res.= '零';
                }
            }
            $res .= '点';

            for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++)
            {
                $res .= $chs[$dec{$i}];
            }
            $res = rtrim($res, '0');// 去掉末尾的0
            $res = str_replace("0", "零", $res); // 替换多个连续的0
        }
        else
        {
            $res .= $int_unit;
        }
        return $res;
    }
}
