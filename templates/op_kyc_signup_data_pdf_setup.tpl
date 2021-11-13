<h2 class="my"><{$action.title}> 簽到表欄位設定</h2>
<form action="index.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal">

    <{$tmt_box}>
    <input type="hidden" name="op" value="kyc_signup_data_pdf_setup_save">
    <input type="hidden" name="action_id" value="<{$action.id}>">
    <div class="bar">
        <button type="submit" class="btn btn-danger" name="file" value="pdf">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> pdf 簽到表
        </button>
        <button type="submit" class="btn btn-primary" name="file" value="word">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> word 簽到表
        </button>
        <button type="submit" class="btn btn-success" name="file" value="odt">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> odt 簽到表
        </button>
    </div>
</form>