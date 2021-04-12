<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\helpers\Url;
use app\models\ProductCategory;

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
                return Html::checkbox("", $model->is_top_selling);
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
                return Html::checkbox("", $model->is_top_trending);
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
        'id' => 'kv-grid-demo-product',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true, // pjax is set to always true for this demo
        'toolbar' => [
            [
                'content' =>
                    Html::button('Add Product', [
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
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'Product',
        'itemLabelPlural' => 'Products',
    ]);
    ?>
</div>
