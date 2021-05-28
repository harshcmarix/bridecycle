<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\widgets\Pjax;
use kartik\editable\Editable;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ColorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Color';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="color-index table-responsive">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $colorPluginOptions =  [
        'showPalette' => true,
        'showPaletteOnly' => true,
        'showSelectionPalette' => true,
        'showAlpha' => false,
        'allowEmpty' => false,
        'preferredFormat' => 'name',
        'palette' => [
            [
                "white", "black", "grey", "silver", "gold", "brown",
            ],
            [
                "red", "orange", "yellow", "indigo", "maroon", "pink"
            ],
            [
                "blue", "green", "violet", "cyan", "magenta", "purple",
            ],
            [
                "nevy blue",
            ],
        ]
    ];

    echo GridView::widget([
        'id' => 'color-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'header' => 'Color Id',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'name',
                'header' => '',
                'vAlign' => 'middle',
                'format' => 'raw',
            ],
            [
                'attribute' => 'code',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
            ],
        ],

        'pjax' => true, // pjax is set to always true for this demo
        // set your toolbar
        'toolbar' => [
            [
                'content' =>
                    Html::button('<i class="fa fa-plus-circle"> Add Color</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Color'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/color/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['color/index']) . "';",
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
            'heading' => 'Colors',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'color',
        'itemLabelPlural' => 'Colors'
    ]);
    ?>
</div>
