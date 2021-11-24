<?php

use \app\modules\admin\widgets\GridView;
use yii\helpers\{
    Html,
    ArrayHelper,
    Url
};
use kartik\editable\Editable;
use app\models\Subscription;


/* @var $this yii\web\View */
/* @var $searchModel app\models\SubscriptionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Subscription';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <?php

        $gridColumns = [


        ];
        echo GridView::widget([
            'id' => 'subscription-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
            // [
            //     'attribute' => 'id',
            //     'value' => function ($model) {
            //         $id = '';
            //         if ($model instanceof Subscription) {
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
                    'attribute' => 'name',
                    'value' => function ($model) {
                        $name = '';
                        if ($model instanceof Subscription) {
                            $name = $model->name;
                        }
                        return $name;
                    },
                    'header' => '',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'amount',
                    'value' => function ($model) {
                        $amount = '';
                        if ($model instanceof Subscription) {
                            $amount = Yii::$app->formatter->asCurrency($model->amount);
                        }
                        return $amount;
                    },
                    'header' => '',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        $status = '';
                        if ($model instanceof Subscription) {
                            $status = Subscription::SUBSCRIPTION_STATUS_ARRAY[$model->status];
                        }
                        return $status;
                    },

                    'filter' => Subscription::SUBSCRIPTION_STATUS_ARRAY,
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['prompt' => ''],
                        'pluginOptions' => [
                            'allowClear' => true,
                        // 'width'=>'20px'
                        ],
                    ],
                    'width' => '10%',
                    'header' => '',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'label' => 'Total Subscribed users',
                    'value' => function ($model) {
                        $created_at = '';
                        if ($model instanceof Subscription) {
                            $created_at = $model->subscribedUsersCount;
                        }
                        return $created_at;
                    },
                    'filter' => false,
                    'header' => '',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
            // [
            //     'attribute' => 'created_at',
            //     'value' => function ($model) {
            //         $created_at = '';
            //         if ($model instanceof Subscription) {
            //             $created_at = $model->created_at;
            //         }
            //         return $created_at;
            //     },
            //     'filter' => false,
            //     'header' => '',
            //     'headerOptions' => ['class' => 'kartik-sheet-style']
            // ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'width' => '12%'
                ],
            ],

        'pjax' => true, // pjax is set to always true for this demo
        // set your toolbar
        'toolbar' => [
            [
                'content' =>
                Html::button('<i class="fa fa-plus-circle"> Add Subscription</i>', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Subscription'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/subscription/create']) . "';",
                ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                Html::button('<i class="fa fa-refresh"> Reset </i>', [
                    'class' => 'btn btn-basic',
                    'title' => 'Reset Filter',
                    'onclick' => "window.location.href = '" . Url::to(['subscription/index']) . "';",
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
        'itemLabelSingle' => 'subscription',
        'itemLabelPlural' => 'Subscriptions'
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
        var filter_selector = '#subscription-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#subscription-grid" , function(event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#subscription-grid" , function(event) {
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
                    $("#subscription-grid").yiiGridView("applyFilter");
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
        var select_filter_selector = '#subscription-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#subscription-grid" , function(event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#subscription-grid" , function(event) {
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
                $("#subscription-grid").yiiGridView("applyFilter");
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