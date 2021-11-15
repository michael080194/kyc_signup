<h2 class="my"><{$action.title}> <{$smarty.const._MD_KYC_SIGNUP_SETUP_SIGNIN_TABLE}></h2>
<form action="index.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal">

    <{$tmt_box}>
    <input type="hidden" name="op" value="kyc_signup_data_pdf_setup_save">
    <input type="hidden" name="action_id" value="<{$action.id}>">
    <div class="bar">
        <button type="submit" class="btn btn-danger" name="file" value="pdf">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> pdf <{$smarty.const._MD_KYC_SIGNUP_SIGNIN_TABLE}>
        </button>
        <button type="submit" class="btn btn-primary" name="file" value="word">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> word <{$smarty.const._MD_KYC_SIGNUP_SIGNIN_TABLE}>
        </button>
        <button type="submit" class="btn btn-success" name="file" value="odt">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> odt <{$smarty.const._MD_KYC_SIGNUP_SIGNIN_TABLE}>
        </button>
    </div>
</form>