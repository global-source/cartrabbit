<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <h3>COD Payment Settings</h3>

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
                            <input type="text" name="cartrabbit[payment][cod_payment_title]"
                                   value="<?php echo $config['cod_payment_title'] ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Subcharge Percentage :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="cartrabbit[payment][cod_subchage_percentage]"
                                   value="<?php echo $config['cod_subchage_percentage'] ?>"
                                   class=" form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Subcharge Fixed Value :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="cartrabbit[payment][cod_subcharge_fixed_value]"
                                   value="<?php echo $config['cod_subcharge_fixed_value'] ?>"
                                   class=" form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Name Of the Subcharge :</lable>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="cartrabbit[payment][cod_subcharge_name]"
                                   value="<?php echo $config['cod_subcharge_name'] ?>"
                                   class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <lable>Default Payment Status :</lable>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" name="cartrabbit[payment][cod_default_payment_status]">
                                <option <?php if ($config['cod_default_payment_status'] == 'pending') { ?>
                                    selected=selected
                                <?php } ?>
                                    value="pending">Pending
                                </option>
                                <option <?php if ($config['cod_default_payment_status'] == 'completed') { ?>
                                    selected=selected
                                <?php } ?>
                                    value="completed">Completed
                                </option>
                                <option <?php if ($config['cod_default_payment_status'] == 'failed') { ?>
                                    selected=selected
                                <?php } ?>
                                    value="failed">Failed
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on selection :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][cod_text_on_selection]"><?php echo $config['cod_text_on_selection'] ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on before payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][cod_text_on_before_payment]"><?php echo $config['cod_text_on_before_payment'] ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <lable>Display text on after payment :</lable>
                        </div>
                        <div class="col-md-4">
                            <textarea class="form-control"
                                      name="cartrabbit[payment][cod_text_on_after_payment]"><?php echo $config['cod_text_on_after_payment'] ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>