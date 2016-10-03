<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <h3>paypal Payment Settings</h3>

                <div class="col-md-12 form-group">
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Enabled/Disabled :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="checkbox"
                                <?php if ($config['enabled'] == 'on') { ?> checked <?php } ?>
                                   class="" name="cartrabbit[payment][enabled]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Payment Option Title :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="cartrabbit[payment][payment_paypal_title]"
                                   value="<?php echo $config['payment_paypal_title'] ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Merchant ID :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="cartrabbit[payment][payment_paypal_merchant_id]"
                                   value="<?php echo $config['payment_paypal_merchant_id'] ?>"
                                   class=" form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>validate IPN :</lable>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" name="cartrabbit[payment][payment_paypal_validate_ipn]">
                                <option <?php if ($config['payment_paypal_validate_ipn'] == 'yes') { ?> selected=selected <?php } ?>
                                    value="yes">Yes
                                </option>
                                <option <?php if ($config['payment_paypal_validate_ipn'] == 'no') { ?> selected=selected <?php } ?>
                                    value="no">No
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Use Paypal Sandbox :</lable>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" name="cartrabbit[payment][payment_paypal_sandbox]">
                                <option <?php if ($config['payment_paypal_sandbox'] == 'yes') { ?> selected=selected <?php } ?>
                                    value="yes">Yes
                                </option>
                                <option <?php if ($config['payment_paypal_sandbox'] == 'no') { ?> selected=selected <?php } ?>
                                    value="no">No
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Sandbox Merchand ID :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['payment_paypal_sandbox_merchant_merchant_id'] ?>"
                                   name="cartrabbit[payment][payment_paypal_sandbox_merchant_merchant_id]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Subcharge Percentage :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['payment_paypal_subcharge_percentage'] ?>"
                                   name="cartrabbit[payment][payment_paypal_subcharge_percentage]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Subcharge Fixed Value :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['payment_paypal_subchare_fixed'] ?>"
                                   name="cartrabbit[payment][payment_paypal_subchare_fixed]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Paypal Callback Text :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['payment_paypal_callback_text'] ?>"
                                   name="cartrabbit[payment][payment_paypal_callback_text]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Header Background Color :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="color" class=""
                                   value="<?php echo $config['payment_paypal_header_background_color'] ?>"
                                   name="cartrabbit[payment][payment_paypal_header_background_color]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Header Border Color :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="color" class=""
                                   value="<?php echo $config['payment_paypal_header_border_color'] ?>"
                                   name="cartrabbit[payment][payment_paypal_header_border_color]">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on select payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][payment_paypal_text_on_select_payment]"><?php echo $config['payment_paypal_text_on_select_payment'] ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on before payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][payment_paypal_text_on_before_payment]"><?php echo $config['payment_paypal_text_on_before_payment'] ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on after payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][payment_paypal_text_on_after_payment]"><?php echo $config['payment_paypal_text_on_after_payment'] ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on error in payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][payment_paypal_text_on_error_in_payment]"><?php echo $config['payment_paypal_text_on_error_in_payment'] ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on if customer cancels payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][payment_paypal_text_if_customer_cancels_payment]"><?php echo $config['payment_paypal_text_if_customer_cancels_payment'] ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Payment Button Text :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['payment_paypal_button_text'] ?>"
                                   name="cartrabbit[payment][payment_paypal_button_text]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Debug :</lable>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" name="cartrabbit[payment][payment_paypal_debug]">
                                <option <?php if ($config['payment_paypal_debug'] == 'yes') { ?> selected=selected <?php } ?>
                                    value="yes">Yes
                                </option>
                                <option <?php if ($config['payment_paypal_debug'] == 'no') { ?> selected=selected <?php } ?>
                                    value="no">No
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>