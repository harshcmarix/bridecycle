<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use app\models\Brand;

/* @var $this yii\web\View */
/* @var $model app\models\Brand */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Brands', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="brand-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
             [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if($model instanceof Brand){
                        $id = $model->id;
                    }
                    return $id;
                },
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],   
            [
                'attribute' => 'name',
                'value' => function ($model) {
                    $name = '';
                    if($model instanceof Brand){
                        $name = $model->name;
                    }
                    return $name;
                },
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],                
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
                    $is_top_brand = '';
                    if($model instanceof Brand){
                        $is_top_brand = Brand::IS_TOP_BRAND_OR_NOT[$model->is_top_brand];
                    }
                    return $is_top_brand;
                },
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    $created_at = '';
                    if($model instanceof Brand){
                        $created_at = $model->created_at;
                    }
                    return $created_at;
                },
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
            [
                'attribute' => 'updated_at',
                'value' => function ($model) {
                    $updated_at = '';
                    if($model instanceof Brand){
                        $updated_at = $model->updated_at;
                    }
                    return $updated_at;
                },
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
                        ],
    ]) ?>
    <p>
       <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>

</div>
<script type="text/javascript">
    function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>