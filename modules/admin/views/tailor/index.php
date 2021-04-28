<?php

use yii\helpers\Html;

use \app\modules\admin\widgets\GridView;
use yii\bootstrap\Modal;
use app\models\Tailor;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel app\models\search\TailorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tailors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tailor-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'tailor-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
             [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof Tailor) {
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
                'attribute' => 'shop_image',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->shop_image) && file_exists(Yii::getAlias('@tailorShopImageRelativePath') . '/' . $model->shop_image)) {
                        $image_path = Yii::getAlias('@tailorShopImageThumbAbsolutePath') . '/' . $model->shop_image;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'tailorimagemodal_' . $model->id,
                        'header' => '<h3>Shop Image</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $tailorimagemodal = "tailorimagemodal('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $tailorimagemodal, 'height' => '100px', 'width' => '100px']);
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
             [
                'attribute' => 'name',
                'value' => function ($model) {
                    $name = '';
                    if ($model instanceof Tailor) {
                        $name = $model->name;
                    }
                    return $name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
             [
                'attribute' => 'shop_name',
                'value' => function ($model) {
                    $shop_name = '';
                    if ($model instanceof Tailor) {
                        $shop_name = $model->shop_name;
                    }
                    return $shop_name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'mobile',
                'value' => function ($model) {
                    $mobile = '';
                    if ($model instanceof Tailor) {
                        $mobile = $model->mobile;
                    }
                    return $mobile;
                },
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
                    Html::button('<i class="fa fa-plus-circle"> Add Tailor</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Tailor'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/tailor/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
             [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['tailor/index']) . "';",
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
            'heading' => 'Tailors',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'tailor',
        'itemLabelPlural' => 'Tailors'
    ]); ?>


</div>
<script type="text/javascript">
    function tailorimagemodal(id) {
        $('#tailorimagemodal_' + id).modal('show');
    }
</script>
