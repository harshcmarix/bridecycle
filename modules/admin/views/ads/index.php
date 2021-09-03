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

            <?php
            echo GridView::widget([
                'id' => 'ads-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'value' => function ($model) {
                            $id = '';
                            if ($model instanceof Ads) {
                                $id = $model->id;
                            }
                            return $id;
                        },
                        'width' => '8%',
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;'],
                    ],
                    [
                        'attribute' => 'title',
                        'value' => function ($model) {

                            return $model->title;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
                    ],
                    [
                        'format' => ['raw'],
                        'enableSorting' => false,
                        'filter' => false,
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@adsImageThumbRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@adsImageThumbAbsolutePath') . '/' . $model->image;
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
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $adsmodal, 'height' => '100px', 'width' => '100px']);
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
                    ],
                    [
                        'attribute' => 'url',
                        'value' => function ($model) {
                            $url = '';
                            if ($model instanceof Ads) {
                                $url = $model->url;
                            }
                            return $url;
                        },
                        'width' => '20%',
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
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
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
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
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
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
                        'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important;']
                    ],
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
                    //'heading' => 'Ads',
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

    $('document').ready(function(){
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
                        $("#ads-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function() {
                if (isInput) {
                    var i = $("[name='" + input + "']");
                    var val = i.val();
                    i.focus().val(val);

                    var searchInput = $(i);
                    var strLength = searchInput.val().length * 2;
                    searchInput[0].setSelectionRange(strLength, strLength);
                }
            });
    });
</script>