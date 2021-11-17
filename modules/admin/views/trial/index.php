<?php

use app\models\Trial;
use app\modules\admin\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\TrialSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Trials';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>

            <?php $trial = new Trial(); ?>

            <?= GridView::widget([
                'id' => 'trial-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn'],
                    [
                        'attribute' => 'name',
                        'value' => function ($model) {
                            return $model->name;
                        },
                        'header' => 'Customer',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'product_id',
                        'value' => function ($model) {
                            $productName = "";
                            if (!empty($model->product) && $model->product instanceof \app\models\Product && !empty($model->product->name)) {
                                $productName = $model->product->name;
                            }
                            return $productName;
                        },
                        'filter' => $product,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],

                        'header' => 'Product',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'sender_id',
                        'value' => function ($model) {
                            $senderName = "";
                            if (!empty($model->sender) && !empty($model->sender->first_name)) {
                                $senderName = $model->sender->first_name . ' ' . $model->sender->last_name;
                            }
                            return $senderName;
                        },
                        'filter' => '',
                        'header' => 'Sender User',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'receiver_id',
                        'value' => function ($model) {
                            $receiverName = "";
                            if (!empty($model->receiver) && !empty($model->receiver->first_name)) {
                                $receiverName = $model->receiver->first_name . ' ' . $model->receiver->last_name;
                            }
                            return $receiverName;
                        },
                        'filter' => '',
                        'header' => 'Store',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    //                    [
                    ////                        'format' => ['raw'],
                    //                        'attribute' => 'status',
                    //                        'value' => function ($model) {
                    //                            if ($model instanceof Trial) {
                    //                                $trial = new Trial();
                    //
                    //                                return Html::dropDownList('status', $model->status, [
                    //                                    Trial::STATUS_PENDING => $trial->arrTrialStatus[Trial::STATUS_PENDING],
                    //                                    Trial::STATUS_ACCEPT => $trial->arrTrialStatus[Trial::STATUS_ACCEPT],
                    //                                    Trial::STATUS_REJECT => $trial->arrTrialStatus[Trial::STATUS_REJECT],
                    //                                ], ['class' => 'form-control trial-status-control', 'onchange' => 'changeStatus(this)', 'data-key' => $model->id]);
                    //                            }
                    //                        },
                    //                        'format' => ['raw'],
                    //                        'filter' => [
                    //                            Trial::STATUS_PENDING => $trial->arrTrialStatus[Trial::STATUS_PENDING],
                    //                            Trial::STATUS_ACCEPT => $trial->arrTrialStatus[Trial::STATUS_ACCEPT],
                    //                            Trial::STATUS_REJECT => $trial->arrTrialStatus[Trial::STATUS_REJECT]
                    //                        ],
                    //                        'filterType' => GridView::FILTER_SELECT2,
                    //                        'filterWidgetOptions' => [
                    //                            'options' => ['prompt' => 'Select'],
                    //                            'pluginOptions' => [
                    //                                'allowClear' => true,
                    //                            ],
                    //                        ],
                    //                        'header' => 'Status',
                    //                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    //                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            if ($model->status == Trial::STATUS_ACCEPT) {
                                $status = $model->arrTrialStatus[Trial::STATUS_ACCEPT];
                            } elseif ($model->status == Trial::STATUS_REJECT) {
                                $status = $model->arrTrialStatus[Trial::STATUS_REJECT];
                            } else {
                                $status = $model->arrTrialStatus[Trial::STATUS_PENDING];
                            }
                            return ucfirst($status);
                        },
                        'filter' => [
                            Trial::STATUS_PENDING => ucfirst($trial->arrTrialStatus[Trial::STATUS_PENDING]),
                            Trial::STATUS_ACCEPT => ucfirst($trial->arrTrialStatus[Trial::STATUS_ACCEPT]),
                            Trial::STATUS_REJECT => ucfirst($trial->arrTrialStatus[Trial::STATUS_REJECT])
                        ],
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
                        'attribute' => 'date',
                        'value' => function ($model) {
                            return $model->date;
                        },
                        'filter' => '',
                        'header' => 'Trial Date',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'time',
                        'value' => function ($model) {
                            return $model->time;
                        },
                        'filter' => '',
                        'header' => 'Trial Time',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    [
                        'attribute' => 'timezone_id',
                        'value' => function ($model) {
                            return (!empty($model->timezone) && $model->timezone->time_zone) ? $model->timezone->time_zone : "-";
                        },
                        'filter' => \yii\helpers\ArrayHelper::map(\app\models\Timezone::find()->all(), 'id', 'time_zone'),
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Trial Timezone',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                    ],
                    //                    [
                    //                        'class' => 'kartik\grid\ActionColumn',
                    //                        'template' => "{delete}"
                    //                    ],
                ],
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
                                'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['trial/index']) . "';",
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
                'itemLabelSingle' => 'trial',
                'itemLabelPlural' => 'Trials',
            ]); ?>
        </div>
    </div>
</div>

<script>
    function changeStatus($this) {
        var id = $this.getAttribute('data-key');
        var status = $($this.selectedOptions).text();
        krajeeDialog.confirm('Are you sure you want to change the status to ' + status + '?', function (out) {
            if (out) {
                $.ajax({
                    url: "<?php echo Url::to(['trial/update-status']); ?>",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        '_csrf': '<?php echo Yii::$app->request->getCsrfToken() ?>',
                        'id': id,
                        'status': $this.value
                    },
                    success: function (response) {
                        // location.reload(true);
                    }
                });
            } else {
                location.reload();
            }
        });
    }

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
        var filter_selector = '#trial-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#trial-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#trial-grid", function (event) {
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
                        $("#trial-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function () {
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
        var select_filter_selector = '#trial-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#trial-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#trial-grid", function (event) {
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
                    $("#trial-grid").yiiGridView("applyFilter");
                }
            })
            .on('pjax:success', function () {
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