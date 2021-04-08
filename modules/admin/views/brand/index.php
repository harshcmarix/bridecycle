<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use kartik\editable\Editable;
use app\models\Brand;
use kartik\select2\Select2;
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
    if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $model->image)) {
        $image_path = Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $model->image;
    } else {
        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
    }
    Modal::begin([
        'id' => 'brandmodal_' . $model->id,
        'header' => '<h3>Brand Image</h3>',
        'size' => Modal::SIZE_DEFAULT
    ]);

    echo Html::img($image_path, ['width' => '570']);

    Modal::end();
    $brandmodal = "brandmodal('" . $model->id . "');";
    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $brandmodal, 'height' => '100px', 'width' => '100px']);
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
    'attribute' => 'is_top_brand',
    'value' => function ($model) {
        if(!empty($model->is_top_brand) && $model->is_top_brand == '1'){
                return 'yes';
        }
        return 'no';
    },
    'filter'=>Brand::IS_TOP_BRAND_OR_NOT,
    // 'filter' => Select2::widget([
    //             'model' => $searchModel,
    //             'attribute' => 'is_top_brand',
    //             // 'value' => $searchModel->is_top_brand,
    //             'data' => Brand::IS_TOP_BRAND_OR_NOT,
    //             'size' => Select2::MEDIUM,
    //             'options' => [
    //                 'placeholder' => 'select',
    //             ],
    //             'pluginOptions' => [
    //                 'allowClear' => true
    //             ]
    //         ]),
            // 'content' => function ($data) {
            //     return isset($data->isShopOwner[$data['is_shop_owner']]) ? $data->isShopOwner[$data['is_shop_owner']] : '-';
            // },
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
                Html::button('Add Brand', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Brand'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/brand/create']) . "';",
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
        'heading' => 'Brands',
    ],
    'persistResize' => false,
    'toggleDataOptions' => ['minCount' => 10],
    //'exportConfig' => $exportConfig,
    'itemLabelSingle' => 'Brand',
    'itemLabelPlural' => 'Brands'
]);


?>
</div>
<script type="text/javascript">
    function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>
