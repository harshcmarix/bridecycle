<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Brand */

$this->title = 'Create New Brand';
$this->params['breadcrumbs'][] = ['label' => 'New Brands', 'url' => ['new-brand']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-create">

    <?= $this->render('_new-brand-form', [
        'model' => $model,
    ]) ?>

</div>
