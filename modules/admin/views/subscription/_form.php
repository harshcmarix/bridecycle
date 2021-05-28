<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use kartik\select2\Select2;


/* @var $this yii\web\View */
/* @var $model app\models\Subscription */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="subscription-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

     <!-- $form->field($model, 'status')->dropDownList($subscription_status, ['prompt' => ''])  -->

     <?= $form->field($model, 'status')->widget(Select2::classname(), [
    'data' => $subscription_status,
    'size' => Select2::MEDIUM,
     'options' => [
                    'placeholder' => 'Select status',
                ],
    'pluginOptions' => [
        'allowClear' => true
    ],
]);?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
