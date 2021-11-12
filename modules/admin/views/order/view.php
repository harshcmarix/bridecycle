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
$this->title = 'View Order Detail';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header">
        <div class="row">
            <div class="col col-md-6">
                <div class="form-group">
                    <h4><?= Html::encode('Order ID: ') ?><strong><?php echo $model->id ?></strong></h4>
                </div>
                <div class="form-group">
                    <?php
                    $status = 'Pending';
                    if ($model->status == \app\models\Order::STATUS_ORDER_INPROGRESS) {
                        $status = 'In progress';
                    } elseif ($model->status == \app\models\Order::STATUS_ORDER_COMPLETED) {
                        $status = 'Completed';
                    } elseif ($model->status == \app\models\Order::STATUS_ORDER_CANCELLED) {
                        $status = 'Cancelled';
                    }
                    ?>
                    <h4><?= Html::encode('Order Status: ') ?><strong><?php echo $status ?></strong></h4>
                </div>
            </div>


            <div class="col col-md-6 text-right">
                <div class="form-group">
                    <h4><?= Html::encode('Order Amount: ') ?>
                        <strong><?php echo (!empty($model->total_amount)) ? Yii::$app->formatter->asCurrency($model->total_amount) : "" ?></strong>
                    </h4>
                </div>
                <div class="form-group">
                    <?php
                    $invoice = '';
                    if (!empty($model->orderItems)) {
                        foreach ($model->orderItems as $keyOrder => $orderItemRow) {
                            if (!empty($orderItemRow) && $orderItemRow instanceof \app\models\OrderItem) {
                                $invoice = $orderItemRow->invoice;
                            }
                        }
                    }
                    ?>
                    <h4>
                        <?= Html::encode('Order Invoice: ') ?>
                        <strong>
                            <?php if (file_exists(Yii::getAlias('@orderInvoiceRelativePath') . "/" . $invoice)) { ?>
                                <a download
                                   href="<?php echo Yii::getAlias('@orderInvoiceAbsolutePath') . "/" . $invoice; ?>"
                                   title="Download Invoice" class="btn btn-default"><i
                                            class="fa fa-download"></i> Download Invoice</a>
                            <?php } else { ?>
                                Not Generated
                            <?php } ?>
                        </strong>
                    </h4>
                </div>
            </div>

        </div>
    </div>
    <div class="box-body">

        <div class="order-view">
            <div class="row">
                <!-- Customer Detail -->
                <div class="col col-md-4">
                    <div class="box box-border">
                        <div class="box-header">
                            <h3 class="box-title"> Customer/Buyer Detail</h3>
                        </div>
                        <div class="box-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    //  'id',
                                    //'user_id',
                                    [
                                        'format' => ['raw'],
                                        'attribute' => 'user_id',
                                        'label' => 'Buyer Name',
                                        'value' => function ($model) {
                                            return '<a href="' . \yii\helpers\Url::to(['user/view', 'id' => $model->user->id, 'f' => 'o', 'oId' => $model->id]) . '" class="view-buyer-profile" title="View Buyer Profile">' . $model->user->first_name . " " . $model->user->last_name . '</a>';
                                        },
                                    ],
                                    [
                                        //'attribute' => 'user_id',
                                        'label' => 'Buyer Email',
                                        'value' => function ($model) {
                                            return $model->user->email;
                                        },
                                    ],
                                    [
                                        //'attribute' => 'user_id',
                                        'label' => 'Buyer Phone',
                                        'value' => function ($model) {
                                            return $model->user->mobile;
                                        },
                                    ],
                                    //'user_address_id',
                                    [
                                        'attribute' => 'user_address_id',
                                        'label' => 'Buyer Address',
                                        'value' => function ($model) {
                                            return $model->userAddress->address . ", " . $model->userAddress->street . ", " . $model->userAddress->city . ", " . $model->userAddress->zip_code . ", " . $model->userAddress->state;
                                        },
                                    ],
