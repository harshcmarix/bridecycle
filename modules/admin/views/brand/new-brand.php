<?php

use \app\modules\admin\widgets\GridView;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\editable\Editable;
use app\models\Brand;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerCssFile("@web/css/toggle-switch.css");
$this->registerJsFile("@web/js/toggle-switch.js");

$this->title = 'New Brand';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <div class="filter-div " id="filter-div" style="display: none">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_search_all_new_brand', ['model' => $searchModel]) ?>
                </div>
            </div>
        </div>

        <?=
        GridView::widget([
            'id' => 'brand-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@brandImageAbsolutePath') . '/' . $model->image;
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
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $brandmodal, 'height' => '50px', 'width' => '50px']);
                    },
                    'header' => '',
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
                ],
                [
                    'attribute' => 'german_name',
                    'value' => function ($model) {
                        $germanName = '';
                        if ($model instanceof Brand) {
                            $germanName = $model->german_name;
                        }
                        return $germanName;
                    },
                    'header' => '',
                ],
                [
                    'format' => ['raw'],
                    'attribute' => 'status',
                    'value' => function ($model) {
                        $status = "";
                        if ($model->status == \app\models\Brand::STATUS_PENDING_APPROVAL) {
                            $status = "Pending Approval";
                        } elseif ($model->status == \app\models\Brand::STATUS_APPROVE) {
                            $status = "Approved";
                        } elseif ($model->status == \app\models\Brand::STATUS_DECLINE) {
                            $status = "Decline";
                        }
                        return $status;
                    },
                    'filter' => Brand::ARR_BRAND_STATUS,
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['prompt' => ''],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ],
                    'header' => '',
                    'width' => '20%',
                ],
                [
                    'header' => 'Brand Of The Week',
                    'value' => function ($data) {
                        $totalSoldProducts = $data->isBrandOfTheWeek($data->id);
                        $isBrandOfTheWeek = 'No';
                        if (!empty($totalSoldProducts) && !empty($totalSoldProducts['total_sold_product']) && $totalSoldProducts['total_sold_product'] > 0) {
                            $isBrandOfTheWeek = 'Yes';
                        }
                        return $isBrandOfTheWeek;
                    },
                    'width' => '5%',

                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'width' => '12%',
                    'urlCreator' => function ($action, $model, $key, $index) {
                        return Url::to(['brand/new-brand-' . $action . '/', 'id' => $model->id]);
                    }
                ],
            ],
            'pjax' => true, // pjax is set to always true for this demo
            // set your toolbar
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fa fa-filter"></i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Filter',
                            'onclick' => "applyFilterAllCustomer()",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-plus-circle"> Add New Brand</i>', [
                            'class' => 'btn btn-success',
                            'title' => \Yii::t('kvgrid', 'Add New Brand'),
                            'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/brand/new-brand-create']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . Url::to(['brand/new-brand']) . "';",
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
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'new brand',
            'itemLabelPlural' => 'New Brands'
        ]);
        ?>
    </div>
</div>
<script type="text/javascript">
    $(document).on('change', '.is-top-brand', function () {
        var id = $(this).attr('data-key');
        if ($(this).is(':checked')) {
            krajeeDialog.confirm('Are you sure you want to add this new brand to top brand?', function (out) {
                if (out) {
                    var is_top_brand = '1';
                    $.ajax({
                        url: "<?php echo Url::to(['brand/update-top-brand']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken() ?>',
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
        } else {
            krajeeDialog.confirm('Are you sure you want to remove this new brand from top brand?', function (out) {
                if (out) {
                    var is_top_brand = '0';
                    $.ajax({
                        url: "<?php echo Url::to(['brand/update-top-brand']); ?>",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            '_csrf': '<?php echo Yii::$app->request->getCsrfToken() ?>',
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

    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function () {
        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');

        var filterDiv = $('.range-value');
        filterDiv.next('i').remove();
        filterDiv.css("width", "100% !important");

        "<?php if (!empty($searchModel->created_at)) { ?>";
        $('#filter-div').show();
        "<?php } else { ?>";
        $('#filter-div').hide();
        "<?php } ?>";

        var input;
        var submit_form = false;
        var filter_selector = '#brand-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#brand-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#brand-grid", function (event) {
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
                        $("#brand-grid").yiiGridView("applyFilter");
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
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
                    }

                    $('.pagination').find('li a').on('click', function () {
                        setTimeout(function () {
                            $(document).scrollTop($(document).innerHeight());
                        }, 200);
                    });
                }
            });

        //select box filter
        var select;
        var submit_form = false;
        var select_filter_selector = '#brand-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#brand-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#brand-grid", function (event) {
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
                    $("#brand-grid").yiiGridView("applyFilter");
                }
            })
            .on('pjax:success', function () {
                window.location.reload();
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
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
                    }

                    $('.pagination').find('li a').on('click', function () {
                        setTimeout(function () {
                            $(document).scrollTop($(document).innerHeight());
                        }, 200);
                    });
                }
            });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    });

    function applyFilterAllCustomer() {
        $('#filter-div').toggle();
    }
</script>