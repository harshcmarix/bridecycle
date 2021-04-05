<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */

$this->title = 'Create Sub Admin';
$this->params['breadcrumbs'][] = ['label' => 'Sub Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sub-admin-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>