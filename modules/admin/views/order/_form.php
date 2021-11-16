<?php

use app\models\UserAddress;
use app\modules\api\v2\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
<div class="order-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'user_id')->widget(\kartik\select2\Select2::class, [
                'data' => ArrayHelper::map(User::find()->where(['id'=>$model->user_id])->all(),'id',function($data){ return $data['first_name']." ".$data['last_name'];}),
                'options' => ['placeholder' => 'Select User', 'value' => $model->user_id],
                'pluginOptions' => [
                    'allowClear' => true,
                    'disabled'=>'readonly'
                ],
            ]); ?>            
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'user_address_id')->widget(\kartik\select2\Select2::class, [
                'data' => ArrayHelper::map(UserAddress::find()->where(['id'=>$model->user_address_id])->all(),'id',function($data){ return $data['address'];}),
                'options' => ['placeholder' => 'Select Address', 'value' => $model->user_address_id],
                'pluginOptions' => [
                    'allowClear' => true,
                    'disabled'=>'readonly'
                ],
            ]); ?>            
        </div>
    </div>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'total_amount')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'status')->widget(\kartik\select2\Select2::classname(), [
                'data' => $model->arrOrderStatus,
                'options' => ['placeholder' => 'Select Status', 'value' => $model->status],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'created_at')->textInput(['readonly' => true])->label('Order Created Date Time') ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'updated_at')->textInput(['readonly' => true])->label('Order Updated Date Time') ?>

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
