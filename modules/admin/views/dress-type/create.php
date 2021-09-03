<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DressType */

$this->title = 'Create Dress Type';
$this->params['breadcrumbs'][] = ['label' => 'Dress Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dress-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
