<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\UserPurchasedSubscriptionsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-purchased-subscriptions-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'transaction_id') ?>

    <?= $form->field($model, 'subscription_id') ?>

    <?= $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'date_time') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'subscription_type') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
