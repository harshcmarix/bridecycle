<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\ProductRating;
use app\models\Product;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ProductRatingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Ratings';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="product-rating-index">

            <?php
            echo GridView::widget([
                'id' => 'product-rating-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'value' => function ($model) {
                            $id = '';
                            if ($model instanceof ProductRating) {
                                $id = $model->id;
                            }
                            return $id;
                        },
                        'width' => '8%',
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'product_id',
                        'value' => function ($model) {
                            $product = '';
                            if ($model->product instanceof Product) {
                                $product = $model->product->name;
                            }
                            return $product;
                        },
                        'filter' => ArrayHelper::map(Product::find()->all(), 'id', 'name'),
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => ''],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'width'=>'20px'
                            ],
                        ],
                        'header' => 'Product',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'user_id',
                        'value' => function ($model) {
                            $user = '';
                            if ($model->user instanceof \app\modules\api\v1\models\User) {
                                $user = $model->user->first_name . " " . $model->user->last_name;
                            }
                            return $user;
                        },
                        'filter' => ArrayHelper::map(\app\modules\api\v1\models\User::find()->where(['user_type' => \app\modules\api\v1\models\User::USER_TYPE_NORMAL])->all(), 'id', function ($model) {
                            return $model->first_name . " " . $model->last_name . " (" . $model->email . ")";
                        }),
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'options' => ['prompt' => ''],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'width'=>'20px'
                            ],
                        ],
                        'header' => 'User',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'rating',
                        'value' => function ($model) {
                            return (!empty($model->rating)) ? $model->rating : '';
                        },
                        'width' => '4%',
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'review',
                        'value' => function ($model) {
                            return (!empty($model->review)) ? $model->review : '';
                        },
                        'header' => '',
                        'format'=>['html'],
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == ProductRating::STATUS_PENDING) {
                                $status = "Pending Approval";
                            } elseif ($model->status == ProductRating::STATUS_APPROVE) {
                                $status = "Approved";
                            } elseif ($model->status == ProductRating::STATUS_DECLINE) {
                                $status = "Decline";
                            }
                            return $status;
                        },
                        'filter' => ProductRating::ARR_PRODUCT_RATING_STATUS,
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
                        'header' => 'Actions',
                        'class' => 'kartik\grid\ActionColumn',
                        'width' => '12%'
                    ],
                ],
                'pjax' => true, // pjax is set to always true for this demo
                // set your toolbar
                'toolbar' => [
                    [
                        'content' =>
                            Html::button('<i class="fa fa-refresh"> Reset </i>', [
                                'class' => 'btn btn-basic',
                                'title' => 'Reset Filter',
                                'onclick' => "window.location.href = '" . Url::to(['product-rating/index']) . "';",
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
                    //'heading' => 'Product Ratings',
                ],
                'persistResize' => false,
                'toggleDataOptions' => ['minCount' => 10],
                'itemLabelSingle' => 'Product Rating',
                'itemLabelPlural' => 'Product Ratings'
            ]);
            ?>
        </div>

    </div>
</div>