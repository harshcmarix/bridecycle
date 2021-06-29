<?php

use \app\modules\admin\widgets\GridView;
use yii\helpers\{
    Html,
    ArrayHelper,
    Url
};
use yii\bootstrap\Modal;
use kartik\editable\Editable;
use app\models\ProductCategory;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Category';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">


    <?php
    echo GridView::widget([
        'id' => 'product-category-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof ProductCategory) {
                        $id = $model->id;
                    }
                    return $id;
                },
                'width' => '8%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'format' => ['raw'],
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'image',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $model->image)) {
                        $image_path = Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $model->image;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'productcategorymodal_' . $model->id,
                        'header' => '<h3>Category Image</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $productcategorymodal = "productcategorymodal('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $productcategorymodal, 'height' => '100px', 'width' => '100px']);
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'name',
                'value' => function ($model) {
                    $name = '';
                    if ($model instanceof ProductCategory) {
                        $name = $model->name;
                    }
                    return $name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'attribute' => 'parent_category_id',
                'value' => function ($model) {
                    $parent_name = '';
                    if ($model->parent instanceof ProductCategory) {
                        $parent_name = $model->parent->name;
                    }
                    return $parent_name;
                },

                'filter' => ArrayHelper::map($parent_category, 'id', 'name'),
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => ''],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width'=>'20px'
                    ],
                ],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            // [
            //     'attribute' => 'created_at',
            //     'value' => function ($model) {
            //         $created_at = '';
            //         if ($model instanceof ProductCategory) {
            //             $created_at = $model->created_at;
            //         }
            //         return $created_at;
            //     },
            //     'filter' => false,
            //     'header' => '',
            //     'headerOptions' => ['class' => 'kartik-sheet-style']
            // ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '12%'
            ],
        ],
        'pjax' => true, // pjax is set to always true for this demo
        // set your toolbar
        'toolbar' => [
            [
                'content' =>
                    Html::button('<i class="fa fa-plus-circle"> Add Category</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Category'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/product-category/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['product-category/index']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}',
        ],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],

        // parameters from the demo form
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => false,
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            //'heading' => 'Product Categories',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'product category',
        'itemLabelPlural' => 'Product Categories'
    ]);
    ?>
    </div>
</div>
<script type="text/javascript">
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }
</script>
