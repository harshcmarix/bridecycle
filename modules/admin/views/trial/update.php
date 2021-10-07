<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Trial */

$this->title = 'Update Trial: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Trials', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="trial-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
