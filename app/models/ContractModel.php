<?php
/**
 *
 * 生成合同页面
 *
 *  @file      ContractModel.php
 *  @author    renlifeng<renlifeng@360.cn>
 *  @date      2013-09-26 17:02:18
 **/

class ContractModel
{
    static $regaddrs = array(
        '北京奇虎科技有限公司'=>'北京市西城区新街口外大街28号D座112室（德胜园区）',
        '天津奇思科技有限公司'=>'天津新技术产业园区华苑产业区海泰西路18号北2-102工业孵化-5',
        '奇飞翔艺（北京）软件有限公司'=>'北京市朝阳区酒仙桥路6号院2号楼A座5层501-506室'
    );

    static $contractName = array(
        'agency' => '网络信息推广服务代理协议',
        'frame'  =>'网络推广服务框架协议',
        'search' => '按点击付费网络推广合同',
        'guess' => '按点击付费网络推广合同',
        'fix' => '网址导航推广合同',
        'brand' => '网络推广合同（品牌直达）',
    );

    static $businessName = array(
        'agency' => '网络信息推广服务',
        'frame'  =>'网络推广服务',
        'search' => '按点击付费',
        'guess' => '按点击付费',
        'fix' => '网址导航推广',
        'brand' => '网络推广',
    );

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

    public static function framePdf($frameId, $isSave=false)
    {
        $model = new FrameModel();
        $frame = $model->fetch($frameId);
        if(empty($frame))
        {
            return show_error('框架不存在');
        }

        $savePdfName = self::CONTRACT_PATH . 'frame' . $frame['frame_serieal_num'] . '.pdf';
        list($partya) = explode(',', $frame['party_a']);
        $pdfName = $partya . '_框架协议_' . $frame['frame_serieal_num'] . '.pdf';
        if (file_exists($savePdfName) && !$isSave)
        {
            return ContractModel::outPdf($savePdfName, $pdfName);
        }

        if($frame['parent_id'] ==-1)
        { //终止协议
            $term = $frame;
            $frame = $model->fetch($term['ancestor_id']);
            $param = array(
                'savePdfName' => $savePdfName,
                'pdfName' => $pdfName,
                'agreement_type' => 'frame',
                'ori_number' => $frame['frame_serieal_num'],   // 原协议编号
                'number' => $term['frame_serieal_num']  ,      // 协议编号
                'ori_create_date' => substr($frame['frame_applied'], 0,10),  // 原协议创建时间
                'party_a' => $term['party_a'],                   // 甲方名称
                'party_a_address' => $term['party_a_addr'],      // 甲方注册地址
                'party_b' => $term['party_b'],                   // 乙方名称
                'business' => $term['party_a_business'],       // 业务
                'end_date' => $term['frame_ended'],
                'contract_version'=>$term['contract_version']
            );
            ContractModel::stop($param, $isSave);
        } else
        {
            $version = $frame['contract_version'];
            $frame['savePdfName'] = $savePdfName;
            $frame['pdfName'] = $pdfName;
            if($frame['frame_type'] == 'fixed')
            {
                if ($frame['product_type'] == 'game_cpt') {
                    $tpl = empty($frame['agency'])?'frame_fixed_game.txt':'frame_fixed_game_agency.txt';
                    $frame['frame_payment_big'] = ContractModel::numToUpper($frame['frame_payment']);
                } else {
                    $tpl = empty($frame['agency'])?'frame_fixed_nav.txt':'frame_fixed_nav_agency.txt';
                }
                $footer = array('网络信息推广服务框架协议（导航）', $version . '版');
            }  else if($frame['frame_type'] == 'search')
            {
                if(empty($frame['back_orders'])){
                    $tpl = empty($frame['agency'])?'frame_search.txt':'frame_search_agency.txt';
                }else{
                    $tpl = empty($frame['agency'])?'frame_search_backdate.txt':'frame_search_agency_backdate.txt';
                }
                $footer = array('网络信息推广服务框架协议（搜索）', $version . '版');
                $frame['frame_payment_big'] = ContractModel::numToUpper($frame['frame_payment']);
            } else if($frame['frame_type'] == 'guess')
            {
                if(empty($frame['back_orders'])){
                    $tpl = empty($frame['agency'])?'frame_guess.txt':'frame_guess_agency.txt';
                }else{
                    $tpl = empty($frame['agency'])?'frame_guess_backdate.txt':'frame_guess_agency_backdate.txt';
                }
                if($version=='201307'){
                    $footer = array('网络信息推广服务框架协议（猜你喜欢）', $version . '版');
                }else{
                    $footer = array('网络信息推广服务框架协议（奇迹推广）', $version . '版');
                }
                $frame['frame_payment_big'] = ContractModel::numToUpper($frame['frame_payment']);
            }
            $frame['frame_budget_big'] = ContractModel::numToUpper($frame['frame_budget']);
            $frame['number'] = $frame['frame_serieal_num'];
            $frame['create_date'] = $frame['frame_applied'];
            //优惠生效时间
            if($frame['discount_started'] ==1)
            {
                $frame['effect_type'] = 1;
                $frame['discount_started']='0000-00-00';
            }else{
                $frame['effect_type'] = 2;
            }
            //合同争议管辖
            if($frame['arbitration'] == '向被告所在地人民法院提起诉讼 ')
            {
                $frame['dispute_type'] = 2;
            }  else
            {
                $frame['dispute_type'] = 1;
            }
            //倒签的订单
            if(!empty($frame['back_orders']))
            {
                foreach (explode('||', $frame['back_orders']) as $_bk)
                {
                    list($_oId,$frame['bkNum'][],,$_bkTime) = explode(':', $_bk);
                    list($frame['bkYear'][], $frame['bkMonth'][],$frame['bkDay'][]) = explode('-', $_bkTime);
                    //确定倒签的合同名
                     $_order = new KAOrderInfoModel($_oId);
                     if($_order->contract_type == KAOrderInfoModel::ORDER_TYPE_BRAND)
                     {
                         $frame['bkName'][] = '网络推广合同（品牌直达）';
                     }else
                     {
                         $frame['bkName'][] = '按点击付费网络推广合同';
                     }
                }
            }
            self::frame($frame, $tpl, $footer, $isSave);
        }
    }

