<h2 class="my">活動列表</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th nowrap class="c"><{$smarty.const._MD_KYC_SIGNUP_TITLE}></th>
            <th nowrap class="c"><{$smarty.const._MD_KYC_SIGNUP_ACTION_DATE}></th>
            <th nowrap class="c"><{$smarty.const._MD_KYC_SIGNUP_END_DATE_COL}></th>
            <th nowrap class="c"><{$smarty.const._MD_KYC_SIGNUP_NUMBER_OF_APPLIED}></th>
            <th nowrap class="c"><{$smarty.const._TAD_FUNCTION}></th>
        </tr>
    </thead>
    <tbody>
        <{foreach from=$all_data key=id item=action name=all_data}>
            <tr>
                <td>
                <{if $action.enable && ($action.number + $action.candidate) > $action.signup_count && $action.end_date|strtotime >= $smarty.now}>
                    <i class="fa fa-check text-success" data-toggle="tooltip" title="<{$smarty.const._MD_KYC_SIGNUP_IN_PROGRESS}>" aria-hidden="true"></i>
                <{else}>
                    <i class="fa fa-times text-danger"  data-toggle="tooltip" title="<{$smarty.const._MB_TAD_SIGNUP_CANT_APPLY}>" aria-hidden="true"></i>
                <{/if}>
                <a href="<{$xoops_url}>/modules/kyc_signup/index.php?id=<{$action.id}>"><{$action.title}></a>


                </td>
                <td><{$action.action_date}></td>
                <td><{$action.end_date}></td>
                <td>
                    <{$action.signup_count}>/<{$action.number}>
                    <{if $action.candidate}><span data-toggle="tooltip" title="<{$smarty.const._MD_KYC_SIGNUP_CANDIDATES_QUOTA}>">(<{$action.candidate}>)</span><{/if}>
                </td>
                <td>
                    <{if $smarty.session.can_add && ($action.uid==$now_uid || $smarty.session.kyc_signup_adm)}>
                        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_actions_edit&id=<{$action.id}>" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i><{$smarty.const._EDIT}></a>
                        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_actions_copy&id=<{$action.id}>" class="btn btn-info btn-sm"><i class="fa fa-copy" aria-hidden="true"></i><{$smarty.const._CLONE}></a>
                    <{/if}>

                    <{if  $action.enable && ($action.number + $action.candidate) > $action.signup_count && $xoops_isuser && $action.end_date|strtotime >= $smarty.now}>
                        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_data_create&action_id=<{$action.id}>" class="btn btn-sm btn-info"><i class="fa fa-plus" aria-hidden="true"></i><{$smarty.const._MD_KYC_SIGNUP_APPLY_NOW}></a>
                    <{else}>
                        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?id=<{$action.id}>" class="btn btn-success btn-sm"><i class="fa fa-file" aria-hidden="true"></i><{$smarty.const._MORE}></a>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
    </tbody>
</table>

<{$bar}>

<{if $smarty.session.can_add}>
    <div class="bar">
        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_actions_create" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._MD_KYC_SIGNUP_ADD_ACTION}></a>
    </div>
<{/if}>