<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */

$this->title = 'Update Sub Admin';
$this->params['breadcrumbs'][] = ['label' => 'Sub Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sub-admin-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>