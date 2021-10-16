<?php
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';

include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';
//權限項目陣列（編號超級重要！設定後，以後切勿隨便亂改。）
$item_list = array(
    '1' => "建立報名活動",
    // '2' => "權限二",
);
$mid = $xoopsModule->mid();
$perm_name = $xoopsModule->dirname();
$formi = new XoopsGroupPermForm('活動報名細部權限設定', $mid, $perm_name, '請勾選欲開放給群組使用的權限：<br>');
foreach ($item_list as $item_id => $item_name) {
    $formi->addItem($item_id, $item_name);
}
echo $formi->render();

require_once __DIR__ . '/footer.php';
