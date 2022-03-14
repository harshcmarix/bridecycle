<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Ads */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Ads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="ads-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'title',
                    [
                        'format' => ['raw'],
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@adsImageRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@adsImageAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            \yii\bootstrap\Modal::begin([
                                'id' => 'adsmodal_' . $model->id,
                                'header' => '<h3>Ads Image</h3>',
                                'size' => \yii\bootstrap\Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            \yii\bootstrap\Modal::end();
                            $adsmodal = "adsmodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => '', 'onclick' => $adsmodal, 'height' => '100px', 'width' => '100px']);
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    //'url:ntext',


                    [
                        'attribute' => 'category_id',
                        'value' => function ($model) {
                            $categoryName = "";
                            if (!empty($model->category) && $model->category instanceof \app\models\ProductCategory && !empty($model->category->name)) {
                                $categoryName = $model->category->name;
                            }
                            return $categoryName;
                        },
//                        'filter' => $product,
//                        'filterType' => GridView::FILTER_SELECT2,
//                        'filterWidgetOptions' => [
//                            'options' => ['prompt' => 'Select'],
//                            'pluginOptions' => [
//                                'allowClear' => true,
//                            ],
//                        ],
//                        'header' => 'Product',
                    ],

                    [
                        'attribute' => 'product_id',
                        'value' => function ($model) {
                            $productName = "";
                            if (!empty($model->product) && $model->product instanceof \app\models\Product && !empty($model->product->name)) {
                                $productName = $model->product->name;
                            }
                            return $productName;
                        },
//                        'filter' => $product,
//                        'filterType' => GridView::FILTER_SELECT2,
//                        'filterWidgetOptions' => [
//                            'options' => ['prompt' => 'Select'],
//                            'pluginOptions' => [
//                                'allowClear' => true,
//                            ],
//                        ],
//                        'header' => 'Product',
                    ],
                    [
                        'attribute' => 'brand_id',
                        'value' => function ($model) {
                            $brandName = "";
                            if (!empty($model->brand) && $model->brand instanceof \app\models\Brand && !empty($model->brand->name)) {
                                $brandName = $model->brand->name;
                            }
                            return $brandName;
                        },
//                        'filter' => $brand,
//                        'filterType' => GridView::FILTER_SELECT2,
//                        'filterWidgetOptions' => [
//                            'options' => ['prompt' => 'Select'],
//                            'pluginOptions' => [
//                                'allowClear' => true,
//                            ],
//                        ],
//                        'header' => 'Brand',
                    ],




                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == \app\models\Ads::STATUS_INACTIVE) {
                                $status = "Inactive";
                            } elseif ($model->status == \app\models\Ads::STATUS_ACTIVE) {
                                $status = "Active";
                            }
                            return $status;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>

    </div>
</div>

<script type="text/javascript">
    function adsmodal(id) {
        $('#adsmodal_' + id).modal('show');
    }
</script>