    /**
     * 2013年度网络推广服务框架协议（猜你喜欢）
        $param = array(
            'number' => 'QH-KW-2013-01-04',               // 编号
            'create_date' => '2013-01-04',                // 创建时间
            'parta' => '百度网络有限公司司公司',          // 甲方名称
            'parta_client' => '腾讯科技有限公司',         // 代理的客户名称，如果不是代理公司可以留空
            'parta_address' => '北京西二旗百度大厦5号',   // 甲方注册地址
            'parta_connect' => '李某某',                  // 甲方联系人
            'parta_email' => 'limm@360.cn',               // 甲方电子邮件
            'parta_phone' => '132098877798',              // 甲方电话

            'partb' => '北京奇虎科技有限公司',            // 乙方名称
            'partb_connect' => '老周',                    // 乙方联系人
            'partb_email' => 'laozhou@360.cn',            // 乙方电子邮件
            'partb_phone' => '123456789012',              // 乙方电话

            'parta_business' => '卖医疗广告假药',         // 甲方的主要业务

            'start_date' => '2013-01-31',                 // 合同有效期开始日期
            'end_date' => '2013-01-31',                   // 合同有效期结束日期

            'frame_budget' => 750,                        // 框架总金额单位为元，可以是两位的小数 244335.54
            'frame_budget_big' => self::numToUpper(750),  // 框架总金额大写
            'prefer_ratio' => 145,                        // 搜索推广优惠比例 100:145
            'effect_type' => 2,                           // 优惠额度生效的类型 1 10日内生效 2 填写日期
            'prefer_date' => '2013-02-03',                // 优惠额度生效时间点  如果是选择的10日内生效则留空
            'frame_payment' => 80,                        // 框架保证金，单位为元，可以保留两位小数
            'frame_payment_big' => self::numToUpper(80),  // 框架保证金大写
            'payment_date' => '2013-03-04',               // 框架保证金支付时间

            'dispute_type' => 1,                          // 纠纷解决方式 1:向北京仲裁委员会申请仲裁 2:向被告所在地人民法院提起诉讼
        );
     * @return void
     */
    private static function frame($param, $tpl, $footer, $isSave=false)
    {
        if (empty($param['contract_version']))
        {
            $param['contract_version'] = OrderNumberModel::CONTRACT_VERSION_FRAME;
        }
        self::getInstance();
        $pdf = self::$contract;
        if (isset(self::$regaddrs[$param['party_b']]))
        {
            $param['party_b_addr'] = self::$regaddrs[$param['party_b']];
        } else {
            $param['party_b_addr'] = '';
        }
        $param['discount10'] = $param['discount']/10;
        $param['discount_100'] = $param['discount'];
        // 设置合同编号，显示在右上角
        $pdf->SetNumber(self::getGBCode('合同编号：' . $param['frame_serieal_num']));
        // 设置图片路径，会取出左上角的图片360head.png和背景图片360bg.jpg
        $pdf->SetPicPath(APPPATH.'views/pdf');
        // 设置页脚左下和右下
        $pdf->SetFooter(mb_convert_encoding($footer[0], 'GB2312'), mb_convert_encoding($footer[1], 'GB2312'));
        // 文件模板路径
        $file = APPPATH . 'views/pdf/'. $param['contract_version'] . '/' . $tpl;
        if (!file_exists($file))
        {
            return show_error('模板文件不存在');
        }

        $pdfName = $param['pdfName'];
        $savePdfName = $param['savePdfName'];

        // 转换字符为GBK字符，这样才可以正常在windows下显示
        self::getArrayGBCode($param);
        // 打印字符串到pdf上
        $pdf->PrintChapter($file, $param);
        // 保存文件
        if ($isSave)
        {
            $pdf->Output($savePdfName, 'F');
        } else
        {
            $pdf->Output(Common::getDownName($pdfName), 'D');
        }
    }


