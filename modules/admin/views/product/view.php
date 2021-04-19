<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\ProductCategory;
use app\models\Brand;
use app\models\Product;
use app\models\ProductImage;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="products-view">

    <h1><?= Html::encode($this->title) ?></h1>

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
            'id',
            'name',
            'number',
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
            'price',
            'option_size',
            'option_price',
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
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
    <p>
        <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>
</div>

<script type="text/javascript">
    function contentmodelProductImg(id) {
        $('#contentmodalProductImg_' + id).modal('show');
    }
</script>