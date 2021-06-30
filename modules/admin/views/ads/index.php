<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use app\models\Ads;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\AdsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ads';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">

            <?php
            echo GridView::widget([
                'id' => 'ads-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'value' => function ($model) {
                            $id = '';
                            if ($model instanceof Ads) {
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
                            if (!empty($model->image) && file_exists(Yii::getAlias('@adsImageThumbRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@adsImageThumbAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            Modal::begin([
                                'id' => 'adsmodal_' . $model->id,
                                'header' => '<h3>Ads Image</h3>',
                                'size' => Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            Modal::end();
                            $adsmodal = "adsmodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $adsmodal, 'height' => '100px', 'width' => '100px']);
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'url',
                        'value' => function ($model) {
                            $url = '';
                            if ($model instanceof Ads) {
                                $url = $model->url;
                            }
                            return $url;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == Ads::STATUS_INACTIVE) {
                                $status = "Inactive";
                            } elseif ($model->status == Ads::STATUS_ACTIVE) {
                                $status = "Active";
                            }
                            return $status;
                        },
                        'filter' => Ads::ARR_ADS_STATUS,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ],
                        'header' => 'Status',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
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
                            Html::button('<i class="fa fa-plus-circle"> Add Ads</i>', [
                                'class' => 'btn btn-success',
                                'title' => \Yii::t('kvgrid', 'Add Ads'),
                                'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/ads/create']) . "';",
                            ]),
                        'options' => ['class' => 'btn-group mr-2']
                    ],
                    [
                        'content' =>
                            Html::button('<i class="fa fa-refresh"> Reset </i>', [
                                'class' => 'btn btn-basic',
                                'title' => 'Reset Filter',
                                'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['ads/index']) . "';",
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
                    //'heading' => 'Ads',
                ],
                'persistResize' => false,
                'toggleDataOptions' => ['minCount' => 10],
                'itemLabelSingle' => 'ads',
                'itemLabelPlural' => 'Ads'
            ]);
            ?>
        </div>


    </div>
</div>

<script>
    function adsmodal(id) {
        $('#adsmodal_' + id).modal('show');
    }
</script>