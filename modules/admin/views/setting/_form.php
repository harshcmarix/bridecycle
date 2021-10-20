<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Setting */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="setting-form">

    <?php $form = ActiveForm::begin(); ?>

    <!--    --><?php //echo $form->field($model, 'transaction_fees')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'km_range')->textInput(['maxlength' => true])->label(strtoupper($model->getAttributeLabel('km_range')) . " (for find the tailor)") ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
