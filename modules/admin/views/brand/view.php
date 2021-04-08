<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Brand */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Brands', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="brand-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',                 
            [
                'format' => ['raw'],
                'attribute' => 'image',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $model->image)) {
                        $image_path = Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $model->image;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'brandmodal_' . $model->id,
                        'header' => '<h3>Brand Image</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $brandmodal = "brandmodal('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $brandmodal, 'height' => '100px', 'width' => '100px']);
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'is_top_brand',
                'value' => function ($model) {
                    if(!empty($model->is_top_brand) && $model->is_top_brand == '1'){
                            return 'yes';
                    }
                    return 'no';
                },
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
                'created_at',
                'updated_at',
                        ],
    ]) ?>

</div>
<script type="text/javascript">
    function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>