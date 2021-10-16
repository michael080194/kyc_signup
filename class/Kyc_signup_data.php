<?php
// 如「模組目錄」= signup，則「首字大寫模組目錄」= Signup
// 如「資料表名」= actions，則「模組物件」= Actions

namespace XoopsModules\Kyc_signup;

use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Tadtools\TadDataCenter;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\BootstrapTable;

class Kyc_signup_data
{
    //列出所有資料
    public static function index($action_id)
    {
        global $xoopsTpl;

        $all_data = self::get_all($action_id);
        $xoopsTpl->assign('all_data', $all_data);
    }

    //編輯表單
    public static function create($action_id,$id = '')
    {
     global $xoopsTpl, $xoopsUser;

     $uid = $_SESSION['kyc_signup_adm'] ? null : $xoopsUser->uid();
        //抓取預設值
        $db_values = empty($id) ? [] : self::get($id);
        if ($id and empty($db_values)) {
            redirect_header($_SERVER['PHP_SELF'] . "?id={$action_id}", 3, "查無報名無資料，無法修改");
        }
        foreach ($db_values as $col_name => $col_val) {
            $$col_name = $col_val;
            $xoopsTpl->assign($col_name, $col_val);
        }

        $op = empty($id) ? "kyc_signup_data_store" : "kyc_signup_data_update";
        $xoopsTpl->assign('next_op', $op);

        //套用formValidator驗證機制
        $formValidator = new FormValidator("#myForm", true);
        $formValidator->render();

        //加入Token安全機制
        include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
        $token = new \XoopsFormHiddenToken();
        $token_form = $token->render();
        $xoopsTpl->assign("token_form", $token_form);

        $action = Kyc_signup_actions::get($action_id , true);
        $action['signup'] = Kyc_signup_data::get_all($action_id);
        if (time() > strtotime($action['end_date'])) {
            redirect_header($_SERVER['PHP_SELF'] . "?id=$action_id", 3, "已報名截止，無法再進行報名或修改報名");
        } elseif (count($action['signup']) >= $action['number']) {
            redirect_header($_SERVER['PHP_SELF'] . "?id=$action_id", 3, "人數已滿，無法再進行報名");
        }

        $xoopsTpl->assign('action', $action);

        $uid = $xoopsUser ? $xoopsUser->uid() : 0;
        $xoopsTpl->assign('uid', $uid);

        $TadDataCenter = new TadDataCenter('kyc_signup');
        $TadDataCenter->set_col('id', $id);
        $signup_form = $TadDataCenter->strToForm($action['setup']);
        $xoopsTpl->assign('signup_form', $signup_form);
    }

    //新增資料
//新增資料
public static function store()
{
    global $xoopsDB;

    //XOOPS表單安全檢查
    Utility::xoops_security_check();

    $myts = \MyTextSanitizer::getInstance();

    foreach ($_POST as $var_name => $var_val) {
        $$var_name = $myts->addSlashes($var_val);
    }
    $action_id = (int) $action_id;
    $uid = (int) $uid;

    $sql = "insert into `" . $xoopsDB->prefix("kyc_signup_data") . "` (
    `action_id`,
    `uid`,
    `signup_date`
    ) values(
    '{$action_id}',
    '{$uid}',
    now()
    )";
    $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    // 取得最後新增資料的流水編號
    $id = $xoopsDB->getInsertId();

