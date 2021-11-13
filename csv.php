<?php
use Xmf\Request;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;

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

$csv = [];

$head = Kyc_signup_data::get_head($action);

$csv[] = implode(',', $head);

if ($type == 'signup') {
    $signup = Kyc_signup_data::get_all($action['id']);
    // Utility::dd($signup);
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

        $csv[] = implode(',', $iteam);
    }
}

$content = implode("\n", $csv);
$content = mb_convert_encoding($content, 'Big5');

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename= {$action['title']}報名名單.csv");
echo $content;
exit;