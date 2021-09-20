<?php
// 如「模組目錄」= signup，則「首字大寫模組目錄」= Signup
// 如「資料表名」= actions，則「模組物件」= Actions
use Xmf\Request;
use XoopsModules\首字大寫模組目錄\模組物件;

/*-----------引入檔案區--------------*/
$GLOBALS['xoopsOption']['template_main'] = '模組目錄_admin.tpl';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';
$_SESSION['模組目錄_adm'] = true;

/*-----------變數過濾----------*/
$op = Request::getString('op');
$id = Request::getInt('id');

/*-----------執行動作判斷區----------*/
switch ($op) {

    //新增表單
    case '資料表名_create':
        模組物件::create();
        break;

    //新增資料
    case '資料表名_store':
        $id = 模組物件::store();
        header("location: {$_SERVER['PHP_SELF']}?id=$id");
        exit;

    //修改用表單
    case '資料表名_edit':
        模組物件::create($id);
        $op = '資料表名_create';
        break;

    //更新資料
    case '資料表名_update':
        模組物件::update($id);
        header("location: {$_SERVER['PHP_SELF']}?id=$id");
        exit;

    //刪除資料
    case '資料表名_destroy':
        模組物件::destroy($id);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    default:
        if (empty($id)) {
            模組物件::index();
            $op = '資料表名_index';
        } else {
            模組物件::show($id);
            $op = '資料表名_show';
        }
        break;
}

/*-----------功能函數區----------*/

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
$xoTheme->addStylesheet('/modules/tadtools/css/font-awesome/css/font-awesome.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/xoops_adm4.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/模組目錄/css/module.css');
require_once __DIR__ . '/footer.php';
