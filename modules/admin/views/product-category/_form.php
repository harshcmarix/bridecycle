<?php

use yii\helpers\{
    Html,
    Url
};
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
<div class="product-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name',['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>

     <!-- $form->field($model, 'image')->fileInput(['maxlength' => true])  -->
     <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                    'options' => ['accept' => 'image/*', 'id' => 'productcategory-image'],
                    'pluginOptions' => [
                        'allowedFileExtensions' => ['jpg', 'png'],
                        'showPreview' => true,
//                        'showCaption' => true,
//                        'showRemove' => true,
                        'showUpload' => false
                    ]
                ]); ?>

    <!-- $form->field($model, 'parent_category_id')->dropDownList(ArrayHelper::map($parent_category,'id','name'),['prompt'=>'select parent category']) -->

   <?= $form->field($model, 'parent_category_id')->widget(Select2::classname(), [
    'data' => ArrayHelper::map($parent_category,'id','name'),
    'size' => Select2::MEDIUM,
     'options' => [
                    'placeholder' => 'Select Parent Category',
                ],
    'pluginOptions' => [
        'allowClear' => true
    ],
]);?>
<!-- image validation code -->
    <?php 
       $is_image_empty = ProductCategory::IMAGE_EMPTY;
    if(!empty($model->image)){
       $is_image_empty = ProductCategory::IMAGE_NOT_EMPTY;
    }
    ?>
    <?= $form->field($model, 'is_image_empty')->hiddenInput(['value' => $is_image_empty])->label(false) ?>

    <!-- image display and image popup -->
    <?php 
    if(!empty($model->image)){
        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
         if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
              $image_path = Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $model->image;
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
    <div class="form-group image-class">
            <?= Html::a('<i class="fa fa-times"> </i>',['javascript:(0)'],['class' => 'pjax-delete-link','delete-url'=>'../product-category/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img($image_path,  ['class'=>'file-preview-image your_class','height' => '100px', 'width' => '100px','onclick' => $productcategorymodal]); ?>
    </div>
    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<!-- image validation -->
<script type="text/javascript">
        $('.pjax-delete-link').on('click', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('delete-url');
            var result = krajeeDialog.confirm('Are you sure you want to delete this image ?', function(result){                                
            if(result) {
                $.ajax({
                    url: deleteUrl,
                    type: 'post',
                    error: function(xhr, status, error) {
                        alert('There was an error with your request.' + xhr.responseText);
                    }
                }).done(function(data) {
                   $('.image-class').hide();
                    $('#productcategory-is_image_empty').val('1');
                });
            }
           }); 
        });
    // image modal popup
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }
</script>