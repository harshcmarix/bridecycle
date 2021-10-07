<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Banner;
use kartik\dialog\Dialog;
use yii\bootstrap\Modal;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Banner */
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
        <div class="banner-form">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*', 'id' => 'banner-image'],
                'pluginOptions' => [
                    'showPreview' => false,
                    'showUpload' => false
                ]
            ])->label('Image <spna class="red">*</span>',['class'=>'labelModalFormInline']); ?>
            <!-- image validation -->
            <?php
            $is_banner_image_empty = Banner::IMAGE_EMPTY;
            if (!empty($model->image)) {
                $is_banner_image_empty = Banner::IMAGE_NOT_EMPTY;
            }
            ?>

            <?= $form->field($model, 'is_banner_image_empty')->hiddenInput(['value' => $is_banner_image_empty])->label(false) ?>
            <!-- image display and popup -->
            <?php
            if (!empty($model->image)) {
                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->image) && file_exists(Yii::getAlias('@bannerImageRelativePath') . '/' . $model->image)) {
                    $image_path = Yii::getAlias('@bannerImageAbsolutePath') . '/' . $model->image;
                }
                Modal::begin([
                    'id' => 'bannermodal_' . $model->id,
                    'header' => '<h3>Banner Image</h3>',
                    'size' => Modal::SIZE_DEFAULT
                ]);

                echo Html::img($image_path, ['width' => '570']);

                Modal::end();
                $bannermodal = "bannermodal('" . $model->id . "');";
                ?>
                <div class="form-group image-class product-image-block">
                    <?= Html::img($image_path, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $bannermodal]); ?>
                    <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'banner-delete-link', 'delete-url' => '../banner/image-delete?id=' . $model->id]) ?>
                </div>

            <?php } ?>

            <div class="form-group">
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
<script>
    var image_empty = <?php echo Banner::IMAGE_EMPTY?>;
    $('.banner-delete-link').on('click', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('delete-url');
        var result = krajeeDialog.confirm('Are you sure you want to delete this image ?', function (result) {
            if (result) {
                // $.ajax({
                //     url: deleteUrl,
                //     type: 'post',
                //     error: function (xhr, status, error) {
                //         alert('There was an error with your request.' + xhr.responseText);
                //     }
                // }).done(function (data) {
                    $('.image-class').hide();
                    $('#banner-is_banner_image_empty').val(image_empty);
                // });
            }
        });
    });

    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
    }

</script>
