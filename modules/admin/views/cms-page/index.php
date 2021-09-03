<?php

use yii\helpers\{
    Html,
    Url
};
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
                [
                    'attribute' => 'id',
                    'value' => function ($model) {
                        $id = '';
                        if ($model instanceof CmsPage) {
                            $id = $model->id;
                        }
                        return $id;
                    },
                    'width' => '8%',
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
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
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],
                [

                    'format' => 'html',
                    'attribute' => 'description',
                    'value' => function ($model) {
                        $description = '';
                        if ($model instanceof CmsPage) {
                            $description = strlen($model->description) > CmsPage::MAX_DESCRIPTION_TEXT ? substr($model->description, CmsPage::MIN_DESCRIPTION_TEXT, CmsPage::MAX_DESCRIPTION_TEXT) . "..." : $model->description;
                            // $description = str_limit($model->description,100);
                        }
                        return $description;
                    },
                    // 'format' => ['raw'],
                    'filter' => false,
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
                ],

                // [
                //     'attribute' => 'created_at',
                //     'value' => function ($model) {
                //         $created_at = '';
                //         if ($model instanceof CmsPage) {
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
                //'heading' => 'Contents',
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
    $('document').ready(function(){
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
                        $("#cms-page-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function() {
                var i = $("[name='"+input+"']");
                var val = i.val();
                i.focus().val(val);

                var searchInput = $(i);
                var strLength = searchInput.val().length * 2;
                searchInput[0].setSelectionRange(strLength, strLength);
            });
    });
</script>