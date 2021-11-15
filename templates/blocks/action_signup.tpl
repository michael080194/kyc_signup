<h2 class="my">
    <{if $block.enable && ($block.number + $block.candidate) > $block.signup_count && $block.end_date|strtotime >= $smarty.now}>
        <i class="fa fa-check text-success" aria-hidden="true"></i>
    <{else}>
        <i class="fa fa-times text-danger" aria-hidden="true"></i>
    <{/if}>
    <{$block.title}>
</h2>

<div class="alert alert-info">
    <{$block.detail}>
</div>

<h4 class="my">
    <small>
        <div><i class="fa fa-calendar" aria-hidden="true"></i> <{$smarty.const._MB_KYC_SIGNUP_ACTION_DATE}><{$smarty.const._TAD_FOR}><{$block.action_date|substr:0:-3}></div>
        <div><i class="fa fa-calendar-check-o" aria-hidden="true"></i> <{$smarty.const._MB_KYC_SIGNUP_END_DATE}><{$smarty.const._TAD_FOR}><{$block.end_date|substr:0:-3}></div>
        <div>
            <i class="fa fa-users" aria-hidden="true"></i> <{$smarty.const._MB_KYC_SIGNUP_STATUS}><{$smarty.const._TAD_FOR}><{$block.signup_count}>/<{$block.number}>
            <{if $block.candidate}><span data-toggle="tooltip" title="<{$smarty.const._MB_KYC_SIGNUP_CANDIDATES_QUOTA}>">(<{$block.candidate}>)</span><{/if}>
        </div>
    </small>
</h4>

<div class="text-center my-3">
    <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_data_create&action_id=<{$block.id}>" class="btn btn-lg btn-info <{if !($block.enable && ($block.number + $block.candidate) > $block.signup_count && $xoops_isuser && $block.end_date|strtotime >= $smarty.now)}>disabled<{/if}>"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._MB_KYC_SIGNUP_APPLY_NOW}></a>
</div>