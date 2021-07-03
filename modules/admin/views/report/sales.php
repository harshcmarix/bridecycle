<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Sale Reports';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-basic">
    <div class="box-header">
        <div class="row text-center">
            <div class="col col-md-4">
                <a href="<?php echo Url::to(['report/sales', 'p' => 'w']) ?>">
                    <button class="btn btn-basic active">Weekly</button>
                </a>
            </div>
            <div class="col col-md-4">
                <a href="<?php echo Url::to(['report/sales', 'p' => 'm']) ?>">
                    <button class="btn btn-basic">Monthly</button>
                </a>
            </div>
            <div class="col col-md-4">
                <a href="<?php echo Url::to(['report/sales', 'p' => 'y']) ?>">
                    <button class="btn btn-basic">Yearly</button>
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
            <div class="col col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h4>Order</h4>
                    </div>
                    <div class="box-body">
                        <table id="order" style="width:100%;border: solid:2px;border-color: black;">
                            <tr>
                                <th><?php echo (Yii::$app->request->get('p') == 'y') ? "Month" : 'Date'; ?></th>
                                <th><?php echo "Orders"; ?></th>
                            </tr>
                            <?php if (!empty($orders)) { ?>
                                <?php foreach ($orders as $keyOrder => $order) { ?>
                                    <tr>
                                        <td><?php echo $keyOrder; ?></td>
                                        <td><?php echo $order; ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td><strong><?php echo "Total Order"; ?></strong></td>
                                    <td><strong><?php echo $totalOrders; ?></strong></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>

            </div>
            <div class="col col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h4>Product</h4>
                    </div>
                    <div class="box-body">
                        <table id="product" style="width:100%;border: solid:2px;border-color: black;">
                            <tr>
                                <th><?php echo (Yii::$app->request->get('p') == 'y') ? "Month" : 'Date'; ?></th>
                                <th><?php echo "Products"; ?></th>
                            </tr>
                            <?php if (!empty($products)) { ?>
                                <?php foreach ($products as $keyProduct => $product) { ?>
                                    <tr>
                                        <td><?php echo $keyProduct; ?></td>
                                        <td><?php echo $product; ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td><strong><?php echo "Total Product"; ?></strong></td>
                                    <td><strong><?php echo $totalProducts; ?></strong></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

