<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */

$this->title = 'Update Product Category';
if(!empty($model->parent_category_id)){
    $this->title = 'Update Subcategory';
}
$this->params['breadcrumbs'][] = ['label' => 'All Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-update">

    <?= $this->render('_form', [
        'model' => $model,
        'parent_category' => $parent_category,
    ]) ?>

</div>