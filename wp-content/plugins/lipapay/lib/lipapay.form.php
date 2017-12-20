<form action="<?php echo  $data['url']; ?>" method="post" class="form-horizontal" id="lipapay_form">
    <div class="box-body">

        <div class="fields-group">
            <input name="version" id="version" type="hidden" value="1.3" >
            <input name="merchantId" id="merchantId"  type="hidden" value="<?php echo  $data['merchantId']; ?>" >
            <input name="signType" id="signType" type="hidden" value="<?php echo  $data['signType']; ?>" >
            <input name="sign" id="sign" type="hidden" value="<?php echo  $data['sign']; ?>" >
            <input name="notifyUrl" id="notifyUrl" type="hidden" value="<?php echo  $data['notifyUrl']; ?>" >
            <input name="returnUrl" id="returnUrl" type="hidden" value="<?php echo  $data['returnUrl']; ?>" >
            <input name="merchantOrderNo" id="merchantOrderNo" type="hidden" value="<?php echo  $data['merchantOrderNo']; ?>" >
            <input name="buyerId" id="buyerId" type="hidden" value="<?php echo  $data['buyerId']; ?>" >
            <input name="amount" id="amount" type="hidden" value="<?php echo  $data['amount']; ?>" >
            <input name="goodsName" id="goodsName" type="hidden" value="<?php echo  $data['goodsName']; ?>" >
            <input name="goodsType" id="goodsType" type="hidden" value="<?php echo  $data['goodsType']; ?>" >
            <input name="expirationTime" id="expirationTime" type="hidden" value="<?php echo  $data['expirationTime']; ?>" >
            <input name="sourceType" id="sourceType" type="hidden" value="<?php echo  $data['sourceType']; ?>" >
            <input name="currency" id="currency" type="hidden" value="<?php echo  $data['currency']; ?>" ><div class="box-footer">

            </div>
            

        </div>

    </div>

</form>

<script type="text/javascript">
    setTimeout("lipapay_form.submit();",1000);
</script>
