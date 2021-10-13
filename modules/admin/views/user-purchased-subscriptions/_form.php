<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserPurchasedSubscriptions */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-purchased-subscriptions-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'transaction_id')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'subscription_id')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'date_time')->textInput() ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subscription_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
