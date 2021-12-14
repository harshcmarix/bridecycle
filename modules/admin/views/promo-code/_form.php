<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\PromoCode;

/* @var $this yii\web\View */
/* @var $model app\models\PromoCode */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-default">
<!--    <div class="box-header"></div>-->
    <div class="box-body">

        <div class="promo-code-form">

            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
                    <div class="row">
                        <div class="col-md-11">
                            <?= $form->field($model, 'value')->textInput(['id' => 'promocodeValue', 'maxlength' => true]) ?>
                        </div>
                        <div class="col-md-1">
                            <label id="promocodeDiscount" class="d-none" for="promocodeValue">%</label>
                        </div>
                    </div>
                    <?= $form->field($model, 'end_date')->widget(\kartik\widgets\DatePicker::className(), ['options' => ['autocomplete' => 'off'], 'pluginOptions' => ['format' => 'yyyy-mm-dd']]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'type')->widget(\kartik\select2\Select2::classname(), [
                        'data' => PromoCode::ARR_PROMOCODE_TYPE,
                        'options' => ['placeholder' => 'Select Type', 'value' => $model->type],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                    <?= $form->field($model, 'start_date')->widget(\kartik\widgets\DatePicker::className(), ['options' => ['autocomplete' => 'off'], 'pluginOptions' => ['format' => 'yyyy-mm-dd']]) ?>
                    <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                        'data' => PromoCode::ARR_PROMOCODE_STATUS,
                        'options' => ['placeholder' => 'Select Status', 'value' => $model->status ? $model->status : PromoCode::STATUS_INACTIVE],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>
</div>

<script type="text/javascript">

    $('#promocode-type').on('change', function () {
        if (this.value == 'discount') {
            $('#promocodeDiscount').removeClass('d-none');
        } else {
            $('#promocodeDiscount').addClass('d-none');
        }
    });

    $('document').ready( function () {
        if ($('#promocode-type').val() == 'discount') {
            $('#promocodeDiscount').removeClass('d-none');
        } else {
            $('#promocodeDiscount').addClass('d-none');
        }
    });

</script>