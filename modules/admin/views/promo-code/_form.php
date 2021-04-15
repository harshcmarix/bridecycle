<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PromoCode */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="promo-code-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
