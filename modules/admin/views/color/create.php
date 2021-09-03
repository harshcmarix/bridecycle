<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Color */

$this->title = 'Create Color';
$this->params['breadcrumbs'][] = ['label' => 'Colors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
