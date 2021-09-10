<?php

use app\models\ProductStatus;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use \app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use yii\helpers\Url;
use app\models\ProductCategory;
use app\models\ProductImage;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;


$this->registerCssFile("@web/css/toggle-switch.css");
$this->registerJsFile("@web/js/toggle-switch.js");

?>

<div class="career-index box box-primary">
    <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <?php
        $gridColumns = [
            ['class' => 'yii\grid\CheckboxColumn'],
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'value' => function ($model) {
                    return $model->name;
                },
                'header' => 'Name',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'format' => 'raw',
                'value' => function ($model) {
                    $images = $model->productImages;
                    $dataImages = [];
                    foreach ($images as $imageRow) {

                        if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                            $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }

                        Modal::begin([
                            'id' => 'contentmodalProductImgIndex_' . $imageRow->id,
                            'header' => '<h4>Product Picture</h4>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);

                        Modal::end();

                        $contentmodel = "contentmodelProductImgIndex('" . $imageRow->id . "');";

                        $dataImages[] = ['content' => Html::img($image_path, ['width' => '570', 'alt' => 'Product Image']),
                            // 'caption' => '<h4>Product Image</h4><p>This is the product caption text</p>',
                            'caption' => '<a href="javascript:void(0);" class="product-index_img_view" onclick="' . $contentmodel . '" ><i class="fa fa-eye"></i></a>',
                            'options' => ['interval' => '600',]
                        ];
                    }

                    $result = "";
                    if (!empty($dataImages)) {
                        $result = \yii\bootstrap\Carousel::widget(
                            ['items' => $dataImages]
                        );
                    }

                    return $result;
                },
                'header' => 'Images',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],

            ],
