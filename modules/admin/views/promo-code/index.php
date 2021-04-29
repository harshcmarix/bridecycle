<?php

use yii\helpers\{
    Html,
    Url
};
use \app\modules\admin\widgets\GridView;
use app\models\PromoCode;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\PromoCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Promo Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promo-code-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
          'id' => 'promo-code-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
             [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof PromoCode) {
                        $id = $model->id;
                    }
                    return $id;
                },
                'width' => '8%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
             [
                'attribute' => 'code',
                'value' => function ($model) {
                    $code = '';
                    if ($model instanceof PromoCode) {
                        $code = $model->code;
                    }
                    return $code;
                },
               
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            //  [
            //     'attribute' => 'created_at',
            //     'value' => function ($model) {
            //         $created_at = '';
            //         if ($model instanceof PromoCode) {
            //             $created_at = $model->created_at;
            //         }
            //         return $created_at;
            //     },
               
            //     'filter'=>false,
            //     'header' => '',
            //     'headerOptions' => ['class' => 'kartik-sheet-style']
            // ],

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
                    Html::button('<i class="fa fa-plus-circle"> Add Promo Code</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Promo Code'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/promo-code/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['promo-code/index']) . "';",
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
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => 'Promo Codes',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'promo code',
        'itemLabelPlural' => 'Promo Codes'
    ]); ?>


</div>
