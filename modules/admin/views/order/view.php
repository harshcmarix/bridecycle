<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use kartik\editable\Editable;
use app\models\ProductImage;
use Mpdf\Tag\Em;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = $model->id;
$this->title = 'View Order Detail';
if ($pageType == '') {
    $this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'Bridecycle To Seller Payments', 'url' => ['bridecycle-to-seller-payments/index']];
}
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
                                <a download href="<?php echo Yii::getAlias('@orderInvoiceAbsolutePath') . "/" . $invoice; ?>" title="Download Invoice" class="btn btn-default"><i class="fa fa-download"></i> Download Invoice</a>
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

                                                $dataImages[] = [
                                                    'content' => Html::img($image_path, ['width' => '570', 'alt' => 'Product Image']),                                             //'caption' => '<h4>Product Image</h4><p>This is the product caption text</p>',
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
                                            return (!empty($model->product) && !empty($model->product->price)) ? Yii::$app->formatter->asCurrency($model->product->price) : "-";
                                        },
                                        'header' => 'Product Price',
                                        'headerOptions' => ['class' => 'kartik-sheet-style']
                                    ],
                                    [
                                        'value' => function ($model) {
                                            return (!empty($model->product) && !empty($model->product->option_price)) ? Yii::$app->formatter->asCurrency($model->product->option_price) : "-";
                                        },
                                        'header' => 'Product Tax',
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
                <!-- Seller Detail -->
                <div class="col col-md-6">
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
                                            $actionUrl = "";
                                            $sellerUserName = "";
                                            if(!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->id)){
                                                $actionUrl = \yii\helpers\Url::to(['user/view', 'id' => $model->orderItems[0]->product->user->id, 'f' => 'o', 'oId' => $model->id]);
                                                $sellerUserName = $model->orderItems[0]->product->user->first_name . " " . $model->orderItems[0]->product->user->last_name;
                                            }elseif(empty($model->orderItems) ){
                                                $actionUrl = "javascript:void(0);";
                                                $sellerUserName = "(not-set)";
                                            }    
                                            return '<a href="' . $actionUrl . '" class="view-seller-profile" title="View Seller Profile">' . $sellerUserName . '</a>';
                                        },
                                    ],
                                    [
                                        'label' => 'Seller Email',
                                        'value' => function ($model) {
                                            $sellerUserEmail = "";
                                            if(!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->email)){                                                
                                                $sellerUserEmail = $model->orderItems[0]->product->user->email;
                                            }elseif(empty($model->orderItems) ){                                                
                                                $sellerUserEmail = "(not-set)";
                                            }
                                            return $sellerUserEmail;
                                        },
                                    ],
                                    [
                                        'label' => 'Seller Phone',
                                        'value' => function ($model) {
                                            $sellerUserContactNo = "";
                                            if(!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->mobile)){                                                
                                                $sellerUserContactNo = $model->orderItems[0]->product->user->mobile;
                                            }elseif(empty($model->orderItems) ){                                                
                                                $sellerUserContactNo = "(not-set)";
                                            }
                                            return $sellerUserContactNo;                                            
                                        },
                                    ],
                                    [
                                        'label' => 'Shop Name',
                                        'visible' => (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user->is_shop_owner == \app\modules\api\v2\models\User::SHOP_OWNER_YES) ? true : false,
                                        'value' => function ($model) {
                                            $shopName = "(not-set)";
                                            if (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product)) {
                                                if (!empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user instanceof app\modules\api\v2\models\User) {
                                                    $sellerUser = $model->orderItems[0]->product->user;
                                                    if (!empty($sellerUser->shopDetail) && $sellerUser->shopDetail instanceof app\models\ShopDetail) {
                                                        $shopName = $sellerUser->shopDetail->shop_name;
                                                    }
                                                }
                                            }
                                            return $shopName;
                                        },
                                    ],
                                    [
                                        'label' => 'Shop Email',
                                        'visible' => (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user->is_shop_owner == \app\modules\api\v2\models\User::SHOP_OWNER_YES) ? true : false,
                                        'value' => function ($model) {
                                            $shopEmail = "(not-set)";
                                            if (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product)) {
                                                if (!empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user instanceof app\modules\api\v2\models\User) {
                                                    $sellerUser = $model->orderItems[0]->product->user;
                                                    if (!empty($sellerUser->shopDetail) && $sellerUser->shopDetail instanceof app\models\ShopDetail) {
                                                        $shopEmail = $sellerUser->shopDetail->shop_email;
                                                    }
                                                }
                                            }
                                            return $shopEmail;
                                        },
                                    ],
                                    [
                                        'label' => 'Shop Phone',
                                        'visible' => (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product) && !empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user->is_shop_owner == \app\modules\api\v2\models\User::SHOP_OWNER_YES) ? true : false,
                                        'value' => function ($model) {
                                            $shopContact = "(not-set)";
                                            if (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product)) {
                                                if (!empty($model->orderItems[0]->product->user) && $model->orderItems[0]->product->user instanceof app\modules\api\v2\models\User) {
                                                    $sellerUser = $model->orderItems[0]->product->user;
                                                    if (!empty($sellerUser->shopDetail) && $sellerUser->shopDetail instanceof app\models\ShopDetail) {
                                                        $shopContact = $sellerUser->shopDetail->shop_phone_number;
                                                    }
                                                }
                                            }
                                            return $shopContact;
                                        },
                                    ],
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
                <div class="col col-md-6">
                    <div class="box box-border">
                        <div class="box-header">
                            <h3 class="box-title"> Seller Bank Detail</h3>
                        </div>
                        <div class="box-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'label' => 'Debit Card',
                                        'value' => function ($model) {
                                            $debitCard = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->debit_card)) {
                                                $debitCard = $model->orderItems[0]->product->user->bankDetail->debit_card;
                                            }
                                            return $debitCard;
                                        },
                                    ],
                                    [

                                        'label' => 'First Name',
                                        'value' => function ($model) {
                                            $firstName = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->first_name)) {
                                                $firstName = $model->orderItems[0]->product->user->bankDetail->first_name;
                                            }
                                            return $firstName;
                                        },
                                    ],
                                    [

                                        'label' => 'Last Name',
                                        'value' => function ($model) {
                                            $lastName = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->last_name)) {
                                                $lastName = $model->orderItems[0]->product->user->bankDetail->last_name;
                                            }
                                            return $lastName;
                                        },
                                    ],
                                    [

                                        'label' => 'Country',
                                        'value' => function ($model) {
                                            $countryName = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->country)) {
                                                $countryName = $model->orderItems[0]->product->user->bankDetail->country;
                                            }
                                            return $countryName;
                                        },
                                    ],
                                    [

                                        'label' => 'IBAN',
                                        'value' => function ($model) {
                                            $iban = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->iban)) {
                                                $iban = $model->orderItems[0]->product->user->bankDetail->iban;
                                            }
                                            return $iban;
                                        },
                                    ],
                                    [

                                        'label' => 'Billing Address Line 1',
                                        'value' => function ($model) {
                                            $billingAddress1 = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->billing_address_line_1)) {
                                                $billingAddress1 = $model->orderItems[0]->product->user->bankDetail->billing_address_line_1;
                                            }
                                            return $billingAddress1;
                                        },
                                    ],
                                    [

                                        'label' => 'Billing Address Line 2',
                                        'value' => function ($model) {
                                            $billingAddress2 = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->billing_address_line_2)) {
                                                $billingAddress2 = $model->orderItems[0]->product->user->bankDetail->billing_address_line_2;
                                            }
                                            return $billingAddress2;
                                        },
                                    ],
                                    [

                                        'label' => 'City',
                                        'value' => function ($model) {
                                            $cityName = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->city)) {
                                                $cityName = $model->orderItems[0]->product->user->bankDetail->city;
                                            }
                                            return $cityName;
                                        },
                                    ],
                                    [

                                        'label' => 'Post Code',
                                        'value' => function ($model) {
                                            $pincode = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->post_code)) {
                                                $pincode = $model->orderItems[0]->product->user->bankDetail->post_code;
                                            }
                                            return $pincode;
                                        },
                                    ],
                                    [

                                        'label' => 'Payment Mode',
                                        'value' => function ($model) {
                                            $paymentType = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->payment_type)) {
                                                $paymentType = $model->orderItems[0]->product->user->bankDetail->payment_type;
                                            }
                                            return $paymentType;
                                        },
                                    ],
                                    [

                                        'label' => 'Paypal Email',
                                        'value' => function ($model) {
                                            $paypalEmail = "(not-set)";
                                            if (!empty($model->orderItems[0]->product->user) && !empty($model->orderItems[0]->product->user->bankDetail) && $model->orderItems[0]->product->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->orderItems[0]->product->user->bankDetail->paypal_email)) {
                                                $paypalEmail = $model->orderItems[0]->product->user->bankDetail->paypal_email;
                                            }
                                            return $paypalEmail;
                                        },
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>                
                  <!-- Customer Detail -->
                  <div class="col col-md-6">
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
                 <!-- Customer Bank Detail -->
                 <div class="col col-md-6">
                    <div class="box box-border">
                        <div class="box-header">
                            <h3 class="box-title"> Customer Bank Detail</h3>
                        </div>
                        <div class="box-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'label' => 'Debit Card',
                                        'value' => function ($model) {
                                            $debitCard = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->debit_card)) {
                                                $debitCard = $model->user->bankDetail->debit_card;
                                            }
                                            return $debitCard;
                                        },
                                    ],
                                    [

                                        'label' => 'First Name',
                                        'value' => function ($model) {
                                            $firstName = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->first_name)) {
                                                $firstName = $model->user->bankDetail->first_name;
                                            }
                                            return $firstName;
                                        },
                                    ],
                                    [

                                        'label' => 'Last Name',
                                        'value' => function ($model) {
                                            $lastName = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->last_name)) {
                                                $lastName = $model->user->bankDetail->last_name;
                                            }
                                            return $lastName;
                                        },
                                    ],
                                    [

                                        'label' => 'Country',
                                        'value' => function ($model) {
                                            $countryName = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->country)) {
                                                $countryName = $model->user->bankDetail->country;
                                            }
                                            return $countryName;
                                        },
                                    ],
                                    [

                                        'label' => 'IBAN',
                                        'value' => function ($model) {
                                            $iban = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->iban)) {
                                                $iban = $model->user->bankDetail->iban;
                                            }
                                            return $iban;
                                        },
                                    ],
                                    [

                                        'label' => 'Billing Address Line 1',
                                        'value' => function ($model) {
                                            $billingAddress1 = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->billing_address_line_1)) {
                                                $billingAddress1 = $model->user->bankDetail->billing_address_line_1;
                                            }
                                            return $billingAddress1;
                                        },
                                    ],
                                    [

                                        'label' => 'Billing Address Line 2',
                                        'value' => function ($model) {
                                            $billingAddress2 = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->billing_address_line_2)) {
                                                $billingAddress2 = $model->user->bankDetail->billing_address_line_2;
                                            }
                                            return $billingAddress2;
                                        },
                                    ],
                                    [

                                        'label' => 'City',
                                        'value' => function ($model) {
                                            $cityName = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->city)) {
                                                $cityName = $model->user->bankDetail->city;
                                            }
                                            return $cityName;
                                        },
                                    ],
                                    [

                                        'label' => 'Post Code',
                                        'value' => function ($model) {
                                            $pincode = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->post_code)) {
                                                $pincode = $model->user->bankDetail->post_code;
                                            }
                                            return $pincode;
                                        },
                                    ],
                                    [

                                        'label' => 'Payment Mode',
                                        'value' => function ($model) {
                                            $paymentType = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->payment_type)) {
                                                $paymentType = $model->user->bankDetail->payment_type;
                                            }
                                            return $paymentType;
                                        },
                                    ],
                                    [

                                        'label' => 'Paypal Email',
                                        'value' => function ($model) {
                                            $paypalEmail = "(not-set)";
                                            if (!empty($model->user) && !empty($model->user->bankDetail) && $model->user->bankDetail instanceof \app\models\UserBankDetails && !empty($model->user->bankDetail->paypal_email)) {
                                                $paypalEmail = $model->user->bankDetail->paypal_email;
                                            }
                                            return $paypalEmail;
                                        },
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div> 
            </div>
            <div class="row">
                <div class="col col-md-12">
                    <p>

                        <?php
                        if ($pageType == '') {
                            echo Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']);
                        } else {
                            echo Html::a('Back', \yii\helpers\Url::to(['bridecycle-to-seller-payments/index']), ['class' => 'btn btn-default']);
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>