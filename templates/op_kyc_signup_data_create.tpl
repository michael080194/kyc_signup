<h2 class="my">
 <{if $action.enable}>
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

<form action="index.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal">
    <div class="alert alert-success">
        <{$signup_form}>
    </div>
 <{$token_form}>
 <input type="hidden" name="op" value="<{$next_op}>">
 <input type="hidden" name="id" value="<{$id}>">
 <input type="hidden" name="action_id" value="<{$action.id}>">
 <input type="hidden" name="uid" value="<{$uid}>">
 <div class="bar">
     <button type="submit" class="btn btn-primary">
         <i class="fa fa-save" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}>
     </button>
 </div>
</form>

<{if $smarty.session.can_add}>
    <div class="bar">
        <a href="<{$xoops_url}>/modules/kyc_signup/index.php?op=kyc_signup_actions_edit&id=<{$action.id}>" class="btn btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i> 編輯活動</a>
    </div>
<{/if}>
