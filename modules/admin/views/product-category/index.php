<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\editable\Editable;
use app\models\ProductCategory;


$gridColumns = [
[
    'attribute' => 'id',
    'value' => function ($model) {
        return $model->id;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'image',
    'value' => function ($model) {
        return Html::img(Yii::getAlias('@productCategoryImageThumbAbsolutePath').'/'.$model->image,  ['class'=>'file-preview-image','width' => '40px','height' => '40px']);
    },
    'format' => 'raw',
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'name',
    'value' => function ($model) {
        return $model->name;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'parent_category_id',
    'value' => function ($model) {
        if($model->parent instanceof ProductCategory){
            return $model->parent->name;
        }
            return null;
    },
    'header'=>'',
    'filter'=>ArrayHelper::map($parent_category,'id','name'),
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'created_at',
    'value' => function ($model) {
        return $model->created_at;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
    'class' => 'kartik\grid\ActionColumn',
],

];
echo GridView::widget([
    'id' => 'kv-grid-demo',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'pjax' => true, // pjax is set to always true for this demo
    // set your toolbar
    'toolbar' =>  [
        [
            'content' =>
                Html::button('Add Category', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Category'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/product-category/create']) . "';",
                ]), 
            'options' => ['class' => 'btn-group mr-2']
        ],
        '{export}',
        '{toggleData}',
    ],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    // set export properties
    'export' => [
        'fontAwesome' => true
    ],
    // parameters from the demo form
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'responsive' => true,
    // 'hover' => $hover,
    // 'showPageSummary' => $pageSummary,
    'panel' => [
        'type' => GridView::TYPE_PRIMARY,
        'heading' => 'Sub Admin',
    ],
    'persistResize' => false,
    'toggleDataOptions' => ['minCount' => 10],
    //'exportConfig' => $exportConfig,
    'itemLabelSingle' => 'Product Category',
    'itemLabelPlural' => 'Product Categories'
]);


?>

