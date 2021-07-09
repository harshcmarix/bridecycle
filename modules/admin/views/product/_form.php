<?php

use app\models\Color;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\models\ProductImage;
use yii\bootstrap\Modal;
use kartik\file\FileInput;
use app\models\Product;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */

$this->registerCssFile("@web/css/toggle-switch.css");
$this->registerJsFile("@web/js/toggle-switch.js");
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="products-form">

            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <div class="row">
                <div class="col col-md-10">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col col-md-2">
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
                <div class="col col-md-4">
                    <?= $form->field($model, 'brand_id')->widget(Select2::classname(), [
                        'data' => $brand,
                        'options' => ['placeholder' => 'Select Brand'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>
                <div class="col col-md-2">
                    <?php if (Yii::$app->controller->action->id == 'update') {
//                        if(!is_string($model->option_color)){
//                            $model->option_color = implode(",", $model->option_color);
//                        }
                        $colorIds = explode(",", $model->option_color);
                        //$color = ArrayHelper::map(Color::find()->where(['in', 'id', $colorIds])->all(), 'id', 'name');
                        $model->option_color = $colorIds;
                    } ?>
                    <?= $form->field($model, 'option_color')->widget(Select2::classname(), [
                        'data' => $color,
                        'options' => ['placeholder' => 'Select Color'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'multiple' => true
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
                <div class="col col-md-12">
                    <?= $form->field($model, 'other_info')->textarea(['rows' => 3]) ?>
                </div>
            </div>
            <!-- image validation -->

            <div class="row">
                <div class="col col-md-4">
                    <lable><strong>Shipping Country</strong></lable>
                    <?= $form->field($model, 'shipping_country_id[]')->checkboxList($shippingCountry, [

                        'item' => function ($index, $label, $name, $checked, $value) {
                            $checked = 'checked';
                            $key = $index + 1;
                            echo "<div class='col-sm-12'><label><input tabindex='{$index}' class='shipping_country_$key' type='checkbox' {$checked} name='{$name}' value='{$value}'> {$label}</label></div>";

                        }])->label(false) ?>
                </div>

                <div class="col col-md-4">
                    <label>Shipping Cost</label>
                    <?php
                    if (Yii::$app->controller->action->id == 'create') {
                        $shippingPrice = $shippingCountry;
                    }
                    ?>
                    <?php foreach ($shippingPrice as $key => $shippingPriceRow) { ?>
                        <?= $form->field($model, 'shipping_country_price[]')->textInput(['value' => (!empty($shippingPrice) && !empty($shippingPrice[$key]) && Yii::$app->controller->action->id == 'update') ? $shippingPrice[$key] : "",
                            'class' => 'shipping_country_' . $key,

                        ])->label(false) ?>
                    <?php } ?>

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
                    <?php
                    $is_product_images_empty = Product::IMAGE_EMPTY;
                    if (!empty($model->productImages)) {
                        $is_product_images_empty = Product::IMAGE_NOT_EMPTY;
                    } ?>

                    <?= $form->field($model, 'is_product_images_empty')->hiddenInput(['value' => $is_product_images_empty])->label(false) ?>
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
                    <?= $form->field($model, 'option_price')->textInput()->label('Tax') ?>
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
                    <?php echo $form->field($model, 'is_top_selling')
                        ->checkBox(['label' => $model->getAttributeLabel('is_top_selling'), 'id' => 'product-is_top_selling', 'selected' => false, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]); ?>
                </div>
                <div class="col col-md-2">
                    <?php echo $form->field($model, 'is_top_trending')
                        ->checkBox(['label' => $model->getAttributeLabel('is_top_trending'), 'id' => 'product-is_top_trending', 'selected' => false, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]);
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
                        //'options' => ['placeholder' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>

                <div class="col col-md-2">
                    <?= $form->field($model, 'is_admin_favourite')->widget(Select2::classname(), [
                        'data' => ['0' => 'No', '1' => 'Yes'],
                        //'options' => ['placeholder' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>

                <div class="col col-md-2">
                    <?= $form->field($model, 'type')->widget(Select2::classname(), [
                        'data' => ['n' => 'New', 'u' => 'Used'],
                        //'options' => ['placeholder' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Product Type'); ?>
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
    $(document).ready(function () {

        $('#product-option_price, #product-price').keypress(function (event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });

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