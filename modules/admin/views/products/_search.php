<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'number') ?>

    <?= $form->field($model, 'category_id') ?>

    <?= $form->field($model, 'sub_category_id') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'option_size') ?>

    <?php // echo $form->field($model, 'option_price') ?>

    <?php // echo $form->field($model, 'option_conditions') ?>

    <?php // echo $form->field($model, 'option_show_only') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'available_quantity') ?>

    <?php // echo $form->field($model, 'is_top_selling') ?>

    <?php // echo $form->field($model, 'is_top_trending') ?>

    <?php // echo $form->field($model, 'brand_id') ?>

    <?php // echo $form->field($model, 'gender') ?>

    <?php // echo $form->field($model, 'is_cleaned') ?>

    <?php // echo $form->field($model, 'height') ?>

    <?php // echo $form->field($model, 'weight') ?>

    <?php // echo $form->field($model, 'width') ?>

    <?php // echo $form->field($model, 'receipt') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
