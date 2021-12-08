<?php

use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Customer Reports';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-basic">
    <div class="box-header">
        <div class="row text-center">
            <div class="col col-md-4">
                <a href="<?php echo Url::to(['report/customers', 'p' => 'w']) ?>">
                    <button class="btn btn-basic btn-sm btn-theme active">Weekly</button>
                </a>
            </div>
            <div class="col col-md-4">
                <a href="<?php echo Url::to(['report/customers', 'p' => 'm']) ?>">
                    <button class="btn btn-basic btn-sm btn-theme">Monthly</button>
                </a>
            </div>
            <div class="col col-md-4">
                <a href="<?php echo Url::to(['report/customers', 'p' => 'y']) ?>">
                    <button class="btn btn-basic btn-sm btn-theme">Yearly</button>
                </a>
            </div>
            <div class="col col-md-12">
                <?php
                if (Yii::$app->request->get('p') == 'm') {
                    $reportTitle = "Monthly Report";
                } elseif (Yii::$app->request->get('p') == 'y') {
                    $reportTitle = "Yearly Report";
                } elseif (Yii::$app->request->get('p') == 'w') {
                    $reportTitle = "Weekly Report";
                } else {
                    $reportTitle = "Weekly Report";
                }
                ?>
                <h3 class="text-center mt-5"><u><?php echo $reportTitle; ?></u></h3>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col col-md-6">
                                <h4>Customer</h4>
                            </div>
                            <div class="col col-md-6">
                                <div class="text-right">
                                    <a class="btn btn-primary btn-sm"
                                       href="<?php echo Url::to(['report/export-customers-report', 'p' => Yii::$app->request->get('p')]) ?>"><i
                                                class="fa fa-arrow-up"></i> Export
                                        to Excel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="customer" border="1" width="100%">
                            <tr>
                                <th style="text-align: center"><?php echo (Yii::$app->request->get('p') == 'y') ? "Month" : 'Date'; ?></th>
                                <th style="text-align: center"><?php echo "Customers"; ?></th>
                            </tr>
                            <?php if (!empty($customers)) { ?>
                                <?php foreach ($customers as $keyCustomer => $customer) { ?>
                                    <tr>
                                        <td><?php echo $keyCustomer; ?></td>
                                        <td><?php echo $customer; ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td><strong><?php echo "Total Customer"; ?></strong></td>
                                    <td><strong><?php echo $totalCustomers; ?></strong></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>