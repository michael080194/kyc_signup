<?php
namespace XoopsModules\Kyc_signup;
use Xmf\Request;
use XoopsModules\Tadtools\SimpleRest;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;
use XoopsModules\Tadtools\TadDataCenter;

require dirname(dirname(dirname(__DIR__))) . '/mainfile.php';

class Kyc_signup_api extends SimpleRest
{
    public $uid = '';
    public $user = [];
    public $groups = [];
    private $token = '';

    public function __construct($token = '')
    {
        $this->token = $token;
        if (!isset($_SESSION['api_mode'])) {
            $_SESSION['api_mode'] = true;
        }

        if ($this->token) {
            $User = $this->getXoopsSUser($this->token);
            $this->uid = (int) $User['uid'];
            $this->groups = $User['groups'];
            $this->user = $User['user'];

            //判斷是否對該模組有管理權限 $_SESSION['kyc_signup_adm']
            if (!isset($this->user['kyc_signup_adm'])) {
                $this->user['kyc_signup_adm'] = $_SESSION['kyc_signup_adm'] = ($this->uid) ? $this->isAdmin('kyc_signup') : false;
            }

            // 判斷有無開設活動的權限
            if (!isset($this->user['can_add'])) {
                $this->user['can_add'] = $_SESSION['can_add'] = $this->powerChk('kyc_signup', 1);
            }

        }
    }

    // 傳回目前使用者資訊
    public function user()
    {
        $data = ['uid' => (int) $this->uid, 'groups' => $this->groups, 'user' => $this->user];
        return $this->encodeJson($data);
    }

    // 轉成 json
    private function encodeJson($responseData)
    {
        if (empty($responseData)) {
            $statusCode = 404;
            $responseData = array('error' => _TAD_EMPTY);
        } else {
            $statusCode = 200;
        }
        $this->setHttpHeaders($statusCode);

        // $jsonResponse = json_encode($responseData, 256);
        // return $jsonResponse;
        return json_encode($responseData, JSON_UNESCAPED_UNICODE);
    }

    // 使用者登入,帳號及密碼檢查,登錄成功回傳token
    public function kyc_signup_login_check($uname="",$pass="")
    {
        global $xoopsDB;
        if(empty($uname) && empty($pass) ){
            $arr = [
            "userId" => "",
            "isAdmin" => "",
            "token" => ""
            ];
            $r['responseStatus'] = "GUEST";
            $r['responseMessage'] = "訪客登入！！";
            $r['responseArray'] = $arr;
            return $this->encodeJson($r);
        }

        $sql="select *
                from ".$xoopsDB->prefix("users")."
                where uname = '{$uname}'
        ";

        $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'], 3, mysql_error());
        $row = $xoopsDB->fetchArray($result);
        // die(print_r($row));
        if(password_verify($pass, $row['pass'])){
            $isAdmin = $row['rank'] == 7 ? "YES" : "NO";
            $arr = [
            "userId" => $row['uid'],
            "isAdmin" => $isAdmin,
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOjEsImV4cCI6MTYzOTQzMzUzNH0.PcjDcQ0DPtugslnuab0OHUBkws-MGO9XsJCn8FDWChU",
            ];
            $r['responseStatus'] = "SUCCESS";
            $r['responseMessage'] = "驗證成功！！";
            $r['responseArray'] = $arr;
        } else {
            $r['responseStatus'] = "FAIL";
            $r['responseMessage'] = "帳號、密碼，錯誤！！";
            $r['responseArray'] = "";
        }
        return $this->encodeJson($r);
    }

    // 取得所有活動資料陣列
    public function kyc_signup_actions_get_all($userId , $isAdmin , $token)
    {
        global $xoopsDB;
        // $signup = Kyc_signup_actions::get_all(false , false, 0, $order = ",`action_date` desc");

        $auto_key = false;
        $myts = \MyTextSanitizer::getInstance();
        // $userId = $myts->htmlSpecialChars($_POST['userId']);
        // $isAdmin = $myts->htmlSpecialChars($_POST['isAdmin']);

        // $userId = Request::getString('userId');
        // $isAdmin = Request::getString('isAdmin');

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
        // return json_encode($r, JSON_UNESCAPED_UNICODE);
        return $this->encodeJson($r);
    }

