<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Sizes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-default">
<!--    <div class="box-header"></div>-->
    <div class="box-body">

        <div class="sizes-form">

            <?php $form = ActiveForm::begin(); ?>

            <div class="row">

                <div class="col col-md-6">

                    <?= $form->field($model, 'size')->textInput(['maxlength' => true]) ?>

                </div>

                <div class="col col-md-6">
                    <?= $form->field($model, 'product_category_id')->widget(Select2::class, [
                        'data' => $productCategories,
                        'options' => ['placeholder' => 'Select Category'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'multiple' => false
                        ],
                    ]); ?>
                </div>

            </div>

            <div class="form-group">
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>
</div>