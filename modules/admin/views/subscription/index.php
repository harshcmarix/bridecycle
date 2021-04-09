<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\editable\Editable;
use app\models\Subscription;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SubscriptionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="subscription-index">
<?php

$gridColumns = [
[
'class' => 'kartik\grid\SerialColumn',
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
 'attribute' => 'amount',
    'value' => function ($model) {
        return $model->amount;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'status',
    'value' => function ($model) {
            return Subscription::SUBSCRIPTION_STATUS_ARRAY[$model->status];
    },
    
    'filter'=>Subscription::SUBSCRIPTION_STATUS_ARRAY,
    'filterType' => GridView::FILTER_SELECT2,
    'filterWidgetOptions' => [
        'options' => ['prompt' => ''],
        'pluginOptions' => [
            'allowClear' => true,
            // 'width'=>'20px'
        ],
    ],
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'created_at',
    'value' => function ($model) {
        return $model->created_at;
    },
    'filter'=>false,
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
                Html::button('Add Subscription', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Subscription'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/subscription/create']) . "';",
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
    // 'hover' => $hover,
    // 'showPageSummary' => $pageSummary,
    'panel' => [
        'type' => GridView::TYPE_PRIMARY,
        'heading' => 'Subscriptions',
    ],
    'persistResize' => false,
    'toggleDataOptions' => ['minCount' => 10],
    //'exportConfig' => $exportConfig,
    'itemLabelSingle' => 'Subscription',
    'itemLabelPlural' => 'Subscriptions'
]);
    ?>


</div>
