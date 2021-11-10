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
  case "insertData": // 寫入使用者從手機所填寫的報名資料
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
# 寫入使用者從手機所填寫的報名資料
#######################################
function insertData(){
  global $xoopsDB;
  #---- 過濾資料 --------------------------
  $myts = &MyTextSanitizer::getInstance();
  $userId = $myts->htmlSpecialChars($_POST['userId']);
  $action_id = $myts->htmlSpecialChars($_POST['action_id']);
  $json = json_decode($_POST['jsonData']);

  foreach($json as $jsonrows){
     $arr = json_decode(json_encode($jsonrows),true);
     foreach($arr as $key => $val){
       echo($key . "==" . $val . "\n");
     }
  }

  //----主機時間 -----------------
  $fill_time=date("Y-m-d  H:i:00",strtotime("now"));
  $_POST['ssn'] = intval($_POST['ssn']);
  $op_type = $_POST['ssn'] ? "EDIT":"ADD";
  $_POST['ofsn'] = intval($_POST['ofsn']);
  $_POST['man_name']=$myts->addSlashes($_POST['man_name']);
  $_POST['email']=$myts->addSlashes($_POST['email']);


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