<?php
// 如「模組目錄」= signup，則「首字大寫模組目錄」= Signup
// 如「資料表名」= actions，則「模組物件」= Actions
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;
use XoopsModules\Tadtools\TadUpFiles;
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'kyc_signup_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------變數過濾----------*/
$op = Request::getString('op');
$id = Request::getInt('id');
$action_id = Request::getInt('action_id');
$accept = Request::getInt('accept');
$files_sn = Request::getInt('files_sn');
$pdf_setup_col = Request::getString('pdf_setup_col');
$file = Request::getWord('file','pdf');
/*-----------執行動作判斷區----------*/
switch ($op) {
    // 下載檔案
    case "tufdl":
        $TadUpFiles = new TadUpFiles('kyc_signup');
        $TadUpFiles->add_file_counter($files_sn);
        exit;
    // 匯入 CSV 並預覽
    case 'kyc_signup_data_preview_csv':
        Kyc_signup_data::preview_csv($id);
        break;
    //批次匯入 CSV
    case 'kyc_signup_data_import_csv':
        Kyc_signup_data::import_csv($id);
        redirect_header("{$_SERVER['PHP_SELF']}?id=$id", 3, _MD_KYC_SIGNUP_IMPORT_SUCCESS);
        break;
    //修改報名表單(Excel)
    case 'kyc_signup_data_preview_excel':
        Kyc_signup_data::preview_excel($id);
        break;
    // 進行pdf的匯出設定
    case 'kyc_signup_data_pdf_setup':
        Kyc_signup_data::pdf_setup($id);
        break;
    //儲存pdf的匯出設定
    case 'kyc_signup_data_pdf_setup_save':
        Kyc_signup_data::pdf_setup_save($action_id, $pdf_setup_col);
        header("location: {$file}_signup.php?id=$action_id");
        exit;

    // 將 Excel 資料存入資料庫
    case 'kyc_signup_data_import_excel':
        Kyc_signup_data::import_excel($id);
        redirect_header("{$_SERVER['PHP_SELF']}?id=$id", 3, _MD_KYC_SIGNUP_IMPORT_SUCCESS);
        exit;

    //新增表單
    case 'kyc_signup_actions_create':
        Kyc_signup_actions::create();
        break;

    //新增活動資料
    case 'kyc_signup_actions_store':
        $id = Kyc_signup_actions::store();
        // header("location: {$_SERVER['PHP_SELF']}?id=$id");
        redirect_header($_SERVER['PHP_SELF'] . "?id=$id", 3, _MD_KYC_SIGNUP_CREATE_SUCCESS);
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
        redirect_header($_SERVER['PHP_SELF'] . "?id=$id", 3, _MD_KYC_SIGNUP_UPDATE_SUCCESS);
        exit;

    //刪除資料
    case 'kyc_signup_actions_destroy':
        Kyc_signup_actions::destroy($id);
        // header("location: {$_SERVER['PHP_SELF']}");
        redirect_header($_SERVER['PHP_SELF'], 3, _MD_KYC_SIGNUP_DESTROY_SUCCESS);
        exit;
    //報名表單
    case 'kyc_signup_data_create':
        Kyc_signup_data::create($action_id);
        break;
    //新增報名資料
    case 'kyc_signup_data_store':
        $id = Kyc_signup_data::store();
        Kyc_signup_data::mail($id,'store');
        redirect_header("{$_SERVER['PHP_SELF']}?op=kyc_signup_data_show&id=$id", 3, _MD_KYC_SIGNUP_APPLY_SUCCESS);
        break;
    //顯示報名表單
    case 'kyc_signup_data_show':
        Kyc_signup_data::show($id);
        break;
    //修改報名表單
    case 'kyc_signup_data_edit':
        Kyc_signup_data::create($action_id, $id);
        $op = 'kyc_signup_data_create';
        break;
    //更新報名資料
    case 'kyc_signup_data_update':
        Kyc_signup_data::update($id);
        Kyc_signup_data::mail($id,'update');
        redirect_header($_SERVER['PHP_SELF'] . "?op=kyc_signup_data_show&id=$id", 3, _MD_KYC_SIGNUP_APPLY_UPDATE_SUCCESS);
        exit;
    //刪除報名資料
    case 'kyc_signup_data_destroy':
        $uid = $_SESSION['can_add'] ? null : $xoopsUser->uid();
        $signup = Kyc_signup_data::get($id , $uid);
        Kyc_signup_data::destroy($id);
        Kyc_signup_data::mail($id,'destroy',$signup);
        redirect_header($_SERVER['PHP_SELF'] . "?id=$action_id", 3, _MD_KYC_SIGNUP_APPLY_DESTROY_SUCCESS);
        exit;
    // 複製活動
    case 'kyc_signup_actions_copy':
        $new_id = Kyc_signup_actions::copy($id);
        header("location: {$_SERVER['PHP_SELF']}?op=kyc_signup_actions_edit&id=$new_id");
        exit;

    //更改錄取狀態
    case 'kyc_signup_data_accept':
        Kyc_signup_data::accept($id, $accept);
        Kyc_signup_data::mail($id,'accept');
        redirect_header($_SERVER['PHP_SELF'] . "?id=$action_id", 3, _MD_KYC_SIGNUP_ACCEPT_SUCCESS);
        exit;
    default:
        if (empty($id)) {
            Kyc_signup_actions::index($xoopsModuleConfig['only_enable']);
            $op = 'kyc_signup_actions_index';
        } else {

            Kyc_signup_actions::show($id);
            $op = 'kyc_signup_actions_show';
        }
        break;
}

/*-----------function區--------------*/

/*-----------秀出結果區--------------*/
unset($_SESSION['api_mode']);
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu));
$xoopsTpl->assign('now_op', $op);
$xoTheme->addStylesheet(XOOPS_URL . '/modules/kyc_signup/css/module.css');
require_once XOOPS_ROOT_PATH . '/footer.php';
