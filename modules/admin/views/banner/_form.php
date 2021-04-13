<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Banner;
use kartik\dialog\Dialog;

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

    <?= $form->field($model, 'image')->fileInput(['maxlength' => true]) ?>

    <?php 
        $is_banner_image_empty = Banner::BANNER_IMAGE_EMPTY;
    if(!empty($model->image)){
        $is_banner_image_empty = Banner::BANNER_IMAGE_NOT_EMPTY;
    }
    ?>

      <?= $form->field($model, 'is_banner_image_empty')->hiddenInput(['value' => $is_banner_image_empty])->label(false) ?>
    <?php 
    if(!empty($model->image)){?>

    <div class="form-group image-class">
            <?= Html::a('',['javascript:(0)'],['class' => 'glyphicon glyphicon-trash banner-delete-link','delete-url'=>'../banner/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img(Yii::getAlias('@bannerImageThumbAbsolutePath').'/'.$model->image,  ['class'=>'file-preview-image','height' => '100px', 'width' => '100px']); ?>
    </div>

    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
var image_empty = <?php echo Banner::BANNER_IMAGE_EMPTY?>;
        $('.banner-delete-link').on('click', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('delete-url');
            var pjaxContainer = $(this).attr('pjax-container');
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
</script>
