<?php
use Xmf\Request;
use XoopsModules\Kyc_signup\Kyc_signup_actions;

/*-----------引入檔案區--------------*/
$GLOBALS['xoopsOption']['template_main'] = 'kyc_signup_admin.tpl';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';
$_SESSION['kyc_signup_adm'] = true;
$_SESSION['can_add'] = true;

/*-----------變數過濾----------*/
$op = Request::getString('op');
$id = Request::getInt('id');

/*-----------執行動作判斷區----------*/
switch ($op) {

    //新增活動表單
    case 'kyc_signup_actions_create':
        Kyc_signup_actions::create();
        break;

    //新增活動資料
    case 'kyc_signup_actions_store':
        $id = Kyc_signup_actions::store();
        // header("location: {$_SERVER['PHP_SELF']}?id=$id");
        redirect_header($_SERVER['PHP_SELF'] . "?id=$id", 3,  _MA_KYC_SIGNUP_CREATE_SUCCESS);
        exit;

    //修改用表單
    case 'kyc_signup_actions_edit':
        Kyc_signup_actions::create($id);
        $op = 'kyc_signup_actions_create';
        break;

    //更新資料
    case 'kyc_signup_actions_update':
        Kyc_signup_actions::update($id);
        // header("location: {$_SERVER['PHP_SELF']}?id=$id");
        redirect_header($_SERVER['PHP_SELF'] . "?id=$id", 3, _MA_KYC_SIGNUP_UPDATE_SUCCESS);
        exit;

    //刪除資料
    case 'kyc_signup_actions_destroy':
        Kyc_signup_actions::destroy($id);
        // header("location: {$_SERVER['PHP_SELF']}");
        redirect_header($_SERVER['PHP_SELF'], 3, _MA_KYC_SIGNUP_DESTROY_SUCCESS);
        exit;

    default:
        if (empty($id)) {
            Kyc_signup_actions::index(false);
            $op = 'kyc_signup_actions_index';
        } else {
            Kyc_signup_actions::show($id);
            $op = 'kyc_signup_actions_show';
        }
        break;
}

/*-----------功能函數區----------*/

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
$xoTheme->addStylesheet('/modules/tadtools/css/font-awesome/css/font-awesome.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/xoops_adm4.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/kyc_signup/css/module.css');
require_once __DIR__ . '/footer.php';
