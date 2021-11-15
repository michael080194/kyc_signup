<?php
header('Access-Control-Allow-Origin: *');//
header("Content-Type:text/html; charset=utf-8");
use Xmf\Request;
use XoopsModules\Kyc_signup\Kyc_signup_api;

require_once dirname(dirname(__DIR__)) . '/mainfile.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');

$uname = Request::getString('username');
$pass = Request::getString('password');

$userId = Request::getString('userId');
$isAdmin = Request::getString('isAdmin');
$token = Request::getString('token');
$action_id = Request::getInt('action_id');
$signupFormJsonData = json_decode(Request::getString('jsonData')); // 接收手機所填寫的報名資料

$api = new Kyc_signup_api($token);

switch ($op) {
    case 'kyc_signup_login_check': // 使用者登入,帳號及密碼檢查,登錄成功回傳token
        echo $api->kyc_signup_login_check($uname,$pass);
        break;
    case 'kyc_signup_actions_get_all': // 取得所有活動資料陣列
        echo $api->kyc_signup_actions_get_all($userId , $isAdmin , $token);
        break;
    case "kyc_signup_action_get": // 取得單一活動所有資料(目前此 Api 暫時沒有用到)
        echo $api->kyc_signup_action_get($action_id);
        break;
    case "kyc_signup_data_get_people": // 取得單一活動的已報名者名單
        echo $api->kyc_signup_data_get_people($userId , $isAdmin , $action_id);
        break;
    case "kyc_signup_form": // 取得報名填寫欄位的資料(目前此 Api 暫時沒有用到)
        echo $api->kyc_signup_form($action_id);
        break;
    case "kyc_signup_data_insert": // 寫入使用者從手機所填寫的報名資料
        echo $api->kyc_signup_data_insert($userId , $action_id , $signupFormJsonData);
        break;
    default:
        echo $api->user();
        break;
}