    /**
     * 生成代理协议的PDF
     */
    public static function agencyPdf($id, $save = false)
    {
        $agencyModel = new AgencyAgreementInfoModel($id);
        if (!$agencyModel->agency_agreement_id)
        {
            return false;
        }

        $partyA = $agencyModel->partyA;
        $paname = array();
        foreach($partyA as $pa)
        {
            $paname[] = $pa['agency_name'];
        }

        $info = $agencyModel->toArray();

        $savePdfName = self::CONTRACT_PATH . 'agency' . $info['agency_agreement_number'] . '.pdf';
        $pdfName = $paname[0] . '_代理协议_' . $info['agency_agreement_number'] . '.pdf';

        if (file_exists($savePdfName) && !$save)
        {
            return ContractModel::outPdf($savePdfName, $pdfName);
        }

        $param = array(
            'pdfName' => $pdfName,
            'savePdfName' => $savePdfName,

            'number' => $info['agency_agreement_number'], // 编号
            'contract_version' => $info['contract_version'],  // 合同版本号
            'create_date' => substr($info['create_time'], 0, 10), // 创建时间
            'parta' => join(";", $paname), // 甲方名称
            'parta_address' => $info['part_a_regaddress'], // 甲方注册地址
            'parta_connectaddress' => $info['part_a_connectaddress'], // 甲方注册地址
            'parta_connect' => $info['part_a_contact_name'], // 甲方联系人
            'parta_email' => $info['part_a_contact_email'], // 甲方电子邮件
            'parta_phone' => $info['part_a_contact_phone'], // 甲方电话
            'parta_business' => $info['part_a_business'], // 甲方的主要业务

            'partb' => $info['part_b_name'],             // 乙方名称
            'partb_connect' => $info['part_b_contact_name'], // 乙方联系人
            'partb_email' => $info['part_b_contact_email'], // 乙方电子邮件
            'partb_phone' => $info['part_b_contact_phone'], // 乙方电话

            'start_date' => $info['begin_date'], // 合同有效期开始日期
            'end_date' => $info['end_date'],     // 合同有效期结束日期
            'dispute_type' => $info['dispute_type'], // 纠纷解决方式 1:向北京仲裁委员会申请仲裁 2:向被告所在地人民法院提起诉讼
            'copy_sum' => $info['copy_sum'], // 一式几份

            'nav_pay_type' => '',
            'nav_float_discount' => '',
            'search_local_fix_discount' => AgencyAgreementPreferModel::SEARCH_LOCAL_FIX_DISCOUNT,
            //'search_local_prepay_discount' => AgencyAgreementPreferModel::SEARCH_LOCAL_PREPAY_DISCOUNT,

            'search_abroad_search_discount' => '',
            'search_abroad_brand_discount' => '',

            'nav_pay_type' => '',
            'nav_float_discount' => '',
            'nav_fix_discount' => AgencyAgreementPreferModel::GUESS_FIX_DISCOUNT,
            //'nav_prepay_discount' => AgencyAgreementPreferModel::GUESS_PREPAY_DISCOUNT,

            'search_local' => '',
            'search_abroad' => '',
            'nav' => '',
        );

        // 终止协议
        if ($info['type'] == AgencyAgreementInfoModel::TYPE_APPEND_STOP)
        {
            $param['party_a'] = $param['parta'];
            $param['party_b'] = $param['partb'];
            $param['party_a_address'] = $param['parta_address'];
            $param['agreement_type'] = 'agency';
            $parentId = $info['parent_id'];
            $model = new AgencyAgreementInfoModel($parentId);
            $param['ori_number'] = $model->agency_agreement_number;
            return ContractModel::stop($param, $save);

            // 原协议
        } else
        {
            $policy = $agencyModel->policy;

            // 增加协议的判断
            foreach($policy as $po)
            {
                switch ($po['prefer_policy_type'])
                {
                case AgencyAgreementInfoModel::SERVICE_SEARCH_LOCAL :
                    $param['search_local'] = true;
                    $param['search_local_pay_type'] = $po['pay_type'];
                    $param['search_local_float_discount'] =$po['float_discount'];
                    break;

                case AgencyAgreementInfoModel::SERVICE_SEARCH_ABROAD :
                    $param['search_abroad'] = true;
                    $param['search_abroad_search_discount'] = $po['search_discount'];
                    $param['search_abroad_brand_discount'] = $po['brand_discount'];
                    break;

                case AgencyAgreementInfoModel::SERVICE_NAV :
                    $param['nav'] = true;
                    $param['nav_pay_type'] = $po['pay_type'];
                    $param['nav_float_discount'] = $po['float_discount'];
                    break;
                }
            }
            return ContractModel::agency($param, $save);
        }
    }


