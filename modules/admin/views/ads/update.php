<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Ads */

$this->title = 'Update Ads: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Ads', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ads-update">

    <?= $this->render('_form', [
        'model' => $model,
        'category' => $category,
        'subCategory' => $subCategory,
        'product' => $product,
        'brand' => $brand,
    ]) ?>

</div>
