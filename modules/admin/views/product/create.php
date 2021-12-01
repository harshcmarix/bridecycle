<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Create Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-create">

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
