<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];

?>

<div class="login-box">

    <div class="login-logo">
        <a href="#"><img src="<?php echo Yii::$app->request->baseUrl . '/uploads/logo.png'; ?>"></a>
    </div>
    <!-- /.login-logo -->

    <div class="login-box-body">

        <p class="login-box-msg">Please fill out the following fields to login:</p>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
        ]); ?>

        <?= $form->field($model, 'email', $fieldOptions1)->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password', $fieldOptions2)->passwordInput() ?>

        <div class="row">

            <div class="col-xs-4">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <div class="col-xs-8">
                <div class="col-lg-offset-1" style="color:#999;">
                </div>
            </div>

        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
