<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\models\ProductImage;
use yii\bootstrap\Modal;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */

$this->registerCssFile("@web/css/toggle-switch.css");
$this->registerJsFile("@web/js/toggle-switch.js");
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

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
        <div class="col col-md-<?php echo (Yii::$app->controller->action->id == 'update') ? '4' : '8' ?>">
            <?= $form->field($model, 'images[]')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*', 'id' => 'product-images', 'multiple' => true],
                'pluginOptions' => [
                    'allowedFileExtensions' => ['jpg', 'png'],
                    'showPreview' => true,
                    //'showCaption' => true,
                    //'showRemove' => true,
                    'showUpload' => false,
                    'maxFileCount' => 5,
                ]
            ]); ?>
        </div>
        <?php if (Yii::$app->controller->action->id == 'update') { ?>
            <div class="col col-md-8 edit-product_images">
                <?php if (!empty($model->productImages)) {
                    $data = "";
                    foreach ($model->productImages as $imageRow) {

                        if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                            $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }


                        Modal::begin([
                            'id' => 'contentmodalProductImgEdit_' . $imageRow->id,
                            'header' => '<h4>Product Picture</h4>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);

                        Modal::end();
                        // $contentmodel = "contentmodelProductImgEdit('" . $imageRow->id . "');";
                        $data .= "<a href='javascript:void(0);' class='Product-edit_view-peoduct_picture' onclick='contentmodelProductImgEdit(" . $imageRow->id . ")'><i class='fa fa-eye'></i> </a>" . Html::a('<i class="fa fa-times"> </i>', ['delete-product-image', 'id' => $imageRow->id, 'product_id' => $model->id], ['class' => '', 'data' => ['confirm' => 'Are you sure you want to delete this item?', 'method' => 'post',],]) . Html::img($image_path, ['alt' => 'some', 'class' => 'update_product_img', 'height' => '100px', 'width' => '100px']);

                    }
                    echo $data;

                    echo \kartik\dialog\Dialog::widget([
                        'libName' => 'krajeeDialog',
                        'options' => [
                            //'class' => 'admin_delete_record',
                            //'type' => \kartik\dialog\Dialog::TYPE_DANGER,
                        ], // default options
                    ]);

                } ?>
            </div>
        <?php } ?>

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
            <?= $form->field($model, 'available_quantity')->textInput(['type' => 'number', 'min' => 0]) ?>
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
            <?php
            if (!empty($model->is_top_selling) && $model->is_top_selling == \app\models\Product::IS_TOP_SELLING_YES) {
                echo $form->field($model, 'is_top_selling')
                    ->checkBox(['label' => $model->getAttributeLabel('is_top_selling'), 'uncheck' => null, 'id' => 'product-is_top_selling', 'checked' => true, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]);
            } else {
                echo $form->field($model, 'is_top_selling')
                    ->checkBox(['label' => $model->getAttributeLabel('is_top_selling'), 'uncheck' => null, 'id' => 'product-is_top_selling', 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]);
            }
            ?>
        </div>
        <div class="col col-md-2">
            <?php
            if (!empty($model->is_top_trending) && $model->is_top_trending == \app\models\Product::IS_TOP_TRENDING_YES) {
                echo $form->field($model, 'is_top_trending')
                    ->checkBox(['label' => $model->getAttributeLabel('is_top_trending'), 'uncheck' => null, 'id' => 'product-is_top_trending', 'checked' => true, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]);
            } else {
                echo $form->field($model, 'is_top_trending')
                    ->checkBox(['label' => $model->getAttributeLabel('is_top_trending'), 'uncheck' => null, 'id' => 'product-is_top_trending', 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]);
            }
            ?>
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

    <div class="row">
        <?php //echo $form->field($model, 'receipt')->textInput(['maxlength' => true]) ?>
        <div class="col col-md-2">
            <?= $form->field($model, 'status_id')->widget(Select2::classname(), [
                'data' => $status,
                'options' => ['placeholder' => 'Select'],
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

    function contentmodelProductImgEdit(id) {
        $('#contentmodalProductImgEdit_' + id).modal('show');
    }
</script>