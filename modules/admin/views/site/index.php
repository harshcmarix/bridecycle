<?php

use yii\helpers\Html;
use miloschuman\highcharts\Highcharts;

$this->title = 'Dashboard';
?>


<div class="row">
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?php echo $totalCustomer ?></h3>

                <p>Total Customer</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/user/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?php echo $totalCustomerToday ?></h3>

                <p>New Customer <?php echo date('d m,Y') ?></p>
            </div>
            <div class="icon">
                <i class="fa fa-user-circle"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/user/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $totalProduct ?></h3>

                <p>Total Products</p>
            </div>
            <div class="icon">
                <i class="fa fa-product-hunt"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/product/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?php echo $totalOrder ?></h3>

                <p>Total Order Placed</p>
            </div>
            <div class="icon">
                <i class="fa fa-reorder"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>


    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?php echo $totalOrderDeliveredAndCompleted ?></h3>

                <p>Order Delivered and Completed</p>
            </div>
            <div class="icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $totalOrderPending ?></h3>

                <p>Order Pending</p>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>150</h3>

                <p>Ads Clicked </p>
            </div>
            <div class="icon">
                <i class="fa fa-hand-pointer-o"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/ads/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?php echo $totalIncome; ?></h3>

                <p>Total income</p>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
            <a href="<?php echo Yii::$app->request->baseUrl. '/admin/order/index' ?>" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-md-6 col-xl-6">
        <div class="box box-basic">
            <div class="box-header">
            </div>
            <div class="box-body">
                <select id="orders">
                    <option value="current-year">Year</option>
                    <option value="current-month">Month</option>
                    <option value="current-week">Week</option>
                    <option value="current-day">Today</option>
                </select>
                <div id="ordersGraph"></div>
                <script>
                    $(document).ready(function () {
                        renderGraph('current-year');
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

                <?php
                echo Highcharts::widget([
                    'options' => [
                        'title' => ['text' => 'Income ' . date('Y')],
                        //'boxplot' => ['fillColor' => '#EADBC4'],
                        'plotOptions' => [
                            'column' => [
                                'cursor' => 'pointer',
                                'color' => '#3366CC',
                            ],
                        ],
                        'xAxis' => [
                            'categories' => $month,

                        ],
                        'yAxis' => [
                            'title' => ['text' => 'Income'],
                            'min' => $minIncome,
                            'max' => $maxIncome,
                        ],
                        'series' => [
                            ['type' => 'column', 'name' => 'Income', 'data' => $monthWiseIncomes],
                        ]
                    ]
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
