<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Trial */

$this->title = 'Create Trial';
$this->params['breadcrumbs'][] = ['label' => 'Trials', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trial-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
