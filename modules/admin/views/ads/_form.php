<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use kartik\dialog\Dialog;
use yii\bootstrap\Modal;
use app\models\Ads;

/* @var $this yii\web\View */
/* @var $model app\models\Ads */
/* @var $form yii\widgets\ActiveForm */


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

        <div class="ads-form">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*', 'id' => 'ads-image'],
                'pluginOptions' => [
                    'allowedFileExtensions' => ['jpg', 'png', 'jpeg'],
                    'showPreview' => true,
//                        'showCaption' => true,
//                        'showRemove' => true,
                    'showUpload' => false
                ]
            ]); ?>

            <!-- image validation -->
            <?php
            $is_ads_image_empty = Ads::IMAGE_EMPTY;
            if (!empty($model->image)) {
                $is_ads_image_empty = Ads::IMAGE_NOT_EMPTY;
            } ?>

            <?= $form->field($model, 'is_ads_image_empty')->hiddenInput(['value' => $is_ads_image_empty])->label(false) ?>

            <?php
            if (!empty($model->image)) {
                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->image) && file_exists(Yii::getAlias('@adsImageThumbRelativePath') . '/' . $model->image)) {
                    $image_path = Yii::getAlias('@adsImageThumbAbsolutePath') . '/' . $model->image;
                }
                Modal::begin([
                    'id' => 'adsmodal_' . $model->id,
                    'header' => '<h3>Ads Image</h3>',
                    'size' => Modal::SIZE_DEFAULT
                ]);

                echo Html::img($image_path, ['width' => '570']);

                Modal::end();
                $adsmodal = "adsmodal('" . $model->id . "');";
                ?>

                <div class="form-group image-class">
                    <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'pjax-delete-link', 'delete-url' => '../ads/image-delete?id=' . $model->id]) ?>
                </div>
                <div class="form-group image-class">
                    <?= Html::img($image_path, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $adsmodal]); ?>
                </div>

            <?php } ?>

            <?= $form->field($model, 'url')->textInput() ?>

            <div class="row">
                <div class="col col-md-4">
                    <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                        'data' => Ads::ARR_ADS_STATUS,
                        'options' => ['placeholder' => 'Select Status', 'value' => 1],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>
                <div class="col col-md-4">
                    <?= $form->field($model, 'category_id')->widget(\kartik\select2\Select2::classname(), [
                        'data' => $category,
                        'options' => ['placeholder' => 'Select Category'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Category'); ?>
                </div>
                <div class="col col-md-4">
                    <?= $form->field($model, 'sub_category_id')->widget(\kartik\select2\Select2::classname(), [
                        'data' => $subCategory,
                        'options' => ['placeholder' => 'Select Sub Category'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Sub category'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-4">
                    <?= $form->field($model, 'product_id')->widget(\kartik\select2\Select2::classname(), [
                        'data' => $product,
                        'options' => ['placeholder' => 'Select Product'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Product'); ?>
                </div>
                <div class="col col-md-4">
                    <?= $form->field($model, 'brand_id')->widget(\kartik\select2\Select2::classname(), [
                        'data' => $brand,
                        'options' => ['placeholder' => 'Select Brand'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Brand'); ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>

<script>
    var image_empty = <?php echo Ads::IMAGE_EMPTY?>;
    $('.pjax-delete-link').on('click', function (e) {
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
                    $('#ads-is_ads_image_empty').val(image_empty);
                });
            }
        });
    });

    function adsmodal(id) {
        $('#adsmodal_' + id).modal('show');
    }

    $('#ads-image').on('fileselect', function(event, numFiles, label) {
        if (this.files.length < 1) {
            $('.file-preview .file-preview-frame').hide();
        }
    });
</script>