//        [
//            'attribute' => 'number',
//            'value' => function ($model) {
//                return $model->number;
//            },
//            'header' => '',
//            'headerOptions' => ['class' => 'kartik-sheet-style']
//        ],
            [
                'attribute' => 'category_id',
                'value' => function ($model) {
                    return (!empty($model->category) && $model->category instanceof ProductCategory && !empty($model->category->name)) ? $model->category->name : "";
                },
                'filter' => $categories,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => 'Category',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'sub_category_id',
                'value' => function ($model) {
                    return (!empty($model->subCategory) && $model->subCategory instanceof ProductCategory && !empty($model->subCategory->name)) ? $model->subCategory->name : "";
                },
                'filter' => $subCategories,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => 'Sub Category',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'price',
                'value' => function ($model) {
                    return (!empty($model->price)) ? Yii::$app->formatter->asCurrency($model->price) : "";
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],

            [
                'attribute' => 'option_price',
                'value' => function ($model) {
                    return (!empty($model->option_price)) ? Yii::$app->formatter->asCurrency($model->option_price) : "";
                },
                'header' => 'Tax',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],
            ],
            [
                'attribute' => 'option_conditions',
                'value' => function ($model) {
                    return $model->option_conditions;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],
            ],
            [
                'attribute' => 'option_size',
                'value' => function ($model) {
                    return $model->option_size;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],
            ],
            [
                'attribute' => 'option_show_only',
                'value' => function ($model) {
                    return ($model->option_show_only == '1') ? "Yes" : "No";
                },
                'filter' => $searchModel->arrOptionIsShowOnly,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],
            ],
            [
                'attribute' => 'available_quantity',
                'value' => function ($model) {
                    return (!empty($model) && !empty($model->available_quantity)) ? $model->available_quantity : "-";
                },
                'header' => '',
                'width' => '5%',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'status_id',
                'value' => function ($model) {
                    return (!empty($model) && !empty($model->status) && $model->status instanceof ProductStatus && !empty($model->status->status)) ? $model->status->status : "";
                },
                'filter' => $arrStatus,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => 'Status',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],

            [
                'format' => ['raw'],
                'attribute' => 'is_top_selling',
                'value' => function ($model) {
                    return Html::checkbox("", $model->is_top_selling, ['class' => 'topSelling', 'data-key' => $model->id, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No"]);
                },
                'filter' => $searchModel->arrIsTopSelling,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'format' => ['raw'],
                'attribute' => 'is_top_trending',
                'value' => function ($model) {
                    return Html::checkbox("", $model->is_top_trending, ['class' => 'topTrending', 'data-key' => $model->id, 'data-toggle' => "toggle", 'data-onstyle' => "success", 'data-on' => "Yes", 'data-off' => "No",]);
                },
                'filter' => $searchModel->arrIsTopTrending,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'type',
                'value' => function ($model) {
                    $producttype = "";
                    if (!empty($model->type) && $model->type == \app\models\Product::PRODUCT_TYPE_NEW) {
                        $producttype = "New";
                    } else if (!empty($model->type) && $model->type == \app\models\Product::PRODUCT_TYPE_USED) {
                        $producttype = "Used";
                    }
                    return $producttype;
                },
                'filter' => $productType,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => 'Product Type',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],

            [
                'attribute' => 'is_admin_favourite',
                'header' => 'Admin Favourite',
                'headerOptions' => ['style' => 'text-align: center !important'],
                'value' => function ($model) {

                    return (!empty($model->is_admin_favourite) && $model->is_admin_favourite == \app\models\Product::IS_ADMIN_FAVOURITE_YES) ? 'Yes' : "No";
                },
                'filter' => ['1' => 'Yes', '0' => 'No'],
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
            ],
        ];

        echo GridView::widget([
            'id' => 'product-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
//        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
//        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
//        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true, // pjax is set to always true for this demo
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fa fa-plus-circle"> Add Product </i>', [
                            'class' => 'btn btn-success',
                            'title' => 'Add Product',
                            'onclick' => "window.location.href = '" . Url::to(['product/create']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . Url::to(['product/index']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Multiple Delete </i>', [
                            'class' => 'btn btn-danger',
                            'title' => 'Multiple Delete',
                            'id' => "btn-delete_all",
                            //'onclick' => "window.location.href = '" . Url::to(['product/index']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                '{toggleData}',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                //'heading' => 'Product',
            ],
            'emptyTextOptions' => [
                'class' => 'empty text-center'
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'product',
            'itemLabelPlural' => 'Products',
        ]);
        ?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#btn-delete_all').click(function () {
            var atLeastOneIsChecked = $('input[name="selection[]"]:checked').length > 0;
            if (atLeastOneIsChecked == true) {
                krajeeDialog.confirm('Are you sure you want to delete this Products?', function (out) {
                    if (out) {
                        var ids = [];
                        $('input[name="selection[]"]:checked').each(function (index, obj) {
                            ids.push(obj.value);
                        });

                        $.ajax({
                            type: 'POST',
                            url: "<?php echo Url::to(['product/delete-multiple']); ?>",
                            data: {
                                'ids': ids,
                                _csrf: yii.getCsrfToken()
                            },
                            dataType: 'json',
                            success: function (data) {
                                if (data.success) {
                                    location.reload(true);
                                    //$(this).closest('tr').remove(); //or whatever html you use for displaying rows
                                }
                            }
                        });
                    } else {
                        location.reload(true);
                    }
                });
            } else {
                krajeeDialog.alert("Please select atleast one product to perform this action");
            }
        });
    });

    $(document).on('change', '#productsearch-category_id', function () {
        var categoryId = $(this).val();
        $.ajax({
            type: "POST",
            url: '<?php echo Url::to(['product/get-sub-category-list', 'category_id' => ""]); ?>' + categoryId,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#productsearch-sub_category_id').html("");
                    $('#productsearch-sub_category_id').html(response.dataList);
                }
            }
        })
    });

    $(document).on('change', '.topSelling', function () {
        var id = $(this).attr('data-key');

        if ($(this).prop('checked') == true) {
            krajeeDialog.confirm('Are you sure you want to add this product to top selling?', function (out) {
                if (out) {
                    var is_top_selling = '1';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-selling']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_selling': is_top_selling
                        },
                        success: function (response) {
                            location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
        } else {
            krajeeDialog.confirm('Are you sure you want to remove this product from top selling?', function (out) {
                if (out) {
                    var is_top_selling = '0';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-selling']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_selling': is_top_selling
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

    $(document).on('change', '.topTrending', function () {
        var id = $(this).attr('data-key');

        if ($(this).prop('checked') == true) {
            krajeeDialog.confirm('Are you sure you want to add this product to top trending?', function (out) {
                if (out) {
                    var is_top_trending = '1';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-trending']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_trending': is_top_trending
                        },
                        success: function (response) {
                            location.reload(true);
                        }
                    });
                } else {
                    location.reload(true);
                }
            });
        } else {
            krajeeDialog.confirm('Are you sure you want to remove this product from top trending?', function (out) {
                if (out) {
                    var is_top_trending = '0';
                    $.ajax({
                        url: "<?php echo Url::to(['product/update-top-trending']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken()?>',
                            'id': id,
                            'is_top_trending': is_top_trending
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

    function contentmodelProductImgIndex(id) {
        $('#contentmodalProductImgIndex_' + id).modal('show');
    }

    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function(){
        $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);

        var input;
        var submit_form = false;
        var filter_selector = '#product-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#product-grid" , function(event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#product-grid" , function(event) {
            if (isInput) {
                submit_form = false;
            }
        });

        $(document)
        .off('keydown.yiiGridView change.yiiGridView', filter_selector)
        .on('keyup', filter_selector, function(e) {
            input = $(this).attr('name');
            var keyCode = e.keyCode ? e.keyCode : e.which;
            if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                if (submit_form === false) {
                    submit_form = true;
                    $("#product-grid").yiiGridView("applyFilter");
                }
            }
        })
        .on('pjax:success', function() {
            if (isInput) {
                var i = $("[name='" + input + "']");
                var val = i.val();
                i.focus().val(val);

                var searchInput = $(i);
                if (searchInput.length > 0) {
                    var strLength = searchInput.val().length * 2;
                    searchInput[0].setSelectionRange(strLength, strLength);
                }

                if ($('thead td i').length == 0) {
                    $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);
                }

                $('.pagination').find('li a').on('click', function () {
                    setTimeout(function () {
                        $(document).scrollTop($(document).innerHeight());
                    }, 200);
                })
            }
        });

        //select box filter
        var select;
        var submit_form = false;
        var select_filter_selector = '#product-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#product-grid" , function(event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#product-grid" , function(event) {
            if (isSelect) {
                submit_form = false;
            }
        });

        $(document)
        .off('keydown.yiiGridView change.yiiGridView', select_filter_selector)
        .on('change', select_filter_selector, function(e) {
            select = $(this).attr('name');
            if (submit_form === false) {
                submit_form = true;
                $("#product-grid").yiiGridView("applyFilter");
            }
        })
        .on('pjax:success', function() {
            var i = $("[name='" + input + "']");
            var val = i.val();
            i.focus().val(val);

            var searchInput = $(i);
            if (searchInput.length > 0) {
                var strLength = searchInput.val().length * 2;
                searchInput[0].setSelectionRange(strLength, strLength);
            }

            if (isSelect) {
                if ($('thead td i').length == 0) {
                    $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);
                }

                $('.pagination').find('li a').on('click', function () {
                    setTimeout(function () {
                        $(document).scrollTop($(document).innerHeight());
                    }, 200);
                })
            }
        });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    })
</script>
