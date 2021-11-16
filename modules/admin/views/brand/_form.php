<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Brand;
use kartik\dialog\Dialog;
use yii\bootstrap\Modal;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Brand */
/* @var $form yii\widgets\ActiveForm */

$this->registerCssFile("@web/css/toggle-switch.css");
$this->registerJsFile("@web/js/toggle-switch.js");

/**
 * defined custom alert widget
 */
echo Dialog::widget(
    ['overrideYiiConfirm' => true]
);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="brand-form">

            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'image/*', 'id' => 'brand-image'],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showUpload' => false
                        ]
                    ])->label('Image <spna class="red">*</span>', ['class' => 'labelModalFormInline']); ?>

                    <!-- image validation -->
                    <?php
                    $is_brand_image_empty = Brand::IMAGE_EMPTY;
                    if (!empty($model->image)) {
                        $is_brand_image_empty = Brand::IMAGE_NOT_EMPTY;
                    } ?>

                    <?= $form->field($model, 'is_brand_image_empty')->hiddenInput(['value' => $is_brand_image_empty])->label(false) ?>
                    <!-- image display and image popup -->
                    <?php
                    if (!empty($model->image)) {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@brandImageAbsolutePath') . '/' . $model->image;
                        }
                        Modal::begin([
                            'id' => 'brandmodal_' . $model->id,
                            'header' => '<h3>Brand Image</h3>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);

                        Modal::end();
                        $brandmodal = "brandmodal('" . $model->id . "');";
                    ?>

                        <div class="form-group image-class product-image-block">
                            <?= Html::img($image_path, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $brandmodal]); ?>
                            <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'pjax-delete-link', 'delete-url' => '../brand/image-delete?id=' . $model->id]) ?>
                        </div>

                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <?php
                    $disabled = false;
                    if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->status) && in_array($model->status, [Brand::STATUS_APPROVE, Brand::STATUS_DECLINE])) {
                        $disabled = true;
                    }
                    ?>
                    <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                        'data' => Brand::ARR_BRAND_STATUS,
                        'pluginOptions' => [
                            'allowClear' => true,
                            'disabled' => $disabled
                        ],
                    ]); ?>
                </div>
                <!-- <div class="col col-md-6">
                    <?php //echo $form->field($model, 'is_top_brand')->checkBox(['label' => $model->getAttributeLabel('is_top_brand'), 'id' => 'brand-is_top_brand', 'data-toggle' => "toggle", 'data-onstyle' => "primary", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No", 'selected' => false]); ?>
                        
                </div> -->
            </div>


            <div class="form-group">
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>
</div>

<script>
    var image_empty = <?php echo Brand::IMAGE_EMPTY ?>;
    $('.pjax-delete-link').on('click', function(e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('delete-url');
        var result = krajeeDialog.confirm('Are you sure you want to delete this image ?', function(result) {
            if (result) {
                // $.ajax({
                //     url: deleteUrl,
                //     type: 'post',
                //     error: function (xhr, status, error) {
                //         alert('There was an error with your request.' + xhr.responseText);
                //     }
                // }).done(function (data) {
                $('.image-class').hide();
                $('#brand-is_brand_image_empty').val(image_empty);
                // });
            }
        });
    });

    function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>