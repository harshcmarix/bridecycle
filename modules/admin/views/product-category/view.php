<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;
use app\models\ProductCategory;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Product Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-category-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
            'attribute' => 'id',
            'value' => function ($model) {
                $id = '';
                if($model instanceof ProductCategory)
                {
                   $id = $model->id;
                }
                return $id;
             },
            ],
            [
            'format' => ['raw'],
            'attribute' => 'image',
            'value' => function ($model) {
                $image_path = "";
                if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
                    $image_path = Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $model->image;
                } else {
                    $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                }
                Modal::begin([
                    'id' => 'productcategorymodal_' . $model->id,
                    'header' => '<h3>Category Image</h3>',
                    'size' => Modal::SIZE_DEFAULT
                ]);

                echo Html::img($image_path, ['width' => '570']);

                Modal::end();
                $productcategorymodal = "productcategorymodal('" . $model->id . "');";
                return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $productcategorymodal, 'height' => '100px', 'width' => '100px']);
            },
            'header' => '',
            'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
           [
            'attribute' => 'name',
                'value' => function ($model) {
                    $name = '';
                    if($model instanceof ProductCategory){
                        $name = $model->name;
                    }
                    return $name;
                },
            ],
            [
                'attribute'=>'product_category_id',
                'label'=>'Parent Category',
                 'value' => function ($model) {
                    $parent_name = '';
                    if($model->parent instanceof ProductCategory){
                        $parent_name = $model->parent->name;
                    }
                    return $parent_name;
                },
            ],
            [
            'attribute' => 'created_at',
                'value' => function ($model) {
                    $created_at = '';
                     if($model instanceof ProductCategory){
                        $created_at = $model->created_at;
                     }
                     return $created_at;
                },
               
            ],
             [
            'attribute' => 'updated_at',
                'value' => function ($model) {
                    $updated_at = '';
                     if($model instanceof ProductCategory){
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
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }
</script>