    // 取得單一活動所有資料(目前此 Api 暫時沒有用到)
    public function kyc_signup_action_get($action_id)
    {
        global $xoopsDB, $xoopsTpl, $xoopsUser;

        $TadDataCenter = new TadDataCenter('kyc_signup');

        $myts = \MyTextSanitizer::getInstance();
        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "` where `id` = '{$action_id}'";

        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data_arr = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $data['title'] = $myts->htmlSpecialChars($data['title']);
            $data['detail'] = strip_tags($data['detail']);
            $head = $TadDataCenter->getAllColItems($data['setup']);
            $data['head'] = $head;
            $data_arr['action'] = $data;
            $signup_form = $this->getColSetupKyc($data['setup']);
            $data_arr['signup_form'] = $signup_form;
        }


        $signup = Kyc_signup_data::get_all($action_id, null , true);
        $data_arr['signup'] = $signup;

        $r = [];
        $r['responseStatus'] = "SUCCESS";
        $r['responseMessage'] = "資料抓取成功";
        $r['responseArray'] = $data_arr;
        return $this->encodeJson($r);
    }

    // 取得單一活動的已報名者名單
    public function kyc_signup_data_get_people($userId , $isAdmin , $action_id)
    {
        global $xoopsDB, $xoopsTpl, $xoopsUser;
        $TadDataCenter = new TadDataCenter('kyc_signup');
        $myts = \MyTextSanitizer::getInstance();

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
          $kycnote = "";
          while ($dataDetail = $xoopsDB->fetchArray($resultDetail)) {
            // $data['signup_uid'] // 報名者的 user_id
            // echo $dataDetail["data_name"]."\n";
            // substr_replace($dataDetail["data_value"],"O",3,3) ;
            if ($dataDetail["data_name"] == "tag") {
               $kycnote = "(候補)";
            } else {
              if ($dataDetail["data_name"] == "姓名") {
                if ($userId == $data['signup_uid'] || $isAdmin == "YES") {
                }else{
                  $dataDetail["data_value"] = substr_replace($dataDetail["data_value"],"O",3,3) ;
                }
                $dataDetail["data_value"] = $dataDetail["data_value"].$kycnote;
                // echo "login id=" . $uid . " signup_uid=" .$data['signup_uid'] . "  isAdmin=" . $isAdmin . " name:" . $dataDetail["data_value"] . "\n";
              }else{
                if ($userId == $data['signup_uid'] || $isAdmin == "YES") {

                } else {
                  $dataDetail["data_value"] = "****";
                }
              }
              if (array_key_exists($dataDetail["data_name"], $arrtemp)) {
                $arrtemp[$dataDetail["data_name"]] = $arrtemp[$dataDetail["data_name"]] . "," . $dataDetail["data_value"];
              } else {
                $arrtemp[$dataDetail["data_name"]] = $dataDetail["data_value"];
              }
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
        return $this->encodeJson($r);
    }

    // 取得報名填寫欄位的資料(目前此 Api 暫時沒有用到)
    public function kyc_signup_form($action_id){
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
        $sql = "select * from `" . $xoopsDB->prefix("kyc_signup_actions") . "` where `id` = '{$action_id}'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $data = $xoopsDB->fetchArray($result);
        $TadDataCenter = new TadDataCenter('kyc_signup');
        $TadDataCenter->set_col('id', $action_id);

        $signup_form = $this->getColSetupKyc($data['setup']);
        $r = [];
        $r['responseStatus'] = "SUCCESS";
        $r['responseMessage'] = "資料抓取成功";
        $r['responseArray'] = $signup_form;
        return $this->encodeJson($r);
    }

    // 寫入使用者從手機所填寫的報名資料
    public function kyc_signup_data_insert($userId , $action_id , $signupFormJsonData){
        global $xoopsDB, $xoopsTpl, $xoopsUser;
        #---- 過濾資料 --------------------------
        // $myts = &MyTextSanitizer::getInstance();
        $sql = 'select mid from ' . $xoopsDB->prefix('modules') . " where dirname='kyc_signup'";
        $result = $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        list($module_id) = $xoopsDB->fetchRow($result);

        $sql = "insert into `" . $xoopsDB->prefix("kyc_signup_data") . "` (
            `action_id`,
            `uid`,
            `signup_date`
            ) values(
                '{$action_id}',
                '{$userId}',
                now()
                )";
                $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
                $col_sn = $xoopsDB->getInsertId(); // 的  id (要存到 kyc_signup_data_center 的 col_sn)


          $sort = 0;
          foreach($signupFormJsonData as $jsonrows){
           $arr = json_decode(json_encode($jsonrows),true);
           $col_name = "id";
           $data_sort = 0;
           foreach($arr as $key => $val){
             if ($key == "參加場次") {
                $val = substr($val, 0, -1);
                $val_arr = explode(",",$val);
                foreach($val_arr as $item){
                  $col_id = $module_id . "-" . $col_name . "-" . $col_sn . "-" . $key . "" . $data_sort;
                  $this->insertDataFun($module_id,$col_name,$col_sn,$key,$item,$data_sort,$col_id,$sort);
                  $data_sort ++;
                }
             } else {
               $col_id = $module_id . "-" . $col_name . "-" . $col_sn . "-" . $key . "" . $data_sort;
               $this->insertDataFun($module_id,$col_name,$col_sn,$key,$val,$data_sort,$col_id,$sort);
             }
             $sort++;
             $data_sort = 0;
          }
        }

        $r = [];
        $r['responseStatus'] = "SUCCESS";
        $r['responseMessage'] = "報名完成";
        $r['responseArray'] = "";
        return $this->encodeJson($r);
    }
    #######################################
    # 寫入使用者從手機所填寫的報名資料
    #######################################
    function insertDataFun($module_id,$col_name,$col_sn,$key,$val,$data_sort,$col_id,$sort){
        global $xoopsDB;
        $sql = "insert into `" . $xoopsDB->prefix("kyc_signup_data_center") . "`
        (`mid` , `col_name` , `col_sn` , `data_name` , `data_value` , `data_sort`, `col_id` , `sort`, `update_time`)
        values('{$module_id}' , '{$col_name}' , '{$col_sn}' , '{$key}' , '{$val}' , '{$data_sort}' , '{$col_id}' , '{$sort}' , now())";

        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

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

    // 我的報名歷史資料
    public function kyc_signup_my_record($userId)
    {
        global $xoopsDB, $xoopsUser;
        $sql = "";
        $sql .= "select a.* , b.* ";
        $sql .= "from `" . $xoopsDB->prefix("kyc_signup_data") . "` as a ";
        $sql .= "LEFT JOIN `" . $xoopsDB->prefix("kyc_signup_actions") . "` as b ";
        $sql .= "ON a.action_id = b.id ";
        $sql .= "where a.`uid` = '{$userId}' ";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $my_signup = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            if ($data['accept'] === '1') {
                $data['accept_name'] = '錄取';
            } elseif ($data['accept'] === '0') {
                $data['accept_name'] = '未錄取';
            } else {
                $data['accept_name'] = '尚未設定';
            }
            $data['action_date'] = substr($data['action_date'], 0, -3);
            $data['signup_date'] = substr($data['signup_date'], 0, -3);
            $my_signup[] = $data;
        }

        $r = [];
        $r['responseStatus'] = "SUCCESS";
        $r['responseMessage'] = "資料抓取成功";
        $r['responseArray'] = $my_signup;
        return $this->encodeJson($r);
    }
}
