<?php
use XoopsModules\Tadtools\Utility;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}

use XoopsModules\Kyc_signup\Update;
if (!class_exists('XoopsModules\Kyc_signup\Update')) {
    require dirname(__DIR__) . '/preloads/autoloader.php';
}

// 更新前
function xoops_module_pre_update_kyc_signup(XoopsModule $module, $old_version)
{
    // 有上傳功能才需要
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup");
    // 若有用到CKEditor編輯器才需要
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup/file");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup/image");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup/image/.thumbs");

    $gperm_handler = xoops_getHandler('groupperm');
    $groupid = Update::mk_group(_MD_KYC_SIGNUP_ADMIN);
    if (!$gperm_handler->checkRight($module->dirname(), 1, $groupid, $module->mid())) {
        $perm_handler = xoops_getHandler('groupperm');
        $perm = $perm_handler->create();
        $perm->setVar('gperm_groupid', $groupid);
        $perm->setVar('gperm_itemid', 1);
        $perm->setVar('gperm_name', $module->dirname()); //一般為模組目錄名稱
        $perm->setVar('gperm_modid', $module->mid());
        $perm_handler->insert($perm);
    }
    return true;
}

// 更新後
function xoops_module_update_kyc_signup(XoopsModule $module, $old_version)
{
    global $xoopsDB;

    // 新增候補欄位
    if (Update::chk_candidate()) {
        Update::go_candidate();
    }

    return true;
}