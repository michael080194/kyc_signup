<ul class="list-group">
    <{foreach from=$block item=action}>
        <li class="list-group-item">
            <span class="badge badge-info"><{$action.action_date|substr:0:-3}></span>
            <small>
            <{$smarty.const._MB_KYC_SIGNUP_QUOTA}><{$action.number}>
                <{if $action.candidate}><span data-toggle="tooltip" title="<{$smarty.const._MB_KYC_SIGNUP_CANDIDATES_QUOTA}>">(<{$action.candidate}>)</span><{/if}>
                <{$smarty.const._MB_KYC_SIGNUP_PEOPLE}><{$smarty.const._MB_KYC_SIGNUP_APPLIED}><{$action.signup_count}><{$smarty.const._MB_KYC_SIGNUP_PEOPLE}>
            </small>
            <div>
                <{if $action.enable && ($action.number + $action.candidate) > $action.signup_count && $action.end_date|strtotime >= $smarty.now}>
                    <i class="fa fa-check text-success" data-toggle="tooltip" title="<{$smarty.const._MB_KYC_SIGNUP_IN_PROGRESS}>" aria-hidden="true"></i>
                <{else}>
                    <i class="fa fa-times text-danger" data-toggle="tooltip" title="<{$smarty.const._MB_KYC_SIGNUP_CANT_APPLY}>" aria-hidden="true"></i>
                <{/if}>
                <a href="<{$xoops_url}>/modules/kyc_signup/index.php?id=<{$action.id}>"><{$action.title}></a>
            </div>
        </li>
    <{/foreach}>
</ul>