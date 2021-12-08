<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Update Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update Product';
?>
<div class="products-update">

    <?= $this->render('_form', [
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
