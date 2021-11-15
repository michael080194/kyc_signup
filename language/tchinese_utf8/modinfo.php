<?php
xoops_loadLanguage('modinfo_common', 'tadtools');

// xoops_version.php & 後台的 menu.php 使用
// define("_MI_XXX_ADMENU1", "主管理頁");
// define("_MI_XXX_ADMENU1_DESC", "後台主管理頁");

// xoops_version.php
define('_MI_KYC_SIGNUP_NAME', '活動報名');
define('_MI_KYC_SIGNUP_DESCRIPTION', '活動報名模組');
define('_MI_KYC_SIGNUP_AUTHOR_WEBSITE_NAME', 'kyc web site');
define('_MI_KYC_SIGNUP_TEMPLATES_INDEX', '前台共同樣板');
define('_MI_KYC_SIGNUP_TEMPLATES_ADMIN', '後台共同樣板');
define('_MI_KYC_SIGNUP_ACTION_LIST_NAME', '可報名活動一覽');
define('_MI_KYC_SIGNUP_ACTION_LIST_DESCRIPTION', '列出所有可報名的活動');
define('_MI_KYC_SIGNUP_ACTION_SIGNUP_NAME', '活動報名焦點');
define('_MI_KYC_SIGNUP_ACTION_SIGNUP_DESCRIPTION', '可選擇某一個活動讓使用者報名');
define('_MI_KYC_SIGNUP_SHOW_NUMBER', '每頁顯示活動數量');
define('_MI_KYC_SIGNUP_SHOW_NUMBER_DESC', '每頁顯示活動的數量，作為分頁依據');
define('_MI_KYC_SIGNUP_ONLY_ENABLE', '是否只顯可報名活動');
define('_MI_KYC_SIGNUP_ONLY_ENABLE_DESC', '若是只會顯示可報名活動，否則無法報名活動也會列出');

// admin\menu.php
define('_MI_KYC_SIGNUP_ACTIONS_MANAGER', '活動管理');
define('_MI_KYC_SIGNUP_PERMISSION_SETTING', '權限設定');