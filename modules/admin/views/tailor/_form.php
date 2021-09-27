<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Tailor;
use kartik\dialog\Dialog;
use yii\bootstrap\Modal;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Tailor */
/* @var $form yii\widgets\ActiveForm */
echo Dialog::widget(
    ['overrideYiiConfirm' => true]
);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <div class="tailor-form">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'shop_name')->textInput(['maxlength' => true]) ?>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'address')->textarea(['rows' => 3]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'mobile', ['enableAjaxValidation' => true])->textInput(['type' => 'number']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'zip_code')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'shop_image')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'image/*', 'id' => 'tailor-shop_image'],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showUpload' => false
                        ]
                    ])->label('Shop Image <spna class="red">*</span>',['class'=>'labelModalFormInline']); ?>

                    <!-- image validation code -->
                    <?php
                    $is_shop_image_empty = Tailor::IMAGE_EMPTY;
                    if (!empty($model->shop_image)) {
                        $is_shop_image_empty = Tailor::IMAGE_NOT_EMPTY;
                    }
                    ?>
                    <?= $form->field($model, 'is_shop_image_empty')->hiddenInput(['value' => $is_shop_image_empty])->label(false) ?>
                    <!-- image code -->
                    <?php
                    if (!empty($model->shop_image)) {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        if (file_exists(Yii::getAlias('@tailorShopImageThumbRelativePath') . '/' . $model->shop_image)) {
                            $image_path = Yii::getAlias('@tailorShopImageThumbAbsolutePath') . '/' . $model->shop_image;
                        }
                        Modal::begin([
                            'id' => 'tailorimagemodal_' . $model->id,
                            'header' => '<h3>Shop Image</h3>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);

                        Modal::end();
                        $tailorimagemodal = "tailorimagemodal('" . $model->id . "');";
                        ?>
                        <div class="form-group image-class product-image-block">
                            <?= Html::img($image_path, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $tailorimagemodal]); ?>
                            <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'shop_image-delete-link', 'delete-url' => '../tailor/image-delete?id=' . $model->id]) ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="col-md-6">
                    <!-- $form->field($model, 'shop_image')->fileInput(['maxlength' => true])  -->
                    <?= $form->field($model, 'voucher')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'image/*', 'id' => 'tailor-voucher'],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showUpload' => false
                        ]
                    ]); ?>

                    <!-- image validation code -->
                    <?php
                    $is_voucher_image_empty = Tailor::IMAGE_EMPTY;
                    if (!empty($model->voucher)) {
                        $is_voucher_image_empty = Tailor::IMAGE_NOT_EMPTY;
                    }
                    ?>
                    <?= $form->field($model, 'is_voucher_image_empty')->hiddenInput(['value' => $is_voucher_image_empty])->label(false) ?>
                    <!-- image code -->
                    <?php
                    if (!empty($model->voucher)) {
                        $image_pathvoucher = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        if (file_exists(Yii::getAlias('@tailorVoucherImageThumbRelativePath') . '/' . $model->voucher)) {
                            $image_pathvoucher = Yii::getAlias('@tailorVoucherImageThumbAbsolutePath') . '/' . $model->voucher;
                        }
                        Modal::begin([
                            'id' => 'tailorimagevouchermodal_' . $model->id,
                            'header' => '<h3>Voucher Image</h3>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_pathvoucher, ['width' => '570']);

                        Modal::end();
                        $tailorimagevouchermodal = "tailorimagemodal('" . $model->id . "');";
                        ?>
                        <div class="form-group image-class-voucher product-image-block">
                            <?= Html::img($image_pathvoucher, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $tailorimagevouchermodal]); ?>
                            <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'shop_image-voucher-delete-link', 'delete-url' => '../tailor/image-delete-voucher?id=' . $model->id]) ?>
                        </div>
                    <?php } ?>
                </div>
                
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
<script>
    //image delete using rejax
    var image_empty = <?php echo Tailor::IMAGE_EMPTY?>;
    var image_not_empty = <?php echo Tailor::IMAGE_NOT_EMPTY?>;
    $('.shop_image-delete-link').on('click', function (e) {
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
                    $('#tailor-is_shop_image_empty').val(image_empty);
                // });
            }
        });
    });

    $('.shop_image-voucher-delete-link').on('click', function (e) {
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
                    $('.image-class-voucher').hide();
                    $('#tailor-is_voucher_image_empty').val(image_empty);
                // });
            }
        });
    });

    $('#tailor-voucher').on('change', function () {
        $('#tailor-is_voucher_image_empty').val(image_not_empty);
    })

    //image popup
    function tailorimagemodal(id) {
        $('#tailorimagemodal_' + id).modal('show');
    }

    //image popup
    function tailorimagevouchermodal(id) {
        $('#tailorimagevouchermodal_' + id).modal('show');
    }
</script>