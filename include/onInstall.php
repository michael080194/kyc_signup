<?php
use XoopsModules\Tadtools\Utility;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}

// 安裝前
function xoops_module_pre_install_kyc_signup(XoopsModule $module)
{
}

// 安裝後
function xoops_module_install_kyc_signup(XoopsModule $module)
{
    // 有上傳功能才需要
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup");
    // 若有用到CKEditor編輯器才需要
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup/file");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup/image");
    Utility::mk_dir(XOOPS_ROOT_PATH . "/uploads/kyc_signup/image/.thumbs");

    $groupid = Update::mk_group("活動報名管理");
    $perm_handler = xoops_getHandler('groupperm');
    $perm = $perm_handler->create();
    $perm->setVar('gperm_groupid', $groupid);
    $perm->setVar('gperm_itemid', 1);
    $perm->setVar('gperm_name', $module->dirname()); //一般為模組目錄名稱
    $perm->setVar('gperm_modid', $module->mid());
    $perm_handler->insert($perm);

    return true;
}