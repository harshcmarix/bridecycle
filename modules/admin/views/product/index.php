<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use \app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use yii\helpers\Url;
use app\models\ProductCategory;
use app\models\ProductImage;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-index">

    <?php
    $gridColumns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'category_id',
            'value' => function ($model) {
                return (!empty($model->category) && $model->category instanceof ProductCategory && !empty($model->category->name)) ? $model->category->name : "";
            },
            'filter' => $categories,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => 'Category',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'sub_category_id',
            'value' => function ($model) {
                return (!empty($model->subCategory) && $model->subCategory instanceof ProductCategory && !empty($model->subCategory->name)) ? $model->subCategory->name : "";
            },
            'filter' => $subCategories,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => 'Sub Category',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'name',
            'value' => function ($model) {
                return $model->name;
            },
            'header' => 'Name',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'format' => 'raw',
            'value' => function ($model) {
                $images = $model->productImages;
                $dataImages = [];
                foreach ($images as $imageRow) {

                    if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                        $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }

                    $dataImages[] = ['content' => Html::img($image_path, ['width' => '570', 'alt' => 'Product Image']),
                       // 'caption' => '<h4>Product Image</h4><p>This is the product caption text</p>',
                        'options' => ['interval' => '600']
                    ];
                }

                $result = "";
                if(!empty($dataImages)){
                    $result = \yii\bootstrap\Carousel::widget(
                        ['items' => $dataImages]
                    );
                }

                return $result;
            },
            'header' => 'Images',
            'headerOptions' => ['class' => 'kartik-sheet-style',],

        ],
        [
            'attribute' => 'number',
            'value' => function ($model) {
                return $model->number;
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'price',
            'value' => function ($model) {
                return $model->price;
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'option_size',
            'value' => function ($model) {
                return $model->option_size;
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'option_price',
            'value' => function ($model) {
                return $model->option_price;
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'option_conditions',
            'value' => function ($model) {
                return $model->option_conditions;
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'option_size',
            'value' => function ($model) {
                return $model->option_size;
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'option_show_only',
            'value' => function ($model) {
                return ($model->option_show_only == '1') ? "Yes" : "No";
            },
            'filter' => $searchModel->arrOptionIsShowOnly,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'available_quantity',
            'value' => function ($model) {
                return (!empty($model) && !empty($model->available_quantity)) ? $model->available_quantity : "-";
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'format' => ['raw'],
            'attribute' => 'is_top_selling',
            'value' => function ($model) {
                return Html::checkbox("", $model->is_top_selling, ['class' => 'topSelling', 'data-key' => $model->id]);
            },
            'filter' => $searchModel->arrIsTopSelling,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'format' => ['raw'],
            'attribute' => 'is_top_trending',
            'value' => function ($model) {
                return Html::checkbox("", $model->is_top_trending, ['class' => 'topTrending', 'data-key' => $model->id]);
            },
            'filter' => $searchModel->arrIsTopTrending,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],

        [
            'class' => 'kartik\grid\ActionColumn',
        ],
    ];

    echo GridView::widget([
        'id' => 'product-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
//        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
//        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
//        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true, // pjax is set to always true for this demo
        'toolbar' => [
            [
                'content' =>
                    Html::button('<i class="fa fa-plus-circle"> Add Product </i>', [
                        'class' => 'btn btn-success',
                        'title' => 'Add Product',
                        'onclick' => "window.location.href = '" . Url::to(['product/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}',
        ],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => 'Product',
        ],
        'emptyTextOptions' => [
            'class' => 'empty text-center'
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'Product',
        'itemLabelPlural' => 'Products',
    ]);
    ?>
</div>

<script type="text/javascript">
    $(document).on('change', '#productsearch-category_id', function () {
        var categoryId = $(this).val();
        $.ajax({
            type: "POST",
            url: '<?php echo Url::to(['product/get-sub-category-list', 'category_id' => ""]); ?>' + categoryId,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#productsearch-sub_category_id').html("");
                    $('#productsearch-sub_category_id').html(response.dataList);
                }
            }
        })
    });

    $(document).on('click', '.topSelling', function () {
        var id = $(this).attr('data-key');

        if ($(this).prop('checked') == true) {
            krajeeDialog.confirm('Are you sure you want to add this product to top selling?', function (out) {
                if (out) {
                    var is_top_selling = '1';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-selling']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_selling': is_top_selling
                        },
                        success: function (response) {
                            // location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
        } else {
            krajeeDialog.confirm('Are you sure you want to remove this product from top selling?', function (out) {
                if (out) {
                    var is_top_selling = '0';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-selling']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_selling': is_top_selling
                        },
                        success: function (response) {
                            // location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
        }
    });

    $(document).on('click', '.topTrending', function () {
        var id = $(this).attr('data-key');

        if ($(this).prop('checked') == true) {
            krajeeDialog.confirm('Are you sure you want to add this product to top trending?', function (out) {
                if (out) {
                    var is_top_trending = '1';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-trending']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_trending': is_top_trending
                        },
                        success: function (response) {
                            //location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
        } else {
            krajeeDialog.confirm('Are you sure you want to remove this product from top trending?', function (out) {
                if (out) {
                    var is_top_trending = '0';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-trending']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_trending': is_top_trending
                        },
                        success: function (response) {
                            //location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
        }
    });
</script>
