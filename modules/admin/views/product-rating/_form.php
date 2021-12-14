<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProductRating */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-default">
<!--    <div class="box-header"></div>-->
    <div class="box-body">
        <div class="product-rating-form">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'user_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\modules\api\v1\models\User::find()->where(['user_type' => \app\modules\api\v1\models\User::USER_TYPE_NORMAL])->all(), 'id', function ($model) {
                return $model->first_name . " " . $model->last_name . " (" . $model->email . ")";
            }), ['disabled' => true])->label('User') ?>

            <?= $form->field($model, 'product_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\Product::find()->all(), 'id', function ($model) {
                return $model->name;
            }), ['disabled' => true])->label('Product') ?>

            <?= $form->field($model, 'rating')->textInput(['readOnly' => true]) ?>

            <?= $form->field($model, 'review')->textarea(['rows' => 6]) ?>

            <?php
            $disabled = false;
            if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->status) && in_array($model->status, [\app\models\ProductRating::STATUS_APPROVE, \app\models\ProductRating::STATUS_DECLINE])) {
                $disabled = true;
            }
            ?>

            <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                'data' => \app\models\ProductRating::ARR_PRODUCT_RATING_STATUS,
                'size' => \kartik\select2\Select2::MEDIUM,
                'options' => [
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'disabled' => $disabled
                ],
            ]); ?>

            <div class="form-group">
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>