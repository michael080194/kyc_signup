<h2 class="my">匯入「<{$action.title}>」報名資料預覽</h2>
<form action="index.php" method="post" id="myForm">
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <{foreach from=$head item=title}>
                    <th><{$title}></th>
                <{/foreach}>
            </tr>
        </thead>
        <tbody>
            <{foreach from=$preview_data key=i item=data name=preview_data}>
                <{if $smarty.foreach.preview_data.iteration > 1}>
                    <tr>
                        <{foreach from=$data key=j item=val}>
                            <{assign var=title value=$head.$j}>
                            <{assign var=input_type value=$type.$j}>
                            <{if $title!=''}>
                            <td>
                                <{if $input_type=="checkbox"}>
                                <{assign var=val_arr value='|'|explode:$val}>
                                    <{foreach from=$val_arr item=val}>
                                        <div class="form-check-inline checkbox-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="tdc[<{$i}>][<{$title}>][]" value="<{$val}>" checked>
                                                <{$val}>
                                            </label>
                                        </div>
                                    <{/foreach}>
                                <{else}>
                                    <input type="text" name="tdc[<{$i}>][<{$title}>]" value="<{$val}>" class="form-control form-control-sm">
                                <{/if}>
                            </td>
                            <{/if}>
                        <{/foreach}>
                    </tr>
                <{/if}>
            <{/foreach}>
        </tbody>
    </table>

    <{$token_form}>
    <input type="hidden" name="id" value="<{$action.id}>">
    <input type="hidden" name="op" value="kyc_signup_data_import_csv">
    <div class="bar">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save" aria-hidden="true"></i> 匯入CSV資料
        </button>
    </div>
</form>