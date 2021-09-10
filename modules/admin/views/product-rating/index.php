<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\ProductRating;
use app\models\Product;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ProductRatingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Ratings';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="product-rating-index">

            <?=
            GridView::widget([
                'id' => 'product-rating-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    // [
                    //     'attribute' => 'id',
                    //     'value' => function ($model) {
                    //         $id = '';
                    //         if ($model instanceof ProductRating) {
                    //             $id = $model->id;
                    //         }
                    //         return $id;
                    //     },
                    //     'width' => '8%',
                    //     'header' => '',
                    //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    // ],
                    ['class' => 'kartik\grid\SerialColumn'],
                    [
                        'attribute' => 'product_id',
                        'value' => function ($model) {
                            $product = '';
                            if ($model->product instanceof Product) {
                                $product = $model->product->name;
                            }
                            return $product;
                        },
                        'filter' => ArrayHelper::map(Product::find()->all(), 'id', 'name'),
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => ''],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'width'=>'20px'
                            ],
                        ],
                        'header' => 'Product',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'user_id',
                        'value' => function ($model) {
                            $user = '';
                            if ($model->user instanceof \app\modules\api\v1\models\User) {
                                $user = $model->user->first_name . " " . $model->user->last_name;
                            }
                            return $user;
                        },
                        'filter' => ArrayHelper::map(\app\modules\api\v1\models\User::find()->where(['user_type' => \app\modules\api\v1\models\User::USER_TYPE_NORMAL])->all(), 'id', function ($model) {
                            return $model->first_name . " " . $model->last_name . " (" . $model->email . ")";
                        }),
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => ''],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'width'=>'20px'
                            ],
                        ],
                        'header' => 'User',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'rating',
                        'value' => function ($model) {
                            return (!empty($model->rating)) ? $model->rating : '';
                        },
                        'width' => '4%',
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'review',
                        'value' => function ($model) {
                            return (!empty($model->review)) ? $model->review : '';
                        },
                        'header' => '',
                        'format'=>['html'],
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == ProductRating::STATUS_PENDING) {
                                $status = "Pending Approval";
                            } elseif ($model->status == ProductRating::STATUS_APPROVE) {
                                $status = "Approved";
                            } elseif ($model->status == ProductRating::STATUS_DECLINE) {
                                $status = "Decline";
                            }
                            return $status;
                        },
                        'filter' => ProductRating::ARR_PRODUCT_RATING_STATUS,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Status',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'header' => 'Actions',
                        'class' => 'kartik\grid\ActionColumn',
                        'width' => '12%'
                    ],
                ],
                'pjax' => true, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' => [
                    [
                        'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . Url::to(['product-rating/index']) . "';",
                        ]),
                        'options' => ['class' => 'btn-group mr-2']
                    ],
                    '{toggleData}',
                ],
                'toggleDataContainer' => ['class' => 'btn-group mr-2'],

                // parameters from the demo form
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => GridView::TYPE_DEFAULT,
                    //'heading' => 'Product Ratings',
                ],
                'persistResize' => false,
                'toggleDataOptions' => ['minCount' => 10],
                'itemLabelSingle' => 'Product Rating',
                'itemLabelPlural' => 'Product Ratings'
            ]);
            ?>
        </div>

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
        var filter_selector = '#product-rating-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#product-rating-grid" , function(event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#product-rating-grid" , function(event) {
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
                    $("#product-rating-grid").yiiGridView("applyFilter");
                }
            }
        })
        .on('pjax:success', function() {
            if (isInput) {
                var i = $("[name='" + input + "']");
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
            }
        });

        //select box filter
        var select;
        var submit_form = false;
        var select_filter_selector = '#product-rating-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#product-rating" , function(event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#product-rating" , function(event) {
            if (isSelect) {
                submit_form = false;
            }
        });

        $(document)
        .off('keydown.yiiGridView change.yiiGridView', select_filter_selector)
        .on('change', select_filter_selector, function(e) {
            select = $(this).attr('name');
            if (submit_form === false) {
                submit_form = true;
                $("#product-rating").yiiGridView("applyFilter");
            }
        })
        .on('pjax:success', function() {
            var i = $("[name='" + input + "']");
            var val = i.val();
            i.focus().val(val);

            var searchInput = $(i);
            if (searchInput.length > 0) {
                var strLength = searchInput.val().length * 2;
                searchInput[0].setSelectionRange(strLength, strLength);
            }

            if (isSelect) {
                if ($('thead td i').length == 0) {
                    $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);
                }

                $('.pagination').find('li a').on('click', function () {
                    setTimeout(function () {
                        $(document).scrollTop($(document).innerHeight());
                    }, 200);
                })
            }
        });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    })
</script>