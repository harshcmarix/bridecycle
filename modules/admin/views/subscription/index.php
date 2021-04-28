<?php

use \app\modules\admin\widgets\GridView;
use yii\helpers\{
    Html,
    ArrayHelper,
    Url
};
use kartik\editable\Editable;
use app\models\Subscription;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SubscriptionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Subscription';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subscription-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php

    $gridColumns = [


    ];
    echo GridView::widget([
        'id' => 'subscription-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof Subscription) {
                        $id = $model->id;
                    }
                    return $id;
                },
                'width' => '8%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'name',
                'value' => function ($model) {
                    $name = '';
                    if ($model instanceof Subscription) {
                        $name = $model->name;
                    }
                    return $name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'amount',
                'value' => function ($model) {
                    $amount = '';
                    if ($model instanceof Subscription) {
                        $amount = $model->amount;
                    }
                    return $amount;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $status = '';
                    if ($model instanceof Subscription) {
                        $status = Subscription::SUBSCRIPTION_STATUS_ARRAY[$model->status];
                    }
                    return $status;
                },

                'filter' => Subscription::SUBSCRIPTION_STATUS_ARRAY,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => ''],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width'=>'20px'
                    ],
                ],
                'width' => '10%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    $created_at = '';
                    if ($model instanceof Subscription) {
                        $created_at = $model->created_at;
                    }
                    return $created_at;
                },
                'filter' => false,
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
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
                    Html::button('<i class="fa fa-plus-circle"> Add Subscription</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Subscription'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/subscription/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['subscription/index']) . "';",
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
        // 'hover' => $hover,
        // 'showPageSummary' => $pageSummary,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => 'Subscriptions',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        //'exportConfig' => $exportConfig,
        'itemLabelSingle' => 'subscription',
        'itemLabelPlural' => 'Subscriptions'
    ]);
    ?>


</div>
