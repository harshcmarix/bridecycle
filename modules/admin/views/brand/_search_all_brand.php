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
    }

    .custom-style .kv-drp-container {
        width: 130%;
    }

    .custom-style .kv-drp-dropdown .kv-clear {
        padding: 0 0.3 rem;
        font-size: 1.1rem;
        cursor: pointer;
        right: 2.5rem;
        line-height: 2.5rem;
    }
</style>

<div class="box box-info box-none">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);
    ?>

    <div class="users-form custom-style">
        <div class="row">
            <div class="col col-md-12 form-inline">

                <?php echo $form->field($model, 'created_at')->widget(DateRangePicker::classname(), [
                    'name' => 'date_range_1',
                    'presetDropdown' => true,
                    'convertFormat' => true,
                    'includeMonthsFilter' => false,
                    'pluginOptions' => ['locale' => ['format' => 'd-M-Y', "separator" => " to "]],
                    'options' => ['placeholder' => 'Select range', 'class' => 'form-control', 'value' => $model->created_at]
                ])->label('Date Filter') ?>
            </div>
        </div>

        <div class="form-group form-inline">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>