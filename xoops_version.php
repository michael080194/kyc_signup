<?php
$modversion = [];

//---模組基本資訊---//
$modversion['name'] = '活動報名';
$modversion['version'] = 1.00;
$modversion['description'] = '模組說明';
$modversion['author'] = '作者姓名';
$modversion['credits'] = '相關有功人員';
$modversion['help'] = 'page=help';
$modversion['license'] = 'GNU GPL 2.0';
$modversion['license_url'] = 'www.gnu.org/licenses/gpl-2.0.html/';
$modversion['image'] = 'images/logo.png';
$modversion['dirname'] = basename(dirname(__FILE__));

//---模組狀態資訊---//
$modversion['release_date'] = '年/月/日';
$modversion['module_website_url'] = 'http://模組官網網址';
$modversion['module_website_name'] = '模組官網名稱';
$modversion['module_status'] = 'release';
$modversion['author_website_url'] = 'http://作者網站網址';
$modversion['author_website_name'] = '作者網站名稱';
$modversion['min_php'] = 5.4;
$modversion['min_xoops'] = '2.5';

//---paypal資訊---//
$modversion['paypal'][] = [
    'business' => '作者@的Email',
    'item_name' => 'Donation : 贊助對象名稱',
    'amount' => 0,
    'currency_code' => 'USD',
];

//---後台使用系統選單---//
$modversion['system_menu'] = 1;

//---模組資料表架構---//
// $modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
// $modversion['tables'] = [];

//---後台管理介面設定---//
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

//---前台主選單設定---//
$modversion['hasMain'] = 1;
// $modversion['sub'][] = ['name' => '子選項文字', 'url' => '子選項連結位址'];

//---模組自動功能---//
// $modversion['onInstall'] = "include/onInstall.php";
// $modversion['onUpdate'] = "include/onUpdate.php";
// $modversion['onUninstall'] = "include/onUninstall.php";

//---樣板設定---//
$modversion['templates'][] = ['file' => '模組目錄_admin.tpl', 'description' => '後台共同樣板'];
$modversion['templates'][] = ['file' => '模組目錄_index.tpl', 'description' => '前台共同樣板'];

//---搜尋---//
// $modversion['hasSearch'] = 1;
// $modversion['search'] = ['file' => 'include/search.php', 'func' => '搜尋函數名稱'];

//---區塊設定---//
// $modversion['blocks'][] = [
//     'file' => '區塊檔.php',
//     'name' => '區塊名稱',
//     'description' => '區塊說明',
//     'show_func' => '執行區塊函數名稱',
//     'template' => '區塊樣板.tpl',
//     'edit_func' => '編輯區塊函數名稱',
//     'options' => '設定值1|設定值2',
// ];

//---偏好設定---//
// $modversion['config'][] = [
//     'name' => '偏好設定名稱（英文）',
//     'title' => '_MI_偏好設定標題_常數',
//     'description' => '_MI_偏好設定說明_常數',
//     'formtype' => '輸入表單類型',
//     'valuetype' => '輸入值類型',
//     'default' => '預設值',
// ];

//---評論---//
// $modversion['hasComments'] = 1;
// $modversion['comments'][] = ['pageName' => '單一頁面.php', 'itemName' => '主編號'];

//---通知---//
// $modversion['hasNotification'] = 1;
