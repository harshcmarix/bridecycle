<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\ShopDetail;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */


$this->title = 'View New Shop Owner';
$this->params['breadcrumbs'][] = ['label' => 'New Shop Owners', 'url' => ['index-new-shop-owner-customer']];
$this->params['breadcrumbs'][] = 'View New Shop Owner';
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="users-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    // 'id',
                    'first_name',
                    'last_name',
                    'email:email',
                    //'password_hash',
                    //'profile_picture',
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
                    //'temporary_password',
                    //'access_token',
                    //'access_token_expired_at',
                    //'password_reset_token',
                    'mobile',
                    //'weight',
                    //'height',
                    //'personal_information:ntext',
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
                    [
                        'attribute' => "is_shop_owner",
                        'value' => function ($model) {
                            return ($model->is_shop_owner == 1) ? "Yes" : "No";
                        }
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'shop_logo',
                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                        'value' => function ($data) {
                            $image_path = "";
                            $shopDetailId = '';
                            if (!empty($data->shopDetail) && $data->shopDetail instanceof ShopDetail && !empty($data->shopDetail->shop_logo) && file_exists(Yii::getAlias('@shopLogoRelativePath') . '/' . $data->shopDetail->shop_logo)) {
                                $image_path = Yii::getAlias('@shopLogoAbsolutePath') . '/' . $data->shopDetail->shop_logo;
                                $shopDetailId = $data->shopDetail->id;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                                $shopDetailId = $data->id;
                            }
                            Modal::begin([
                                'id' => 'contentmodalShopLogo_' . $shopDetailId,
                                'header' => '<h3>Shop Logo</h3>',
                            ]);
                            echo Html::img($image_path, ['width' => '570']);
                            Modal::end();
                            $contentmodelShopLogo = "contentmodalShopLogo('" . $shopDetailId . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodelShopLogo, 'height' => '100px', 'width' => '100px']);
                        },
                    ],
                    [
                        'attribute' => 'shop_name',
                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                        'value' => function ($model) {
                            return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_name)) ? $model->shopDetail->shop_name : "";
                        }
                    ],
                    [
                        'format' => 'email',
                        'attribute' => 'shop_email',
                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                        'value' => function ($model) {
                            return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_email)) ? $model->shopDetail->shop_email : "";
                        }
                    ],
                    [
                        'attribute' => 'shop_phone_number',
                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                        'value' => function ($model) {
                            return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_phone_number)) ? $model->shopDetail->shop_phone_number : "";
                        }
                    ],
                    [
                        'label' => 'Shop Address',
                        'visible' => ($model->is_shop_owner == 1) ? true : false,
                        'value' => (!empty($shopAddress) && $shopAddress instanceof \app\models\UserAddress && !empty($shopAddress->address)) ? $shopAddress->address : "",
                    ],
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
                                        <tbody>"; //<th style='text-align: center; border: 1px solid'>Month</th> //<th style='text-align: center; border: 1px solid'>End date</th>
                            foreach ($userSubscriptions as $subscription) {
                                if ($subscription instanceof \app\models\UserPurchasedSubscriptions) {
                                    $subscriptionData = $subscription;
                                    //$endDate = date('Y-m-d h:m:s', strtotime('+3 months', strtotime($subscriptionData->created_at)));
                                    $html .= "<tr style='border: 1px solid'>
                                                    <td style='text-align: center;border: 1px solid'>$subscriptionData->subscription_type</td>                                                    
                                                    <td style='text-align: center;border: 1px solid'>" . Yii::$app->formatter->asCurrency($subscriptionData->amount) . "</td>
                                                    <td style='text-align: center;border: 1px solid'>$subscriptionData->created_at</td>
                                                   
                                                </tr>"; // <td style='border: 1px solid'>$subscriptionData->month</td> //  <td style='border: 1px solid'>$endDate</td>
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
                    <?= DetailView::widget([
                        'model' => $bankDetails,
                        'attributes' => [
                            'first_name',
                            'last_name',
                            'country',
                            'city',
                            'billing_address_line_1',
                            'billing_address_line_2',
                            'post_code',

                        ],
                    ]) ?>
                </div>
            </div>
            <p>

                <?php
                if ($pageId == '') {
                    echo Html::a('Back', \yii\helpers\Url::to(['index-new-shop-owner-customer']), ['class' => 'btn btn-default']);
                } else {
                    if ($pageType == '') {
                        echo Html::a('Back', \yii\helpers\Url::to(['product/view?id=' . $pageId]), ['class' => 'btn btn-default']);
                    } else {
                        echo Html::a('Back', \yii\helpers\Url::to(['product/new-product-view?id=' . $pageId]), ['class' => 'btn btn-default']);
                    }
                }
                ?>
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