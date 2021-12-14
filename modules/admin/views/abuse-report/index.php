<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\AbuseReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Abuse Reports';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">


            <?= GridView::widget([
                'id' => 'abuse-report-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'user_id',
                        'value' => function ($model) {
                            return (!empty($model) && !empty($model->user) && $model->user instanceof \app\modules\api\v2\models\User && !empty($model->user->first_name)) ? $model->user->first_name . " " . $model->user->last_name . " (" . $model->user->email . ")" : "user";
                        },
                        'filter' => $users,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select User'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'User',
                        //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
                    ],
                    [
                        'attribute' => 'seller_id',
                        'value' => function ($model) {
                            $status = "In-active";
                            if (!empty($model) && !empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User && $model->seller->user_status == \app\modules\api\v2\models\User::USER_STATUS_ACTIVE) {
                                $status = "Active";
                            }
                            return (!empty($model) && !empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User && !empty($model->seller->first_name)) ? $model->seller->first_name . " " . $model->seller->last_name . " (" . $model->seller->email . ")(Status:" . $status . ")" : "seller";
                        },
                        'filter' => $sellers,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select Seller'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Seller',
                    ],
                    [
                        'format' => 'html',
                        'attribute' => 'content',
                        'header' => '',
                    ],
                    [
                        'attribute' => 'created_at',
                        'filter' => false,
                        'header' => '',
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => "{view} {delete}"
                    ],
                ],

                'pjax' => true,
                // set your toolbar
                'toolbar' => [
                    [
                        'content' =>
                            Html::button('<i class="fa fa-refresh"> Reset </i>', [
                                'class' => 'btn btn-basic',
                                'title' => 'Reset Filter',
                                'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['abuse-report/index']) . "';",
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
                ],
                'persistResize' => false,
                'toggleDataOptions' => ['minCount' => 10],
                'itemLabelSingle' => 'abuse report',
                'itemLabelPlural' => 'Abuse Reports'
            ]); ?>
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

    $('document').ready(function () {
        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');

        var input;
        var submit_form = false;
        var filter_selector = '#abuse-report-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#abuse-report-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#abuse-report-grid", function (event) {
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
                        $("#abuse-report-grid").yiiGridView("applyFilter");
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
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
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
        var select_filter_selector = '#abuse-report-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#abuse-report-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#abuse-report-grid", function (event) {
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
                    $("#abuse-report-grid").yiiGridView("applyFilter");
                }
            })
            .on('pjax:success', function () {
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
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
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
    });

</script>