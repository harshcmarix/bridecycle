<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\widgets\Pjax;
use kartik\editable\Editable;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ColorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Color';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <?php
        $colorPluginOptions = [
            'showPalette' => true,
            'showPaletteOnly' => true,
            'showSelectionPalette' => true,
            'showAlpha' => false,
            'allowEmpty' => false,
            'preferredFormat' => 'name',
            'palette' => [
                [
                    "white", "black", "grey", "silver", "gold", "brown",
                ],
                [
                    "red", "orange", "yellow", "indigo", "maroon", "pink"
                ],
                [
                    "blue", "green", "violet", "cyan", "magenta", "purple",
                ],
                [
                    "nevy blue",
                ],
            ]
        ];

        echo GridView::widget([
            'id' => 'color-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'id',
                    'header' => 'Color Id',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'name',
                    'header' => '',
                    'vAlign' => 'middle',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'code',
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        $status = "";
                        if ($model->status == \app\models\Color::STATUS_PENDING_APPROVAL) {
                            $status = "Pending Approval";
                        } elseif ($model->status == \app\models\Color::STATUS_APPROVE) {
                            $status = "Approved";
                        } elseif ($model->status == \app\models\Color::STATUS_DECLINE) {
                            $status = "Decline";
                        }
                        return $status;
                    },
                    'filter' => $arrStatus,
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
                    'class' => 'kartik\grid\ActionColumn',
                ],
            ],

            'pjax' => true, // pjax is set to always true for this demo
            // set your toolbar
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fa fa-plus-circle"> Add Color</i>', [
                            'class' => 'btn btn-success',
                            'title' => \Yii::t('kvgrid', 'Add Color'),
                            'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/color/create']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['color/index']) . "';",
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
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                //'heading' => 'Colors',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'color',
            'itemLabelPlural' => 'Colors'
        ]);
        ?>

    </div>
</div>

<script>
    $('document').ready(function(){
        var input;
        var submit_form = false;
        var filter_selector = '#color-grid-filters input';

        $("body").on('beforeFilter', "#color-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#color-grid" , function(event) {
            submit_form = false;
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', filter_selector)
            .on('keyup', filter_selector, function(e) {
                input = $(this).attr('name');
                var keyCode = e.keyCode ? e.keyCode : e.which;
                if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                    if (submit_form === false) {
                        submit_form = true;
                        $("#color-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function() {
                var i = $("[name='"+input+"']");
                var val = i.val();
                i.focus().val(val);

                var searchInput = $(i);
                var strLength = searchInput.val().length * 2;
                searchInput[0].setSelectionRange(strLength, strLength);
            });
    });
</script>