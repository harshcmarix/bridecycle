<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */

$this->title = 'Create Sub Admin';
$this->params['breadcrumbs'][] = ['label' => 'Sub Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sub-admin-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>