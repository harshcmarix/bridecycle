<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BridecycleToSellerPayments */

$this->title = 'Create Bridecycle To Seller Payments';
$this->params['breadcrumbs'][] = ['label' => 'Bridecycle To Seller Payments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bridecycle-to-seller-payments-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
