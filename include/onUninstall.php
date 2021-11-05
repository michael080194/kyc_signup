<?php
// 反安裝前
function xoops_module_pre_uninstall_kyc_signup(XoopsModule $module)
{
}

// 反安裝後
function xoops_module_uninstall_kyc_signup(XoopsModule $module)
{
    global $xoopsDB;
    $date = date("Ymd");
    rename(XOOPS_ROOT_PATH . "/uploads/kyc_signup", XOOPS_ROOT_PATH . "/uploads/tad_signup_bak_{$date}");

    return true;
}