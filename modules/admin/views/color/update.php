<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Color */

//$this->title = 'Update Color: ' . $model->name;
$this->title = 'Update Color';
$this->params['breadcrumbs'][] = ['label' => 'Colors', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
