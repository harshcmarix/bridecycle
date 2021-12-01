<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Sizes */

$this->title = 'Create Size';
$this->params['breadcrumbs'][] = ['label' => 'Sizes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sizes-create">

    <?= $this->render('_form', [
        'model' => $model,
        'productCategories' => $productCategories
    ]) ?>

</div>
