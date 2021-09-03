<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

//$this->title = 'Update Product: ' . $model->name;
$this->title = 'Update Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update Product';
?>
<div class="products-update">

    <?= $this->render('_form', [
        'model' => $model,
        'category' => $category,
        'subcategory' => $subcategory,
        'brand' => $brand,
        'color' => $color,
        'status' => $status,
        'shippingCountry' => $shippingCountry,
        'shippingPrice' => $shippingPrice
    ]) ?>

</div>
