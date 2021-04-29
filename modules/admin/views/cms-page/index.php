<?php

use yii\helpers\{
    Html,
    Url
};
use \app\modules\admin\widgets\GridView;
use app\models\CmsPage;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CmsPageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Cms Pages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cms-page-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    echo GridView::widget([
        'id' => 'cms-page-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof CmsPage) {
                        $id = $model->id;
                    }
                    return $id;
                },
                'width' => '8%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],           
            [
                'attribute' => 'title',
                'value' => function ($model) {
                    $title = '';
                    if ($model instanceof CmsPage) {
                        $title = $model->title;
                    }
                    return $title;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'format' => 'html',
                'attribute' => 'description',
                'value' => function ($model) {
                    $description = '';
                    if ($model instanceof CmsPage) {
                         $description = strlen($model->description) > CmsPage::MAX_DESCRIPTION_TEXT ? substr($model->description,CmsPage::MIN_DESCRIPTION_TEXT,CmsPage::MAX_DESCRIPTION_TEXT)."..." : $model->description;
                        // $description = str_limit($model->description,100);
                    }
                    return $description;
                },
                // 'format' => ['raw'],
                'filter'=>false,
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
           
            // [
            //     'attribute' => 'created_at',
            //     'value' => function ($model) {
            //         $created_at = '';
            //         if ($model instanceof CmsPage) {
            //             $created_at = $model->created_at;
            //         }
            //         return $created_at;
            //     },
            //     'filter' => false,
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
                    Html::button('<i class="fa fa-plus-circle"> Add Content</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Content'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/cms-page/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['cms-page/index']) . "';",
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
            'heading' => 'Contents',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'content',
        'itemLabelPlural' => 'Contents'
    ]);


    ?>


</div>
