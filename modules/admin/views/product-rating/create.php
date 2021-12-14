<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductRating */

$this->title = 'Create Product Rating';
$this->params['breadcrumbs'][] = ['label' => 'Product Ratings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-rating-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
