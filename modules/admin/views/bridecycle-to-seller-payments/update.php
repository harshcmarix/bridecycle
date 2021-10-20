<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BridecycleToSellerPayments */

$this->title = 'Update Bridecycle To Seller Payments: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Bridecycle To Seller Payments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="bridecycle-to-seller-payments-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
