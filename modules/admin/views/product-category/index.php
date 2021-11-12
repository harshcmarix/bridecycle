<?php

use app\models\ProductCategory;
use app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use yii\bootstrap\Modal;
use yii\helpers\{ArrayHelper, Html, Url};

/* @var $this yii\web\View */
/* @var $searchModel app\models\BrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'All Category';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">


        <?=
        GridView::widget([
            'id' => 'product-category-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                // [
                //     'attribute' => 'id',
                //     'value' => function ($model) {
                //         $id = '';
                //         if ($model instanceof ProductCategory) {
                //             $id = $model->id;
                //         }
                //         return $id;
                //     },
                //     'width' => '8%',
                //     'header' => '',
                //     ////'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                // ],
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $model->image;
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
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $productcategorymodal, 'height' => '50px', 'width' => '50px']);
                    },
                    'header' => '',
                    ////'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'parent_category_id',
                    'value' => function ($model) {
                        $parent_name = '(not-set)';
                        if ($model->parent instanceof ProductCategory) {
                            $parent_name = $model->parent->name;
                        }
                        return $parent_name;
                    },
                    'filter' => ArrayHelper::map($parent_category, 'id', 'name'),
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['prompt' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ],
                    'header' => '',
                    ////'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'name',
                    'value' => function ($model) {
                        $name = '';
                        if ((!empty($model->parent_category_id) && !empty($model->parent) && !empty($model->parent->name)) && $model instanceof ProductCategory) {
                            $name = $model->parent->name;
                        } else {
                            $name = $model->name;
                        }
                        return $name;
                    },
                    'header' => 'Parent Category Name',
                    ////'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'sub_cat_name',
                    'value' => function ($model) {
                        $name = '';
                        if ($model instanceof ProductCategory) {
                            $name = (!empty($model->parent_category_id) && !empty($model->parent) && $model->parent instanceof ProductCategory) ? $model->name : "-";
                        }
                        return $name;
                    },
                    'header' => 'Subcategory Name',
                    ////'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        if ($model->status == ProductCategory::STATUS_PENDING_APPROVAL) {
                            $status = "Pending Approval";
                        } elseif ($model->status == ProductCategory::STATUS_APPROVE) {
                            $status = "Approved";
                        } elseif ($model->status == ProductCategory::STATUS_DECLINE) {
                            $status = "Decline";
                        } else {
                            $status = "";
                        }
                        return $status;
                    },
                    'filter' => ProductCategory::ARR_CATEGORY_STATUS,
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['prompt' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ],
                    'header' => 'Status',
                    ////'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'buttons' => [//
                        'delete' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/admin/product-category/delete', 'id' => $model->id], ['title' => 'Delete', 'data-method' => 'post', 'data-confirm' => "Are you sure to delete this category/subcategory and its related products?"]);
                        },],
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
            'itemLabelSingle' => 'category',
            'itemLabelPlural' => 'All Categories'
        ]);
        ?>
    </div>
</div>
<script type="text/javascript">
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }

    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function () {
        $('input[type=text]').after(`<i class="fa fa-times" onclick="clearFilter(this)"></i>`);

        var input;
        var submit_form = false;
        var filter_selector = '#product-category-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#product-category-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#product-category-grid", function (event) {
            if (isInput) {
                submit_form = false;
            }
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', filter_selector)
            .on('keyup', filter_selector, function (e) {
                input = $(this).attr('name');
                var keyCode = e.keyCode ? e.keyCode : e.which;
                if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                    if (submit_form === false) {
                        submit_form = true;
                        $("#product-category-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function () {
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
        var select_filter_selector = '#product-category-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#product-category-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#product-category-grid", function (event) {
            if (isSelect) {
                submit_form = false;
            }
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', select_filter_selector)
            .on('change', select_filter_selector, function (e) {
                select = $(this).attr('name');
                if (submit_form === false) {
                    submit_form = true;
                    $("#product-category-grid").yiiGridView("applyFilter");
                }
            })
            .on('pjax:success', function () {
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
