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

<div class="banner-form">

    <?php $form = ActiveForm::begin(); ?>

     <!-- $form->field($model, 'image')->fileInput(['maxlength' => true])  -->
      <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                    'options' => ['accept' => 'image/*', 'id' => 'banner-image'],
                    'pluginOptions' => [
                        'allowedFileExtensions' => ['jpg', 'png'],
                        'showPreview' => true,
//                        'showCaption' => true,
//                        'showRemove' => true,
                        'showUpload' => false
                    ]
                ]); ?>
<!-- image validation -->
    <?php 
        $is_banner_image_empty = Banner::IMAGE_EMPTY;
    if(!empty($model->image)){
        $is_banner_image_empty = Banner::IMAGE_NOT_EMPTY;
    }
    ?>

      <?= $form->field($model, 'is_banner_image_empty')->hiddenInput(['value' => $is_banner_image_empty])->label(false) ?>
      <!-- image display and popup -->
    <?php 
    if(!empty($model->image)){
         $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
          if (!empty($model->image) && file_exists(Yii::getAlias('@bannerImageRelativePath') . '/' . $model->image)) {
                        $image_path = Yii::getAlias('@bannerImageThumbAbsolutePath') . '/' . $model->image;
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
    <div class="form-group image-class">
            <?= Html::a('<i class="fa fa-times"> </i>',['javascript:(0)'],['class' => 'banner-delete-link','delete-url'=>'../banner/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img($image_path,  ['class'=>'file-preview-image your_class','height' => '100px', 'width' => '100px', 'onclick' => $bannermodal]); ?>
    </div>

    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
var image_empty = <?php echo Banner::IMAGE_EMPTY?>;
        $('.banner-delete-link').on('click', function(e) {
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
                   $('#banner-is_banner_image_empty').val(image_empty);
                });
            }
            });
        });
       
    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
    }

</script>
