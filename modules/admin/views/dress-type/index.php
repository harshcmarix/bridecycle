<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\DressTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Dress Type';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="career-index box box-primary">
    <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">
        <?php
        echo GridView::widget([
            'id' => 'color-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'id',
                    'header' => 'Id',
                    'width' => '8%',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],
                [
                    'attribute' => 'name',
                    'header' => '',
                    'vAlign' => 'middle',
                    'format' => 'raw',
                    'width' => '20%',
                ],
                [
                    'format' => ['raw'],
                    'enableSorting' => false,
                    'filter' => false,
                    'attribute' => 'image',
                    'value' => function ($model) {
                        $image_path = "";
                        if (!empty($model->image) && file_exists(Yii::getAlias('@dressTypeImageThumbRelativePath') . '/' . $model->image)) {
                            $image_path = Yii::getAlias('@dressTypeImageThumbAbsolutePath') . '/' . $model->image;
                        } else {
                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        Modal::begin([
                            'id' => 'dresstypemodal_' . $model->id,
                            'header' => '<h3>Dress Type Icon</h3>',
                            'size' => Modal::SIZE_SMALL
                        ]);

                        echo Html::img($image_path, ['width' => '50', 'class' => 'text-center']);

                        Modal::end();
                        $dresstypemodal = "dresstypemodal('" . $model->id . "');";
                        return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $dresstypemodal, 'height' => '50px', 'width' => '50px']);
                    },
                    'header' => '',
                    'width' => '22%',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],
//                [
//                    'attribute' => 'status',
//                    'value' => function ($model) {
//                        $status = "";
//                        if ($model->status == \app\models\Color::STATUS_PENDING_APPROVAL) {
//                            $status = "Pending Approval";
//                        } elseif ($model->status == \app\models\Color::STATUS_APPROVE) {
//                            $status = "Approved";
//                        } elseif ($model->status == \app\models\Color::STATUS_DECLINE) {
//                            $status = "Decline";
//                        }
//                        return $status;
//                    },
//                    'filter' => '',
//                    'filterType' => GridView::FILTER_SELECT2,
//                    'filterWidgetOptions' => [
//                        'options' => ['prompt' => 'Select'],
//                        'pluginOptions' => [
//                            'allowClear' => true,
//                        ],
//                    ],
//                    'header' => 'Status',
//                    'headerOptions' => ['class' => 'kartik-sheet-style']
//                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template'=>'{view} {delete}'
                ],
            ],

            'pjax' => true, // pjax is set to always true for this demo
            // set your toolbar
            'toolbar' => [
//                [
//                    'content' =>
//                        Html::button('<i class="fa fa-plus-circle"> Add Dress Type</i>', [
//                            'class' => 'btn btn-success',
//                            'title' => \Yii::t('kvgrid', 'Add Dress Type'),
//                            'onclick' => "window.location.href = '" . \Yii::$app->urlManager->createUrl(['/admin/dress-type/create']) . "';",
//                        ]),
//                    'options' => ['class' => 'btn-group mr-2']
//                ],
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['dress-type/index']) . "';",
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
            'responsive' => true,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                //'heading' => 'Dress Types',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'Dress Type',
            'itemLabelPlural' => 'Dress Type'
        ]);
        ?>
    </div>
</div>

<script type="text/javascript">
    function dresstypemodal(id) {
        $('#dresstypemodal_' + id).modal('show');
    }
</script>