<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <h3>Flat Rate Shipping</h3>

                <div class="col-md-12 form-group">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Enabled/Disabled :</label>
                        </div>
                        <div class="col-md-1">
                            <input type="checkbox"
                                   class="form-control"
                                <?php if ($config['list'][0]['enableShipping'][0] == 'on') { ?> checked=checked <?php } ?>
                                   name="cartrabbit[shipping][enabled]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label>Shipping Cost :</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['list'][0]['shipping_cost'][0] ?>"
                                   name="cartrabbit[shipping][shipping_cost]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label>Handling Charge :</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control"
                                   value="<?php echo $config['list'][0]['handling_cost'][0] ?>"
                                   name="cartrabbit[shipping][handling_cost]">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>