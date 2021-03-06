<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use app\models\Order;
use app\models\ProductImage;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <?php
        $gridColumns = [
        // [
        //     'attribute' => 'id',
        //     'value' => function ($model) {
        //         return $model->id;
        //     },
        //     'header' => 'Order ID',
        //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
        // ],
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return date('Y-m-d', strtotime($model->created_at));
                },
                'filter' => '',
                'header' => 'Order Date',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'value' => function ($model) {
                    return $model->user->first_name . " " . $model->user->last_name;
                },
                'header' => 'Customer Name',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'value' => function ($model) {
                    return $model->user->email;
                },
                'header' => 'Customer Email',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'value' => function ($model) {
                    return $model->user->mobile;
                },
                'header' => 'Customer Phone',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'value' => function ($model) {
                    return $model->userAddress->address . ", " . $model->userAddress->street . ", " . $model->userAddress->city . ", " . $model->userAddress->zip_code . ", " . $model->userAddress->state;
                },
                'header' => 'Customer Address',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'format' => 'raw',
                'value' => function ($model) {
                    $html = "";
                    $html .= "<table class='order-products_tbl table-responsive ' style='border: solid 1px; text-align: center'>";
                    $html .= "<th style='border: solid 1px;'>Product Image</th>";
                    $html .= "<th style='border: solid 1px;'>Product Name</th>";
                    $html .= "<th style='border: solid 1px;'>Product Category</th>";
                    $html .= "<th style='border: solid 1px;'>Product Price</th>";
                    $html .= "<th style='border: solid 1px;'>Total Quantity</th>";
                    if (!empty($model->orderItems)) {
                        foreach ($model->orderItems as $key => $orderItem) {

                            $images = $orderItem->product->productImages;
                            $dataImages = [];
                            foreach ($images as $imageRow) {

                                if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                                    $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
                                } else {
                                    $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                                }

                                $dataImages[] = ['content' => Html::img($image_path, ['width' => '570', 'alt' => 'Product Image']),
                                // 'caption' => '<h4>Product Image</h4><p>This is the product caption text</p>',
                                'options' => ['interval' => '600']
                            ];
                        }

                        $result = "";
                        if (!empty($dataImages)) {
                            $result = \yii\bootstrap\Carousel::widget(
                                ['items' => $dataImages]
                            );
                        }
                        $productPrice = (!empty($orderItem->product->price)) ? Yii::$app->formatter->asCurrency($orderItem->product->price) : '';

                        $html .= "<tr style='border: solid 1px;'>";
                        $html .= "<td style='border: solid 1px;'>" . $result . "</td>";
                        $html .= "<td style='border: solid 1px;'>" . $orderItem->product->name . "</td>";
                        $html .= "<td style='border: solid 1px;'>" . $orderItem->product->category->name . "</td>";
                        $html .= "<td style='border: solid 1px;'>" . $productPrice . "</td>";
                        $html .= "<td style='border: solid 1px;'>" . $orderItem->quantity . "</td>";
                        $html .= "</tr>";
                    }
                } else {
                    $html .= "<tr><td colspan='5'><p>Product data not found.</p></td></tr>";

                }
                $html .= "</table>";

                return $html;
            },
            'header' => 'Order Products',
            'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
        ],
        [
            'attribute' => 'total_amount',
            'value' => function ($model) {
                return (!empty($model->total_amount)) ? Yii::$app->formatter->asCurrency($model->total_amount) : "";
            },
            'filter' => '',
            'header' => 'Total Amount Paid',
            'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
        ],
        [
            'attribute' => 'status',
            'value' => function ($model) {
                $status = 'Pending';
                if ($model->status == Order::STATUS_ORDER_INPROGRESS) {
                    $status = 'In Progress';
                } elseif ($model->status == Order::STATUS_ORDER_COMPLETED) {
                    $status = 'Completed';
                } elseif ($model->status == Order::STATUS_ORDER_CANCELLED) {
                    $status = 'Cancelled';
                }
                return $status;
            },
            'filter' => $searchModel->arrOrderStatus,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => 'Order Status',
            'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => "{update} {view}"
        ],
    ];

    echo GridView::widget([
        'id' => 'order-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true, // pjax is set to always true for this demo
        'toolbar' => [
            [
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                Html::button('<i class="fa fa-refresh"> Reset </i>', [
                    'class' => 'btn btn-basic',
                    'title' => 'Reset Filter',
                    'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['order/index']) . "';",
                ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}',
        ],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => false,
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            //'heading' => 'Order',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'order',
        'itemLabelPlural' => 'Orders',
    ]);
    ?>

</div>
</div>

<script>
    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function(){
        $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);

        var input;
        var submit_form = false;
        var filter_selector = '#order-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#order-grid" , function(event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#order-grid" , function(event) {
            if (isInput) {
                submit_form = false;
            }
        });

        $(document)
        .off('keydown.yiiGridView change.yiiGridView', filter_selector)
        .on('keyup', filter_selector, function(e) {
            input = $(this).attr('name');
            var keyCode = e.keyCode ? e.keyCode : e.which;
            if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                if (submit_form === false) {
                    submit_form = true;
                    $("#order-grid").yiiGridView("applyFilter");
                }
            }
        })
        .on('pjax:success', function() {
            var i = $("[name='"+input+"']");
            var val = i.val();
            i.focus().val(val);

                var searchInput = $(i);
                if (searchInput.length > 0) {
                    var strLength = searchInput.val().length * 2;
                    searchInput[0].setSelectionRange(strLength, strLength);
                }

                if ($('thead td i').length == 0) {
                    $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);
                }

                $('.pagination').find('li a').on('click', function () {
                    setTimeout(function () {
                        $(document).scrollTop($(document).innerHeight());
                    }, 200);
                })
            });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    })
</script>
