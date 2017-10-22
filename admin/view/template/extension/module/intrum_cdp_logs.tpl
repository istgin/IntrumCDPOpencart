<!-- merging the 'header' and 'column left' part with this template -->
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <!-- Form submit button -->
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
            <div class="panel-body">
                <!-- form starts here -->
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-intrum-cdp"
                      class="form-horizontal">
                    <ul class="nav nav-tabs">
                        <li><a href="<?php echo $intrumsettingstab; ?>">Settings</a></li>
                        <li class="active"><a href="<?php echo $intrumlogtab; ?>">Intrum logs</a></li>
                    </ul>
                    <div class="tab-content">
                        <div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <td class="text-left">ID</td>
                                        <td class="text-left">Request ID</a></td>
                                        <td class="text-left">Request type</a></td>
                                        <td class="text-left">First name</a></td>
                                        <td class="text-left">Last name</a></td>
                                        <td class="text-left">Ip</a></td>
                                        <td class="text-left">Status</a></td>
                                        <td class="text-left">Date</a></td>
                                        <td class="text-right">Options</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if ($logs) { ?>
                                    <?php foreach ($logs as $log) { ?>
                                        <tr>
                                            <td class="text-left"><?php echo $log['id']; ?></td>
                                            <td class="text-left"><?php echo $log['requestid']; ?></td>
                                            <td class="text-left"><?php echo $log['requesttype']; ?></td>
                                            <td class="text-left"><?php echo $log['firstname']; ?></td>
                                            <td class="text-left"><?php echo $log['lastname']; ?></td>
                                            <td class="text-left"><?php echo $log['ip']; ?></td>
                                            <td class="text-left"><?php echo $log['status']; ?></td>
                                            <td class="text-left"><?php echo $log['datecolumn']; ?></td>
                                            <td class="text-right"><a href="<?php echo $log['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a></td>
                                        </tr>
                                    <?php } ?>
                                    <?php } else { ?>
                                    <tr>
                                        <td class="text-center" colspan="9">No logs found</td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                                <div class="row" style="width: 99%;">
                                    <div class="col-xs-12">
                                    <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                                    <div class="col-sm-6 text-right"><?php echo $results; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- merges the footer with the template -->
<?php echo $footer; ?>