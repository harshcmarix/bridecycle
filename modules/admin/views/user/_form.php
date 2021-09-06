<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\bootstrap\Modal;
use kartik\dialog\Dialog;

echo Dialog::widget(
    ['overrideYiiConfirm' => true]
);
?>

<div class="box box-default">
    <div class="box-header"></div>

    <div class="box-body">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['autocomplete' => 'off']]); ?>
        <div class="users-form">


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
                    <?= $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'autocomplete' => "off"]) ?>
                </div>
                <div class="col col-md-6">
                    <?= $form->field($model, 'mobile', ['enableAjaxValidation' => true])->textInput() ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <?php echo $form->field($model, 'password')->passwordInput(['maxlength' => true, 'value' => ""]) ?>
                </div>
                <div class="col col-md-6">
                    <?php echo $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true, 'value' => ""]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <input type="file" name="profile_picture" id="profile_picture">
                </div>
                
                <div class="col col-md-6">
                    <?= $form->field($model, 'is_shop_owner')->checkbox(['label' => 'Is Shop Owner', 'uncheck' => null, 'selected' => false])->label(false) ?>
                </div>
            </div>

            <div id="shop-details" class="shop-personal-details">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'shop_name')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'shop_email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'shop_logo')->widget(FileInput::classname(), [
                            'options' => ['accept' => 'image/*', 'id' => 'user-shop_logo'],
                            'pluginOptions' => [
                                'allowedFileExtensions' => ['jpg', 'png'],
                                'showPreview' => true,
                                'showUpload' => false
                            ]
                        ]); ?>
                        <!-- image code -->
                        <?php
                        // if (!empty($model->shop_logo)) {
                        //     $profile = Html::img(Yii::getAlias('@shopLogoAbsolutePath') . '/' . $model->shop_logo, ['alt' => 'shop logo', 'class' => 'your_class', 'height' => '100px', 'width' => '100px']);
                        //     echo $profile;
                        // }
                        ?>
                        <!-- image validation code -->
                        <?php
                        $is_shop_logo_empty = '1';
                        if (!empty($model->shop_logo)) {
                            $is_shop_logo_empty = '0';
                        }
                        ?>
                        <?= $form->field($model, 'is_shop_logo_empty')->hiddenInput(['value' => $is_shop_logo_empty])->label(false) ?>
                        <!-- image code -->
                        <?php
                        if (!empty($model->shop_logo)) {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            if (file_exists(Yii::getAlias('@shopLogoThumbRelativePath') . '/' . $model->shop_logo)) {
                                $image_path = Yii::getAlias('@shopLogoThumbAbsolutePath') . '/' . $model->shop_logo;
                            }
                            Modal::begin([
                                'id' => 'shoplogomodal_' . $model->id,
                                'header' => '<h3>Shop Image</h3>',
                                'size' => Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            Modal::end();
                            $shoplogomodal = "shoplogomodal('" . $model->id . "');";
                            ?>

                            <div class="form-group image-class">
                                <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'shop_logo-delete-link', 'delete-url' => '../user/shop-logo-delete?id=' . $model->shopDetail->id]) ?>
                            </div>
                            <div class="form-group image-class">
                                <?= Html::img($image_path, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $shoplogomodal]); ?>
                            </div>
                        <?php } ?>
                        <!-- image code end -->
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'shop_phone_number', ['enableAjaxValidation' => true])->textInput() ?>
                    </div>
                    <div class="row">
                        <div class="col col-md-6">
                            <?= $form->field($model, 'shop_address_street')->textInput() ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($model, 'shop_address_city')->textInput() ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($model, 'shop_address_state')->textInput() ?>
                        </div>
                    </div>

                    <div class="col col-md-6">
                        <?= $form->field($model, 'shop_address_country')->textInput() ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'shop_address_zip_code')->textInput() ?>
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

</div>
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
    //image popup
    $('.shop_logo-delete-link').on('click', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('delete-url');
        var result = krajeeDialog.confirm('Are you sure you want to delete this image ?', function (result) {
            if (result) {
                $.ajax({
                    url: deleteUrl,
                    type: 'post',
                    error: function (xhr, status, error) {
                        alert('There was an error with your request.' + xhr.responseText);
                    }
                }).done(function (data) {
                    $('.image-class').hide();
                    $('#user-is_shop_logo_empty').val('1');
                });
            }
        });
    });

    function shoplogomodal(id) {
        $('#shoplogomodal_' + id).modal('show');
    }
</script>
