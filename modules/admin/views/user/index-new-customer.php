<?php

use app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Customers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <div class="filter-div " id="filter-div" style="display: none">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_search_new_customer', ['model' => $searchModel]) ?>
                </div>
            </div>
        </div>

        <?php
        $gridColumns = [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'first_name',
                'value' => function ($model) {
                    return $model->first_name;
                },
                'header' => '',
            ],
            [
                'attribute' => 'last_name',
                'value' => function ($model) {
                    return $model->last_name;
                },
                'header' => '',
            ],
            [
                'attribute' => 'username',
                'value' => function ($model) {
                    return $model->username;
                },
                'header' => '',
            ],
            [
                'attribute' => 'email',
                'value' => function ($model) {
                    return $model->email;
                },
                'header' => '',
            ],
            [
                'format' => ['raw'],
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'profile_picture',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $model->profile_picture)) {
                        $image_path = Yii::getAlias('@profilePictureAbsolutePath') . '/' . $model->profile_picture;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'contentmodal_' . $model->id,
                        'header' => '<h3>Profile Picture</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $contentmodel = "contentmodel('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodel, 'height' => '50px', 'width' => '50px']);
                },
                'header' => '',
            ],
            [
                'attribute' => 'mobile',
                'value' => function ($model) {
                    return (!empty($model) && !empty($model->mobile)) ? $model->mobile : "-";
                },
                'header' => '',
            ],
            [
                'attribute' => 'stripe_account_connect_id',
                'value' => function ($model) {
                    return (!empty($model) && !empty($model->stripe_account_connect_id)) ? $model->stripe_account_connect_id : "-";
                },
                'header' => 'Stripe Account ID',
            ],
            [
                'attribute' => 'is_newsletter_subscription',
                'value' => function ($data) {
                    $result = "";
                    if ($data->is_newsletter_subscription == '1') {
                        $result = 'Yes';
                    } else {
                        $result = 'No';
                    }
                    return $result;
                },
                'filter' => ['0' => 'No', '1' => "Yes"],
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'header' => 'Is Newsletter',
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'urlCreator' => function ($action, $model, $key, $index) {
                    return Url::to(['user/new-customer-' . $action . '/', 'id' => $model->id]);
                }
            ],
        ];

        echo GridView::widget([
            'id' => 'new-customer-user-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns, // check the configuration for grid columns by clicking button above

            'exportConfig' => [
                GridView::CSV => ['label' => 'Export as CSV', 'filename' => "new_customers_" . date('d_m_Y_His')],
//                GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'File_Name -' . date('d-M-Y')],
//                GridView::PDF => ['label' => 'Export as PDF', 'filename' => 'File_Name -' . date('d-M-Y')],
//                GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'File_Name -' . date('d-M-Y')],
//                GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'File_Name -' . date('d-M-Y')],
            ],
            'pjax' => true, // pjax is set to always true for this demo
            'export' => [
                'fontAwesome' => true,
                'showConfirmAlert' => false,
            ],
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fa fa-filter"></i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Filter',
                            'onclick' => "applyFilter()",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                "{export}",
                [
                    'content' =>
                        Html::button('<i class="fa fa-plus-circle"> Add New Customer </i>', [
                            'class' => 'btn btn-success',
                            'title' => 'Add New Customer',
                            'onclick' => "window.location.href = '" . Url::to(['user/new-customer-create']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . Url::to(['user/index-new-customer']) . "';",
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
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'customer',
            'itemLabelPlural' => 'Customers',
        ]);
        ?>
    </div>
</div>

<script type="text/javascript">
    function contentmodel(id) {
        $('#contentmodal_' + id).modal('show');
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

        "<?php if(!empty($searchModel->created_at)){ ?>";
        $('#filter-div').show();
        "<?php }else{ ?>";
        $('#filter-div').hide();
        "<?php } ?>";

        var input;
        var submit_form = false;
        var filter_selector = '#new-customer-user-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#new-customer-user-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#new-customer-user-grid", function (event) {
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
                        setTimeout(function () {
                            $("#new-customer-user-grid").yiiGridView("applyFilter");
                        }, 700);
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
        var select_filter_selector = '#new-customer-user-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#new-customer-user-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#new-customer-user-grid", function (event) {
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
                    $("#new-customer-user-grid").yiiGridView("applyFilter");
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
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
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
    });

    function applyFilter() {
        $('#filter-div').toggle();
    }
</script>