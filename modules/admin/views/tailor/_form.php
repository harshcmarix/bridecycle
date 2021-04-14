<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Tailor;
use kartik\dialog\Dialog;

/* @var $this yii\web\View */
/* @var $model app\models\Tailor */
/* @var $form yii\widgets\ActiveForm */
echo Dialog::widget(
   ['overrideYiiConfirm' => true]
);
?>

<div class="tailor-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shop_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput(['maxlength' => 10]) ?>

    <?= $form->field($model, 'shop_image')->fileInput(['maxlength' => true]) ?>
    
    <?php 
       $is_shop_image_empty = Tailor::IMAGE_EMPTY;
    if(!empty($model->shop_image)){
       $is_shop_image_empty = Tailor::IMAGE_NOT_EMPTY;
    }
    ?>
    <?= $form->field($model, 'is_shop_image_empty')->hiddenInput(['value' => $is_shop_image_empty])->label(false) ?>
    
    <?php 
    if(!empty($model->shop_image)){?>
    <div class="form-group image-class">
            <?= Html::a('',['javascript:(0)'],['class' => 'glyphicon glyphicon-trash shop_image-delete-link','delete-url'=>'../tailor/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img(Yii::getAlias('@tailorShopImageThumbAbsolutePath').'/'.$model->shop_image,  ['class'=>'file-preview-image','height' => '100px', 'width' => '100px']); ?>
    </div>
    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
var image_empty = <?php echo Tailor::IMAGE_EMPTY?>;
 $('.shop_image-delete-link').on('click', function(e) {
            e.preventDefault();
            // krajeeDialog.alert('An alert');
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
                    $('#tailor-is_shop_image_empty').val(image_empty);
                });
            }
           }); 
        });
</script>
