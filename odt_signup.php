<?php
use Xmf\Request;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;
use PhpOffice\PhpWord\Shared\Converter;
use XoopsModules\Tadtools\TadDataCenter;
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
if (!$_SESSION['can_add']) {
 redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
}
$id = Request::getInt('id');
$action = Kyc_signup_actions::get($id);

require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/autoload.php';
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord->setDefaultFontName('DFKai-SB'); //設定預設字型
$phpWord->setDefaultFontSize(12); //設定預設字型大小
// $header  = $section->addHeader(); //頁首
// $footer  = $section->addFooter(); //頁尾
// $footer->addPreserveText('{PAGE} / {NUMPAGES}', $fontStyle, $paraStyle);
// 標題文字樣式設定
$TitleStyle = ['color' => '000000', 'size' => 18, 'bold' => true];
// 內文文字設定
$fontStyle = ['color' => '000000', 'size' => 14, 'bold' => false];
// 置中段落樣式設定
$paraStyle = ['align' => 'center', 'valign' => 'center'];
// 靠左段落樣式設定
$left_paraStyle = ['align' => 'left', 'valign' => 'center'];
// 靠又段落樣式設定
$right_paraStyle = ['align' => 'right', 'valign' => 'center'];
// 表格樣式設定
$tableStyle = ['borderColor' => '000000', 'borderSize' => 6, 'cellMargin' => 80];
// 橫列樣式
$rowStyle = ['cantSplit' => true, 'tblHeader' => true];
// 儲存格標題文字樣式設定
$headStyle = ['bold' => true];
// 儲存格內文文字樣式設定
$cellStyle = ['valign' => 'center'];

$phpWord->addTitleStyle(1, $TitleStyle, $paraStyle); //設定標題N樣式
//產生內容
$section = $phpWord->addSection();
$sectionStyle = $section->getStyle();
$sectionStyle->setMarginTop(Converter::cmToTwip(2.5));
$sectionStyle->setMarginLeft(Converter::cmToTwip(2.2));
$sectionStyle->setMarginRight(Converter::cmToTwip(2.2));
$phpWord->addTitleStyle($depth, $TitleStyle, $paraStyle); //設定標題N樣式
//$section->addTitle('標題文字', $depth); //新增標題

//產生內容
$title = $action['title'] . _MD_KYC_SIGNUP_SIGNIN_TABLE;
$section->addTextBreak(1);
$section->addTitle($title, 1); //新增標題
$section->addTextBreak(1);

$section->addTextBreak(1);
$section->addText(_MD_KYC_SIGNUP_ACTION_DATE . _TAD_FOR .$action['action_date'], $fontStyle, $left_paraStyle);

$TadDataCenter = new TadDataCenter('kyc_signup');
$TadDataCenter->set_col('pdf_setup_id', $id);
$pdf_setup_col = $TadDataCenter->getData('pdf_setup_col', 0);
$col_arr = explode(',', $pdf_setup_col);
$col_count = count($col_arr);
if (empty($col_count)) {
    $col_count = 1;
}

$w = 10.6 / $col_count;

$table = $section->addTable($tableStyle);
$table->addRow();
$table->addCell(Converter::cmToTwip(1.5), $cellStyle)->addText(_MD_KYC_SIGNUP_ID, $fontStyle, $paraStyle);
foreach ($col_arr as $col_name) {
    $table->addCell(Converter::cmToTwip($w), $cellStyle)->addText($col_name, $fontStyle, $paraStyle);
}
$table->addCell(Converter::cmToTwip(4.5), $cellStyle)->addText(_MD_KYC_SIGNUP_SIGNIN, $fontStyle, $paraStyle);

$signup = Kyc_signup_data::get_all($action['id'], null, true, true);
$i = 1;
foreach ($signup as $signup_data) {
    $table->addRow();
    $table->addCell(Converter::cmToTwip(1.5), $cellStyle)->addText($i, $fontStyle, $paraStyle);
    foreach ($col_arr as $col_name) {
        $table->addCell(Converter::cmToTwip($w), $cellStyle)->addText(implode('、', $signup_data['tdc'][$col_name]), $fontStyle, $paraStyle);
    }

    $table->addCell(Converter::cmToTwip(4.5), $cellStyle)->addText('', $fontStyle, $paraStyle);
    $i++;
}
// $filename  = iconv("UTF-8", "Big5", $filename);
 $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
 header('Content-Type: application/vnd.oasis.opendocument.text');
 header("Content-Disposition: attachment;filename={$title}.odt");

 header('Cache-Control: max-age=0');
 $objWriter->save('php://output');
