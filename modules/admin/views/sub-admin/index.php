<?php

use yii\helpers\{
    Html,
    ArrayHelper,
    Url
};
use \app\modules\admin\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\SubAdminSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sub Admin';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">
        <?php
        echo GridView::widget([
            'id' => 'sub-admin-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                // [
                //     'attribute' => 'id',
                //     'value' => function ($model) {
                //         return $model->id;
                //     },
                //     'header' => '',
                //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],
                //     'width' => '8%'
                // ],
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'attribute' => 'first_name',
                    'value' => function ($model) {
                        return $model->first_name;
                    },
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'last_name',
                    'value' => function ($model) {
                        return $model->last_name;
                    },
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'attribute' => 'email',
                    'value' => function ($model) {
                        return $model->email;
                    },
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'width' => '12%'
                ],
            ], // check the configuration for grid columns by clicking button above
            'pjax' => true, // pjax is set to always true for this demo
            // set your toolbar
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fa fa-plus-circle"> Add Sub Admin</i>', [
                            'class' => 'btn btn-success',
                            'title' => \Yii::t('kvgrid', 'Add Sub Admin'),
                            'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/sub-admin/create']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . Url::to(['sub-admin/index']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                '{toggleData}',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => false,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                //'heading' => 'Sub Admin',
            ],
            'emptyTextOptions' => [
                'class' => 'empty text-center'
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'sub admin',
            'itemLabelPlural' => 'Sub Admins'
        ]);
        ?>
    </div>
</div>

<script>
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
        var filter_selector = '#sub-admin-grid-filters input';

        $("body").on('beforeFilter', "#sub-admin-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#sub-admin-grid" , function(event) {
            submit_form = false;
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', filter_selector)
            .on('keyup', filter_selector, function(e) {
                input = $(this).attr('name');
                var keyCode = e.keyCode ? e.keyCode : e.which;
                if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                    if (submit_form === false) {
                        submit_form = true;
                        $("#sub-admin-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function() {
                var i = $("[name='"+input+"']");
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
            });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    })
</script>
