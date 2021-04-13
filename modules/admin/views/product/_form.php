<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col col-md-6">
            <?= $form->field($model, 'number')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col col-md-4">
            <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                'data' => $category,
                'options' => ['placeholder' => 'Select Category'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-4">
            <?= $form->field($model, 'sub_category_id')->widget(Select2::classname(), [
                'data' => $subcategory,
                'options' => ['placeholder' => 'Select Sub-Category'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-4">
            <?= $form->field($model, 'price')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-6">
            <?= $form->field($model, 'brand_id')->widget(Select2::classname(), [
                'data' => $brand,
                'options' => ['placeholder' => 'Select Brand'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'height')->textInput() ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'weight')->textInput() ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'width')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-4">
            <?= $form->field($model, 'option_size')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col col-md-4">
            <?= $form->field($model, 'option_price')->textInput() ?>
        </div>
        <div class="col col-md-4">
            <?= $form->field($model, 'option_conditions')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col col-md-2">
            <?= $form->field($model, 'available_quantity')->textInput() ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'option_show_only')->widget(Select2::classname(), [
                'data' => $model->arrOptionIsShowOnly,
                'options' => ['placeholder' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'is_top_selling')->widget(Select2::classname(), [
                'data' => $model->arrIsTopSelling,
                'options' => ['placeholder' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'is_top_trending')->widget(Select2::classname(), [
                'data' => $model->arrIsTopTrending,
                'options' => ['placeholder' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'gender')->widget(Select2::classname(), [
                'data' => $model->arrGender,
                'options' => ['placeholder' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
        <div class="col col-md-2">
            <?= $form->field($model, 'is_cleaned')->widget(Select2::classname(), [
                'data' => $model->arrIsCleaned,
                'options' => ['placeholder' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
    </div>


    <?php //echo $form->field($model, 'receipt')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#product-category_id').change(function () {
            var categoryId = $(this).val();
            $.ajax({
                type: "POST",
                url: '<?php echo Url::to(['product/get-sub-category-list', 'category_id' => ""]); ?>' + categoryId,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#product-sub_category_id').html(response.dataList);
                    }
                }
            })
        });
    });
</script>