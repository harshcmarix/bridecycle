<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->textInput(['readonly' => true]) ?>

    <?= $form->field($model, 'user_address_id')->textInput(['readonly' => true]) ?>

    <?= $form->field($model, 'total_amount')->textInput(['readonly' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList([ 1 => 'pending', 2 => 'in progress', 3 => 'completed', 4 => 'cancelled', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'created_at')->textInput(['readonly' => true]) ?>

    <?= $form->field($model, 'updated_at')->textInput(['readonly' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
