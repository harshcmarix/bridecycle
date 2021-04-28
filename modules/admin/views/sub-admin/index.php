<?php

use yii\helpers\{
    Html,
    ArrayHelper,
    Url
};
use \app\modules\admin\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\SubAdminSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sub Admin';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="sub-admin-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    echo GridView::widget([
        'id' => 'sub-admin-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    return $model->id;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'width' => '8%'
            ],
            [
                'attribute' => 'first_name',
                'value' => function ($model) {
                    return $model->first_name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'last_name',
                'value' => function ($model) {
                    return $model->last_name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'email',
                'value' => function ($model) {
                    return $model->email;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '12%'
            ],
        ], // check the configuration for grid columns by clicking button above
        'pjax' => true, // pjax is set to always true for this demo
        // set your toolbar
        'toolbar' => [
            [
                'content' =>
                    Html::button('<i class="fa fa-plus-circle"> Add Sub Admin</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Sub Admin'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/sub-admin/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
             [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['sub-admin/index']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}',
        ],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => false,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => 'Sub Admin',
        ],
        'emptyTextOptions' => [
            'class' => 'empty text-center'
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'Sub Admin',
        'itemLabelPlural' => 'Sub Admins'
    ]);
    ?>
</div>
