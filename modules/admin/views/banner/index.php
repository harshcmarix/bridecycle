<?php

use yii\helpers\{
    Html,
    Url
};
use \app\modules\admin\widgets\GridView;
use app\models\Banner;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Search\BannerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Banners';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

       <?php
       echo GridView::widget([
        'id' => 'banner-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // [
            //     'attribute' => 'id',
            //     'value' => function ($model) {
            //         $id = '';
            //         if ($model instanceof Banner) {
            //             $id = $model->id;
            //         }
            //         return $id;
            //     },
            //     'width' => '8%',
            //     'header' => '',
            //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            // ],
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'headerOptions' => ['style' => 'text-align: center !important']
            ],
            [
                'format' => ['raw'],
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'image',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->image) && file_exists(Yii::getAlias('@bannerImageThumbRelativePath') . '/' . $model->image)) {
                        $image_path = Yii::getAlias('@bannerImageThumbAbsolutePath') . '/' . $model->image;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'bannermodal_' . $model->id,
                        'header' => '<h3>Banner Image</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $bannermodal = "bannermodal('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $bannermodal, 'height' => '50px', 'width' => '50px']);
                },
                'width' => '20%',
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],

            // [
            //     'attribute' => 'created_at',
            //     'value' => function ($model) {
            //         $created_at = '';
            //         if ($model instanceof Banner) {
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
                'width' => '10%'
            ],
        ],

        'pjax' => true, // pjax is set to always true for this demo
        // set your toolbar
        'toolbar' => [
            [
                'content' =>
                Html::button('<i class="fa fa-plus-circle"> Add Banner</i>', [
                    'class' => 'btn btn-success',
                    'title' => \Yii::t('kvgrid', 'Add Banner'),
                    'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/banner/create']) . "';",
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
            //'heading' => 'Banners',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'banner',
        'itemLabelPlural' => 'Banners'
    ]);


    ?>

</div>
</div>
<script type="text/javascript">
    function bannermodal(id) {
        $('#bannermodal_' + id).modal('show');
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
        var filter_selector = '#banner-grid-filters input';

        $("body").on('beforeFilter', "#banner-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#banner-grid" , function(event) {
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
                    $("#banner-grid").yiiGridView("applyFilter");
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
