<?php

use yii\helpers\Html;
use miloschuman\highcharts\Highcharts;

$this->title = 'Dashboard';
?>


<div class="row">
    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/user/index' ?>" class="small-box-footer">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?php echo $totalCustomer ?></h3>
                    <p>Customers</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </a>
    </div> 

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/user/index' ?>" class="small-box-footer">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?php echo $totalShopOwnerCustomer ?></h3>
                    <p>Shop Owner Customers</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/sub-admin/index' ?>" class="small-box-footer">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3><?php echo $totSubAdmin ?></h3>
                    <p>Sub Admin</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-circle"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/user/index' ?>" class="small-box-footer">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3><?php echo $totalCustomerToday ?></h3>
                    <p>New Customer <?php echo date('M j, Y') ?></p>
                </div>
                <div class="icon">
                    <i class="fa fa-user"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/ads/index' ?>" class="small-box-footer">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3><?php echo $totalActiveAds ?></h3>
                    <p>Active Ads</p>
                </div>
                <div class="icon">
                    <i class="fa fa-film"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/brand/index' ?>" class="small-box-footer">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3><?php echo $totalBrand ?></h3>
                    <p>Brand</p>
                </div>
                <div class="icon">
                    <i class="fa fa-tag"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/product/index' ?>" class="small-box-footer">
            <div class="small-box bg-fuchsia">
                <div class="inner">
                    <h3><?php echo $totalProduct ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="icon">
                    <i class="fa fa-product-hunt"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/tailor/index' ?>" class="small-box-footer">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3><?php echo $totalTailor; ?></h3>
                    <p>Tailor</p>
                </div>
                <div class="icon">
                    <i class="fa fa-cut"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box bg-lime">
                <div class="inner">
                    <h3><?php echo $totalOrder ?></h3>
                    <p>Total Order Placed</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>


    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box bg-lime">
                <div class="inner">
                    <h3><?php echo $totalOrderDeliveredAndCompleted ?></h3>
                    <p>Order Delivered and Completed</p>
                </div>
                <div class="icon">
                    <i class="fa fa-reorder"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box bg-lime">
                <div class="inner">
                    <h3><?php echo $totalOrderPending ?></h3>
                    <p>Order Pending</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-xs-6">
        <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3><?php echo $totalIncome; ?></h3>
                    <p>Total income</p>
                </div>
                <div class="icon">
                    <i class="fa fa-money"></i>
                </div>
            </div>
        </a>
    </div>

</div>

<div class="row">
    <div class="col-md-6 col-xl-6">
        <div class="box box-basic">
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
                            url: '<?php echo Yii::$app->request->baseUrl. '/admin/site/' ?>'+action,
                            type: 'get',
                            success: function (data) {
                                var graphData = JSON.parse(data);
                                const d = new Date();

                                // var chart = Highcharts.chart('ordersGraph', {
                                //     title: {
                                //         text: 'Orders ' + d.getFullYear()
                                //     },
                                //     plotOptions: {
                                //        column: {
                                //            cursor: 'pointer',
                                //            color: '#3366CC',
                                //        },
                                //     },
                                //     series: {
                                //         name: "Browsers",
                                //         colorByPoint: true,
                                //         data: [
                                //             {name: "1", y: 62.74,}, {name: "2", y: 10.57,},{name: "3", y: 7.23,}, {name: "4", y: 5.58}, {name: "5", y: 4.02}
                                //         ]
                                //     },
                                //     xAxis: {
                                //         categories: 'categories',
                                //     },
                                //     yAxis: {
                                //         title: {text: 'Order'},
                                //         min: 0,
                                //         max: 2,
                                //     }
                                // });
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
                                    series: [
                                        {
                                            name: "Orders",
                                            colorByPoint: false,
                                            data: graphData
                                        }
                                    ]
                                });
                            }
                        });
                    }
                </script>
                <?php

//                echo Highcharts::widget([
//                    'options' => [
//                        'title' => ['text' => 'Orders ' . date('Y')],
//                        //'boxplot' => ['fillColor' => '#EADBC4'],
//                        'plotOptions' => [
//                            'column' => [
//                                'cursor' => 'pointer',
//                                'color' => '#3366CC',
//                            ],
//                        ],
//                        'xAxis' => [
//                            'categories' => $month,
//
//                        ],
//                        'yAxis' => [
//                            'title' => ['text' => 'Order'],
//                            'min' => $min,
//                            'max' => $max,
//                        ],
//                        'series' => [
//                            ['type' => 'column', 'name' => 'Order', 'data' => $monthWiseOrders],
//                        ]
//                    ]
//                ]);

                /* Year, Month, Week, Day wise data start */
//                echo Highcharts::widget([
//                    'options' => [
//                        'title' => ['text' => 'Orders ' . date('Y')],
//                        //'boxplot' => ['fillColor' => '#EADBC4'],
//                        'plotOptions' => [
//                            'column' => [
//                                'cursor' => 'pointer',
//                                'color' => '#3366CC',
//                            ],
//                        ],
//                        'xAxis' => [
//                            'categories' => $month,
//                        ],
//                        'yAxis' => [
//                            'title' => ['text' => 'Order'],
//                            'min' => $min,
//                            'max' => $max,
//                        ],
//                        'series' => [
//                            ['type' => 'column', 'name' => 'Order', 'data' => $monthWiseOrders],
//                        ]
//                    ]
//                ]);
                /* Year, Month, Week, Day wise data end */
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-6">
        <div class="box box-basic">
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
                            url: '<?php echo Yii::$app->request->baseUrl. '/admin/site/' ?>'+action,
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
                                    series: [
                                        {
                                            name: "Income",
                                            colorByPoint: false,
                                            data: incomeGraphData
                                        }
                                    ]
                                });
                            }
                        });
                    }
                </script>
                <?php
//                echo Highcharts::widget([
//                    'options' => [
//                        'title' => ['text' => 'Income ' . date('Y')],
//                        //'boxplot' => ['fillColor' => '#EADBC4'],
//                        'plotOptions' => [
//                            'column' => [
//                                'cursor' => 'pointer',
//                                'color' => '#3366CC',
//                            ],
//                        ],
//                        'xAxis' => [
//                            'categories' => $month,
//
//                        ],
//                        'yAxis' => [
//                            'title' => ['text' => 'Income'],
//                            'min' => $minIncome,
//                            'max' => $maxIncome,
//                        ],
//                        'series' => [
//                            ['type' => 'column', 'name' => 'Income', 'data' => $monthWiseIncomes],
//                        ]
//                    ]
//                ]);
                ?>
            </div>
        </div>
    </div>
</div>
