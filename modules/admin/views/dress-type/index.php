<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\DressTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Dress Type';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="career-index box box-primary">
    <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">
        <?php
        echo GridView::widget([
            'id' => 'dress-type-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                // [
                //     'attribute' => 'id',
                //     'header' => 'Id',
                //     'width' => '8%',
                //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                // ],
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'header' => '',
                    'vAlign' => 'middle',
                    'format' => 'raw',
                    'width' => '65%',
                    //'headerOptions' => ['style' => 'text-align: center !important']
                ],
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->image) && file_exists(Yii::getAlias('@dressTypeImageRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@dressTypeImageAbsolutePath') . '/' . $model->image;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        Modal::begin([
                            'id' => 'dresstypemodal_' . $model->id,
                            'header' => '<h3>Dress Type Icon</h3>',
                            'size' => Modal::SIZE_SMALL
                        ]);

                        echo Html::img($image_path, ['width' => '50', 'class' => 'text-center']);

                        Modal::end();
                        $dresstypemodal = "dresstypemodal('" . $model->id . "');";
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $dresstypemodal, 'height' => '50px', 'width' => '50px']);
                    },
                    'header' => '',
                    'width' => '20%',
                    //'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
//                [
//                    'attribute' => 'status',
//                    'value' => function ($model) {
//                        $status = "";
//                        if ($model->status == \app\models\Color::STATUS_PENDING_APPROVAL) {
//                            $status = "Pending Approval";
//                        } elseif ($model->status == \app\models\Color::STATUS_APPROVE) {
//                            $status = "Approved";
//                        } elseif ($model->status == \app\models\Color::STATUS_DECLINE) {
//                            $status = "Decline";
//                        }
//                        return $status;
//                    },
//                    'filter' => '',
//                    'filterType' => GridView::FILTER_SELECT2,
//                    'filterWidgetOptions' => [
//                        'options' => ['prompt' => 'Select'],
//                        'pluginOptions' => [
//                            'allowClear' => true,
//                        ],
//                    ],
//                    'header' => 'Status',
//                    'headerOptions' => ['class' => 'kartik-sheet-style']
//                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template'=>'{view} {delete}',
                    'width' => '10%'
                ],
            ],

            'pjax' => true, // pjax is set to always true for this demo
            // set your toolbar
            'toolbar' => [
//                [
//                    'content' =>
//                        Html::button('<i class="fa fa-plus-circle"> Add Dress Type</i>', [
//                            'class' => 'btn btn-success',
//                            'title' => \Yii::t('kvgrid', 'Add Dress Type'),
//                            'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/dress-type/create']) . "';",
//                        ]),
//                    'options' => ['class' => 'btn-group mr-2']
//                ],
                [
                    'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['dress-type/index']) . "';",
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
                //'heading' => 'Dress Types',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'Dress Type',
            'itemLabelPlural' => 'Dress Type'
        ]);
        ?>
    </div>
</div>

<script type="text/javascript">
    function dresstypemodal(id) {
        $('#dresstypemodal_' + id).modal('show');
    }

    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function(){
        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');

        var input;
        var submit_form = false;
        var filter_selector = '#dress-type-grid-filters input';

        $("body").on('beforeFilter', "#dress-type-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#dress-type-grid" , function(event) {
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
                    $("#dress-type-grid").yiiGridView("applyFilter");
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
                    $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
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