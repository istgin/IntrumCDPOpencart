<!-- merging the 'header' and 'column left' part with this template -->
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <!-- Form submit button -->
                <button type="submit" form="form-intrum-cdp" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <!-- Back button -->
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <!-- Heading is mentioned here -->
            <h1><?php echo $heading_title; ?></h1>
            <!-- Breadcrumbs are listed here -->
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <!-- if it contains a warning then it will be visible as an alert -->
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <!-- form starts here -->
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-intrum-cdp"
                      class="form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="<?php echo $intrumsettingstab; ?>">Settings</a></li>
                        <li><a href="<?php echo $intrumlogtab; ?>">Intrum logs</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-general">
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <select name="intrum_cdp_status" id="input-status" class="form-control">
                                        <?php if ($intrum_cdp_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Mode</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <select name="intrum_cdp_mode" id="input-status" class="form-control">
                                        <?php if ($intrum_cdp_mode == 'live') { ?>
                                        <option value="test">Test</option>
                                        <option value="live" selected="selected">Live</option>
                                        <?php } else { ?>
                                        <option value="test" selected="selected">Test</option>
                                        <option value="live">Live</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">B2B</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <select name="intrum_cdp_b2b" id="input-status" class="form-control">
                                        <?php if ($intrum_cdp_b2b == 'enabled') { ?>
                                        <option value="enabled" selected="selected">Enabled</option>
                                        <option value="disabled">Disabled</option>
                                        <?php } else { ?>
                                        <option value="enabled">Enabled</option>
                                        <option value="disabled" selected="selected">Disabled</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Client ID</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_client_id" id="input-status"
                                           value="<?php echo $intrum_cdp_client_id; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">User ID</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_user_id" id="input-status"
                                           value="<?php echo $intrum_cdp_user_id; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Password</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_password" type="password" id="input-status"
                                           value="<?php echo $intrum_cdp_password; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Technical email</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_tech_email" type="text" id="input-status"
                                           value="<?php echo $intrum_cdp_tech_email; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Threatmetrix enabled</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <select name="intrum_cdp_threatmetrix_enabled" id="input-status" class="form-control">
                                        <?php if ($intrum_cdp_threatmetrix_enabled == 'enabled') { ?>
                                        <option value="disabled">Disabled</option>
                                        <option value="enabled" selected="selected">Enabled</option>
                                        <?php } else { ?>
                                        <option value="disabled" selected="selected">Disabled</option>
                                        <option value="enabled">Enabled</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Threatmetrix Org ID</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_threatmetrix_id" type="text" id="input-status"
                                           value="<?php echo $intrum_cdp_threatmetrix_id; ?>" class="form-control"/>
                                </div>
                            </div>


                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Custom field gender id (in opencart address field)</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_gender_id" type="text" id="input-status"
                                           value="<?php echo $intrum_cdp_gender_id; ?>" class="form-control"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Possible male prefix (use semicol (;) to separate)</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_gender_male_possible_prefix_array" type="text" id="input-status"
                                           value="<?php echo $intrum_cdp_gender_male_possible_prefix_array; ?>" class="form-control"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Possible female prefix (use semicol (;) to separate)</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_gender_female_possible_prefix_array" type="text" id="input-status"
                                           value="<?php echo $intrum_cdp_gender_female_possible_prefix_array; ?>" class="form-control"/>
                                </div>
                            </div>


                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status">Custom field date of birth id (in opencart address field)</label>

                                <div class="col-sm-10">
                                    <!-- The name of the form inputs must start with the controller file name followed by a underscore
                                    like in this case "intrum_cdp_" after that status is added -->
                                    <input name="intrum_cdp_dob_id" type="text" id="input-status"
                                           value="<?php echo $intrum_cdp_dob_id; ?>" class="form-control"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-10">
                                    <b style="font-size: 20px">Payment method mapping</b>
                                </div>
                            </div>
                            <?php foreach($payment_methods as $payment) { ?>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $payment["name"]; ?></label>

                                <div class="col-sm-10">
                                    <select name="intrum_cdp_map_<?php echo $payment["code"]; ?>" id="input-status" class="form-control">
                                        <option value="INVOICE" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'INVOICE') echo 'selected="selected"'; ?>>INVOICE</option>
                                        <option value="DIRECT-DEBIT" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'DIRECT-DEBIT') echo 'selected="selected"'; ?>>DIRECT-DEBIT</option>
                                        <option value="CREDIT-CARD" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'CREDIT-CARD') echo 'selected="selected"'; ?>>CREDIT-CARD</option>
                                        <option value="PRE-PAY" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'PRE-PAY') echo 'selected="selected"'; ?>>PRE-PAY</option>
                                        <option value="CASH-ON-DELIVERY" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'CASH-ON-DELIVERY') echo 'selected="selected"'; ?>>CASH-ON-DELIVERY</option>
                                        <option value="E-PAYMENT" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'E-PAYMENT') echo 'selected="selected"'; ?>>E-PAYMENT</option>
                                        <option value="PAYMENT" <?php if (${"intrum_cdp_map_".$payment["code"]} == 'PAYMENT') echo 'selected="selected"'; ?>>PAYMENT</option>
                                    </select>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="form-group">
                                <div class="col-sm-10">
                                    <b style="font-size: 20px">Disabled payment method</b>
                                </div>
                            </div>
                            <?php foreach($statuses as $status) { ?>
                            <div class="form-group">
                                <!-- Entry label is mentioned here -->
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $status["name"]; ?></label>

                                <div class="col-sm-10">
                                    <?php
                                        foreach($payment_methods as $payment) {
                                            $checked = '';
                                            if (in_array($payment["code"], ${"intrum_cdp_status_".$status["id"]})) {
                                                $checked = ' checked="checked"';
                                            }

                                        ?>

                                    <div style="padding: 0 0 5px 0;"><input<?php echo $checked; ?> type="checkbox"
                                        name="intrum_cdp_status_<?php echo ''.$status["id"]; ?>[]" value="<?php echo $payment["code"]; ?>"
                                        style="transform: scale(1.5)" />&nbsp;&nbsp;&nbsp;<?php echo $payment["name"]; ?></div>
                                    <?php }
                                        ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                     </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- merges the footer with the template -->
<?php echo $footer; ?>