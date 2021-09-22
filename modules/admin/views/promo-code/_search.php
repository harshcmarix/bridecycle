<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\PromoCodeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="promo-code-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'value') ?>

    <?= $form->field($model, 'start_date') ?>

    <?= $form->field($model, 'end_date') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
