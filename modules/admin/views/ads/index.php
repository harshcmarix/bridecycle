<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use app\models\Ads;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\AdsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ads';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

            <?=
            GridView::widget([
                'id' => 'ads-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn'],
                    [
                        'attribute' => 'title',
                        'value' => function ($model) {

                            return $model->title;
                        },
                        'header' => '',
                    ],
                    [
                        'format' => ['raw'],
                        'enableSorting' => false,
                        'filter' => false,
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@adsImageRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@adsImageAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            Modal::begin([
                                'id' => 'adsmodal_' . $model->id,
                                'header' => '<h3>Ads Image</h3>',
                                'size' => Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            Modal::end();
                            $adsmodal = "adsmodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $adsmodal, 'height' => '50px', 'width' => '50px']);
                        },
                        'header' => '',
                    ],
//                    [
//                        'attribute' => 'url',
//                        'value' => function ($model) {
//                            $url = '';
//                            if ($model instanceof Ads) {
//                                $url = $model->url;
//                            }
//                            return $url;
//                        },
//                        'header' => '',
//                    ],

                    [
                        'attribute' => 'category_id',
                        'value' => function ($model) {
                            $categoryName = "";
                            if (!empty($model->category) && $model->category instanceof \app\models\ProductCategory && !empty($model->category->name)) {
                                $categoryName = $model->category->name;
                            }
                            return $categoryName;
                        },
                        'filter' => $category,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Category',
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
                    ],
                    [
                        'attribute' => 'brand_id',
                        'value' => function ($model) {
                            $brandName = "";
                            if (!empty($model->brand) && $model->brand instanceof \app\models\Brand && !empty($model->brand->name)) {
                                $brandName = $model->brand->name;
                            }
                            return $brandName;
                        },
                        'filter' => $brand,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Brand',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == Ads::STATUS_INACTIVE) {
                                $status = "Inactive";
                            } elseif ($model->status == Ads::STATUS_ACTIVE) {
                                $status = "Active";
                            }
                            return $status;
                        },
                        'filter' => Ads::ARR_ADS_STATUS,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Status',
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
                        Html::button('<i class="fa fa-plus-circle"> Add Ads</i>', [
                            'class' => 'btn btn-success',
                            'title' => \Yii::t('kvgrid', 'Add Ads'),
                            'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/ads/create']) . "';",
                        ]),
                        'options' => ['class' => 'btn-group mr-2']
                    ],
                    [
                        'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['ads/index']) . "';",
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
                'itemLabelSingle' => 'ads',
                'itemLabelPlural' => 'Ads'
            ]);
            ?>
        </div>
    </div>
</div>

<script>
    function adsmodal(id) {
        $('#adsmodal_' + id).modal('show');
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
        var filter_selector = '#ads-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#ads-grid" , function(event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#ads-grid" , function(event) {
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
                    setTimeout( function(){
                        $("#ads-grid").yiiGridView("applyFilter");
                    }  , 600);
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
                    $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
                }

                $('.pagination').find('li a').on('click', function () {
                    setTimeout(function () {
                        $(document).scrollTop($(document).innerHeight());
                    }, 200);
                });
            }
        });

        //select box filter
        var select;
        var submit_form = false;
        var select_filter_selector = '#ads-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#ads-grid" , function(event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#ads-grid" , function(event) {
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
                    $("#ads-grid").yiiGridView("applyFilter");
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
                    });
                }
            });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    });
</script>