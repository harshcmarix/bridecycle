<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\ShopDetail;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */


$this->title = 'View Customer';
$this->params['breadcrumbs'][] = ['label' => 'Customers', 'url' => ['index-new-customer']];
$this->params['breadcrumbs'][] = 'View Customer';
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="users-view">

            <!--    <p>-->
            <!--        --><?php //echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) 
            ?>
            <!--        --><?php //echo Html::a('Delete', ['delete', 'id' => $model->id], [
            //            'class' => 'btn btn-danger',
            //            'data' => [
            //                'confirm' => 'Are you sure you want to delete this item?',
            //                'method' => 'post',
            //            ],
            //        ])
            ?>
            <!--    </p>-->

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'first_name',
                    'last_name',
                    'email:email',
                    [
                        'format' => 'raw',
                        'attribute' => 'profile_picture',
                        'value' => function ($data) {
                            $image_path = "";
                            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                                $image_path = Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            Modal::begin([
                                'id' => 'contentmodal_' . $data->id,
                                'header' => '<h3>Profile Picture</h3>',
                            ]);
                            echo Html::img($image_path, ['width' => '570']);
                            Modal::end();
                            $contentmodel = "contentmodel('" . $data->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodel, 'height' => '100px', 'width' => '100px']);
                        },
                    ],
                    'mobile',
                    [
                        'attribute' => "user_type",
                        'value' => function ($model) {
                            $userType = "-";
                            if ($model->user_type == 1) {
                                $userType = 'Admin';
                            } elseif ($model->user_type == 2) {
                                $userType = 'Sub-admin';
                            } elseif ($model->user_type == 3) {
                                $userType = 'Normal-user';
                            }
                            return $userType;
                        }
                    ],
                    [
                        'attribute' => "user_status",
                        'value' => function ($model) {
                            $userStatus = "-";
                            if ($model->user_status == \app\modules\admin\models\User::USER_STATUS_ACTIVE) {
                                $userStatus = 'Active';
                            } elseif ($model->user_status == \app\modules\admin\models\User::USER_STATUS_IN_ACTIVE) {
                                $userStatus = 'In-Active';
                            }
                            return $userStatus;
                        }
                    ],
                    //                    [
                    //                        'attribute' => "is_shop_owner",
                    //                        'value' => function ($model) {
                    //                            return ($model->is_shop_owner == 1) ? "Yes" : "No";
                    //                        }
                    //                    ],
                    //                    [
                    //                        'format' => 'raw',
                    //                        'attribute' => 'shop_logo',
                    //                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                    //                        'value' => function ($data) {
                    //                            $image_path = "";
                    //                            $shopDetailId = '';
                    //                            if (!empty($data->shopDetail) && $data->shopDetail instanceof ShopDetail && !empty($data->shopDetail->shop_logo) && file_exists(Yii::getAlias('@shopLogoRelativePath') . '/' . $data->shopDetail->shop_logo)) {
                    //                                $image_path = Yii::getAlias('@shopLogoAbsolutePath') . '/' . $data->shopDetail->shop_logo;
                    //                                $shopDetailId = $data->shopDetail->id;
                    //                            } else {
                    //                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    //                                $shopDetailId = $data->id;
                    //                            }
                    //                            Modal::begin([
                    //                                'id' => 'contentmodalShopLogo_' . $shopDetailId,
                    //                                'header' => '<h3>Shop Logo</h3>',
                    //                            ]);
                    //                            echo Html::img($image_path, ['width' => '570']);
                    //                            Modal::end();
                    //                            $contentmodelShopLogo = "contentmodalShopLogo('" . $shopDetailId . "');";
                    //                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodelShopLogo, 'height' => '100px', 'width' => '100px']);
                    //                        },
                    //                    ],
                    //                    [
                    //                        'attribute' => 'shop_name',
                    //                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                    //                        'value' => function ($model) {
                    //                            return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_name)) ? $model->shopDetail->shop_name : "";
                    //                        }
                    //                    ],
                    //                    [
                    //                        'format' => 'email',
                    //                        'attribute' => 'shop_email',
                    //                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                    //                        'value' => function ($model) {
                    //                            return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_email)) ? $model->shopDetail->shop_email : "";
                    //                        }
                    //                    ],
                    //                    [
                    //                        'attribute' => 'shop_phone_number',
                    //                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                    //                        'value' => function ($model) {
                    //                            return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_phone_number)) ? $model->shopDetail->shop_phone_number : "";
                    //                        }
                    //                    ],
                    //                    [
                    //                        'label' => 'Shop Address',
                    //                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                    //                        'value' => (!empty($shopAddress) && $shopAddress instanceof \app\models\UserAddress && !empty($shopAddress->address)) ? $shopAddress->address : "",
                    //                    ],
                    [
                        'label' => 'Is Newsletter',
                        'attribute' => "is_newsletter_subscription",
                        'value' => function ($model) {
                            $result = "-";
                            if ($model->is_newsletter_subscription == '1') {
                                $result = 'Yes';
                            } else {
                                $result = 'No';
                            }
                            return $result;
                        }
                    ],
                    [
                        'label' => 'Subscription Details',
                        'visible' => ($model->is_shop_owner == '1' && !empty($model->userPurchasedSubscriptions)) ? true : false,
                        'format' => ['raw'],
                        'value' => function ($data) {
                            $userSubscriptions = $data->userPurchasedSubscriptions;
                            $html = "<table style='border: 1px solid;'>
                                        <thead>
                                            <th style='text-align: center; border: 1px solid'>Plan</th>                                            
                                            <th style='text-align: center; border: 1px solid'>Amount</th>
                                            <th style='text-align: center; border: 1px solid'>Start date</th>                              
                                        </thead>
                                        <tbody>";
                            foreach ($userSubscriptions as $subscription) {
                                if ($subscription instanceof \app\models\UserPurchasedSubscriptions) {
                                    $subscriptionData = $subscription;
                                    $html .= "<tr style='border: 1px solid'>
                                                    <td style='text-align: center;border: 1px solid'>$subscriptionData->subscription_type</td>                                                    
                                                    <td style='text-align: center;border: 1px solid'>" . Yii::$app->formatter->asCurrency($subscriptionData->amount) . "</td>
                                                    <td style='text-align: center;border: 1px solid'>$subscriptionData->created_at</td>
                                                   
                                                </tr>";
                                }
                            }
                            $html .= "</tbody>
                                    </table>";
                            return $html;
                        }
                    ],
                ],
            ]) ?>

            <div class="box box-border">
                <div class="box-header">
                    <h3 class="box-title">Bank Details</h3>
                </div>
                <div class="box-body table-responsive">

                    <?php if ($bankDetails != '') { ?>
                        <?=
                        DetailView::widget([
                            'model' => $bankDetails,
                            'attributes' => [
                                'first_name',
                                'last_name',
                                'debit_card',
                                'iban',
                                'country',
                                'city',
                                'billing_address_line_1',
                                'billing_address_line_2',
                                'post_code',
                                'payment_type',
                                'paypal_email',
                            ],
                        ]) ?>

                        <?php
                    } else {
                        echo "<center><h5>Bank details not available.</h5></center>";
                    }
                    ?>
                </div>
            </div>

            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index-new-customer']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>

    </div>
</div>

<script type="text/javascript">
    function contentmodel(id) {
        $('#contentmodal_' + id).modal('show');
    }

    function contentmodalShopLogo(id) {
        $('#contentmodalShopLogo_' + id).modal('show');
    }
</script>