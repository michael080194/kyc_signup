<h2 class="my">
    <{if $action.enable==1}>
        <i class="fa fa-check text-success" aria-hidden="true"></i>
    <{else}>
        <i class="fa fa-times text-danger" aria-hidden="true"></i>
    <{/if}>
    <{$action.title}>
    <small><i class="fa fa-calendar" aria-hidden="true"></i> 活動日期：<{$action.action_date}></small>
</h2>

<div class="alert alert-info">
    <{$action.detail}>
</div>

<h3 class="my">
    報名表
    <small>
        <i class="fa fa-calendar-check-o" aria-hidden="true"></i> 報名截止日期：<{$action.end_date}>
        <i class="fa fa-users" aria-hidden="true"></i> 報名人數上限：<{$action.number}>
    </small>
</h3>

<table class="table">
    <{foreach from=$tdc key=title item=signup name=data}>
        <tr>
            <th><{$title}></th>
            <td>
                <{foreach from=$signup key=i item=val name=signup}>
                    <div><{$val}></div>
                <{/foreach}>
            </td>
        </tr>
    <{/foreach}>
</table>

<{if $smarty.session.kyc_signup_adm || $uid==$now_uid}>
    <div class="bar">
        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_data_edit&action_id=<{$action_id}>&id=<{$id}>" class="btn btn-warning">
            <i class="fa fa-pencil" aria-hidden="true"></i> 修改報名資料
        </a>
        <a href="javascript:del_data('<{$id}>')" class="btn btn-danger">
            <i class="fa fa-times" aria-hidden="true"></i> 取消報名
        </a>
    </div>
<{/if}>