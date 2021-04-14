<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use kartik\editable\Editable;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-index table-responsive">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $gridColumns = [
        //['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'id',
            'value' => function ($model) {
                return $model->id;
            },
            'header' => 'Order ID',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($model) {
                return $model->created_at;
            },
            'header' => 'Order Date',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->user->first_name . " " . $model->user->last_name;
            },
            'header' => 'Customer Name',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->user->email;
            },
            'header' => 'Customer Email',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->user->mobile;
            },
            'header' => 'Customer Phone',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->userAddress->address . ", " . $model->userAddress->street . ", " . $model->userAddress->city . ", " . $model->userAddress->zip_code . ", " . $model->userAddress->state;
            },
            'header' => 'Customer Address',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => "{view}"
        ],
    ];

    echo GridView::widget([
        'id' => 'kv-grid-orders',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true, // pjax is set to always true for this demo
        'toolbar' => [
            [
                'content' => ""
//                    Html::button('<i class="fa fa-plus-circle"> Add User </i>', [
//                        'class' => 'btn btn-success',
//                        'title' => 'Add User',
//                        'onclick' => "window.location.href = '" . Url::to(['user/create']) . "';",
//                    ])
                ,
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
            'heading' => 'User',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'Order',
        'itemLabelPlural' => 'Orders',
    ]);
    ?>


</div>
