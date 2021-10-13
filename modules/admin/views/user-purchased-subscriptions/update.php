<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserPurchasedSubscriptions */

$this->title = 'Update User Purchased Subscriptions: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'User Purchased Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-purchased-subscriptions-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
