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
                                <?php if (array_get($config['list'], 'enabled', 'off') == 'on') { ?> checked=checked <?php } ?>
                                   name="cartrabbit[shipping][enabled]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label>Shipping Cost :</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control"
                                   value="<?php echo array_get($config['list'], 'shipping_cost', '') ?>"
                                   name="cartrabbit[shipping][shipping_cost]">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label>Handling Charge :</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control"
                                   value="<?php echo array_get($config['list'], 'handling_cost', '') ?>"
                                   name="cartrabbit[shipping][handling_cost]">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>