<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Create New Product';
$this->params['breadcrumbs'][] = ['label' => 'New Products', 'url' => ['new-product']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-create">

    <?= $this->render('_new-product-form', [
        'model' => $model,
        'category' => $category,
        'subcategory' => $subcategory,
        'size' => $size,
        'brand' => $brand,
        'color' => $color,
        'status' => $status,
        'shippingCountry'=>$shippingCountry,
        'shippingPrice'=>$shippingPrice
    ]) ?>

</div>
