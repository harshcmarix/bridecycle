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

$this->title = 'View Banner';
$this->params['breadcrumbs'][] = ['label' => 'Banners', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'name',
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->image) && file_exists(Yii::getAlias('@bannerImageRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@bannerImageAbsolutePath') . '/' . $model->image;
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
//                [
//                    'attribute' => 'brand_id',
//                    'value' => function ($model) {
//                        return (!empty($model) && $model instanceof Banner && !empty($model->brand_id) && !empty($model->brand) && $model->brand instanceof \app\models\Brand) ? $model->brand->name : "";
//                    },
//                ],
            ],
        ]) ?>
    </div>
    <div class="box-footer">
        <p>
            <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        </p>
    </div>
</div>
<script type="text/javascript">
    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
    }
</script>
