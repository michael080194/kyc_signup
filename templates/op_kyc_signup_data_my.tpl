<h2 class="my"><{$smarty.const._MD_KYC_SIGNUP_MY_RECORD}></h2>

<table class="table" data-toggle="table" data-pagination="true" data-search="true" data-mobile-responsive="true">
    <thead>
        <tr>
            <th data-sortable="true"><{$smarty.const._MD_KYC_SIGNUP_TITLE}></th>
            <th data-sortable="true"><{$smarty.const._MD_KYC_SIGNUP_ACTION_DATE}></th>
            <th data-sortable="true"><{$smarty.const._MD_KYC_SIGNUP_APPLY_DATE}></th>
            <th data-sortable="true"><{$smarty.const._MD_KYC_SIGNUP_ACCEPT_STATUS}></th>
        </tr>
    </thead>
    <tbody>
        <{foreach from=$my_signup item=signup_data}>
            <tr>
                <td>
                    <a href="<{$xoops_url}>/modules/kyc_signup/index.php?id=<{$signup_data.action_id}>">
                        <{$signup_data.action.title}>
                    </a>
                </td>
                <td><{$signup_data.action.action_date}></td>
                <td><{$signup_data.signup_date}></td>
                <td>
                    <{if $signup_data.accept === '1'}>
                        <div class="text-primary"><{$smarty.const._MD_KYC_SIGNUP_ACCEPT}></div>
                    <{elseif $signup_data.accept === '0'}>
                        <div class="text-muted"><{$smarty.const._MD_KYC_SIGNUP_NOT_ACCEPT}></div>
                    <{else}>
                    <div class="text-warning"><{$smarty.const._MD_KYC_SIGNUP_ANNOUNCEMENT_NOT_YET}></div>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
    </tbody>
</table>