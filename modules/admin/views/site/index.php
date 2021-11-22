<?php

use app\models\Order;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>

<div class="row">

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['user/index', 'UserSearch[is_shop_owner]' => '0']) ?>" class="small-box-footer">
            <div class="small-box bg-light-blue-gradient">
                <div class="inner">
                    <h3><?php echo $totalCustomer ?></h3>
                    <p>All Customers</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['user/index', 'UserSearch[is_shop_owner]' => '1']) ?>" class="small-box-footer">
            <div class="small-box bg-light-blue-gradient">
                <div class="inner">
                    <h3><?php echo $totalShopOwnerCustomer ?></h3>
                    <p>All Shop Owner Customers</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['user/index-new-customer', 'UserSearch[created_at]' => date('d-M-Y') . " to " . date('d-M-Y')]) ?>"
           class="small-box-footer">
            <div class="small-box bg-light-blue-gradient">
                <div class="inner">
                    <h3><?php echo $totalCustomerToday ?></h3>
                    <p>New Customer <?php echo date('j M, Y') ?></p>
                </div>
                <div class="icon">
                    <i class="fa fa-user"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['user/index-new-shop-owner-customer', 'UserSearch[created_at]' => date('d-M-Y') . " to " . date('d-M-Y')]) ?>"
           class="small-box-footer">
            <div class="small-box bg-light-blue-gradient">
                <div class="inner">
                    <h3><?php echo $totalShopOwnerCustomerToday ?></h3>
                    <p>New Shop Owner <?php echo date('j M, Y') ?></p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-plus"></i>
                </div>
            </div>
        </a>
    </div>

</div>

<div class="row">

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['product/index']) ?>" class="small-box-footer">
            <div class="small-box bg-orange-active">
                <div class="inner">
                    <h3><?php echo $totalProduct ?></h3>
                    <p>All Products</p>
                </div>
                <div class="icon">
                    <i class="fa fa-product-hunt"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="
                <?php echo Url::to(['product/new-product', 'ProductSearch[created_at]' => date('d-M-Y') . " to " . date('d-M-Y')]) ?>"
           class="small-box-footer">
            <div class="small-box bg-orange-active">
                <div class="inner">
                    <h3><?php echo $totalProductPendingApproval ?></h3>
                    <p>New Products <?php echo date('j M, Y') ?></p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-bag"></i>
                </div>
            </div>
        </a>

    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['brand/index']) ?>" class="small-box-footer">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?php echo $totalBrand ?></h3>
                    <p>All Brand</p>
                </div>
                <div class="icon">
                    <i class="fa fa-tag"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['brand/new-brand', 'BrandSearch[created_at]' => date('d-M-Y') . " to " . date('d-M-Y')]) ?>"
           class="small-box-footer">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?php echo $totalNewBrandToday ?></h3>
                    <p>New Brand <?php echo date('j M, Y') ?></p>
                </div>
                <div class="icon">
                    <i class="fa fa-tag"></i>
                </div>
            </div>
        </a>
    </div>

</div>

<div class="row">

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['order/index']) ?>" class="small-box-footer">
            <div class="small-box bg-green-gradient">
                <div class="inner">
                    <h3><?php echo $totalOrder ?></h3>
                    <p>Total Order So Far</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['order/index', 'OrderSearch[status]' => Order::STATUS_ORDER_INPROGRESS]) ?>"
           class="small-box-footer">
            <div class="small-box bg-green-gradient">
                <div class="inner">
                    <h3><?php echo $totalOrderPending ?></h3>
                    <p>Order Inprogress</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['order/index', 'OrderSearch[status]' => Order::STATUS_ORDER_COMPLETED]) ?>"
           class="small-box-footer">
            <div class="small-box bg-green-gradient">
                <div class="inner">
                    <h3><?php echo $totalOrderDeliveredAndCompleted ?></h3>
                    <!-- <p>Order Delivered and Completed</p> -->
                    <p>Order Completed</p>
                </div>
                <div class="icon">
                    <i class="fa fa-reorder"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Url::to(['order/index', 'OrderSearch[created_at]' => date('d-M-Y') . " to " . date('d-M-Y')]) ?>"
           class="small-box-footer">
            <div class="small-box bg-green-gradient">
                <div class="inner">
                    <h3><?php echo $totalOrderToday ?></h3>
                    <p>Total Order Today</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>

