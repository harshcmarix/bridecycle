<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;


/* @var $this yii\web\View */
/* @var $model app\models\Subscription */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="subscription-form">

            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col col-md-6">
                    <?= $form->field($model, 'month')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'status')->widget(Select2::classname(), [
                        'data' => $subscription_status,
                        'size' => Select2::MEDIUM,
                        'options' => [
                            'placeholder' => 'Select status',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>

            </div>

            <div class="form-group">
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>
</div>