    // 儲存報名資訊
    $TadDataCenter = new TadDataCenter('kyc_signup');
    $TadDataCenter->set_col('id', $id);
    $TadDataCenter->saveData();
    return $id;
}

    //以流水號秀出某筆資料內容
    public static function show($id = '')
    {
        global $xoopsDB, $xoopsTpl, $xoopsUser;

        if (empty($id)) {
            return;
        }
        $uid = $_SESSION['kyc_signup_adm'] ? null : $xoopsUser->uid();
        $id = (int) $id;
        $data = self::get($id, $uid);

        if ($id and empty($data)) {
            redirect_header($_SERVER['PHP_SELF'] . "?id={$action_id}", 3, "查無報名無資料，無法修改");
        }

        $myts = \MyTextSanitizer::getInstance();

        foreach ($data as $col_name => $col_val) {
            $col_val = $myts->htmlSpecialChars($col_val);
            $xoopsTpl->assign($col_name, $col_val);
            $$col_name = $col_val;
        }

        $TadDataCenter = new TadDataCenter('kyc_signup');
        $TadDataCenter->set_col('id', $id);
        $tdc = $TadDataCenter->getData();
        $xoopsTpl->assign('tdc', $tdc);

        $action = Kyc_signup_actions::get($action_id,true);
        $xoopsTpl->assign('action', $action);
        $now_uid = $xoopsUser ? $xoopsUser->uid() : 0;
        $xoopsTpl->assign('now_uid', $now_uid);
        $SweetAlert = new SweetAlert();
        $SweetAlert->render("del_data", "index.php?op=kyc_signup_data_destroy&action_id={$action_id}&id=", 'id');
    }

    //更新某一筆資料
    public static function update($id = '')
    {
        global $xoopsDB, $xoopsUser;

        //XOOPS表單安全檢查
        Utility::xoops_security_check();

        $myts = \MyTextSanitizer::getInstance();

        foreach ($_POST as $var_name => $var_val) {
            $$var_name = $myts->addSlashes($var_val);
        }
        $action_id = (int) $action_id;
        $uid = (int) $uid;

        $now_uid = $xoopsUser ? $xoopsUser->uid() : 0;

        $sql = "update `" . $xoopsDB->prefix("kyc_signup_data") . "` set
        `signup_date` = now()
        where `id` = '$id' and `uid` = '$now_uid'";
        if ($xoopsDB->queryF($sql)) {
            $TadDataCenter = new TadDataCenter('kyc_signup');
            $TadDataCenter->set_col('id', $id);
            $TadDataCenter->saveData();
        } else {
            Utility::web_error($sql, __FILE__, __LINE__);
        }

        return $id;
    }

    //刪除某筆資料資料
    public static function destroy($id = '')
    {
        global $xoopsDB, $xoopsUser;

        if (empty($id)) {
            return;
        }

        $now_uid = $xoopsUser ? $xoopsUser->uid() : 0;

        $sql = "delete from `" . $xoopsDB->prefix("kyc_signup_data") . "`
        where `id` = '{$id}' and `uid`='$now_uid'";
        if ($xoopsDB->queryF($sql)) {
            $TadDataCenter = new TadDataCenter('kyc_signup');
            $TadDataCenter->set_col('id', $id);
            $TadDataCenter->delData();
        } else {
            Utility::web_error($sql, __FILE__, __LINE__);
        }
    }
    //以流水號取得某筆資料
    public static function get($id = '', $uid = '')
    {
        global $xoopsDB;

        if (empty($id)) {
            return;
        }
        $and_uid = $uid ? "and `uid`='$uid'" : '';
        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_data") . "`
        where `id` = '{$id}' $and_uid";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data = $xoopsDB->fetchArray($result);
        return $data;
    }

    //取得所有資料陣列
    public static function get_all($action_id = '', $uid = '', $auto_key = false)
    {
        global $xoopsDB, $xoopsUser;
        $myts = \MyTextSanitizer::getInstance();

        if ($action_id) {
            $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_data") . "` where `action_id`='$action_id' order by `signup_date`";
        } else {
            if (!$_SESSION['kyc_signup_adm'] or !$uid) {
                $uid = $xoopsUser ? $xoopsUser->uid() : 0;
            }
            $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_data") . "` where `uid`='$uid' order by `signup_date`";
        }

        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data_arr = [];
        $TadDataCenter = new TadDataCenter('kyc_signup');
        while ($data = $xoopsDB->fetchArray($result)) {
            $TadDataCenter->set_col('id', $data['id']);
            $data['tdc'] = $TadDataCenter->getData();
            $data['action'] = Kyc_signup_actions::get($data['action_id'], true);
            if ($_SESSION['api_mode'] or $auto_key) {
                $data_arr[] = $data;
            } else {
                $data_arr[$data['id']] = $data;
            }
        }
        return $data_arr;
    }
    // 查詢某人的報名紀錄
    public static function my($uid)
    {
        global $xoopsTpl, $xoopsUser;
        $my_signup = self::get_all(null, $uid);
        $xoopsTpl->assign('my_signup', $my_signup);
        BootstrapTable::render();
    }

    // 更改錄取狀態
    public static function accept($id, $accept)
    {
        global $xoopsDB;

        if (!$_SESSION['kyc_signup_adm']) {
            redirect_header($_SERVER['PHP_SELF'], 3, "您沒有權限使用此功能");
        }

        $id = (int) $id;
        $accept = (int) $accept;

        $sql = "update `" . $xoopsDB->prefix("kyc_signup_data") . "` set
        `accept` = '$accept'
        where `id` = '$id'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //立即寄出
    public static function send($title = "無標題", $content = "無內容", $email = "")
    {
        global $xoopsUser;
        if (empty($email)) {
            $email = $xoopsUser->email();
        }
        $xoopsMailer = xoops_getMailer();
        $xoopsMailer->multimailer->ContentType = "text/html";
        $xoopsMailer->addHeaders("MIME-Version: 1.0");
        $header = '';
        return $xoopsMailer->sendMail($email, $title, $content, $header);
    }

}
