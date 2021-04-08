<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\editable\Editable;


$gridColumns = [
[
'class' => 'kartik\grid\SerialColumn',
],
// [
//     'attribute' => 'id',
//     'value' => function ($model) {
//         return $model->id;
//     },
//     'header'=>'',
//     'headerOptions'=>['class'=>'kartik-sheet-style']
// ],
[
 'attribute' => 'first_name',
    'value' => function ($model) {
        return $model->first_name;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'last_name',
    'value' => function ($model) {
        return $model->last_name;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'email',
    'value' => function ($model) {
        return $model->email;
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
                Html::button('Add Sub Admin', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Sub Admin'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/sub-admin/create']) . "';",
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
    'itemLabelSingle' => 'Sub Admin',
    'itemLabelPlural' => 'Sub Admins'
]);


?>