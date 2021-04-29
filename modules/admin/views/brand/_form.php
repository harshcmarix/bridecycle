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

<div class="brand-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name',['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>

     <!-- $form->field($model, 'image')->fileInput(['maxlength' => true])  -->
      <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                    'options' => ['accept' => 'image/*', 'id' => 'brand-image'],
                    'pluginOptions' => [
                        'allowedFileExtensions' => ['jpg', 'png'],
                        'showPreview' => true,
//                        'showCaption' => true,
//                        'showRemove' => true,
                        'showUpload' => false
                    ]
                ]); ?>

      <!-- $form->field($model, 'is_top_brand')->checkbox(['label' => 'Is Top Brand', 'selected' => false])->label(false)  -->
     <?php  echo $form->field($model, 'is_top_brand')
                    ->checkBox(['label' => $model->getAttributeLabel('is_top_brand'),'id' => 'brand-is_top_brand','data-toggle' => "toggle", 'data-onstyle' => "primary",'data-onstyle' => "success",'data-on' => "Yes", 'data-off' => "No",'selected' => false]); ?>
     

    <!-- image validation -->
    <?php
       $is_brand_image_empty = Brand::IMAGE_EMPTY;
    if(!empty($model->image)){
       $is_brand_image_empty = Brand::IMAGE_NOT_EMPTY;
    }?>

    <?= $form->field($model, 'is_brand_image_empty')->hiddenInput(['value' => $is_brand_image_empty])->label(false) ?>
    <!-- image display and image popup -->
    <?php 
    if(!empty($model->image)){
         $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
         if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $model->image)) {
                        $image_path = Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $model->image;
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

    <div class="form-group image-class">
            <?= Html::a('<i class="fa fa-times"> </i>',['javascript:(0)'],['class' => 'pjax-delete-link','delete-url'=>'../brand/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img($image_path,  ['class'=>'file-preview-image your_class','height' => '100px', 'width' => '100px', 'onclick' => $brandmodal]); ?>
    </div>

    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
var image_empty = <?php echo Brand::IMAGE_EMPTY?>;
        $('.pjax-delete-link').on('click', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('delete-url');
            var result = krajeeDialog.confirm('Are you sure You want to delete this image ?', function(result){                                     
            if(result) {
                $.ajax({
                    url: deleteUrl,
                    type: 'post',
                    error: function(xhr, status, error) {
                        alert('There was an error with your request.' + xhr.responseText);
                    }
                }).done(function(data) {
                   $('.image-class').hide();
                   $('#brand-is_brand_image_empty').val(image_empty);
                });
            }
            });
        });
         function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>