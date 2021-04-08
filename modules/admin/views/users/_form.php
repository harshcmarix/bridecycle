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
            <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => true, 'value' => ""]) ?>
        </div>
        <div class="col col-md-5">
            <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true, 'value' => ""]) ?>
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
        <div class="row">
            <div class="col col-md-6">
                <?= $form->field($model, 'shop_logo')->fileInput(['maxlength' => true]) ?>

                <?php
                if (!empty($model->shop_logo)) {
                    $profile = Html::img(Yii::getAlias('@shopLogoAbsolutePath') . '/' . $model->shop_logo, ['alt' => 'shop logo', 'class' => 'your_class', 'height' => '100px', 'width' => '100px']);
                    echo $profile;
                }
                ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'shop_phone_number')->textInput() ?>
            </div>
            <div class="col col-md-12">
                <?= $form->field($model, 'shop_address')->textInput() ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#shop-details').hide();

        "<?php if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->is_shop_owner)) { ?>"
        $('#shop-details').show();
        "<?php } ?>"

        if ($('#users-is_shop_owner').prop('checked') == true) {
            $('#shop-details').show();
        } else {
            $('#shop-details').hide();
        }

        $('#users-is_shop_owner').change(function () {
            if ($(this).prop('checked') == true) {
                $('#shop-details').show();
            } else {
                $('#shop-details').hide();
            }
        });

    });
</script>
