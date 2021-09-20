<?php
// 如「模組目錄」= signup，則「首字大寫模組目錄」= Signup
// 如「資料表名」= actions，則「模組物件」= Actions

namespace XoopsModules\首字大寫模組目錄;

class Update
{
    // 進行某些檢查
    public static function chk_1()
    {
        global $xoopsDB;
        // $sql = 'SELECT count(*) FROM ' . $xoopsDB->prefix('資料表名') . ' ';
        // $result = $xoopsDB->query($sql);
        // if (empty($result)) {
        //     return true;
        // }

        return false;
    }

    // 執行某些調整
    public static function go_1()
    {
        global $xoopsDB;
        // $sql = 'ALTER TABLE ' . $xoopsDB->prefix('資料表名') . '';
        // $xoopsDB->queryF($sql) or redirect_header(XOOPS_URL . '/modules/system/admin.php?fct=modulesadmin', 30, $xoopsDB->error());
    }
}
