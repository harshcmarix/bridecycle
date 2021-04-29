<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\ActiveForm;
use dosamigos\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model app\models\CmsPage */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cms-page-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

     <!-- $form->field($model, 'slug')->textInput(['maxlength' => true])  -->

    <?= $form->field($model, 'description')->widget(CKEditor::className(), [
        'options' => ['rows' => 6],
        'preset' => 'basic',
        // 'preset' => 'classic',
        'clientOptions' => [
        'filebrowserUploadUrl' => yii\helpers\Url::to(['cms-page/ckeditor-image-upload']),
        ]
    ]) ?> 

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
