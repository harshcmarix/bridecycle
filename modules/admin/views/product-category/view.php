<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\ProductCategory;
/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Product Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-category-view">

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
                'attribute' => 'image',
                'value' => function ($model) {
                    return Html::img(Yii::getAlias('@productCategoryImageThumbAbsolutePath').'/'.$model->image,  ['class'=>'file-preview-image']);
                },
                'format' => 'raw',
            ],
            [
                'attribute'=>'product_category_id',
                'value' => function ($model) {
                    if($model->parent instanceof ProductCategory){
                        return $model->parent->name;
                    }
                        return null;
                },
            ],
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>