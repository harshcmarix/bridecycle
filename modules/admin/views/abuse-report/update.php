<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\AbuseReport */

$this->title = 'Update Abuse Report: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Abuse Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="abuse-report-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
