<?php
// 如「模組目錄」= signup，則「首字大寫模組目錄」= Signup
// 如「資料表名」= actions，則「模組物件」= Actions

namespace XoopsModules\Kyc_signup;

use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Tadtools\TadDataCenter;

class Kyc_signup_data
{
    //列出所有資料
    public static function index()
    {
        global $xoopsTpl;

        $all_data = self::get_all();
        $xoopsTpl->assign('all_data', $all_data);
    }

    //編輯表單
    public static function create($action_id,$id = '')
    {
     global $xoopsTpl, $xoopsUser;

        //抓取預設值
        $db_values = empty($id) ? [] : self::get($id);

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

        $action = Kyc_signup_actions::get($action_id);
        if (time() > strtotime($action['end_date'])) {
            redirect_header($_SERVER['PHP_SELF'] . "?id=$action_id", 3, "已報名截止，無法再進行報名或修改報名");
        }
        $myts = \MyTextSanitizer::getInstance();
        foreach ($action as $col_name => $col_val) {
            //過濾讀出的變數值
            if ($col_name == 'detail') {
                $col_val = $myts->displayTarea($col_val, 0, 1, 0, 1, 1);
            } else {
                $col_val = $myts->htmlSpecialChars($col_val);
            }
            $action[$col_name] = $col_val;
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

        $id = (int) $id;
        $data = self::get($id);

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

        $action = Kyc_signup_actions::get($action_id);
        foreach ($action as $col_name => $col_val) {
            //過濾讀出的變數值
            if ($col_name == 'detail') {
                $col_val = $myts->displayTarea($col_val, 0, 1, 0, 1, 1);
            } else {
                $col_val = $myts->htmlSpecialChars($col_val);
            }
            $action[$col_name] = $col_val;
        }
        $xoopsTpl->assign('action', $action);
        $now_uid = $xoopsUser ? $xoopsUser->uid() : 0;
        $xoopsTpl->assign('now_uid', $now_uid);
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
        global $xoopsDB;

        if (empty($id)) {
            return;
        }

        $sql = "delete from `" . $xoopsDB->prefix("kyc_signup_data") . "`
        where `id` = '{$id}'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    //以流水號取得某筆資料
    public static function get($id = '')
    {
        global $xoopsDB;

        if (empty($id)) {
            return;
        }

        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_data") . "`
        where `id` = '{$id}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data = $xoopsDB->fetchArray($result);
        return $data;
    }

    //取得所有資料陣列
    public static function get_all($auto_key = false)
    {
        global $xoopsDB;
        $myts = \MyTextSanitizer::getInstance();

        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_data") . "` where 1 ";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data_arr = [];
        while ($data = $xoopsDB->fetchArray($result)) {

            // $data['文字欄'] = $myts->htmlSpecialChars($data['文字欄']);
            // $data['大量文字欄'] = $myts->displayTarea($data['大量文字欄'], 0, 1, 0, 1, 1);
            // $data['HTML文字欄'] = $myts->displayTarea($data['HTML文字欄'], 1, 0, 0, 0, 0);
            // $data['數字欄'] = (int) $data['數字欄'];

            if ($_SESSION['api_mode'] or $auto_key) {
                $data_arr[] = $data;
            } else {
                $data_arr[$data['id']] = $data;
            }
        }
        return $data_arr;
    }

}
