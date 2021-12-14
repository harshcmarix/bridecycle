<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-default">
    <div class="box-body">

        <div class="sub-admin-form">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">

                <div class="col col-md-6">
                    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
                </div>

            </div>

            <div class="row">

                <div class="col col-md-6">
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
                </div>

            </div>

            <div class="row">

                <div class="col col-md-6">
                    <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true]) ?>
                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'mobile')->textInput(['type' => 'number']) ?>
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