    /**
     * 根据参数生成pdf
     */
    public static function agency($param, $save = false)
    {
        if (empty($param['contract_version']))
        {
            $param['contract_version'] = OrderNumberModel::CONTRACT_VERSION_AGENCY;
        }

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
     * 生成订单的信息
     */
    public static function orderPdf($orderId, $type = '', $save = false)
    {
        $order = new KAOrderInfoModel($orderId);
        if (empty($order->order_id))
        {
            return false;
        }

        $savePdfName = self::CONTRACT_PATH . 'one' . $order->contract_num . '.pdf';
        $pdfName = $order->party_a . '_订单_' . $order->contract_num . '.pdf';

        if ($type != 'schedule' && file_exists($savePdfName) && !$save)
        {
            return ContractModel::outPdf($savePdfName, $pdfName);
        }

        $orderExtend = $order->extendInfo;
        $param = array(
            'pdfName' => $pdfName,
            'savePdfName' => $savePdfName,
            'contract_num' => $order->contract_num,                                 // 合同编号
            'contract_version' => $order->contract_version,                         // 合同版本号
            'type' =>$order->contract_type,                                         // 合同类型
            'party_a' => $order->party_a,
            'party_a_address' => $orderExtend->party_a_address,
            'party_a_contact' => $orderExtend->party_a_contact,                                             // 甲方联系方式
            'is_agent' => $order->is_agent,                                                                 // 是否代理公司
            'customer_name' => $order->customer_name,                                                       // 客户名称
            'party_b' => $order->party_b,                                                                   // 乙方名称
            'party_b_contact' => $orderExtend->party_b_contact,                                                   // 乙方联系方式
            'frame_num' => $order->frame_num,                                                               // 框架协议编号
            'begin_time' => $order->contract_begin_time,                                                    // 推广开始时间
            'end_time'=> $order->contract_end_time,                                                         // 推广结束时间
            'amount' => $order->amount,                                                                     // 订单总金额
            'create_time' => date('Y-m-d', $order->create_time),

            'before_day' => isset($orderExtend->cooperation_days) ? $orderExtend->cooperation_days : '',    // 提前几天通知
            'confirmation_type' => isset($orderExtend->confirmation_type) ? $orderExtend->confirmation_type : '',       // 续费确认函方式
            'confirm_email' => isset($orderExtend->confirm_email) ? $orderExtend->confirm_email : '',                   // 续费确认邮箱

            // 搜索推广和猜你喜欢特有的
            'pay_type' => isset($orderExtend->pay_type) ? $orderExtend->pay_type : '',                       // 付款方式:1现金 2支票 3电汇 4汇票 5其他
            'other_payment' => isset($orderExtend->other_payment) ? $orderExtend->other_payment : '',        // 其他付款方式
            'pay_time_name' => isset($orderExtend->payTimeType) ? $orderExtend->payTimeType : '',            // 支付时间的名称，下一个月15日前支付完毕等

            // 品牌直达和固定位置特有的
            'settle_type' => isset($orderExtend->settle_type) ? $orderExtend->settle_type : '',              // 结算方式:1一次性支付 2按季度支付 3按月支付 4其他
            'settle_other' => isset($orderExtend->settle_other) ? $orderExtend->settle_other : '',           // 其他结算方式名称

            'dispute_type' => isset($orderExtend->dispute_type) ? $orderExtend->dispute_type : '',           // 纠纷解决方式
            'spread_brand' => isset($orderExtend->spread_brand) ? $orderExtend->spread_brand : '',           // 推广的品牌
            'party_a_email' => isset($orderExtend->party_a_email) ? $orderExtend->party_a_email : '',        // 甲方指定联系邮箱

            'journal_amount' => isset($orderExtend->journal_amount) ? $orderExtend->journal_amount : '',     // 刊例价为      元/月
            'discount' => isset($orderExtend->discount) ? $orderExtend->discount : '',                       // 折扣
            'is_bill_time' => isset($orderExtend->is_bill_time) ? $orderExtend->is_bill_time : '',           // 是否有账期

            'receipts_amount' => isset($orderExtend->receipts_amount) ? $orderExtend->receipts_amount : '',  // 猜你喜欢，搜索推广 预付费金额
            'min_amount' => isset($orderExtend->min_amount) ? $orderExtend->min_amount : '',                 // 猜你喜欢，搜索推广 最低续费金额

            // 固定位置
            'spread_product' => isset($orderExtend->spread_product) ? $orderExtend->spread_product : '',     // 推广产品

            // 是否享受买二増一
            'two_get_one' => isset($orderExtend->two_get_one) ? $orderExtend->two_get_one : '',              // 是否享受买二赠一
        );

        if ( (!empty($orderExtend->extra_premium)) && ($orderExtend->extra_premium != 0) )
        {
            $param['extra_premium'] = $orderExtend->extra_premium;       // 额外优惠新加
        }

        if ($order->order_status==KAOrderInfoModel::ORDER_APPROVED )
        {
            $urls = $order->validSpreadUrls;
        } else
        {
            $urls = $order->spreadUrls;
        }
        if (!empty($urls))
        {
            $param['party_a_url'] = join(', ', $urls);                              // 推广的URL
        }

        // 收款日期排期的处理
        if (!empty($order->receipts))
        {
            $receipts = $order->receipts;
            $receipt = array();
            foreach($receipts as $k => $obj)
            {
                $receipt[$obj->receipt_endtime] = $obj->receipt_amount;
            }

            // 键值中日期和收款需要键值一致
            ksort($receipt);
            $receipt_endtimes = array_keys($receipt);
            foreach($receipt_endtimes as $key=>$date)
            {
                list($y, $m, $d) = explode('-', $date);
                $param['receipt_year'][$key] = $y;
                $param['receipt_month'][$key] = $m;
                $param['receipt_day'][$key] = $d;
            }
            $param['receipt_amount'] = array_values($receipt);
        }

        if (!empty($order->fixedSchedules))
        {
            $schedules = $order->fixedSchedules;
            $schedule = array();
            foreach($schedules as $k => $obj)
            {
                // 只取出最后一个作为
                if (!empty($obj->adInfo))
                {
                    $adinfos = $obj->adInfo;
                    $schedule[] = rtrim(end($adinfos), '0..9');
                }
            }
            $param['position'] = join('；', array_unique($schedule));
        }

        if ($type == 'schedule')
        {
            $param['times'] = count($param['receipt_amount']);

            $schedules = $order->fixedSchedules;
            $schedule = array();
            foreach($schedules as $k => $obj)
            {
                // 只取出最后一个作为
                if (!empty($obj->adInfo))
                {
                    $adinfos = $obj->adInfo;
                    $schedule[] = array(
                        'position' => rtrim(end($adinfos), '0..9'),
                        'style' => $obj->ad_format,
                        'amount' => $obj->amount,
                        'discount' => $obj->discount,
                        'real_amount' => $obj->real_amount,
                        'schedule' => isset($obj->scheduleDates[$obj->ad_id]) ? $obj->scheduleDates[$obj->ad_id] : array(),
                    );
                }
            }

            ContractModel::schedule($schedule, $param);

        } elseif ($order->is_stop_contract == KAOrderInfoModel::IS_STOP_CONTRACT )
        {
            switch($param['type'])
            {
            case KAOrderInfoModel::ORDER_TYPE_GUESS:
                $param['agreement_type'] = 'guess';
                break;

            case KAOrderInfoModel::ORDER_TYPE_BRAND :
                $param['agreement_type'] = 'brand';
                break;

            case KAOrderInfoModel::ORDER_TYPE_FIXED :
                $param['agreement_type'] = 'fix';
                break;

            case KAOrderInfoModel::ORDER_TYPE_NAV :
                $param['agreement_type'] = 'search';
                break;
            }

            $param['end_date'] = $order->contract_end_time;                                                     // 推广结束时间
            $parentOrder = new KAOrderInfoModel($order->parent_order_id);
            $param['ori_number'] = $parentOrder->contract_num;
            $param['number'] = $param['contract_num'];

            ContractModel::stop($param, $save);
        } else
        {
            ContractModel::order($param, $save);
        }
    }


    /**
     * 订单合同
     * $param = array(
     *
     */
    public static function order($param, $save = false)
    {
        if (empty($param['contract_version']))
        {
            $param['contract_version'] = OrderNumberModel::CONTRACT_VERSION;
        }

        $savePdfName = $param['savePdfName'];
        $pdfName = $param['pdfName'];

        self::getInstance();

        $pdf = self::$contract;

        if (isset(self::$regaddrs[$param['party_b']]))
        {
            $param['party_b_address'] = self::$regaddrs[$param['party_b']];
        } else {
            $param['party_b_address'] = '';
        }

        if (!empty($param['amount']))
        {
            $param['amount_big'] = self::numToUpper($param['amount']);
        } else
        {
            $param['amount_big'] = '';
        }

        if (!empty($param['receipts_amount']))
        {
            $param['receipts_amount_big'] = self::numToUpper($param['receipts_amount']);
        } else
        {
            $param['receipts_amount_big'] = '';
        }

        $footer_right = self::getGBCode($param['contract_version'] . '版');

        // 数据处理
        // 猜你喜欢
        if ($param['type'] == KAOrderInfoModel::ORDER_TYPE_GUESS)
        {
            // 如果有账期
            if ($param['is_bill_time'])
            {
                $footer_left = self::getGBCode('按点击付费网络推广合同（猜你喜欢）');
                $tpl = 'guess_bill';

            // 是否框架
            } elseif ($param['frame_num'])
            {
                $footer_left = self::getGBCode('按点击付费网络推广合同（奇迹框架下单笔）');
                $tpl = 'guess_frame';

            // 纯单笔无账期
            } else
            {
                $footer_left = self::getGBCode('按点击付费网络推广合同（单笔）');
                $tpl = 'guess_one';
            }

        // 品牌直达
        } elseif ($param['type'] == KAOrderInfoModel::ORDER_TYPE_BRAND)
        {
            // 如果有框架协议，说明是框架下单笔
            if ($param['frame_num'])
            {
                $footer_left = self::getGBCode('网络推广合同（品牌直达）（搜索框架下单笔）');
                $tpl = 'brand_frame';
            } else
            {
                $footer_left = self::getGBCode('网络推广合同（品牌直达）（单笔）');
                $tpl = 'brand_one';
            }

        // 导航固定位置
        } elseif ($param['type'] == KAOrderInfoModel::ORDER_TYPE_FIXED)
        {
            // 固定位置只有一种
            $footer_left = self::getGBCode('网址导航推广合同');
            $tpl = 'nav_fix';

        // 搜索推广
        } elseif ($param['type'] == KAOrderInfoModel::ORDER_TYPE_NAV )
        {
            // 如果有账期
            if ($param['is_bill_time'])
            {
                $footer_left = self::getGBCode('按点击付费网络推广合同（搜索）');
                $tpl = 'search_one_bill';

            // 是否框架
            } elseif ($param['frame_num'])
            {
                $footer_left = self::getGBCode('按点击付费网络推广合同（搜索框架下单笔）');
                $tpl = 'search_frame';

            // 纯单笔无账期
            } else
            {
                $footer_left = self::getGBCode('按点击付费网络推广合同（搜索）');
                $tpl = 'search_one';
            }
        }

        // 转换字符为GBK字符，这样才可以正常在windows下显示
        self::getArrayGBCode($param);

        // 设置合同编号，显示在右上角
        $pdf->SetNumber(self::getGBCode('合同编号：' . $param['contract_num']));

        // 设置图片路径，会取出左上角的图片360head.png和背景图片360bg.jpg
        $pdf->SetPicPath(APPPATH . 'views/pdf');
        // 设置页脚左下和右下
        $pdf->SetFooter($footer_left, $footer_right);

        // 文件模板路径
        $file = APPPATH. 'views/pdf/' . $param['contract_version'] . '/' . $tpl . '.txt';

        if (!file_exists($file))
        {
            return show_error('模板文件不存在');
        }

        // 打印字符串到pdf上
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
     * 终止协议
     * $param = array(
     *      'agreement_type' => 'agency',
            'ori_number' => 'QH-KW-2013-01-04',           // 原协议编号
            'number' => 'QH-KW-2013-01-04',               // 协议编号
            'party_a' => '百度网络有限公司司公司',          // 甲方名称
            'party_a_address' => '北京西二旗百度大厦5号',   // 甲方注册地址
            'party_b' => '北京奇虎科技有限公司',            // 乙方名称
            'end_date' => '2014-01-04',                   // 终止时间
            'contract_version' => '2013-v1.0',            // 合同版本号
     */
    public static function stop($param, $save = false)
    {
        if (empty($param['contract_version']))
        {
            $param['contract_version'] = OrderNumberModel::CONTRACT_VERSION_STOP;
        }
        self::getInstance();

        $pdf = self::$contract;

        $pdfName = $param['pdfName'];
        $savePdfName = $param['savePdfName'];
        // 乙方注册地址
        if (isset(self::$regaddrs[$param['party_b']]))
        {
            $param['party_b_address'] = self::$regaddrs[$param['party_b']];
        } else {
            $param['party_b_address'] = '';
        }

        // 合同名称
        if (isset(self::$contractName[$param['agreement_type']]))
        {
            $param['agreement_name'] = self::$contractName[$param['agreement_type']];
        } else {
            $param['agreement_name'] = '';
        }

        // 合作事项
        if (isset(self::$businessName[$param['agreement_type']]))
        {
            $param['business'] = self::$businessName[$param['agreement_type']];
        } else {
            $param['business'] = '';
        }

        // 设置合同编号，显示在右上角
        $pdf->SetNumber(self::getGBCode('合同编号：' . $param['number']));

        // 设置图片路径，会取出左上角的图片360head.png和背景图片360bg.jpg
        $pdf->SetPicPath(APPPATH . 'views/pdf');
        // 设置页脚左下和右下
        $footer_left = self::getGBCode($param['contract_version'] . '版本');
        $pdf->SetFooter($footer_left, '');

        // 文件模板路径
        $file = APPPATH . 'views/pdf/' . $param['contract_version'] . '/stopagreement.txt';
        if (!file_exists($file))
        {
            return show_error('模板文件不存在' . $param['contract_version'] . '/stopagreement.txt');
        }
        // 转换字符为GBK字符，这样才可以正常在windows下显示
        self::getArrayGBCode($param);

        // 打印字符串到pdf上
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
