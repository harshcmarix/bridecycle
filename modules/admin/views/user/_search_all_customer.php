<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\UserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<style>
    .form-inline .form-control {
        width: 100%;
    }

    .kv-drp-dropdown .range-value {
        padding-left: 2em;
    }

    .glyphicon {
        line-height: 1.8;
    }

    .box-none {
        border-top: 0px solid #8A9673 !important;
        box-shadow: none !important;
</style>

<div class="box box-info box-none">


    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
//        'options' => [
//            'data-pjax' => 1
//        ],
    ]); ?>
    <div class="users-form">
        <div class="row">
            <div class="col col-md-12 form-inline">

                <?php echo $form->field($model, 'created_at')->widget(DateRangePicker::classname(), [
                    'name' => 'date_range_1',
                    'presetDropdown' => true,
                    'convertFormat' => true,
                    'includeMonthsFilter' => false,
                    'pluginOptions' => ['locale' => ['format' => 'd-M-Y', "separator" => " to "]],
                    'options' => ['placeholder' => 'Select range', 'class' => 'form-control']
                ])->label('Date Filter') ?>
            </div>
        </div>

        <div class="form-group form-inline">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            <!--        --><?php //echo Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>


</div>
