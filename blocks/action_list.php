<?php
use XoopsModules\Kyc_signup\Kyc_signup_actions;
use XoopsModules\Tadtools\Utility;
// 可報名活動一覽
function action_list($options)
{
    $block = Kyc_signup_actions::get_all(true, false, $options[0], $options[1]);
    return $block;
}

// 可報名活動一覽的編輯函式
function action_list_edit($options)
{
    $form = "
    <ol class='my-form'>
        <li class='my-row'>
            <lable class='my-label'>" . _MB_KYC_SIGNUP_SHOW_ACTIONS_NUMBER. "</lable>
            <div class='my-content'>
                <input type='text' class='my-input' name='options[0]' value='{$options[0]}' size=6>
            </div>
        </li>
        <li class='my-row'>
            <lable class='my-label'>" . _MB_KYC_SIGNUP_ORDER_BY. "</lable>
            <div class='my-content'>
                <select name='options[1]' class='my-input'>
                    <option value=', `action_date` desc' " . Utility::chk($options[1], ', `action_date` desc', '1', "selected") . ">" . _MB_KYC_SIGNUP_ORDER_BY_ACTION_DATE. "</option>
                    <option value=', `action_date`' " . Utility::chk($options[1], ', `action_date`', '', "selected") . ">" . _MB_KYC_SIGNUP_ORDER_BY_ACTION_DATE_DESC. "</option>
                    <option value=', `end_date` desc' " . Utility::chk($options[1], ', `end_date` desc', '', "selected") . ">" . _MB_KYC_SIGNUP_ORDER_BY_END_DATE. "</option>
                    <option value=', `end_date`' " . Utility::chk($options[1], ', `end_date`', '', "selected") . ">" . _MB_KYC_SIGNUP_ORDER_BY_END_DATE_DESC. "</option>
                </select>
            </div>
        </li>
    </ol>
    ";
    return $form;
}