<h2 class="my">活動設定</h2>
<form action="index.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal">

    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            活動標題
        </label>
        <div class="col-sm-10">
            <input type="text" name="title" id="title" class="form-control validate[required]" value="<{$title}>" placeholder="請輸入活動標題">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            活動說明
        </label>
        <div class="col-sm-10">
          <{$editor}>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            報名截止日期
        </label>
        <div class="col-sm-10">
            <input type="text" name="end_date" id="end_date" class="form-control validate[required]" value="<{$end_date}>" placeholder="請輸入報名截止日期" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00'})">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            活動日期
        </label>
        <div class="col-sm-10">
            <input type="text" name="action_date" id="action_date" class="form-control validate[required]" value="<{$action_date}>" placeholder="請輸入活動日期" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00'})">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            報名人數
        </label>
        <div class="col-sm-10">
            <input type="number" name="number" id="number" class="form-control validate[required]" value="<{$number}>" placeholder="請輸入報名人數">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            欄位設定
        </label>
        <div class="col-sm-10">
            <textarea name="setup" id="setup" class="form-control validate[required]" placeholder="請輸入欄位設定"><{$setup}></textarea>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label col-form-label text-md-right">
            是否啟用
        </label>
        <div class="col-sm-10" style="padding-top: 8px;">
            <div class="form-check-inline radio-inline">
                <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="enable" value="1" <{if $enable=='1'}>checked<{/if}>>
                    是
                </label>
            </div>
            <div class="form-check-inline radio-inline">
                <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="enable" value="0" <{if $enable=='0'}>checked<{/if}>>
                    否
                </label>
            </div>
        </div>
    </div>

    <div class="bar">
        <{$token_form}>
        <input type="hidden" name="uid" value="<{$uid}>">
        <input type="hidden" name="id" value="<{$id}>">
        <input type="hidden" name="op" value="<{$next_op}>">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save" aria-hidden="true"></i> <{$smarty.const._TAD_SAVE}>
        </button>
    </div>
</form>