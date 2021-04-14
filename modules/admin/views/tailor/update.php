<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Tailor */

$this->title = 'Update Tailor: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tailors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tailor-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
