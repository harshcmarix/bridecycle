<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Sizes */

$this->title = 'Update Size';
$this->params['breadcrumbs'][] = ['label' => 'Sizes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sizes-update">

    <?= $this->render('_form', [
        'model' => $model,
        'productCategories' => $productCategories
    ]) ?>

</div>
