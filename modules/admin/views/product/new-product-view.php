<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\ProductCategory;
use app\models\Brand;
use app\models\Color;
use app\models\Product;
use app\models\ProductImage;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'View New Product';
$this->params['breadcrumbs'][] = ['label' => 'New Products', 'url' => ['new-product']];
$this->params['breadcrumbs'][] = 'View New Product';
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="products-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'format' => ['raw'],
                        'attribute' => 'user_id',
                        'label' => 'Seller',
                        'value' => function ($model) {
                            if (!empty($model) && !empty($model->user) && $model->user instanceof \app\modules\api\v2\models\User) {
                                $sellerName = Html::a($model->user->first_name . " " . $model->user->last_name, \yii\helpers\Url::to(['user/view?id=' . $model->user->id . "&pageId=" . $model->id . "&pageType=new"]), ['class' => '']);
                            } else {
                                $sellerName = '';
                            }

                            return $sellerName;
                        },
                    ],
                    [
                        'attribute' => 'name',
                        'label' => 'Product Name',
                    ],
                    [
                        'attribute' => 'category_id',
                        'label' => 'Category',
                        'value' => function ($model) {
                            return (!empty($model->category) && $model->category instanceof ProductCategory && !empty($model->category->name)) ? $model->category->name : "";
                        },
                    ],
                    [
                        'attribute' => 'sub_category_id',
                        'label' => 'Sub Category',
                        'value' => function ($model) {
                            return (!empty($model->subCategory) && $model->subCategory instanceof ProductCategory && !empty($model->subCategory->name)) ? $model->subCategory->name : "";
                        },
                    ],
                    [
                        'attribute' => 'price',
                        'value' => function ($model) {
                            return (!empty($model->price)) ? Yii::$app->formatter->asCurrency($model->price) : "";
                        },
                    ],
                    [
                        'attribute' => 'option_size',
                        'value' => function ($model) {
                            $result = "";
                            if (!empty($model) && $model instanceof Product) {
                                $result = $model->getProductSizeString();
                            }
                            return $result;
                        }
                    ],
                    [
                        'attribute' => 'option_price',
                        'label' => 'Tax',
                        'value' => function ($model) {
                            return (!empty($model->option_price)) ? Yii::$app->formatter->asCurrency($model->option_price) : "";
                        },
                    ],
                    'description:ntext',
                    [
                        'attribute' => 'images',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $data = "";
                            $images = $model->productImages;
                            if (!empty($images)) {
                                foreach ($images as $imageRow) {
                                    if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . '/' . $imageRow->name)) {
                                        $image_path = Yii::getAlias('@productImageAbsolutePath') . '/' . $imageRow->name;
                                    } else {
                                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                                    }

                                    Modal::begin([
                                        'id' => 'contentmodalProductImg_' . $imageRow->id,
                                        'header' => '<h4>Product Picture</h4>',
                                        'size' => Modal::SIZE_DEFAULT
                                    ]);

                                    echo Html::img($image_path, ['width' => '570']);

                                    Modal::end();

                                    $contentmodel = "contentmodelProductImg('" . $imageRow->id . "');";
                                    $data .= Html::img($image_path, ['alt' => 'some', 'class' => 'your_class_product_img', 'height' => '100px', 'width' => '100px', 'onclick' => $contentmodel]);
                                }
                            }
                            return $data;
                        },
                    ],
                    'available_quantity',
                    [
                        'attribute' => 'brand_id',
                        'value' => function ($model) {
                            return (!empty($model->brand) && $model->brand instanceof Brand && !empty($model->brand->name)) ? $model->brand->name : "-";
                        },
                    ],
                    [
                        'attribute' => 'option_color',
                        'value' => function ($model) {
                            $colorIds = "";
                            if (!empty($model) && !empty($model->option_color)) {
                                $colorIds = explode(",", $model->option_color);
                            }
                            $color = "";
                            if (!empty($colorIds)) {
                                $modelColors = Color::find()->where(['in', 'id', $colorIds])->all();
                                if (!empty($modelColors)) {
                                    foreach ($modelColors as $key => $modelColor) {
                                        if (!empty($modelColor) && $modelColor instanceof Color) {
                                            if ($key < count($modelColors) - 1) {
                                                $color .= $modelColor->name . ",";
                                            } else {
                                                $color .= $modelColor->name;
                                            }
                                        }
                                    }
                                }
                            }
                            return $color;
                        },
                    ],
                    [
                        'attribute' => 'gender',
                        'value' => function ($model) {
                            $genderFor = "";
                            if (!empty($model->gender)) {
                                if ($model->gender == Product::GENDER_FOR_FEMALE) {
                                    $genderFor = "Female";
                                } elseif ($model->gender == Product::GENDER_FOR_MALE) {
                                    $genderFor = "Male";
                                }
                            }
                            return $genderFor;
                        },
                    ],
                    [
                        'attribute' => 'is_cleaned',
                        'value' => function ($model) {
                            return (!empty($model->is_cleaned) && $model->is_cleaned == '1') ? "Yes" : "No";
                        },
                    ],
                    'height',
                    'weight',
                    'width',
                    [
                        'format' => ['raw'],
                        'enableSorting' => false,
                        'filter' => false,
                        'attribute' => 'receipt',
                        'value' => function ($model) {
                            $receiptImages = array_column($model->productReceipt, 'file');
                            $data = "";
                            $image_path = "";
                            if (!empty($receiptImages)) {
                                foreach ($receiptImages as $receiptImage) {
                                    if (!empty($receiptImage) && file_exists(Yii::getAlias('@productReceiptImageRelativePath') . '/' . $receiptImage)) {
                                        $image_path = Yii::getAlias('@productReceiptImageAbsolutePath') . '/' . $receiptImage;
                                    } else {
                                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                                    }
                                    Modal::begin([
                                        'id' => 'productReceiptModal_' . $model->id,
                                        'header' => '<h3>Product Receipt</h3>',
                                        'size' => Modal::SIZE_DEFAULT
                                    ]);

                                    echo Html::img($image_path, ['width' => '570']);

                                    Modal::end();
                                    $productImageModal = "productReceiptModal('" . $model->id . "');";
                                    $data .= Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $productImageModal, 'height' => '100px', 'width' => '100px']);
                                }
                            }
                            return $data;
                        },

                    ],
                    [
                        'attribute' => 'type',
                        'label' => 'Conditions',
                        'value' => function ($model) {
                            $producttype = "";
                            if (!empty($model->type) && $model->type == Product::PRODUCT_TYPE_NEW) {
                                $producttype = "New";
                            } else if (!empty($model->type) && $model->type == Product::PRODUCT_TYPE_USED) {
                                $producttype = "Used";
                            }
                            return $producttype;
                        },
                    ],
                    [
                        'attribute' => 'status_id',
                        'value' => function ($model) {
                            return (!empty($model) && !empty($model->status) && $model->status instanceof \app\models\ProductStatus) ? ucfirst($model->status->status) : '';
                        },
                    ],
                    [
                        'attribute' => 'is_admin_favourite',
                        'value' => function ($model) {
                            return (!empty($model) && !empty($model->is_admin_favourite) && $model->is_admin_favourite == Product::IS_ADMIN_FAVOURITE_YES) ? 'Yes' : 'No';
                        },
                    ],
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['new-product']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>

    </div>
</div>

<script type="text/javascript">
    function contentmodelProductImg(id) {
        $('#contentmodalProductImg_' + id).modal('show');
    }

    function productReceiptModal(id) {
        $('#productReceiptModal_' + id).modal('show');
    }
</script>