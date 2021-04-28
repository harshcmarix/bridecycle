<?php

use yii\helpers\{
    Html,
    Url
};
use \app\modules\admin\widgets\GridView;
use app\models\Banner;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Search\BannerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Banners';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="banner-index">

    <h1><?= Html::encode($this->title) ?></h1>

 <?php
    echo GridView::widget([
        'id' => 'brand-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof Banner) {
                        $id = $model->id;
                    }
                    return $id;
                },
                'width' => '8%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'format' => ['raw'],
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'image',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->image) && file_exists(Yii::getAlias('@bannerImageRelativePath') . '/' . $model->image)) {
                        $image_path = Yii::getAlias('@bannerImageThumbAbsolutePath') . '/' . $model->image;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'bannermodal_' . $model->id,
                        'header' => '<h3>Banner Image</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $bannermodal = "bannermodal('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $bannermodal, 'height' => '100px', 'width' => '100px']);
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
           
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    $created_at = '';
                    if ($model instanceof Banner) {
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
                    Html::button('<i class="fa fa-plus-circle"> Add Banner</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Banner'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/banner/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['banner/index']) . "';",
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
            'heading' => 'Banners',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'banner',
        'itemLabelPlural' => 'Banners'
    ]);


    ?>


</div>
<script type="text/javascript">
    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
    }
</script>
