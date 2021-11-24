<?php

use app\models\UserPurchasedSubscriptions;
use app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use yii\helpers\{Html, Url};

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\UserPurchasedSubscriptionsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Subscriptions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <div class="box-header">
            <div class="row">
                <div class="col-md-3 col-xs-6 pull-right">
                    <a href="javascript:void(0);" class="small-box-footer">
                        <div class="small-box" style="background-color: #8A9673 !important;"
                             id="tot_income_from_subscription_box">
                            <div class="inner">
                                <h3><?php echo Yii::$app->formatter->asCurrency($totalEarn); ?></h3>
                                <p>Total Subscription Income</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <?php

        $gridColumns = [


        ];
        echo GridView::widget([
            'id' => 'user_purchased_subscriptions-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                // [
                //     'attribute' => 'id',
                //     'width' => '8%',
                //     'header' => '',
                //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                // ],
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'attribute' => 'subscription_type',
                    'value' => function ($model) {
                        $type = '';
                        if ($model instanceof UserPurchasedSubscriptions) {
                            $type = $model->subscription_type;
                        }
                        return $type;
                    },
                    'header' => 'Subscription Type',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'user_id',
                    'value' => function ($model) {
                        $user = '';
                        if ($model instanceof UserPurchasedSubscriptions) {
                            $user = (!empty($model->user) && !empty($model->user->first_name)) ? $model->user->first_name . " " . $model->user->last_name . " (" . $model->user->email . ")" : "";
                        }
                        return $user;
                    },
                    'filter' => $users,
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['prompt' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ],
                    'header' => 'User',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'amount',
                    'value' => function ($model) {
                        $amount = '';
                        if ($model instanceof UserPurchasedSubscriptions) {
                            $amount = Yii::$app->formatter->asCurrency($model->amount);
                        }
                        return $amount;
                    },
                    'header' => '',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'transaction_id',
                    'value' => function ($model) {
                        $transaction_id = '';
                        if ($model instanceof UserPurchasedSubscriptions) {
                            $transaction_id = $model->transaction_id;
                        }
                        return $transaction_id;
                    },
                    'header' => 'Transaction ID',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'subscription_id',
                    'value' => function ($model) {
                        $subscription_id = '';
                        if ($model instanceof UserPurchasedSubscriptions) {
                            $subscription_id = $model->subscription_id;
                        }
                        return $subscription_id;
                    },
                    'header' => 'Subscription ID',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'date_time',
                    'value' => function ($model) {
                        $subscription_date_time = '';
                        if ($model instanceof UserPurchasedSubscriptions) {
                            $subscription_date_time = $model->date_time;
                        }
                        return $subscription_date_time;
                    },
                    'header' => '',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view}', //{delete}
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
                            'onclick' => "window.location.href = '" . Url::to(['user-purchased-subscriptions/index']) . "';",
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
            'responsive' => false,
            // 'hover' => $hover,
            // 'showPageSummary' => $pageSummary,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                //'heading' => 'Subscriptions',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            //'exportConfig' => $exportConfig,
            'itemLabelSingle' => 'user subscription',
            'itemLabelPlural' => 'user subscriptions'
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

    $('document').ready(function () {
        $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);

        var input;
        var submit_form = false;
        var filter_selector = '#user_purchased_subscriptions-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#user_purchased_subscriptions-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#user_purchased_subscriptions-grid", function (event) {
            if (isInput) {
                submit_form = false;
            }
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', filter_selector)
            .on('keyup', filter_selector, function (e) {
                input = $(this).attr('name');
                var keyCode = e.keyCode ? e.keyCode : e.which;
                if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                    if (submit_form === false) {
                        submit_form = true;
                        $("#user_purchased_subscriptions-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function () {
                window.location.reload();
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
        var select_filter_selector = '#user_purchased_subscriptions-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#user_purchased_subscriptions-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#user_purchased_subscriptions-grid", function (event) {
            if (isSelect) {
                submit_form = false;
            }
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', select_filter_selector)
            .on('change', select_filter_selector, function (e) {
                select = $(this).attr('name');
                if (submit_form === false) {
                    submit_form = true;
                    $("#user_purchased_subscriptions-grid").yiiGridView("applyFilter");
                }
            })
            .on('pjax:success', function (data) {
                window.location.reload();
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