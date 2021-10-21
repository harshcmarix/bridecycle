<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Color */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <div class="color-form">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

            <?php
            $disabled = false;
            if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->status) && in_array($model->status, [\app\models\Color::STATUS_APPROVE, \app\models\Color::STATUS_DECLINE])) {
                $disabled = true;
            }
            ?>

            <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                'data' => $model->arrStatus,
                //'options' => ['placeholder' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'disabled' => $disabled
                ],
            ]); ?>

            <div class="form-group">
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
