<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use app\models\Banner;

/* @var $this yii\web\View */
/* @var $model app\models\Banner */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Banners', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="banner-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof Banner) {
                        $id = $model->id;
                    }
                    return $id;
                },
                
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
               
            ],
             [
                'attribute' => 'updated_at',
                'value' => function ($model) {
                    $updated_at = '';
                    if ($model instanceof Banner) {
                        $updated_at = $model->updated_at;
                    }
                    return $updated_at;
                },
               
            ],
        ],
    ]) ?>
     <p>
       <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>

</div>
<script type="text/javascript">
    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
    }
</script>
