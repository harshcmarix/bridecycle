<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */
/* @var $form yii\widgets\ActiveForm */
// p($parent_category);
?>
<div class="product-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image')->fileInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parent_category_id')->dropDownList(ArrayHelper::map($parent_category,'id','name'),['prompt'=>'select parent category']) ?>

    <?php 
    if(!empty($model->image)){?>
    <div class="form-group image-class">
            <?= Html::a('Delete image',['javascript:(0)'],['class' => 'pjax-delete-link','delete-url'=>'../product-category/picture?id='.$model->id]) ?>
    </div>
    <div class="form-group image-class">
             <?= Html::img(Yii::getAlias('@productCategoryImageThumbAbsolutePath').'/'.$model->image,  ['class'=>'file-preview-image']); ?>
    </div>
    <?php } ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs("
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
                });
            }
        });
");
?>