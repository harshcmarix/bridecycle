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

//$this->title = $model->name;
$this->title = 'View Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'View Product';
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="products-view">

            <!--    <p>-->
            <!--        --><?php //echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <!--        --><?php //echo Html::a('Delete', ['delete', 'id' => $model->id], [
            //            'class' => 'btn btn-danger',
            //            'data' => [
            //                'confirm' => 'Are you sure you want to delete this item?',
            //                'method' => 'post',
            //            ],
            //        ]) ?>
            <!--    </p>-->

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    [
                        'attribute' => 'user_id',
                        'label' => 'User',
                        'value' => function ($model) {
                            return (!empty($model) && !empty($model->user) && $model->user instanceof \app\modules\api\v1\models\User) ? $model->user->first_name . " " . $model->user->last_name : '';
                        },
                    ],
                    'name',
                    //'number',
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
                    'option_size',
                    [
                        'attribute' => 'option_price',
                        'value' => function ($model) {
                            return (!empty($model->option_price)) ? Yii::$app->formatter->asCurrency($model->option_price) : "";
                        },
                    ],
                    'option_conditions',
                    [
                        'attribute' => 'option_show_only',
                        'value' => function ($model) {
                            return (!empty($model->option_show_only) && $model->option_show_only == 1) ? "Yes" : "No";
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
                                    if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                                        $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
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
                        'attribute' => 'is_top_selling',
                        'value' => function ($model) {
                            return (!empty($model->is_top_selling) && $model->is_top_selling == '1') ? "Yes" : "No";
                        },
                    ],
                    [
                        'attribute' => 'is_top_trending',
                        'value' => function ($model) {
                            return (!empty($model->is_top_trending) && $model->is_top_trending == '1') ? "Yes" : "No";
                        },
                    ],
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
                                            if ($key < count($modelColors)-1) {
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
                    'receipt',
                    [
                        'attribute' => 'type',
                        'label' => 'Product Type',
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
//            'created_at:datetime',
//            'updated_at:datetime',
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>

    </div>
</div>

<script type="text/javascript">
    function contentmodelProductImg(id) {
        $('#contentmodalProductImg_' + id).modal('show');
    }
</script>