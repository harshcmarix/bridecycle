<?php

use yii\helpers\Html;
use yii\helpers\Url;
use \app\modules\admin\widgets\GridView;
use app\models\CmsPage;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CmsPageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Cms Pages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

        <?php
        echo GridView::widget([
            'id' => 'cms-page-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'attribute' => 'title',
                    'value' => function ($model) {
                        $title = '';
                        if ($model instanceof CmsPage) {
                            $title = $model->title;
                        }
                        return $title;
                    },
                    'header' => '',
                ],
                [

                    'format' => 'html',
                    'attribute' => 'description',
                    'value' => function ($model) {
                        $description = '';
                        if ($model instanceof CmsPage) {
                            $description = strlen($model->description) > CmsPage::MAX_DESCRIPTION_TEXT ? substr($model->description, CmsPage::MIN_DESCRIPTION_TEXT, CmsPage::MAX_DESCRIPTION_TEXT) . "..." : $model->description;
                        }
                        return $description;
                    },
                    'filter' => false,
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
                    Html::button('<i class="fa fa-plus-circle"> Add Content</i>', [
                        'class' => 'btn btn-success',
                        'title' => \Yii::t('kvgrid', 'Add Content'),
                        'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/cms-page/create']) . "';",
                    ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['cms-page/index']) . "';",
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
            'itemLabelSingle' => 'content',
            'itemLabelPlural' => 'Contents'
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
        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');

        var input;
        var submit_form = false;
        var filter_selector = '#cms-page-grid-filters input';

        $("body").on('beforeFilter', "#cms-page-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#cms-page-grid" , function(event) {
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
                    setTimeout(function () {
                        $("#cms-page-grid").yiiGridView("applyFilter");
                    }, 700);
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