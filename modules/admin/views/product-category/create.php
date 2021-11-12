<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */

$this->title = 'Create Category';
$this->params['breadcrumbs'][] = ['label' => 'All Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-create">

    <?= $this->render('_form', [
        'model' => $model,
        'parent_category' => $parent_category,
    ]) ?>

</div>