<?php
use Xmf\Request;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';

if (!$_SESSION['can_add']) {
    redirect_header($_SERVER['PHP_SELF'], 3, "您沒有權限使用此功能");
}

$id = Request::getInt('id');
$type = Request::getString('type');

$action = Kyc_signup_actions::get($id);
if ($action['uid'] != $xoopsUser->uid()) {
    redirect_header($_SERVER['PHP_SELF'], 3, "您沒有權限使用此功能");
}

require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel.php'; //引入 PHPExcel 物件庫
require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php'; //引入PHPExcel_IOFactory 物件庫
$objPHPExcel = new PHPExcel(); //實體化Excel
//----------內容-----------//
$title = "{$action['title']}報名名單";
$objPHPExcel->setActiveSheetIndex(0); //設定預設顯示的工作表
$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
$objActSheet->setTitle($title); //設定標題
$objPHPExcel->createSheet(); //建立新的工作表，上面那三行再來一次，編號要改

// 抓出標題資料
$head = Kyc_signup_data::get_head($action);
$row = 1;

// 抓出內容部份
//設定預設工作表中一個儲存格的外觀
$head_style = [
 'font' => [
     'bold' => true,
     'color' => ['rgb' => '000000'],
     // 'size' => 12,
     'name' => '新細明體',
 ],
 'alignment' => [
     'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
 ],
 'fill' => [
     'type' => PHPExcel_Style_Fill::FILL_SOLID,
     'color' => ['rgb' => 'cfcfcf'],
 ],
 'borders' => [
     'allborders' => [
         'style' => PHPExcel_Style_Border::BORDER_THIN,
         'color' => ['rgb' => '000000'],
     ],
 ],
];
$content_style = [
 'font' => [
     'bold' => false,
     'color' => ['rgb' => '000000'],
     // 'size' => 12,
     'name' => '新細明體',
 ],
 'alignment' => [
     'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
 ],
 // 'fill' => [
 //     'type' => PHPExcel_Style_Fill::FILL_SOLID,
 //     'color' => ['rgb' => 'ffffff'],
 // ],
 'borders' => [
     'allborders' => [
         'style' => PHPExcel_Style_Border::BORDER_THIN,
         'color' => ['rgb' => '000000'],
     ],
 ],
];
foreach ($head as $column => $value) {
 $objActSheet->setCellValueByColumnAndRow($column, $row, $value); //直欄從0開始，橫列從1開始
 $objActSheet->getStyleByColumnAndRow($column, $row)->applyFromArray($head_style);
 $len = strlen($value);
 if (!isset($_SESSION['length'][$column])) {
     $_SESSION['length'][$column] = $len;
     $objActSheet->getColumnDimensionByColumn($column)->setWidth($len);
 }
}

if ($type == 'signup') {
 $signup = Kyc_signup_data::get_all($action['id']);
 foreach ($signup as $signup_data) {
     $iteam = [];
     foreach ($signup_data['tdc'] as $user_data) {
         $iteam[] = implode('|', $user_data);
     }

     if ($signup_data['accept'] === '1') {
         $iteam[] = '錄取';
     } elseif ($signup_data['accept'] === '0') {
         $iteam[] = '未錄取';
     } else {
         $iteam[] = '尚未設定';
     }
     $iteam[] = $signup_data['signup_date'];
     $iteam[] = $signup_data['tag'];

     $row++;
     foreach ($iteam as $column => $value) {
         $objActSheet->setCellValueByColumnAndRow($column, $row, $value); //直欄從0開始，橫列從1開始
         $objActSheet->getStyleByColumnAndRow($column, $row)->applyFromArray($content_style);
         $len = strlen($value);
         if (!isset($_SESSION['length'][$column]) || $len > $_SESSION['length'][$column]) {
             $_SESSION['length'][$column] = $len;
             $objActSheet->getColumnDimensionByColumn($column)->setWidth($len);
         }
     }
 }
}



// $title = (_CHARSET === 'UTF-8') ? iconv('UTF-8', 'Big5', $title) : $title;
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename={$title}.xlsx");
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

// 避免excel下載錯誤訊息
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(1);
ob_clean();
$objWriter->setPreCalculateFormulas(false);
$objWriter->save('php://output');
unset($_SESSION['length']);
exit;
