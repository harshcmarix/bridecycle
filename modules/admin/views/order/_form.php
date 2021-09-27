<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'user_id')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'user_address_id')->textInput(['readonly' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'total_amount')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                'data' => $model->arrOrderStatus,
                'options' => ['placeholder' => 'Select Status', 'value' => $model->status],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'created_at')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'updated_at')->textInput(['readonly' => true]) ?>

        </div>
    </div>
    
    <div class="form-group">
        <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
