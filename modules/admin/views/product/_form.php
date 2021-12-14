<?php

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
                <div class="col col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col col-md-6">
                    <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                        'data' => $category,
                        'options' => ['placeholder' => 'Select Category'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'sub_category_id')->widget(Select2::classname(), [
                        'data' => $subcategory,
                        'options' => ['placeholder' => 'Select Sub-Category'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>
                <div class="col col-md-3">
                    <?= $form->field($model, 'price')->textInput() ?>
                </div>
                <div class="col col-md-3">
                    <?= $form->field($model, 'option_price')->textInput()->label('Tax') ?>
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
                <div class="col col-md-6">
                    <?php if (Yii::$app->controller->action->id == 'update') { //
                        $colorIds = explode(",", $model->option_color);
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
            </div>

            <div class="row">
                <div class="col col-md-2">
                    <?= $form->field($model, 'height')->textInput() ?>
                </div>
                <div class="col col-md-2">
                    <?= $form->field($model, 'weight')->textInput() ?>
                </div>
                <div class="col col-md-2">
                    <?= $form->field($model, 'width')->textInput() ?>
                </div>
                <div class="col col-md-4">
                    <?php if (Yii::$app->controller->action->id == 'update') { //
                        $sizeIds = explode(",", $model->option_size);
                        $model->option_size = $sizeIds;

                    } ?>
                    <?= $form->field($model, 'option_size')->widget(Select2::class, [
                        'data' => $size,
                        'options' => ['placeholder' => 'Select Size'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'multiple' => true
                        ],
                    ]); ?>
                </div>
                <div class="col col-md-2">
                    <?= $form->field($model, 'available_quantity')->textInput(['type' => 'number', 'min' => 0]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
                </div>
                <div class="col col-md-6">
                    <?= $form->field($model, 'other_info')->textarea(['rows' => 3]) ?>
                </div>
            </div>

            <!-- image validation -->
            <div class="row">

                <div class="col col-md-3 ship-country">

                    <lable><strong>Shipping Country</strong></lable>

                    <?php
                    echo $form->field($model, 'shipping_country[]')->checkboxList($shippingCountry, [
                        'item' => function ($index, $label, $name, $checked, $value) {
                            if (Yii::$app->controller->action->id == 'create') {
                                $checked = "checked";
                                $key = $index + 1;
                                echo "<div class='col-sm-12'><label><input tabindex='{$index}' class='shipping_country_$key' onclick='shippingCost(this)' type='checkbox' {$checked} name='{$name}' value='$value'> {$label}</label></div>";
                            } else {
                                $checked = "";
                                $key = $index + 1;
                                echo "<div class='col-sm-12'><label><input tabindex='{$index}' class='shipping_country_$key' onclick=\"return false;\" onkeydown=\"return false;\" type='checkbox' {$checked} name='{$name}' value='$value'> {$label}</label></div>";
                            }
                        }
                    ])->label(false) ?>

                    <?php echo $form->field($model, 'shipping_country_ids')->hiddenInput()->label(false) ?>

                </div>

                <div class="col col-md-3">

                    <label>Shipping Cost</label>
                    <div class="shipping_cost_price_tex_boxes">
                        <?php
                        if (Yii::$app->controller->action->id == 'create') {
                            $shippingPrice = $shippingCountry;
                        }
                        ?>
                        <?php foreach ($shippingPrice as $key => $shippingPriceRow) { ?>
                            <?php
                            $pKey = $key;

                            $class = 'shipping_country_cost_' . $pKey;
                            $readonly = false;
                            if (Yii::$app->controller->action->id == 'update') {
                                $readonly = true;
                            }
                            ?>

                            <?php echo $form->field($model, 'shipping_country_price[]')->textInput([
                                'value' => (!empty($shippingPrice) && !empty($shippingPrice[$key]) && !empty($shippingPrice[$key]['price']) && Yii::$app->controller->action->id == 'update') ? $shippingPrice[$key]['price'] : "",
                                'class' => $class,
                                'disabled' => $readonly

                            ])->label(false) ?>
                        <?php } ?>
                    </div>

                </div>

                <div class="col col-md-6">

                    <?= $form->field($model, 'images[]')->widget(FileInput::class, [
                        'options' => ['accept' => 'image/*', 'id' => 'product-images', 'multiple' => true],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showUpload' => false,
                            'maxFileCount' => 5,
                        ]
                    ])->label('Image <spna class="red">*</span>', ['class' => 'labelModalFormInline']); ?>


                    <?php if (Yii::$app->controller->action->id == 'update') { ?>
                        <?php
                        $is_product_images_empty = Product::IMAGE_EMPTY;
                        if (!empty($model->productImages)) {
                            $is_product_images_empty = Product::IMAGE_NOT_EMPTY;
                        } ?>

                        <?= $form->field($model, 'is_product_images_empty')->hiddenInput(['value' => $is_product_images_empty])->label(false) ?>
                        <div class="col col-md-12 edit-product_images">
                            <?php if (!empty($model->productImages)) {
                                $data = "";
                                foreach ($model->productImages as $imageRow) {

                                    if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . '/' . $imageRow->name)) {
                                        $image_path = Yii::getAlias('@productImageAbsolutePath') . '/' . $imageRow->name;
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
                                    $productImageModal = 'contentmodelProductImgEdit("' . $imageRow->id . '")';
                                    $data .= "<div class='product-image-block'>" . Html::img($image_path, ['class' => 'file-preview-image your_class', 'width' => '570', 'onclick' => $productImageModal]) . Html::a('<i class="fa fa-times"> </i>', ['delete-product-image', 'id' => $imageRow->id, 'product_id' => $model->id], ['class' => 'product-image-remove', 'data' => ['confirm' => 'Are you sure you want to delete this item?', 'method' => 'post',],]) . "</div>";
                                }

                                echo "<div>" . $data . "</div>";

                                echo \kartik\dialog\Dialog::widget([
                                    'libName' => 'krajeeDialog',
                                    'options' => [
                                    ], // default options
                                ]);

                            } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">

                <div class="col col-md-4">
                    <?= $form->field($model, 'gender')->widget(Select2::classname(), [
                        'data' => $model->arrGender,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]); ?>
                </div>

                <div class="col col-md-4">
                    <?= $form->field($model, 'is_admin_favourite')->widget(Select2::classname(), [
                        'data' => ['0' => 'No', '1' => 'Yes'],
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]); ?>
                </div>

                <div class="col col-md-4">
                    <?php
                    $disabledProductType = false;
                    if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->type) && $model->type == Product::PRODUCT_TYPE_USED) {
                        $disabledProductType = true;
                    }
                    ?>
                    <?= $form->field($model, 'type')->widget(Select2::classname(), [
                        'data' => ['n' => 'New', 'u' => 'Used'],
                        'pluginOptions' => [
                            'allowClear' => false,
                            'disabled' => $disabledProductType
                        ],
                    ])->label('Conditions'); ?>
                </div>

            </div>

            <div class="row">

                <div class="col col-md-6">
                    <?= $form->field($model, 'is_cleaned')->widget(Select2::classname(), [
                        'data' => $model->arrIsCleaned,
                        'options' => ['placeholder' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>
                </div>

                <div class="col col-md-6">
                    <?php
                    $disabled = false;
                    if (Yii::$app->controller->action->id == 'update' && !empty($model) && !empty($model->status_id) && in_array($model->status_id, [\app\models\ProductStatus::STATUS_APPROVED, \app\models\ProductStatus::STATUS_IN_STOCK])) {
                        $disabled = true;
                    }
                    ?>
                    <?= $form->field($model, 'status_id')->widget(Select2::classname(), [
                        'data' => $status,
                        'pluginOptions' => [
                            'allowClear' => false,
                            'disabled' => $disabled
                        ],
                    ]); ?>
                </div>

            </div>

            <div class="row">

                <div class="col col-md-6 receiptUpload"
                     style="display: <?php echo (Yii::$app->controller->action->id == 'update' && $model->is_cleaned == 1) ? 'block' : 'none'; ?>">
                    <?php
                    $is_product_receipt_images_empty = Product::IMAGE_EMPTY;
                    if (Yii::$app->controller->action->id == 'update') {
                        if (!empty($model->productReceipt)) {
                            $is_product_receipt_images_empty = Product::IMAGE_NOT_EMPTY;
                        }
                    }
                    ?>
                    <?= $form->field($model, 'is_product_receipt_images_empty')->hiddenInput(['value' => $is_product_receipt_images_empty])->label(false) ?>

                    <?= $form->field($model, 'receipt[]')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'image/*', 'id' => 'product-receipt', 'multiple' => true],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showUpload' => false,
                            'maxFileCount' => 5,
                        ]
                    ])->label('Receipt <spna class="red">*</span>', ['class' => 'labelModalFormInline']); ?>
                </div>

            </div>

            <div class="row">

                <?php if ((Yii::$app->controller->action->id == 'update') && !empty($model->productReceipt)) {
                    $data = "";
                    foreach ($model->productReceipt as $imageRow) {
                        if (!empty($imageRow) && $imageRow instanceof \app\models\ProductReceipt && !empty($imageRow->file) && file_exists(Yii::getAlias('@productReceiptImageRelativePath') . '/' . $imageRow->file)) {
                            $image_path = Yii::getAlias('@productReceiptImageAbsolutePath') . '/' . $imageRow->file;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        Modal::begin([
                            'id' => 'contentmodalProductImgReceiptEdit_' . $imageRow->id,
                            'header' => '<h4>Receipt Picture</h4>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);
                        echo Html::img($image_path, ['width' => '570']);
                        Modal::end();
                        $productReceiptModal = 'contentmodelProductImgReceiptEdit("' . $imageRow->id . '")';
                        $data .= "<div class='product-receipt-image-block'>" . Html::img($image_path, ['class' => 'file-preview-image your_class', 'width' => '570', 'onclick' => $productReceiptModal]) . Html::a('<i class="fa fa-times"> </i>', ['delete-product-receipt-image', 'id' => $imageRow->id, 'product_id' => $model->id], ['class' => 'product-receipt-remove', 'data' => ['confirm' => 'Are you sure you want to delete this item?', 'method' => 'post',],]) . "</div>";
                    }
                    echo "<div class='product-receipt-preview'>" . $data . "</div>";

                    echo \kartik\dialog\Dialog::widget([
                        'libName' => 'krajeeDialog',
                        'options' => [
                        ], // default options
                    ]);
                } ?>

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

        var isProductType = $('#product-type').val();

        if (isProductType == 'u') {
            $('#product-option_color').select2({
                width: 523,
                placeholder: "Select Color",
                allowClear: true,
                multiple: false
            });
            $('#product-option_size').select2({
                width: 338,
                placeholder: "Select Size",
                allowClear: true,
                multiple: false
            });
        } else {
            $('#product-option_color').select2({
                width: 523,
                placeholder: "Select Color",
                allowClear: true,
                multiple: true
            });
            $('#product-option_size').select2({
                width: 338,
                placeholder: "Select Size",
                allowClear: true,
                multiple: true
            });
        }

        $('#product-option_price, #product-price, .shipping_country_cost_1, .shipping_country_cost_2, .shipping_country_cost_3, .shipping_country_cost_4, .shipping_country_cost_5').keypress(function (event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });

        "<?php if (Yii::$app->controller->action->id == 'update') { ?>"
        var arrCheckedCountry = [];
        "<?php if (!empty($shippingPrice)) { ?>"
        "<?php foreach ($shippingPrice as $key => $list) { ?>"
        "<?php if (!empty($list) && $list instanceof \app\models\ShippingPrice) { ?>"
        "<?php if (!empty($list->id)) { ?>"
        var Id = "<?php echo $key + 1 ?>";
        $('.shipping_country_' + Id).prop("checked", true);
        arrCheckedCountry.push(Id);
        "<?php } ?>"
        "<?php } ?>"
        "<?php } ?>"
        "<?php } ?>"
        $("#product-shipping_country_ids").val(arrCheckedCountry.join(','));
        "<?php } ?>"

        $('#product-category_id').change(function () {
            var categoryId = $(this).val();
            $.ajax({
                type: "POST",
                url: '<?php echo Url::to(['product/get-sub-category-list', 'category_id' => ""]); ?>' + categoryId,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#product-sub_category_id').html(response.dataList);
                        $('#product-option_size').html(response.dataSizeList);
                    }
                }
            })
        });

        $('#product-is_cleaned').change(function () {
            var valueData = $(this).val();
            if (valueData == 1) {
                $('.receiptUpload').show();
            } else {
                $('.receiptUpload').hide();
            }
        });

        $('#product-type').change(function () {
            var productType = $(this).val();

            if (productType == 'u') {
                $('#product-option_color').select2({
                    width: 523,
                    placeholder: "Select Color",
                    allowClear: true,
                    multiple: false
                });
                $('#product-option_size').select2({
                    width: 338,
                    placeholder: "Select Size",
                    allowClear: true,
                    multiple: false
                });
            } else {
                $('#product-option_color').select2({
                    width: 523,
                    placeholder: "Select Color",
                    allowClear: true,
                    multiple: true
                });
                $('#product-option_size').select2({
                    width: 338,
                    placeholder: "Select Size",
                    allowClear: true,
                    multiple: true
                });
            }
        });
    });

    function contentmodelProductImgEdit(id) {
        $('#contentmodalProductImgEdit_' + id).modal('show');
    }

    function contentmodelProductImgReceiptEdit(id) {
        $('#contentmodalProductImgReceiptEdit_' + id).modal('show');
    }

    function shippingCost(obj) {
        var previousCheckedDataVal = "";
        if ($("#product-shipping_country_ids").val()) {
            previousCheckedDataVal = $("#product-shipping_country_ids").val()
        }
        var idIndex = $(obj).attr('tabindex');
        idIndex = parseInt(idIndex) + 1;

        var updatedString = "";
        var errDiv = $('.shipping_country_cost_' + idIndex).parent('.field-product-shipping_country_price').children('.help-block');
        if ($(obj).prop("checked") == true) {
            $('.shipping_country_' + idIndex).val(idIndex);
            var html = '';
            "<?php if (Yii::$app->controller->action->id == 'update') { ?>"
            html += '<div class="form-group field-product-shipping_country_price">';
            html += '<input type="text" id="product-shipping_country_price" class="shipping_country_cost_' + idIndex + '" name="Product[shipping_country_price][]" value="">';
            html += '<div class="help-block"></div></div>';
            $('.shipping_cost_price_tex_boxes').append(html);
            previousCheckedDataVal = previousCheckedDataVal + "," + idIndex;
            updatedString = previousCheckedDataVal;
            "<?php } else { ?>"
            $('.shipping_country_cost_' + idIndex).show();
            errDiv.show();
            "<?php } ?>"
        } else if ($(obj).prop("checked") == false) {
            //$('.shipping_country_' + idIndex).val('');
            "<?php if (Yii::$app->controller->action->id == 'update') { ?>"
            $('.shipping_country_cost_' + (idIndex)).parent('.field-product-shipping_country_price').remove();
            if (idIndex > 1) {
                updatedString = previousCheckedDataVal.replace("," + idIndex, "");
            } else {
                updatedString = previousCheckedDataVal.replace(idIndex, "");
            }
            "<?php } else { ?>"
            $('.shipping_country_cost_' + idIndex).hide();
            $('.shipping_country_cost_' + idIndex).val('');
            errDiv.html("");
            errDiv.hide();
            "<?php } ?>"
        }
        $("#product-shipping_country_ids").val(updatedString);
    }

</script>