<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Kyc_signup\Kyc_signup_data;

require_once __DIR__ . '/header.php';

$id = Request::getInt('id');

$action = Kyc_signup_actions::get($id);

$action['signup_count'] = count(Kyc_signup_data::get_all($id));

header("Content-type: text/html");

$content = "
<h2 class='my'>
    {$action['title']}
</h2>
<div class='alert alert-info'>
    {$action['detail']}
</div>
{$action['files']}
<h4 class='my'>
    <small>
        <div><i class='fa fa-calendar' aria-hidden='true'></i> " . _MD_KYC_SIGNUP_ACTION_DATE . _TAD_FOR . "{$action['action_date']}</div>
        <div><i class='fa fa-calendar-check-o' aria-hidden='true'></i>  " . _MD_KYC_SIGNUP_END_DATE . _TAD_FOR . "{$action['end_date']}</div>
        <div>
            <i class='fa fa-users' aria-hidden='true'></i> ". _MD_KYC_SIGNUP_ACTION_DATE . _TAD_FOR . "" . ($action['signup_count']) . "/{$action['number']}
            <span data-toggle='tooltip' title='" . _MD_KYC_SIGNUP_CANDIDATES_QUOTA . "'>({$action['candidate']})</span>
        </div>
    </small>
</h4>
<div class='text-center my-3'>
    <a href='" . XOOPS_URL . "/modules/kyc_signup/index.php?op=kyc_signup_data_create&action_id={$action['id']}' class='btn btn-lg btn-info'><i class='fa fa-plus' aria-hidden='true'></i> " . _MD_KYC_SIGNUP_APPLY_NOW . "</a>
</div>
";
// echo '主要內容=' . $action['title'];
$content=Utility::html5($content, false, true, 4, true, 'container', $action['title'] , '');

// echo $content;
if (file_put_contents(XOOPS_ROOT_PATH . "/uploads/kyc_signup/{$action['id']}.html", $content)) {
    header("location: " . XOOPS_URL . "/uploads/kyc_signup/{$action['id']}.html");
}
exit;