//                                    [
//                                        'attribute' => 'total_amount',
//                                        'value' => function ($model) {
//                                            return (!empty($model->total_amount)) ? Yii::$app->formatter->asCurrency($model->total_amount) : "";
//                                        },
//                                    ],
//                                    [
//                                        'attribute' => 'status',
//                                        'value' => function ($model) {
//                                            $status = 'Pending';
//                                            if ($model->status == \app\models\Order::STATUS_ORDER_INPROGRESS) {
//                                                $status = 'In progress';
//                                            } elseif ($model->status == \app\models\Order::STATUS_ORDER_COMPLETED) {
//                                                $status = 'Completed';
//                                            } elseif ($model->status == \app\models\Order::STATUS_ORDER_CANCELLED) {
//                                                $status = 'Cancelled';
//                                            }
//                                            return $status;
//                                        }
//
//                                    ],
//                            'created_at',
//                            'updated_at',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <!-- Seller Detail -->
                <div class="col col-md-4">
                    <div class="box box-border">
                        <div class="box-header">
                            <h3 class="box-title"> Seller Detail</h3>
                        </div>
                        <div class="box-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'format' => ['raw'],
                                        'attribute' => 'user_id',
                                        'label' => 'Seller Name',
                                        'value' => function ($model) {
                                            return '<a href="' . \yii\helpers\Url::to(['user/view', 'id' => $model->orderItems[0]->product->user->id, 'f' => 'o', 'oId' => $model->id]) . '" class="view-seller-profile" title="View Seller Profile">' . $model->orderItems[0]->product->user->first_name . " " . $model->orderItems[0]->product->user->last_name . '</a>';
                                        },
                                    ],
                                    [
                                        'label' => 'Seller Email',
                                        'value' => function ($model) {
                                            return $model->orderItems[0]->product->user->email;
                                        },
                                    ],
                                    [
                                        'label' => 'Seller Phone',
                                        'value' => function ($model) {
                                            return $model->orderItems[0]->product->user->mobile;
                                        },
                                    ],
                                    [
                                        'label' => 'Shop Name',
                                        'value' => function ($model) {
                                            $shopName = "(not-set)";
                                            if (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product)) {
                                                if (!empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user instanceof \app\modules\api\v2\models\User) {
                                                    $sellerUser = $model->orderItems[0]->product->user;
                                                    // p($sellerUser->ShopDetails);

                                                    if (!empty($sellerUser->ShopDetails) && $sellerUser->ShopDetails instanceof \app\models\ShopDetail && !empty($sellerUser->ShopDetails->shop_name)) {


                                                        //   p($sellerUser->ShopDetails->shop_name);
                                                        $shopName = $sellerUser->ShopDetails->shop_name;
                                                        // p($shopName);
                                                    }
                                                }
                                            }
                                            return $shopName;
                                        },
                                    ],
//                                    [
//                                        'label' => 'Seller Address',
//                                        'value' => function ($model) {
//                                            return (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->userAddresses) && !empty($model->orderItems[0]->product->user->userAddresses[0]) && !empty($model->orderItems[0]->product->user->userAddresses[0]->address)) ? $model->orderItems[0]->product->user->userAddresses[0]->address : "(not-set)";
//                                        },
//                                    ],
                                    [
                                        'label' => 'Seller Address',
                                        'value' => function ($model) {
                                            $shopAddress = "(not-set)";
                                            if (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->userAddresses) && $model->orderItems[0]->product->user->is_shop_owner == \app\modules\api\v2\models\User::SHOP_OWNER_YES) {
                                                foreach ($model->orderItems[0]->product->user->userAddresses as $shopAddressRow) {
                                                    if (!empty($shopAddressRow) && $shopAddressRow instanceof \app\models\UserAddress && $shopAddressRow->type == \app\models\UserAddress::TYPE_SHOP) {
                                                        $shopAddress = $shopAddressRow->address;
                                                    } elseif (!empty($shopAddressRow) && $shopAddressRow instanceof \app\models\UserAddress && $shopAddressRow->is_primary_address == \app\models\UserAddress::IS_ADDRESS_PRIMARY_YES) {
                                                        $shopAddress = $shopAddressRow->address;
                                                    } else {
                                                        $shopAddress = $shopAddressRow->address;
                                                    }
                                                }
                                            } else if (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->userAddresses) && !empty($model->orderItems[0]->product->user->userAddresses[0]) && !empty($model->orderItems[0]->product->user->userAddresses[0]->address)) {
                                                $shopAddress = $model->orderItems[0]->product->user->userAddresses[0]->address;
                                            }
                                            return $shopAddress;
                                        },
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <!-- Seller Bank Detail -->
                <div class="col col-md-4">
                    <div class="box box-border">
                        <div class="box-header">
                            <h3 class="box-title"> Seller Bank Detail</h3>
                        </div>
                        <div class="box-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    //  'id',
                                    //'user_id',
                                    [
                                        'attribute' => 'user_id',
                                        'label' => 'Seller Name',
                                        'value' => function ($model) {
                                            return $model->user->first_name . " " . $model->user->last_name;
                                        },
                                    ],
                                    [
                                        'attribute' => 'user_id',
                                        'label' => 'Seller Email',
                                        'value' => function ($model) {
                                            return $model->user->email;
                                        },
                                    ],
                                    [
                                        'attribute' => 'user_id',
                                        'label' => 'Seller Phone',
                                        'value' => function ($model) {
                                            return $model->user->mobile;
                                        },
                                    ],
                                    //'user_address_id',
                                    [
                                        'attribute' => 'user_address_id',
                                        'label' => 'Seller Address',
                                        'value' => function ($model) {
                                            return $model->userAddress->address . ", " . $model->userAddress->street . ", " . $model->userAddress->city . ", " . $model->userAddress->zip_code . ", " . $model->userAddress->state;
                                        },
                                    ],
//                            'created_at',
//                            'updated_at',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <!-- Product Detail -->
                <div class="col col-md-12">
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

                                                if (!empty($imageRow) && $imageRow instanceof ProductImage && !empty($imageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . '/' . $imageRow->name)) {
                                                    $image_path = Yii::getAlias('@productImageAbsolutePath') . '/' . $imageRow->name;
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
                                            return (!empty($model->product) && !empty($model->product->price)) ? Yii::$app->formatter->asCurrency($model->product->price) : "";
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
            <div class="row">
                <div class="col col-md-12">
                    <p>
                        <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
