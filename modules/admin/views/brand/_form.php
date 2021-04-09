<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Brand;
/* @var $this yii\web\View */
/* @var $model app\models\Brand */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="brand-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image')->fileInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_top_brand')->checkbox(['label' => 'Is Top Brand', 'selected' => false])->label(false) ?>

    <?= $form->field($model, 'is_brand_image_empty')->hiddenInput(['value' => 0])->label(false) ?>
    <?php 
    if(!empty($model->image)){?>

    <div class="form-group image-class">
            <?= Html::a('',['javascript:(0)'],['class' => 'glyphicon glyphicon-trash pjax-delete-link','delete-url'=>'../brand/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img(Yii::getAlias('@brandImageThumbAbsolutePath').'/'.$model->image,  ['class'=>'file-preview-image','height' => '100px', 'width' => '100px']); ?>
    </div>

    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
var image_empty = <?php echo Brand::BRAND_IMAGE_EMPTY?>;
        $('.pjax-delete-link').on('click', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('delete-url');
            var pjaxContainer = $(this).attr('pjax-container');
            var result = confirm('Delete this image, are you sure?');                                
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
</script>