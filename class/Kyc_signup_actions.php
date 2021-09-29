<?php
// 如「模組目錄」= signup，則「首字大寫模組目錄」= Signup
// 如「資料表名」= actions，則「模組物件」= Actions

namespace XoopsModules\Kyc_signup;

use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\My97DatePicker;

class Kyc_signup_actions
{
    //列出所有資料
    public static function index()
    {
        global $xoopsTpl;

        $all_data = self::get_all();
        $xoopsTpl->assign('all_data', $all_data);
    }

    //編輯表單
    public static function create($id = '')
    {
        global $xoopsTpl, $xoopsUser;
        if (!$_SESSION['kyc_signup_adm']) {
            redirect_header($_SERVER['PHP_SELF'], 3, "非管理員，無法執行此動作");
        }
        //抓取預設值
        $db_values = empty($id) ? [] : self::get($id);
        $db_values['number'] = empty($id) ? 50 : $db_values['number'];
        $db_values['enable'] = empty($id) ? 1 : $db_values['enable'];

        foreach ($db_values as $col_name => $col_val) {
            $$col_name = $col_val;
            $xoopsTpl->assign($col_name, $col_val);
        }

        $op = empty($id) ? "kyc_signup_actions_store" : "kyc_signup_actions_update";
        $xoopsTpl->assign('next_op', $op);

        //套用formValidator驗證機制
        $formValidator = new FormValidator("#myForm", true);
        $formValidator->render();

        //加入Token安全機制
        include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
        $token = new \XoopsFormHiddenToken();
        $token_form = $token->render();
        $xoopsTpl->assign("token_form", $token_form);
        $uid = $xoopsUser ? $xoopsUser->uid() : 0;
        $xoopsTpl->assign("uid", $uid);
        My97DatePicker::render();
    }

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

        $sql = "insert into `" . $xoopsDB->prefix("kyc_signup_actions") . "` (
        `欄位1`,
        `欄位2`,
        `欄位3`
        ) values(
        '{$欄位1值}',
        '{$欄位2值}',
        '{$欄位3值}'
        )";
        $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        //取得最後新增資料的流水編號
        $id = $xoopsDB->getInsertId();
        return $id;
    }

    //以流水號秀出某筆資料內容
    public static function show($id = '')
    {
        global $xoopsDB, $xoopsTpl;

        if (empty($id)) {
            return;
        }

        $id = (int) $id;
        $data = self::get($id);

        $myts = \MyTextSanitizer::getInstance();
        foreach ($data as $col_name => $col_val) {
            $col_val = $myts->htmlSpecialChars($col_val);

            //過濾讀出的變數值 displayTarea($text, $html=0, $smiley=1, $xcode=1, $image=1, $br=1);
            // $data['大量文字欄'] = $myts->displayTarea($data['大量文字欄'], 0, 1, 0, 1, 1);
            // $data['HTML文字欄'] = $myts->displayTarea($data['HTML文字欄'], 1, 0, 0, 0, 0);

            $xoopsTpl->assign($col_name, $col_val);
        }
    }

    //更新某一筆資料
    public static function update($id = '')
    {
        global $xoopsDB;

        //XOOPS表單安全檢查
        Utility::xoops_security_check();

        $myts = \MyTextSanitizer::getInstance();

        foreach ($_POST as $var_name => $var_val) {
            $$var_name = $myts->addSlashes($var_val);
        }

        $sql = "update `" . $xoopsDB->prefix("kyc_signup_actions") . "` set
        `欄位1` = '{$欄位1值}',
        `欄位2` = '{$欄位2值}',
        `欄位3` = '{$欄位3值}'
        where `id` = '$id'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        return $id;
    }

    //刪除某筆資料資料
    public static function destroy($id = '')
    {
        global $xoopsDB;

        if (empty($id)) {
            return;
        }

        $sql = "delete from `" . $xoopsDB->prefix("kyc_signup_actions") . "`
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

        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "`
        where `id` = '{$id}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data = $xoopsDB->fetchArray($result);
        return $data;
    }

    //取得所有資料陣列
    public static function get_all($only_enable=true , $auto_key = false)
    {
        global $xoopsDB;
        $myts = \MyTextSanitizer::getInstance();
        $and_enable = $only_enable ? "and `enable` = '1' and `action_date` >= now()" : '';

        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "` where 1 $and_enable";
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
