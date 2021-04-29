<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sub-admin-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput(['type' => 'number']) ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>