<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\bootstrap\Modal;
use app\models\Tailor;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\TailorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tailors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <?= GridView::widget([
            'id' => 'tailor-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'shop_image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->shop_image) && file_exists(Yii::getAlias('@tailorShopImageRelativePath') . '/' . $model->shop_image)) {
                            $image_path = Yii::getAlias('@tailorShopImageAbsolutePath') . '/' . $model->shop_image;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        Modal::begin([
                            'id' => 'tailorimagemodal_' . $model->id,
                            'header' => '<h3>Shop Image</h3>',
                            'size' => Modal::SIZE_DEFAULT
                        ]);

                        echo Html::img($image_path, ['width' => '570']);

                        Modal::end();
                        $tailorimagemodal = "tailorimagemodal('" . $model->id . "');";
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $tailorimagemodal, 'height' => '50px', 'width' => '50px']);
                    },
                    'header' => '',
                ],
                [
                    'attribute' => 'name',
                    'value' => function ($model) {
                        $name = '';
                        if ($model instanceof Tailor) {
                            $name = $model->name;
                        }
                        return $name;
                    },
                    'header' => '',
                ],
                [
                    'attribute' => 'shop_name',
                    'value' => function ($model) {
                        $shop_name = '';
                        if ($model instanceof Tailor) {
                            $shop_name = $model->shop_name;
                        }
                        return $shop_name;
                    },
                    'header' => '',
                ],
                [
                    'attribute' => 'mobile',
                    'value' => function ($model) {
                        $mobile = '';
                        if ($model instanceof Tailor) {
                            $mobile = $model->mobile;
                        }
                        return $mobile;
                    },
                    'header' => '',
                ],
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
                Html::button('<i class="fa fa-plus-circle"> Add Tailor</i>', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Tailor'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/tailor/create']) . "';",
                ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                Html::button('<i class="fa fa-refresh"> Reset </i>', [
                    'class' => 'btn btn-basic',
                    'title' => 'Reset Filter',
                    'onclick' => "window.location.href = '" . Url::to(['tailor/index']) . "';",
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
        'itemLabelSingle' => 'tailor',
        'itemLabelPlural' => 'Tailors'
    ]); ?>

</div>
</div>
<script type="text/javascript">
    function tailorimagemodal(id) {
        $('#tailorimagemodal_' + id).modal('show');
    }

    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function(){
        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');

        var input;
        var submit_form = false;
        var filter_selector = '#tailor-grid-filters input';

        $("body").on('beforeFilter', "#tailor-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#tailor-grid" , function(event) {
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
                    $("#tailor-grid").yiiGridView("applyFilter");
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
                    $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
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
    });
</script>
