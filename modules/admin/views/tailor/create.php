<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Tailor */

$this->title = 'Create Tailor';
$this->params['breadcrumbs'][] = ['label' => 'Tailors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tailor-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
