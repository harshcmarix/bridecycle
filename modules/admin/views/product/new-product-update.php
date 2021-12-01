<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

//$this->title = 'Update Product: ' . $model->name;
$this->title = 'Update New Product';
$this->params['breadcrumbs'][] = ['label' => 'New Products', 'url' => ['new-product']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update New Product';
?>
<div class="products-update">

    <?= $this->render('_new-product-form', [
        'model' => $model,
        'category' => $category,
        'subcategory' => $subcategory,
        'size' => $size,
        'brand' => $brand,
        'color' => $color,
        'status' => $status,
        'shippingCountry' => $shippingCountry,
        'shippingPrice' => $shippingPrice
    ]) ?>

</div>
