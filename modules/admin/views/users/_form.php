<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
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
            <?= $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'mobile')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col col-md-5">
            <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => true]) ?>
        </div>
        <div class="col col-md-5">
            <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true]) ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'is_shop_owner')->checkbox(['label' => 'Is Shop Owner', 'uncheck' => null, 'selected' => false])->label(false) ?>
        </div>
    </div>


    <div id="shop-details" class="shop-personal-details">
        <div class="row">
            <div class="col col-md-6">
                <?= $form->field($model, 'shop_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'shop_email')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <?= $form->field($model, 'shop_logo')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'shop_phone_number')->textInput() ?>

        <?= $form->field($model, 'shop_address')->textInput() ?>

    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#shop-details').hide();

        $('#users-is_shop_owner').change(function () {
            if ($(this).prop('checked') == true) {
                $('#shop-details').show();
            } else {
                $('#shop-details').hide();
            }
        });

    });
</script>
