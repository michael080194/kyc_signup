<?php
header('Access-Control-Allow-Origin: *');//
header("Content-Type:text/html; charset=utf-8");
include "header.php";
include_once XOOPS_ROOT_PATH."/header.php";
include_once(XOOPS_ROOT_PATH."/modules/tad_form/app_up_file.php");
#強制關除錯
// ugm_module_debug_mode(0);
ob_end_clean(); //清除鍰衝區

/*-----------執行動作判斷區----------*/
include_once $GLOBALS['xoops']->path( '/modules/system/include/functions.php' );

use XoopsModules\Tadtools\Utility;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;
use XoopsModules\Tadtools\TadDataCenter;

$op = system_CleanVars($_REQUEST, 'op', '', 'string');

switch($op){
  /*---判斷動作請貼在下方---*/
  case "login": // 帳密檢查
    echo login();
    exit;
  case "get_all_action": // 取得所有活動資料陣列
    echo get_all_action();
    exit;
  case "get_single_action": // 取得單一活動資料陣列
    echo get_single_action();
    exit;
  case "get_signup_people": // 取得單一活動的已報名者名單
    echo get_signup_people();
    exit;
  case "get_signup_form": // 取得報名填寫欄位的資料
    echo get_signup_form();
    exit;
  case "op_insert": // 寫入使用者從 手機 所填寫的問卷資料
    echo insertData();
    exit;
  default:
    echo tad_web_news();
    exit;

  /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
//$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
include_once XOOPS_ROOT_PATH.'/footer.php';


/*-----------功能函數區--------------*/

################################
# 填寫表單
# 傳入 uname、pass、serial、ofsn、ssn(空：新增 、 有值：編輯)
#
#
# responseStatus：[ SUCCESS | WARNING | FAIL ]
#
# 帳號、密碼、錯誤，回傳 FAIL
#
# 所有資料的更新、刪除成功時回傳SUCCESS
# 部分資料不完整、或更新、刪除失敗時，回傳WARNING
# responseMessage：回傳錯誤原因
# responseArray：回傳各資料的執行結果

#################################
//
function op_form($ofsn = "", $ssn = "")
{
  global $xoopsDB, $xoopsModule;
  if(!$ofsn){
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = "問卷資料錯誤！！";
    $r['responseArray'] = "";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }
  #---- 過濾資料 --------------------------
  $myts = &MyTextSanitizer::getInstance();
  # ---------------------------------------------------
  #此處目前 post get 都可，正式上線時，只能 POST
  $uname = $myts->htmlSpecialChars($_REQUEST['uname']);
  $pass = $myts->htmlSpecialChars($_REQUEST['pass']);
  $serial = $myts->htmlSpecialChars($_REQUEST['serial']);
  #帳號、密碼、序號認證
  $uid=check_userRegister($uname,$pass,$serial);
  if(!$uid){
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = "帳號、密碼錯誤，序號認證失敗！！";
    $r['responseArray'] = "";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }
  # ---------------------------------------------------
  #用uid 取得 使用者資料
  $member_handler = & xoops_gethandler('member');
  $xoopsUser =& $member_handler->getUser($uid);
  #模組id
  $mid = $xoopsModule->getVar('mid');
  #判斷該使用者是否為管理者，若傳回true表示有管理權限，若不給mid表示目前所在的模組，若指定$mid為-1且傳回true表示其至少有某一模組的管理權。
  $isAdmin=$xoopsUser->isAdmin($mid);
  # ---------------------------------------------------

  $today = date("Y-m-d H:i:s", xoops_getUserTimestamp(time()));//現在時間
  $form  = get_tad_form_main($ofsn, $ssn);//取得問卷  from function.php
  #檢查權限
  if(!check_purview($ofsn,$uid)){
    #沒有權限
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = _MD_TADFORM_ONLY_MEM;
    $r['responseArray'] = "";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }
  #檢查填報日期
  #確認日期
  if (!$isAdmin) {//問卷已關閉

  }

  #取得email
  $email     = $xoopsUser->getVar('email');//email
  #取得會員名稱
  $uid_name = $xoopsUser->getVar('name');
  if (empty($uid_name)) {
    $uid_name = $xoopsUser->getVar('uname');
  }
  if (empty($uid_name)) {
    $uid_name = $xoopsUser->getVar('loginname');
  }

  $r['responseStatus'] = "SUCCESS";
  $r['responseMessage'] = "";
  $r['responseArray'] = json_encode($main, JSON_UNESCAPED_UNICODE);
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 使用者登入判斷
# 帳、密正確，返回 true 、不正確 返回 false
#
# responseStatus：[ SUCCESS | WARNING | FAIL ]
#
# 帳號、密碼、錯誤，回傳 FAIL
#
# 所有資料的更新、刪除成功時回傳SUCCESS
# 部分資料不完整、或更新、刪除失敗時，回傳WARNING
# responseMessage：回傳錯誤原因
# responseArray：回傳各資料的執行結果

#################################
function login() {//
  global $xoopsDB;
  #---- 過濾資料 --------------------------
  $myts = &MyTextSanitizer::getInstance();
  #此處目前為post都可，若要測試則可設為 REQUEST
  $uname = $myts->htmlSpecialChars($_POST['username']);
  $pass = $myts->htmlSpecialChars($_POST['password']);

  if(empty($uname) && empty($pass) ){
    $arr = [
      "userId" => "",
      "isAdmin" => ""
    ];
    $r['responseStatus'] = "GUEST";
    $r['responseMessage'] = "訪客登入！！";
    $r['responseArray'] = $arr;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }

  #檢查帳號、密碼是否正確
  $data = check_user($uname,$pass);
  // die(print_r($data ));
  if(!$data['userId']){
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = "帳號、密碼，錯誤！！";
    $r['responseArray'] = "";
  } else {
    $r['responseStatus'] = "SUCCESS";
    $r['responseMessage'] = "驗證成功！！";
    $r['responseArray'] = $data;
  }
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 取得所有活動資料陣列
#################################
function get_all_action(){
  global $xoopsDB;
  // $signup = Kyc_signup_actions::get_all(false , false, 0, $order = ",`action_date` desc");

  $auto_key = false;
  $myts = \MyTextSanitizer::getInstance();
  $userId = $myts->htmlSpecialChars($_POST['userId']);
  $isAdmin = $myts->htmlSpecialChars($_POST['isAdmin']);
  $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "` order by `enable` ,`action_date` desc";

  $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
  $data_arr = [];
  while ($data = $xoopsDB->fetchArray($result)) {
    $sqla = "";
    $sqla .= "select a.* , b.* ";
    $sqla .= "from `" . $xoopsDB->prefix("kyc_signup_data") . "` as a ";
    $sqla .= "LEFT JOIN `" . $xoopsDB->prefix("kyc_signup_data_center") . "` as b ";
    $sqla .= "ON a.id = b.col_sn ";
    $sqla .= "where a.`action_id` = '{$data['id']}' and b.data_name='tag' ";
    $resultDetail = $xoopsDB->query($sqla) or Utility::web_error($sql, __FILE__, __LINE__);
    $count = 0;
    while ($dataDetail = $xoopsDB->fetchArray($resultDetail)) {
      $count++;
    }
      $data['title'] = $myts->htmlSpecialChars($data['title']);
      $data['detail'] = strip_tags($data['detail']);
      $data['action_date'] = substr($data['action_date'], 0, -3);
      $data['end_date'] = substr($data['end_date'], 0, -3);
      $data['signup'] = Kyc_signup_data::get_all($data['id']);
      $data['signup_people'] = count($data['signup']);
      $data['signup_candidate'] = $count;
      $data_arr[] = $data;
  }

  $r = [];
  $r['responseStatus'] = "SUCCESS";
  $r['responseMessage'] = "資料抓取成功";
  $r['responseArray'] = $data_arr;
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 取得單一活動資料陣列
#################################
function get_single_action(){
  global $xoopsDB, $xoopsTpl, $xoopsUser;

  $TadDataCenter = new TadDataCenter('kyc_signup');

  $myts = \MyTextSanitizer::getInstance();
  $action_id = $myts->htmlSpecialChars($_POST['action_id']);
  $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "` where `id` = '{$action_id}'";

  $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
  $data_arr = [];
  while ($data = $xoopsDB->fetchArray($result)) {
      $data['title'] = $myts->htmlSpecialChars($data['title']);
      $data['detail'] = strip_tags($data['detail']);
      $head = $TadDataCenter->getAllColItems($data['setup']);
      $data['head'] = $head;
      $data_arr['action'] = $data;
      $signup_form = getColSetupKyc($data['setup']);
      $data_arr['signup_form'] = $signup_form;
  }


  $signup = Kyc_signup_data::get_all($action_id, null , true);
  $data_arr['signup'] = $signup;

  $r = [];
  $r['responseStatus'] = "SUCCESS";
  $r['responseMessage'] = "資料抓取成功";
  $r['responseArray'] = $data_arr;
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 取得單一活動的已報名者名單
#################################
function get_signup_people(){
  global $xoopsDB, $xoopsTpl, $xoopsUser;

  $TadDataCenter = new TadDataCenter('kyc_signup');

  $myts = \MyTextSanitizer::getInstance();
  $uid = $myts->htmlSpecialChars($_POST['uid']);
  $isAdmin = $myts->htmlSpecialChars($_POST['isAdmin']);
  $action_id = $myts->htmlSpecialChars($_POST['action_id']);


  $sql = "";
  $sql .= "(select a.id as action_id ,a.title as action_title , b.id as signup_id , b.uid as signup_uid, b.signup_date ";
  $sql .= "from `" . $xoopsDB->prefix("kyc_signup_actions") . "` as a ";
  $sql .= "LEFT JOIN `" . $xoopsDB->prefix("kyc_signup_data") . "` as b ";
  $sql .= "ON a.id = b.action_id ";
  $sql .= "where a.`id` = '{$action_id}') ";
  /*
  $sql .= "LEFT JOIN `" . $xoopsDB->prefix("kyc_signup_data_center") . "` as c ";
  $sql .= "ON b.id = c.col_sn ";
  */

  $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
  $data_arr = [];
  $arr = [];
  $arrHead = [];
  $arrDetail = [];
  while ($data = $xoopsDB->fetchArray($result)) {
    // $data['action_title'] = $myts->htmlSpecialChars($data['action_title']);
    $data['signup_date'] = substr($data['signup_date'], 0, -3);
    // $arrHead[]=$data;

    $sqla = "";
    $sqla .= "select * from `" . $xoopsDB->prefix("kyc_signup_data_center") . "`  ";
    $sqla .= "where `col_sn` = '{$data['signup_id']}' order by sort , data_sort";
    $resultDetail = $xoopsDB->query($sqla) or Utility::web_error($sql, __FILE__, __LINE__);
    $arrtemp = [];
    while ($dataDetail = $xoopsDB->fetchArray($resultDetail)) {
      if (array_key_exists($dataDetail["data_name"], $arrtemp)) {
        $arrtemp[$dataDetail["data_name"]] = $arrtemp[$dataDetail["data_name"]] . "," . $dataDetail["data_value"];
      } else {
        $arrtemp[$dataDetail["data_name"]] = $dataDetail["data_value"];
      }
    }
    // $arr[] = [ $arrHead , $arrtemp];
    if(count($arrtemp) > 0){
      $arrtemp2 = [];
      $arrtemp2[] =["title"=>"報名日期" , "val" =>$data['signup_date']];
      foreach ($arrtemp as $key => $value){
        $arrtemp2[] =["title"=>$key , "val" =>$value];
      }
      $arr[] = $arrtemp2;
    }
    // die(print_r($arr));
  }


  $r = [];
  if(count($arr) > 0){
    $r['responseStatus'] = "SUCCESS";
    $r['responseMessage'] = "資料抓取成功";
    $r['responseArray'] = $arr;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  } else {
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = "查無資料";
    $r['responseArray'] = $arr;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }
}
################################
# 取得報名填寫欄位的資料
#################################
function get_signup_form(){
  global $xoopsDB, $xoopsTpl, $xoopsUser;
  /*
    姓名*
    飲食*,radio,葷食+,素食,不用餐
    日期場次,const,2021-06-27
    參加場次*,checkbox,上午場,下午場,午夜場
    交通方式,select,自行前往,公車,火車,高鐵,飛機1
  */
  $TadDataCenter = new TadDataCenter('kyc_signup');

  $myts = \MyTextSanitizer::getInstance();
  $action_id = $myts->htmlSpecialChars($_POST['action_id']);
  $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "` where `id` = '{$action_id}'";
  $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
  $data = $xoopsDB->fetchArray($result);
  $TadDataCenter = new TadDataCenter('kyc_signup');
  $TadDataCenter->set_col('id', $action_id);
  // $signup_form = $TadDataCenter->strToForm($data['setup']);
  // $signup_form = $TadDataCenter->getColSetupKyc($data['setup']);

  $signup_form = getColSetupKyc($data['setup']);
  // die(print_r($data['setup']));
  // die(print_r($signup_form['options']));
  $r = [];
  $r['responseStatus'] = "SUCCESS";
  $r['responseMessage'] = "資料抓取成功";
  $r['responseArray'] = $signup_form;
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 檢查帳號、密碼是否正確
# 正確返回 "OK"
# 不正確返回 "FAIL"
#################################
function check_user($uname="",$pass=""){
  global $xoopsDB;
  if(!$uname or !$pass)return;
  $sql="select uid , pass
        from ".$xoopsDB->prefix("users")."
        where uname = '{$uname}'
  ";

  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'], 3, mysql_error());
  $row = $xoopsDB->fetchArray($result);

  if(password_verify($pass, $row['pass'])){
    $isAdmin = $row['rank'] == 7 ? "YES" : "NO";
    $arr = [
      "userId" => $row['uid'],
      "isAdmin" => $isAdmin
    ];
    return $arr;
  }
}
#######################################
# 取得欄位設定
#######################################
function getColSetupKyc($setup)
{
    /*
    #這是測試用
    姓名*
    飲食*,radio,葷食+,素食,不用餐
    日期場次,const,2021-06-27
    參加場次*,checkbox,上午場,下午場,午夜場
    交通方式,select,自行前往,公車,火車,高鐵,飛機1
    */
    $setup = \trim($setup);
    $datas = \explode("\r\n", $setup);
    $ret_data = [];
    foreach ($datas as $index => $item) {
        $cols = \explode(",", $item);
        $flag = 0;
        foreach ($cols as $i => $col) {
            $type = $val = '';
            $require = '';
            $label   = '';
            $default   = '';
            if (\strpos($col, '#') !== false) {
                $type = "remark";
                $label = \str_replace('#', '', $col);
            }elseif (!isset($cols[1])) {
                $type = "input";
                $label   = $col;
                if (\strpos($label, '*') !== false) {
                    $require = 1;
                    $label = \str_replace('*', '', $label);
                }
            } else {
                switch ($cols[1]) {
                    case 'const':
                        if($flag == 0){
                            $flag = 1 ;
                            $type = "const";
                            $label   = $cols[0];
                            $val   = $cols[2];
                        }
                        break;
                    case 'radio':
                        if($flag == 0){
                            $flag = 1 ;
                            $type = "radio";
                            $label= $cols[0];
                            if (\strpos($label, '*') !== false) {
                                $require = 1;
                                $label = \str_replace('*', '', $label);
                            }
                            foreach ($cols as $j => $multicol) {
                                if($j >=2){
                                    if(strpos($multicol,"+")){
                                        $default   = \str_replace('+', '', $multicol);
                                        $val .=  \str_replace('+', '', $multicol) ."," ;
                                    } else {
                                        $val .= $multicol ."," ;
                                    }
                                }
                            }
                        }
                        break;
                    case 'checkbox':
                        if($flag == 0){
                            $flag = 1 ;
                            $type = "checkbox";
                            $label= $cols[0];
                            if (\strpos($label, '*') !== false) {
                                $require = 1;
                                $label = \str_replace('*', '', $label);
                            }
                            foreach ($cols as $j => $multicol) {
                                if($j >=2){
                                    if(strpos($multicol,"+")){
                                        $default   = \str_replace('+', '', $multicol);
                                        $val .=  \str_replace('+', '', $multicol) ."," ;
                                    } else {
                                        $val .= $multicol ."," ;
                                    }
                                }
                            }
                        }
                        break;
                    case 'select':
                        if($flag == 0){
                            $flag = 1 ;
                            $type = "select";
                            $label= $cols[0];
                            if (\strpos($label, '*') !== false) {
                                $require = 1;
                                $label = \str_replace('*', '', $label);
                            }
                            foreach ($cols as $j => $multicol) {
                                if($j >=2){
                                    if(strpos($multicol,"+")){
                                        $default   = \str_replace('+', '', $multicol);
                                        $val .=  \str_replace('+', '', $multicol) ."," ;
                                    } else {
                                        $val .= $multicol ."," ;
                                    }
                                }
                            }
                        }
                        break;
                }
            }
            if($type !== ""){
              $ret_data[] = [
                  "label" => $label ,
                    "type" => $type ,
                    "val" => $val ,
                    "default" => $default ,
                    "require" => $require
                ];
            }
        }
    }

    return $ret_data;
}
#######################################
# 寫入使用者從手機所填寫的問卷資料
#######################################
function insertData(){
  global $xoopsDB;
  #---- 過濾資料 --------------------------
  $myts = &MyTextSanitizer::getInstance();
  $uname = $myts->htmlSpecialChars($_POST['uname']);
  $pass = $myts->htmlSpecialChars($_POST['pass']);
  $serial = $myts->htmlSpecialChars($_POST['serial']);
  #確認身份
  $uid = check_userRegister($uname,$pass,$serial);
  if(!$uid){
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = "認證失敗！！";
    $r['responseArray'] = "";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }

  #----------------------------------------------
  #
  $json = json_decode($_POST['jsonData']);

  // $file = XOOPS_ROOT_PATH . "/uploads/tad_form/".strtotime("now").".json";
  // $f = fopen($file, 'w'); //以寫入方式開啟文件
  // fwrite($f, print_r($json)); //將新的資料寫入到原始的文件中
  // fclose($f);


  //----主機時間 -----------------
  $fill_time=date("Y-m-d  H:i:00",strtotime("now"));
  $_POST['ssn'] = intval($_POST['ssn']);
  $op_type = $_POST['ssn'] ? "EDIT":"ADD";
  $_POST['ofsn'] = intval($_POST['ofsn']);
  $_POST['man_name']=$myts->addSlashes($_POST['man_name']);
  $_POST['email']=$myts->addSlashes($_POST['email']);
  #得到總表資料
  $form=get_tad_form_main($_POST['ofsn']);


  # -------  有上傳 ------------------------------------------------------------*/
  /*------------------------------------*/
  // #---- 檢查資料夾

  /*------------------------------------*/
  // if($form['upload_pic']=='1'){
  //   # 處理相片分類
  //   # 2017-07-21 ugm => 可以上傳，則要檢查 tad_form_col -> fun='upload' 做為類別，依順序為大類、小類
  //   #撈出問卷有「上傳」 的 csn
  //   $sql="select `csn`
  //         from ".$xoopsDB->prefix("tad_form_col")."
  //         where `ofsn` = '{$_POST['ofsn']}' and `func` = 'upload'
  //         order by sort
  //   ";//die($sql);
  //   $result = $xoopsDB->query($sql) or redirect_header(XOOPS_URL,3, mysql_error());

  //   $_POST['csn'] = 0;

  //   while($row = $xoopsDB->fetchArray($result)){
  //     $title = $myts->addSlashes($ans[$row['csn']]);//得到回答的答案
  //     $_POST['csn']=check_subkind($_POST['csn'],$title);//得到 tad_form_kind -> csn
  //   }

  //   $_POST['csn']=intval($_POST['csn']);
  // }else{
  //   $_POST['csn']=0;
  // }

  if($form['upload_pic']=='1'){
    /*  106-07-22 因客戶提出學校及班級 , 已在問卷調查表輸入了 , 所以改寫程式
    # 處理相片分類
    $_POST['csn']=intval($_POST['csn']);
    # ----  次類別 ----
    $_POST['sub_kind']=$myts->addSlashes($_POST['sub_kind']);
    if(!empty($_POST['sub_kind'])){
      $_POST['csn']=add_subkind_app($_POST['csn'],$_POST['sub_kind'],$uid);
    }
    */

    $_POST['csn'] = 0;
   foreach($json as $jsonrows){
      foreach($jsonrows as $jsonrow){
         if( $jsonrow ->func == "upload"){
           $title = $myts->addSlashes($jsonrow -> val);//得到回答的答案
           $_POST['csn']=app_check_subkind($_POST['csn'],$title,$uid);//得到 tad_form_kind -> csn
         }
      }
   }
    $_POST['csn']=intval($_POST['csn']);
  }else{
    $_POST['csn']=0;
  }
  //-----------------------
  //先存基本資料，得到ssn
  $sql = "replace into ".$xoopsDB->prefix("tad_form_fill")." (`ssn`,`ofsn`,`uid`,`man_name`,`email`,`fill_time`,`csn`)
    values
    ('{$_POST['ssn']}','{$_POST['ofsn']}','{$uid}','{$_POST['man_name']}','{$_POST['email']}','{$fill_time}','{$_POST['csn']}')";//die($sql);
  $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  $ssn=$xoopsDB->getInsertId();

  // 新增時用 ofsn 產生 x_tad_form_fill(流手序號ssn 給x_tad_form_value)  ;
  //-----------------------/
  // $wkss1 = ""; // debug 用的欄位
  foreach($json as $rows){
     foreach($rows as $row){
        $csn = $row -> csn;
        $val = $row -> val;
        $val = $myts->addSlashes($val);
        $sql = "replace into ".$xoopsDB->prefix("tad_form_value")."
            (`ssn`,`csn`,`val`)
            values
            ('{$ssn}','{$csn}','{$val}')";
        $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
        // $wkss1 .=  "csn=" . $csn . " val=" . $value  . "\n";
     }
  }
  $r=array();
  $r['responseStatus'] = "SUCCESS";
  if($op_type== "ADD") {
    $r['responseMessage'] = "新增成功";
  } else {
    $r['responseMessage'] = "更新成功";
  }
  // echo json_encode($r);
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
###############################
# 接收從手機傳來的圖片
# $ssn 產生 x_tad_form_files_center相關欄位
# x_tad_form_files_center 的 col_name = "ssn"
# x_tad_form_files_center 的 col_sn = $ssn
# 圖片位置 /uploads/tad_form/image
#################################
function upload($ssn = "") {
  global $xoopsDB;
  $myts = &MyTextSanitizer::getInstance();
  $uname = $myts->htmlSpecialChars($_POST['uname']);
  $pass = $myts->htmlSpecialChars($_POST['pass']);
  $serial = $myts->htmlSpecialChars($_POST['serial']);
  #確認身份
  $uid = check_userRegister($uname,$pass,$serial);
  if(!$uid){
    $r['responseStatus'] = "FAIL";
    $r['responseMessage'] = "認證失敗！！";
    $r['responseArray'] = "";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }

  $_POST['upload_seq'] = intval($_POST['upload_seq']);
  $_POST['ssn'] = intval($_POST['ssn']);
  //$op_type = $_POST['ssn'] ? "EDIT":"ADD";
  $_POST['ofsn'] = intval($_POST['ofsn']);
  #得到總表資料
  $form=get_tad_form_main($_POST['ofsn']);

  if($form['upload_pic']=='1'){
    /* 106-07-22 因客戶提出學校及班級 , 已在問卷調查表輸入了 , 上傳照片時 , 就不用選上傳路徑
    # 處理相片分類
    $_POST['csn']=intval($_POST['csn']);
    # ----  次類別 ----
    $_POST['sub_kind']=$myts->addSlashes($_POST['sub_kind']);
    if(!empty($_POST['sub_kind'])){
      $_POST['csn']=add_subkind_app($_POST['csn'],$_POST['sub_kind'],$uid);
    }
     $sql = "update ".$xoopsDB->prefix("tad_form_fill")." set `csn`='{$_POST['csn']}' where ofsn='{$_POST['ofsn']}' and ssn='{$_POST['ssn']}'";
    $xoopsDB->queryF($sql);
    */
    # 處理上傳的檔案
    upload_file('ssn',"ssn",$_POST['ssn'],null,null,null,true,$_POST['upload_seq']); //(form_表單名稱,col_name_資料庫,col_sn,,sort,description,是否縮圖)
    # ---- 訂閱評論 ----
    $notification_handler =& xoops_gethandler('notification');
    $notification_handler->subscribe("new_comment", $_POST['ssn'], "comment");
    # ----寄信 ---
    // tad_form_send_now($_POST['ssn']);
    # 轉至編輯資料 result.php?op=modify_result&ofsn={$ofsn}&ssn={$ssn}
    //redirect_header("index.php?op=op_form&ofsn={$ofsn}&ssn={$ssn}",3, _MD_TADFORM_SAVE_OK._MA_TADFORM_UPLOAD_P_PIC);

    $r['responseStatus'] = "SUCCESS";
    $r['responseMessage'] = "上傳成功";
    $r['responseArray'] = "";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
  }
  $r['responseStatus'] = "WARNING";
  $r['responseMessage'] = "無上傳檔案！！";
  $r['responseArray'] = "";
  return json_encode($r, JSON_UNESCAPED_UNICODE);

  /*
  這裡不是只將檔案上傳，而是還有用資表管理「tad_form_files_center」
  它會將上傳的檔名記錄並改名
  檔案位置 /uploads/tad_form/image
  檔名會照 ssn 及 sort 重新命名 如 ssn_17_1.jpg

  XOOPS_ROOT_PATH."/uploads/tad_form/image/ssn_17_1.jpg"
  這邊我需要再確認，才有辦法處理
   */

  /*
  app_upload_file('ssn',"ssn",$ssn,null,null,null,true); //(
  $upload_seq = system_CleanVars($_REQUEST, 'upload_seq', '', 'string');

  $target_dir =  XOOPS_ROOT_PATH."/uploads/tad_form/";
  $target_file = $target_dir . basename($_FILES["ssn"]["name"]);
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);


  if (file_exists($target_file)) {
      $msg_text .= $upload_seq. " 檔案已存在\n";
  }

  if ($_FILES["ssn"]["size"] > 3000000) {
      $msg_text .= $upload_seq. " 檔案超過3M\n";
  }

  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" ) {
      if ($imageFileType != "") {
         $msg_text .= $upload_seq. " 檔案格式不符(jpg.png.jpeg.gif)\n";
      }
  }

  if ($msg_text != "") { // 有錯誤

  } else {
      if (move_uploaded_file($_FILES["ssn"]["tmp_name"], $target_file)) {
      } else {
        $msg_text .= $upload_seq. " 上傳失敗\n";
      }
  }

  if ($msg_text != "") {
     $r['responseStatus'] = "FAIL";
     $r['responseMessage'] = $msg_text ; // 上傳失敗
    } else {
     $r['responseStatus'] = "SUCCESS";
     $r['responseMessage'] = "上傳成功";
  }
  echo json_encode($r);
  */

 }
 #######################################
 # 從 手機 刪除圖片
 #######################################
 function del_pic(){
   global $xoopsDB;
   $myts = &MyTextSanitizer::getInstance();
   # files_sn 欲刪除之圖片序號
   # 請用 files_sn 去 刪除 tad_form_files_center 的記錄($files_sn = files_sn)
   $_POST['files_sn'] = intval($_POST['files_sn']);
   $uname = $myts->htmlSpecialChars($_REQUEST['uname']);
   $pass = $myts->htmlSpecialChars($_REQUEST['pass']);
   $serial = $myts->htmlSpecialChars($_REQUEST['serial']);

   del_files($_POST['files_sn']);

   $r['responseStatus'] = "SUCCESS";
   $r['responseMessage'] = "刪除成功";
   echo json_encode($r);
 }
###############################################################################
#  抓取 tad_web_news 最新消息
#        發布日期           最新消息                                人氣                  所屬網頁
#  app=> $new['NewsDate']   @$new['CateName']  @$new['NewsTitle']   $new['NewsCounter'] @$new['WebName']
#
###############################################################################
function tad_web_news() {
  global $xoopsDB;

  $myts = MyTextSanitizer::getInstance();

  $sql = "select a.NewsID,a.CateID,a.WebID,a.NewsDate,c.CateName,a.NewsTitle,a.NewsCounter,b.WebTitle
           from " . $xoopsDB->prefix("tad_web_news") . "  as a
           left join ".$xoopsDB->prefix("tad_web")."      as b on a.WebID = b.WebID
           left join ".$xoopsDB->prefix("tad_web_cate")." as c on a.CateID = c.CateID
           where `NewsEnable`='1'
           order by NewsDate desc
           limit 3";//die($sql);
  $result = $xoopsDB->query($sql) or web_error($sql);
  $news = "";
  while($new=$xoopsDB->fetchArray($result)){
    $new['NewsID']= intval($new['NewsID']);
    $new['CateID']= intval($new['CateID']);
    $new['WebID']= intval($new['WebID']);
    $new['NewsDate']= date("Y-m-d",strtotime($myts->htmlSpecialChars($new['NewsDate'])));//發布日期

    #最新消息-類別  link => modules/tad_web/news.php?WebID=8&CateID=55
    $new['CateName']= $myts->htmlSpecialChars($new['CateName']);

    #最新消息-標題 link => modules/tad_web/news.php?WebID=8&NewsID=95
    $new['NewsTitle']= $myts->htmlSpecialChars($new['NewsTitle']);

    $new['NewsCounter']= intval($new['NewsCounter']);//人氣

    #所屬網頁 link => modules/tad_web/index.php?WebID=8
    $new['WebTitle']= $myts->htmlSpecialChars($new['WebTitle']);

    $news[] = json_encode($new, JSON_UNESCAPED_UNICODE) ;
  }
  if($news){
    $r['responseStatus'] = "SUCCESS";
    $r['responseArray'] = json_encode($news, JSON_UNESCAPED_UNICODE);

  }else{
    $r['responseStatus'] = "WARNING";
    $r['responseMessage'] = "目前沒有新聞！！";
    $r['responseArray'] = "";
  }
  return json_encode($r, JSON_UNESCAPED_UNICODE);
}
 ###############################################################################
 #  抓取 tad_news 最新消息
 ###############################################################################
 function get_news() {
   global $xoopsDB;

   $myts = MyTextSanitizer::getInstance();

   $sql = "select a.news_title,a.news_content,b.file_name
           from " . $xoopsDB->prefix("tad_news") . " as a
           left join  " . $xoopsDB->prefix("tadnews_files_center") . " as b on a.nsn=b.col_sn
           where a.always_top='1' and a.enable='1' order by always_top_date";//die($sql);

   $result = $xoopsDB->query($sql) or web_error($sql);
   $news    = "";

   while($data=$xoopsDB->fetchArray($result)){
     $data['file_name']= $myts->htmlSpecialChars($data['file_name']);
     $data['news_title']= $myts->htmlSpecialChars($data['news_title']);
     $data['news_content']= $data['news_content'];
     $news[] = json_encode($data, JSON_UNESCAPED_UNICODE) ;
   }

   $r['responseStatus'] = "SUCCESS";
   $r['responseArray'] = json_encode($news, JSON_UNESCAPED_UNICODE);
   return json_encode($r, JSON_UNESCAPED_UNICODE);
 }
  ###############################################################################
  #  新增下層類別並回傳  csn
  ###############################################################################
  function app_add_subkind($ofsn,$title,$uid){
    global $xoopsDB,$xoopsUser;
    // $uid=$xoopsUser->getVar('uid');
    $post_date=userTimeToServerTime(strtotime("now"));
    $sql = "select max(`sort`) from ".$xoopsDB->prefix("tad_form_kind")." where `ofsn`='{$ofsn}'";
    $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

    list($sort)=$xoopsDB->fetchRow($result);
    ++$sort;
    //新增
    $sql = "insert into ".$xoopsDB->prefix("tad_form_kind")."(`ofsn`,`title`,`enable`,`sort`,`post_date`,`uid`) values ('{$ofsn}','{$title}','1','{$sort}','{$post_date}','{$uid}')";
    $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
    //取得最後新增資料的流水編號(新增)
    return $xoopsDB->getInsertId();
  }


  ###############################################################################
  #  檢查有無 csn -> title
  #  若無，則建立 回傳 csn
  #  若有，則回傳 csn
  ###############################################################################
  function app_check_subkind($ofsn,$title,$uid){
    global $xoopsDB;
    $sql="select csn
          from ".$xoopsDB->prefix("tad_form_kind")."
          where `ofsn`='{$ofsn}' and `title`='{$title}'
    ";
    $result = $xoopsDB->query($sql) or redirect_header(XOOPS_URL,3, mysql_error());
    $row=$xoopsDB->fetchArray($result);
    if(!$row['csn']){
      $row['csn'] = app_add_subkind($ofsn,$title,$uid);
    }
    return $row['csn'];
  }
  ############㕔############################################################
 #  產生訊息檔,以供 debug
 ########################################################################
 function genMsgFile($fileName="msg",$fileType="txt",$msgText="") {
  // genMsgFile("msg0","txt","uname=".$uname );
  // genMsgFile("op_home","json",$json);
  global $xoopsDB;
  $file = XOOPS_ROOT_PATH."/uploads/tad_form/".$fileName.strtotime("now").".".$fileType;
  $f = fopen($file, 'w'); //以寫入方式開啟文件
  fwrite($f, $msgText); //將新的資料寫入到原始的文件中
  fclose($f);
 }