<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ProductCategory;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use kartik\dialog\Dialog;
use yii\bootstrap\Modal;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */
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

        <div class="product-category-form">

            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'image/*', 'id' => 'productcategory-image'],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showUpload' => false
                        ]
                    ])->label('Image <spna class="red">*</span>', ['class' => 'labelModalFormInline']); ?>

                    <!-- image display and image popup -->
                    <?php
                    if (!empty($model->image)) {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $model->image;
                        }
                        Modal::begin([
                            'id' => 'productcategorymodal_' . $model->id,
                            'header' => '<h3>Category Image</h3>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);
                        Modal::end();
                        $productcategorymodal = "productcategorymodal('" . $model->id . "');";
                        ?>

                        <div class="form-group image-class product-image-block">
                            <?= Html::img($image_path, ['class' => 'file-preview-image your_class', 'height' => '100px', 'width' => '100px', 'onclick' => $productcategorymodal]); ?>
                            <?= Html::a('<i class="fa fa-times"> </i>', ['javascript:(0)'], ['class' => 'pjax-delete-link', 'delete-url' => '../product-category/image-delete?id=' . $model->id]) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'parent_category_id')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map($parent_category, 'id', 'name'),
                        'size' => Select2::MEDIUM,
                        'options' => [
                            'placeholder' => 'Select Parent Category',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>

                <div class="col col-md-6">
                    <?php
                    $disabled = false;
                    if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->status) && in_array($model->status, [ProductCategory::STATUS_APPROVE, ProductCategory::STATUS_DECLINE])) {
                        $disabled = true;
                    }
                    ?>
                    <?= $form->field($model, 'status')->widget(Select2::classname(), [
                        'data' => ProductCategory::ARR_CATEGORY_STATUS,
                        'size' => Select2::MEDIUM,
                        'options' => [
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'disabled' => $disabled
                        ],
                    ]); ?>
                </div>
            </div>

            <!-- image validation code -->
            <?php
            $is_image_empty = ProductCategory::IMAGE_EMPTY;
            if (!empty($model->image)) {
                $is_image_empty = ProductCategory::IMAGE_NOT_EMPTY;
            }
            ?>
            <?= $form->field($model, 'is_image_empty')->hiddenInput(['value' => $is_image_empty])->label(false) ?>

            <div class="form-group">
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>

<!-- image validation -->
<script type="text/javascript">
    $('.pjax-delete-link').on('click', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('delete-url');
        var result = krajeeDialog.confirm('Are you sure you want to delete this image ?', function (result) {
            if (result) {
                $('.image-class').hide();
                $('#productcategory-is_image_empty').val('1');

            }
        });
    });

    // image modal popup
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }
</script>