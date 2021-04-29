<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['autocomplete' => 'off']]); ?>
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
            <?= $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true,'autocomplete'=>"off"]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'mobile', ['enableAjaxValidation' => true])->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col col-md-5">
            <?php echo $form->field($model, 'password')->passwordInput(['maxlength' => true, 'value' => ""]) ?>
        </div>
        <div class="col col-md-5">
            <?php echo $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true, 'value' => ""]) ?>
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
                <?= $form->field($model, 'shop_phone_number', ['enableAjaxValidation' => true])->textInput() ?>
            </div>
            <div class="row">
                <div class="col col-md-4">
                    <?= $form->field($model, 'shop_address_street')->textInput() ?>
                </div>
                <div class="col col-md-4">
                    <?= $form->field($model, 'shop_address_city')->textInput() ?>
                </div>
                <div class="col col-md-4">
                    <?= $form->field($model, 'shop_address_state')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'shop_address_country')->textInput() ?>
                </div>
                <div class="col col-md-6">
                    <?= $form->field($model, 'shop_address_zip_code')->textInput() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
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

        if ($('#user-is_shop_owner').prop('checked') == true) {
            $('#shop-details').show();
        } else {
            $('#shop-details').hide();
        }

        $('#user-is_shop_owner').change(function () {
            if ($(this).prop('checked') == true) {
                $('#shop-details').show();
            } else {
                $('#shop-details').hide();
            }
        });

    });
</script>
