<?php

use yii\helpers\Html;
use \app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use app\models\Order;
use app\models\ProductImage;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-index">

    <?php
    $gridColumns = [
        //['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'id',
            'value' => function ($model) {
                return $model->id;
            },
            'header' => 'Order ID',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($model) {
                return date('Y-m-d',strtotime($model->created_at));
            },
            'filter' => '',
            'header' => 'Order Date',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->user->first_name . " " . $model->user->last_name;
            },
            'header' => 'Customer Name',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->user->email;
            },
            'header' => 'Customer Email',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->user->mobile;
            },
            'header' => 'Customer Phone',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'value' => function ($model) {
                return $model->userAddress->address . ", " . $model->userAddress->street . ", " . $model->userAddress->city . ", " . $model->userAddress->zip_code . ", " . $model->userAddress->state;
            },
            'header' => 'Customer Address',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'format' => 'raw',
            'value' => function ($model) {
                $html = "";
                $html .= "<table class='order-products_tbl'>";
                $html .= "<th>Product Image</th>";
                $html .= "<th>Product Name</th>";
                $html .= "<th>Product Category</th>";
                $html .= "<th>Product Price</th>";
                $html .= "<th>Total Quantity</th>";
                if (!empty($model->orderItems)) {
                    foreach ($model->orderItems as $key => $orderItem) {

                        $images = $orderItem->product->productImages;
                        $dataImages = [];
                        foreach ($images as $imageRow) {

                            if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                                $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }

                            $dataImages[] = ['content' => Html::img($image_path, ['width' => '570', 'alt' => 'Product Image']),
                                // 'caption' => '<h4>Product Image</h4><p>This is the product caption text</p>',
                                'options' => ['interval' => '600']
                            ];
                        }

                        $result = "";
                        if (!empty($dataImages)) {
                            $result = \yii\bootstrap\Carousel::widget(
                                ['items' => $dataImages]
                            );
                        }


                        $html .= "<tr>";
                        $html .= "<td>" . $result . "</td>";
                        $html .= "<td>" . $orderItem->product->name . "</td>";
                        $html .= "<td>" . $orderItem->product->category->name . "</td>";
                        $html .= "<td>" . $orderItem->product->price . "</td>";
                        $html .= "<td>" . $orderItem->quantity . "</td>";
                        $html .= "</tr>";
                    }
                } else {
                    $html .= "<tr><td colspan='5'><p>Product data not found.</p></td></tr>";

                }
                $html .= "</table>";

                return $html;
            },
            'header' => 'Order Products',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'total_amount',
            'value' => function ($model) {
                return $model->total_amount;
            },
            'filter' => '',
            'header' => 'Total Amount Paid',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'attribute' => 'status',
            'value' => function ($model) {
                $status = 'Pending';
                if ($model->status == Order::STATUS_ORDER_INPROGRESS) {
                    $status = 'In Progress';
                } elseif ($model->status == Order::STATUS_ORDER_COMPLETED) {
                    $status = 'Completed';
                } elseif ($model->status == Order::STATUS_ORDER_CANCELLED) {
                    $status = 'Cancelled';
                }
                return $status;
            },
            'filter' => $searchModel->arrOrderStatus,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['prompt' => 'Select'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],
            'header' => 'Order Status',
            'headerOptions' => ['class' => 'kartik-sheet-style']
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => "{view}"
        ],
    ];

    echo GridView::widget([
        'id' => 'kv-grid-demo',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true, // pjax is set to always true for this demo
        'toolbar' => [
            [
                'options' => ['class' => 'btn-group mr-2']
            ],
            [
                'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . \yii\helpers\Url::to(['order/index']) . "';",
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
            'type' => GridView::TYPE_PRIMARY,
            'heading' => 'Order',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'order',
        'itemLabelPlural' => 'Orders',
    ]);
    ?>


</div>
