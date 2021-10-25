<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\AbuseReport */

$this->title = 'Create Abuse Report';
$this->params['breadcrumbs'][] = ['label' => 'Abuse Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="abuse-report-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
