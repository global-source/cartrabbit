<form method="post" action="https://www.sandbox.paypal.com/cgi-bin/webscr">
    <!--USER INFO-->
    <input type="hidden" value="<?php echo $data['shipping']['address']['fname'] ?>" name="first_name">
    <input type="hidden" value="<?php echo $data['shipping']['address']['lname'] ?>" name="last_name">
    <input type="hidden" value="<?php echo $data['shipping']['address']['email'] ?>" name="email">

    <!--SHIPPING ADDRESS PROVIDED-->
    <input type="hidden" value="<?php echo $data['shipping']['address']['address1'] ?>" name="address1">
    <input type="hidden" value="<?php echo $data['shipping']['address']['address2'] ?>" name="address2">
    <input type="hidden" value="<?php echo $data['shipping']['address']['city'] ?>" name="city">
    <input type="hidden" value="<?php echo $data['shipping']['address']['country'] ?>" name="country">
    <input type="hidden" value="<?php echo $data['shipping']['address']['zone'] ?>" name="state">
    <input type="hidden" value="<?php echo $data['shipping']['address']['zipcode'] ?>" name="zip">

    <!-- IPN-PDT  ONLY -->
    <input type="hidden" value="<?php echo $data['plugin']['order_id'] ?>" name="custom">
    <input type="hidden" value="INV_<?php echo $data['plugin']['invoice_no'] ?>" name="invoice">

    <?php $id = 1 ?>
    <!--CART INFO ITEMISED-->
    <?php foreach ($data['plugin']['order']->cart_contents as $key => $item) { ?>
        <input type="hidden" value="<?php echo number_format((float)$item['line_price'], 2, '.', '') ?>"
               name="amount_<?php echo (int)$id ?>">
        <input type="hidden" value="<?php echo $item['product']['post_title'] ?>"
               name="item_name_<?php echo (int)$id ?>">
        <input type="hidden" value="" name="item_number_<?php echo (int)$id ?>">
        <input type="hidden" value="<?php echo $item['quantity'] ?>" name="quantity_<?php echo (int)$id ?>">
        <?php $id++ ?>
    <?php } ?>

    <!--SHIPPING CHARGES-->
    <?php if ($data['plugin']['order']->shipping_total > 0) { ?>
        <input type="hidden" value="<?php echo $data['plugin']['order']->shipping_total ?>"
               name="amount_<?php echo (int)$id ?>">
        <input type="hidden" value="Shipping"
               name="item_name_<?php echo (int)$id ?>">
        <input type="hidden" value="" name="item_number_<?php echo (int)$id ?>">
        <input type="hidden" value="1" name="quantity_<?php echo (int)$id ?>">
        <?php $id++ ?>
    <?php } ?>

    <!--TAXES-->
    <?php if ($data['plugin']['order']->tax_total > 0) { ?>
        <input type="hidden" value="<?php echo $data['plugin']['order']->tax_total ?>"
               name="amount_<?php echo (int)$id ?>">
        <input type="hidden" value="Tax"
               name="item_name_<?php echo (int)$id ?>">
        <input type="hidden" value="" name="item_number_<?php echo (int)$id ?>">
        <input type="hidden" value="1" name="quantity_<?php echo (int)$id ?>">
        <?php $id++ ?>
    <?php } ?>

    <input type="hidden" value="0.00" name="discount_amount_cart">

    <!--PAYPAL VARIABLES-->
    <input type="hidden" value="_cart" name="cmd">
    <input type="hidden" value="2" name="rm">
    <input type="hidden" value="<?php echo $data['config']['payment_paypal_merchant_id'] ?>" name="business">
    <input type="hidden"
           value="<?php echo get_site_url() ?>/confirmPayment/?paction=display&payment_name=<?php echo $data['plugin_name'] ?>"
           name="return">
    <input type="hidden"
           value="<?php echo get_site_url() ?>/confirmPayment/?paction=cancel&payment_name=<?php echo $data['plugin_name'] ?>"
           name="cancel_return">
    <input type="hidden"
           value="<?php echo get_site_url() ?>/confirmPayment/?paction=process&payment_name=<?php echo $data['plugin_name'] ?>"
           name="notify_url">
    <input type="hidden" value="<?php echo "EUR";//$data['plugin']['currency'] ?>" name="currency_code">
    <input type="hidden" value="1" name="no_note">
    <input type="hidden" value="CartRabbit" name="bn">
    <input type="hidden" value="1" name="upload">
    <input type="hidden" value="utf-8" name="charset">

    <!-- payment screen style variables -->

    <input type="submit" id="btn_place_order" value="Place Order" class="btn btn-primary button">
</form>