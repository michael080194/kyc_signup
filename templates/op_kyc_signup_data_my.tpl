<h2 class="my">我的報名紀錄</h2>

<table class="table" data-toggle="table" data-pagination="true" data-search="true" data-mobile-responsive="true">
    <thead>
        <tr>
            <th data-sortable="true">活動名稱</th>
            <th data-sortable="true">活動日期</th>
            <th data-sortable="true">報名日期</th>
            <th data-sortable="true">錄取狀況</th>
        </tr>
    </thead>
    <tbody>
        <{foreach from=$my_signup item=signup_data}>
            <tr>
                <td>
                    <a href="index.php?id=<{$signup_data.action_id}>">
                        <{$signup_data.action.title}>
                    </a>
                </td>
                <td><{$signup_data.action.action_date}></td>
                <td><{$signup_data.signup_date}></td>
                <td>
                    <{if $signup_data.accept === '1'}>
                        <div class="text-primary">錄取</div>
                    <{elseif $signup_data.accept === '0'}>
                        <div class="text-muted">未錄取</div>
                    <{else}>
                    <div class="text-warning">尚未公佈</div>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
    </tbody>
</table>