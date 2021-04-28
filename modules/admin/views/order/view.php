<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use kartik\editable\Editable;
use app\models\ProductImage;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = $model->id;
$this->title = 'View Order';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="order-view">

    <h1><?= Html::encode('Order ID: ' . $model->id) ?></h1>

    <!--<p>
        <?php /*echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) */ ?>
        <?php /*echo Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) */ ?>
    </p>-->
    <div class="row">
        <div class="col col-md-6">
            <div class="box box-border">
                <div class="box-header">
                    <h3 class="box-title"> Order Detail</h3>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            //'user_id',
                            [
                                'attribute' => 'user_id',
                                'label' => 'Customer Name',
                                'value' => function ($model) {
                                    return $model->user->first_name . " " . $model->user->last_name;
                                },
                            ],
                            [
                                'attribute' => 'user_id',
                                'label' => 'Customer Email',
                                'value' => function ($model) {
                                    return $model->user->email;
                                },
                            ],
                            [
                                'attribute' => 'user_id',
                                'label' => 'Customer Phone',
                                'value' => function ($model) {
                                    return $model->user->mobile;
                                },
                            ],
                            //'user_address_id',
                            [
                                'attribute' => 'user_address_id',
                                'label' => 'Customer Address',
                                'value' => function ($model) {
                                    return $model->userAddress->address . ", " . $model->userAddress->street . ", " . $model->userAddress->city . ", " . $model->userAddress->zip_code . ", " . $model->userAddress->state;
                                },
                            ],
                            'total_amount',
                            'status',
//                            'created_at',
//                            'updated_at',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col col-md-6">
            <div class="box box-border">
                <div class="box-header">
                    <h3 class="box-title"> Order Products</h3>
                </div>
                <div class="box-body table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}\n{summary}\n{pager}",
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],


                            [
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $images = $model->product->productImages;
                                    $dataImages = [];
                                    foreach ($images as $imageRow) {

                                        if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . '/' . $imageRow->name)) {
                                            $image_path = Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $imageRow->name;
                                        } else {
                                            $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                                        }

                                        $dataImages[] = ['content' => Html::img($image_path, ['width' => '570', 'alt' => 'Product Image']),                                             //'caption' => '<h4>Product Image</h4><p>This is the product caption text</p>',
                                            'options' => ['interval' => '600']
                                        ];
                                    }

                                    $result = "";
                                    if (!empty($dataImages)) {
                                        $result = \yii\bootstrap\Carousel::widget(
                                            ['items' => $dataImages]
                                        );
                                    }
                                    return $result;
                                },
                                'filter' => false,
                                'header' => 'Product Image',
                                'headerOptions' => ['class' => 'kartik-sheet-style']
                            ],

                            [
                                'attribute' => 'Order id',
                                'value' => function ($model) {
                                    return $model->order_id;
                                },
                                'filter' => false,
                                'header' => 'Order ID',
                                'headerOptions' => ['class' => 'kartik-sheet-style']
                            ],
                            [
                                'value' => function ($model) {
                                    return $model->product->name;
                                },
                                'header' => 'Product Name',
                                'headerOptions' => ['class' => 'kartik-sheet-style']
                            ],
                            [
                                'value' => function ($model) {
                                    return $model->product->category->name;
                                },
                                'header' => 'Product Category',
                                'headerOptions' => ['class' => 'kartik-sheet-style']
                            ],
                            [
                                'value' => function ($model) {
                                    return $model->product->price;
                                },
                                'header' => 'Product Price',
                                'headerOptions' => ['class' => 'kartik-sheet-style']
                            ],
                            [
                                'value' => function ($model) {
                                    return $model->quantity;
                                },
                                'header' => 'Product Quantity',
                                'headerOptions' => ['class' => 'kartik-sheet-style']
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <p>
        <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>

</div>
