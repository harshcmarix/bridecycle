<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use app\models\Tailor;
use kartik\dialog\Dialog;
use yii\bootstrap\Modal;

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

    <?= $form->field($model, 'address')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'mobile',['enableAjaxValidation' => true])->textInput() ?>

    <?= $form->field($model, 'shop_image')->fileInput(['maxlength' => true]) ?>
    
   <!-- image validation code -->
    <?php 
       $is_shop_image_empty = Tailor::IMAGE_EMPTY;
    if(!empty($model->shop_image)){
       $is_shop_image_empty = Tailor::IMAGE_NOT_EMPTY;
    }
    ?>
    <?= $form->field($model, 'is_shop_image_empty')->hiddenInput(['value' => $is_shop_image_empty])->label(false) ?>
     <!-- image code -->
    <?php 
    if(!empty($model->shop_image)){
         $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        if(file_exists(Yii::getAlias('@tailorShopImageRelativePath') . '/' . $model->shop_image)){
            $image_path = Yii::getAlias('@tailorShopImageThumbAbsolutePath').'/'.$model->shop_image;
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
    
    <div class="form-group image-class">
            <?= Html::a('<i class="fa fa-times"> </i>',['javascript:(0)'],['class' => 'shop_image-delete-link','delete-url'=>'../tailor/image-delete?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img($image_path,  ['class'=>'file-preview-image','height' => '100px', 'width' => '100px','onclick' => $tailorimagemodal]); ?>
    </div>
    <?php } ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
//image delete using rejax
var image_empty = <?php echo Tailor::IMAGE_EMPTY?>;
 $('.shop_image-delete-link').on('click', function(e) {
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
                    $('#tailor-is_shop_image_empty').val(image_empty);
                });
            }
           }); 
        });
        //image popup
    function tailorimagemodal(id) {
        $('#tailorimagemodal_' + id).modal('show');
    }
</script>
