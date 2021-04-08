<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use kartik\editable\Editable;
use app\models\ProductCategory;
?>

<div class="users-index table-responsive">
<?php
$gridColumns = [
[
'class' => 'kartik\grid\SerialColumn',
],
// [
//     'attribute' => 'id',
//     'value' => function ($model) {
//         return $model->id;
//     },
//     'header'=>'',
//     'headerOptions'=>['class'=>'kartik-sheet-style']
// ],
[
'format' => ['raw'],
'enableSorting' => false,
'filter' => false,
'attribute' => 'image',
'value' => function ($model) {
    $image_path = "";
    if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
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
        return $model->name;
    },
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'parent_category_id',
    'value' => function ($model) {
        if($model->parent instanceof ProductCategory){
            return $model->parent->name;
        }
            return null;
    },
    
    'filter'=>ArrayHelper::map($parent_category,'id','name'),
    'filterType' => GridView::FILTER_SELECT2,
    'filterWidgetOptions' => [
        'options' => ['prompt' => ''],
        'pluginOptions' => [
            'allowClear' => true,
            // 'width'=>'20px'
        ],
    ],
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
 'attribute' => 'created_at',
    'value' => function ($model) {
        return $model->created_at;
    },
    'filter'=>false,
    'header'=>'',
    'headerOptions'=>['class'=>'kartik-sheet-style']
],
[
    'class' => 'kartik\grid\ActionColumn',
],

];
echo GridView::widget([
    'id' => 'kv-grid-demo',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'pjax' => true, // pjax is set to always true for this demo
    // set your toolbar
    'toolbar' =>  [
        [
            'content' =>
                Html::button('Add Category', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Category'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/product-category/create']) . "';",
                ]), 
            'options' => ['class' => 'btn-group mr-2']
        ],
//        '{export}',
        '{toggleData}',
    ],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    // set export properties
//    'export' => [
//        'fontAwesome' => true
//    ],
    // parameters from the demo form
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'responsive' => true,
    // 'hover' => $hover,
    // 'showPageSummary' => $pageSummary,
    'panel' => [
        'type' => GridView::TYPE_PRIMARY,
        'heading' => 'Product Categories',
    ],
    'persistResize' => false,
    'toggleDataOptions' => ['minCount' => 10],
    //'exportConfig' => $exportConfig,
    'itemLabelSingle' => 'Product Category',
    'itemLabelPlural' => 'Product Categories'
]);


?>
</div>
<script type="text/javascript">
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }
</script>
