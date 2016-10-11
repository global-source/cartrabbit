<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <h3>Shipping</h3>

                <div class="col-md-12 form-group">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Enabled/Disabled :</label>
                        </div>
                        <div class="col-md-1">
                            <input type="checkbox"
                                   class="form-control" <?php if (isset($config['items']['enabled'])) {
                                if ($config['items']['enabled'] == 'on') { ?> checked <?php }
                            } ?>
                                   name="cartrabbit[shipping][enabled]">
                        </div>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Sets :</label>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-responsive">
                                <thead>
                                <tr>
                                    <td>ID</td>
                                    <td>Minimum Quantity</td>
                                    <td>Maximum Quantity</td>
                                    <td>Rate</td>
                                    <td>Handling Charge</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody class="shipping_table">
                                <?php
                                $id = 1;
                                if (isset($config['items']['list'])) {
                                    foreach ($config['items']['list'] as $i => $val) { ?>
                                        <tr>
                                            <td><?php echo $id ?></td>
                                            <td><input type="text"
                                                       name="cartrabbit[shipping][<?php echo $i ?>][minQty]"
                                                       class="form-control"
                                                       value="<?php echo array_first($val['minQty']) ?>"></td>
                                            <td><input type="text"
                                                       name="cartrabbit[shipping][<?php echo $i ?>][maxQty]"
                                                       class="form-control"
                                                       value="<?php echo array_first($val['maxQty']) ?>"></td>
                                            <td><input type="text"
                                                       name="cartrabbit[shipping][<?php echo $i ?>][rate]"
                                                       class="form-control"
                                                       value="<?php echo array_first($val['rate']) ?>"></td>
                                            <td><input type="text"
                                                       name="cartrabbit[shipping][<?php echo $i ?>][extra]"
                                                       class="form-control"
                                                       value="<?php echo array_first($val['extra']) ?>"></td>
                                            <td>
                                                <i id=" <?php echo $i ?>"
                                                   class="glyphicon glyphicon-remove text-danger btn_remove_shipping_rate"></i>
                                            </td>
                                            <input type="hidden"
                                                   name="cartrabbit[shipping][<?php echo $i ?>][update]"
                                                   value="true">

                                            <input type="hidden"
                                                   name="cartrabbit[shipping][<?php echo $i ?>][id]"
                                                   value="<?php echo $val['id'] ?>">
                                        </tr>
                                        <?php
                                        $id++;
                                    }
                                } ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>ID</td>
                                    <td>Minimum Quantity</td>
                                    <td>Maximum Quantity</td>
                                    <td>Rate</td>
                                    <td>Handling Charge</td>
                                    <td>Action</td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td colspan="6"></td>
                                </tr>
                                </tfoot>
                            </table>
                            <input type="button"
                                   class="btn btn-default btn_add_shipping_rate"
                                   value="Add Shipping Rate">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>