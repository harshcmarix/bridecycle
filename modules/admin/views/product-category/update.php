<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */

$this->title = 'Update Product Category';
$this->params['breadcrumbs'][] = ['label' => 'Product Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'parent_category'=> $parent_category,
    ]) ?>

</div>