</div>

<div class="row">

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl . '/admin/ads/index' ?>" class="small-box-footer">
            <div class="small-box bg-yellow-gradient">
                <div class="inner">
                    <h3><?php echo $totalActiveAds ?></h3>
                    <p>Total Ads</p>
                </div>
                <div class="icon">
                    <i class="fa fa-film"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl . '/admin/tailor/index' ?>" class="small-box-footer">
            <div class="small-box bg-purple-gradient">
                <div class="inner">
                    <h3><?php echo $totalTailor; ?></h3>
                    <p>Total Tailor</p>
                </div>
                <div class="icon">
                    <i class="fa fa-cut"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl . '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box" id="tot_income_box" style="background-color: #8A9673 !important">
                <div class="inner">
                    <h3><?php echo Yii::$app->formatter->asCurrency($totalIncome); ?></h3>
                    <p>Total Sales</p>
                </div>
                <div class="icon">
                    <i class="fa fa-money"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl . '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box bg-lime">
                <div class="inner">
                    <h3><?php echo $totalOrderLastMonth ?></h3>
                    <p>Total Order Last 30 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl . '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box bg-lime">
                <div class="inner">
                    <h3><?php echo $totalOrderLastWeek ?></h3>
                    <p>Total Order Last 7 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div> -->

</div>

<div class="row">
    <div class="col-md-6 col-xl-6">
        <div class="box box-basic chart-shadow">
            <div class="box-header">
            </div>
            <div class="box-body">
                <select id="orders">
                    <option value="current-year-orders">Year</option>
                    <option value="current-month-orders">Month</option>
                    <option value="current-week-orders">Week</option>
                    <option value="current-day-orders">Today</option>
                </select>
                <div id="ordersGraph"></div>
                <script>
                    $(document).ready(function () {
                        renderGraph('current-year-orders');
                    });
                    $('#orders').on('change', function () {
                        var action = this.value;
                        renderGraph(action);
                    })

                    function renderGraph(action) {
                        $.ajax({
                            url: '<?php echo Yii::$app->request->baseUrl . '/admin/site/' ?>' + action,
                            type: 'get',
                            success: function (data) {
                                var graphData = JSON.parse(data);
                                const d = new Date();
                                var chart = Highcharts.chart('ordersGraph', {
                                    chart: {
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Orders ' + d.getFullYear()
                                    },
                                    xAxis: {
                                        type: 'category'
                                    },
                                    yAxis: {
                                        title: {
                                            text: 'Orders'
                                        }
                                    },
                                    tooltip: {
                                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b>'
                                    },
                                    series: [{
                                        name: "Orders",
                                        colorByPoint: false,
                                        data: graphData
                                    }]
                                });
                            }
                        });
                    }
                </script>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-6">
        <div class="box box-basic chart-shadow">
            <div class="box-header">
            </div>
            <div class="box-body">
                <select id="income">
                    <option value="current-year-income">Year</option>
                    <option value="current-month-income">Month</option>
                    <option value="current-week-income">Week</option>
                    <option value="current-day-income">Today</option>
                </select>
                <div id="incomeGraph"></div>
                <script>
                    $(document).ready(function () {
                        renderIncomeGraph('current-year-income');
                    });
                    $('#income').on('change', function () {
                        var action = this.value;
                        renderIncomeGraph(action);
                    })

                    function renderIncomeGraph(action) {
                        $.ajax({
                            url: '<?php echo Yii::$app->request->baseUrl . '/admin/site/' ?>' + action,
                            type: 'get',
                            success: function (data) {
                                var incomeGraphData = JSON.parse(data);
                                const d = new Date();
                                var chart = Highcharts.chart('incomeGraph', {
                                    chart: {
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Income ' + d.getFullYear()
                                    },
                                    xAxis: {
                                        type: 'category'
                                    },
                                    yAxis: {
                                        title: {
                                            text: 'Income'
                                        }
                                    },
                                    tooltip: {
                                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b>'
                                    },
                                    series: [{
                                        name: "Income",
                                        colorByPoint: false,
                                        data: incomeGraphData
                                    }]
                                });
                            }
                        });
                    }
                </script>
            </div>
        </div>
    </div>
</div>