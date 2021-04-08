<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Products */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'number')->textInput() ?>

    <?= $form->field($model, 'category_id')->textInput() ?>

    <?= $form->field($model, 'sub_category_id')->textInput() ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'option_size')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'option_price')->textInput() ?>

    <?= $form->field($model, 'option_conditions')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'option_show_only')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'available_quantity')->textInput() ?>

    <?= $form->field($model, 'is_top_selling')->dropDownList([ '0', '1', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'is_top_trending')->dropDownList([ '0', '1', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'brand_id')->textInput() ?>

    <?= $form->field($model, 'gender')->dropDownList([ 1 => '1', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'is_cleaned')->dropDownList([ '0', '1', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'height')->textInput() ?>

    <?= $form->field($model, 'weight')->textInput() ?>

    <?= $form->field($model, 'width')->textInput() ?>

    <?= $form->field($model, 'receipt')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
