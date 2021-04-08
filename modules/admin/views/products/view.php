<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Products */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="products-view">

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
            'number',
            'category_id',
            'sub_category_id',
            'price',
            'option_size',
            'option_price',
            'option_conditions',
            'option_show_only',
            'description:ntext',
            'available_quantity',
            'is_top_selling',
            'is_top_trending',
            'brand_id',
            'gender',
            'is_cleaned',
            'height',
            'weight',
            'width',
            'receipt',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
