<h2 class="my">活動列表</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>活動名稱</th>
            <th>活動日期</th>
            <th>報名截止日</th>
            <th>已報名人數</th>
            <th>功能</th>
        </tr>
    </thead>
    <tbody>
        <{foreach from=$all_data key=id item=action name=all_data}>
            <tr>
                <td>
                    <a href="index.php?id=<{$action.id}>"> <{$action.title}></a>
                </td>
                <td><{$action.action_date}></td>
                <td><{$action.end_date}></td>
                <td><{$action.signup|@count}>/<{$action.number}></td>
                <td>
                    <{if $smarty.session.kyc_signup_adm}>
                        <a href="index.php?op=kyc_signup_actions_edit&id=<{$action.id}>" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i> 編輯活動</a>
                    <{/if}>

                    <{if  $action.number > $action.signup|@count && $xoops_isuser && $action.end_date|strtotime >= $smarty.now}>
                        <a href="index.php?op=kyc_signup_data_create&action_id=<{$action.id}>" class="btn btn-sm btn-info"><i class="fa fa-plus" aria-hidden="true"></i> 立即報名</a>
                    <{else}>
                        <a href="index.php?id=<{$action.id}>" class="btn btn-success btn-sm"><i class="fa fa-file" aria-hidden="true"></i> 詳情</a>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
    </tbody>
</table>

<{if $smarty.session.kyc_signup_adm}>
    <div class="bar">
        <a href="index.php?op=kyc_signup_actions_create" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> 新增活動</a>
    </div>
<{/if}>