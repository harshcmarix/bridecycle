<?php

use \app\modules\admin\widgets\GridView;
use yii\helpers\{
    Html,
    ArrayHelper,
    Url
};
use yii\bootstrap\Modal;
use kartik\editable\Editable;
use app\models\Brand;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerCssFile("@web/css/toggle-switch.css");
$this->registerJsFile("@web/js/toggle-switch.js");

$this->title = 'Brand';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="users-index table-responsive">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    echo GridView::widget([
        'id' => 'brand-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof Brand) {
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
                    $name = '';
                    if ($model instanceof Brand) {
                        $name = $model->name;
                    }
                    return $name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style']
            ],
            [
                'format' => ['raw'],
                'attribute' => 'is_top_brand',
                'value' => function ($model) {
                    $is_top_brand = '';
                    if ($model instanceof Brand) {
                        // $is_top_brand = Brand::IS_TOP_BRAND_OR_NOT[$model->is_top_brand];                      
                        $is_top_brand = Html::checkbox("", $model->is_top_brand, ['class' => 'is-top-brand', 'data-key' => $model->id, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No",]);
                    }
                    return $is_top_brand;
                },
                'filter' => Brand::IS_TOP_BRAND_OR_NOT,
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
            //         if ($model instanceof Brand) {
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

        'pjax' => false, // pjax is set to always true for this demo
        // set your toolbar
        'toolbar' => [
            [
                'content' =>
                    Html::button('<i class="fa fa-plus-circle"> Add Brand</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Brand'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/brand/create']) . "';",
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['brand/index']) . "';",
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
            'type' => GridView::TYPE_PRIMARY,
            'heading' => 'Brands',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'brand',
        'itemLabelPlural' => 'Brands'
    ]);


    ?>
</div>
<script type="text/javascript">
$(document).on('change','.is-top-brand',function(){
     var id = $(this).attr('data-key');
    if($(this).is(':checked')){
        krajeeDialog.confirm('Are you sure you want to add this brand to top brand?', function (out) {
                if (out) {
                    var is_top_brand = '1';
                    $.ajax({
                        url: "<?php echo Url::to(['brand/update-top-brand']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_brand': is_top_brand
                        },
                        success: function (response) {
                            location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
       
    }else{
         krajeeDialog.confirm('Are you sure you want to remove this brand from top brand?', function (out) {
                if (out) {
                    var is_top_brand = '0';
                    $.ajax({
                        url: "<?php echo Url::to(['brand/update-top-brand']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_brand': is_top_brand
                        },
                        success: function (response) {
                            location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
       
    }
});
    function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>
