<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Search\BannerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Banners';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

            <?php
            echo GridView::widget([
                'id' => 'banners-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,

                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn'],
                    [
                        'attribute' => 'name',
                        'value' => function ($model) {

                            return $model->name;
                        },
                        'header' => '',
                    ],
                    [
                        'format' => ['raw'],
                        'enableSorting' => false,
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@bannerImageRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@bannerImageAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            Modal::begin([
                                'id' => 'bannermodal_' . $model->id,
                                'header' => '<h3>Banner Image</h3>',
                                'size' => Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            Modal::end();
                            $bannermodal = "bannermodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $bannermodal, 'height' => '50px', 'width' => '50px']);
                        },
                        'filter' => false,
                        'width' => '20%',
                        'header' => '',
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'width' => '10%'
                    ],
                ],

                'pjax' => true, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' => [
                    [
                        'content' =>
                            Html::button('<i class="fa fa-plus-circle"> Add Banner</i>', [
                                'class' => 'btn btn-success',
                                'title' => \Yii::t('kvgrid', 'Add Banner'),
                                'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/banner/create']) . "';",
                            ]),
                        'options' => ['class' => 'btn-group mr-2']
                    ],
                    [
                        'content' =>
                            Html::button('<i class="fa fa-refresh"> Reset </i>', [
                                'class' => 'btn btn-basic',
                                'title' => 'Reset Filter',
                                'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['banner/index']) . "';",
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
                'itemLabelSingle' => 'banner',
                'itemLabelPlural' => 'Banners'
            ]);
            ?>
        </div>
    </div>
</div>

<script>
    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
    }

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
        var filter_selector = '#banners-grid-filters input';

        $("body").on('beforeFilter', "#banners-grid", function (event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#banners-grid", function (event) {
            submit_form = false;
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', filter_selector)
            .on('keyup', filter_selector, function (e) {
                input = $(this).attr('name');
                var keyCode = e.keyCode ? e.keyCode : e.which;
                if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                    if (submit_form === false) {
                        submit_form = true;
                        $("#banners-grid").yiiGridView("applyFilter");
                    }
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

                if ($('thead td i').length == 0) {
                    $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
                }

                $('.pagination').find('li a').on('click', function () {
                    setTimeout(function () {
                        $(document).scrollTop($(document).innerHeight());
                    }, 200);
                })
            });

        //select box filter
        var select;
        var submit_form = false;
        var select_filter_selector = '#banners-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#banners-grid" , function(event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#banners-grid" , function(event) {
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
                    $("#banners-grid").yiiGridView("applyFilter");
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
