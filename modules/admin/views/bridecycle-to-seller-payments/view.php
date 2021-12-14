<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\BridecycleToSellerPayments */

$this->title = 'View Bridecycle To Seller Payment';
$this->params['breadcrumbs'][] = ['label' => 'Bridecycle To Seller Payments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <div class="bridecycle-to-seller-payments-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'order_id',
                    [
                        'attribute' => 'order_item_id',
                        'label' => 'Order Item',
                        'value' => function ($model) {
                            $item = "";
                            if (!empty($model->orderItem) && $model->orderItem instanceof \app\models\OrderItem && !empty($model->orderItem->product) && $model->orderItem->product instanceof \app\models\Product) {
                                $item = (!empty($model->orderItem->product->name)) ? $model->orderItem->product->name : "";
                            }
                            return $item;
                        },
                    ],
                    [
                        'attribute' => 'seller_id',
                        'value' => function ($model) {
                            $seller = "";
                            if (!empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User) {
                                $seller = (!empty($model->seller->first_name)) ? $model->seller->first_name . " " . $model->seller->last_name : "Seller";
                            }
                            return $seller;
                        },
                    ],
                    [
                        'attribute' => 'buyer_id',
                        'label' => 'Buyer',
                        'value' => function ($model) {
                            $buyer = "";
                            if ($model instanceof \app\models\BridecycleToSellerPayments && !empty($model->order) && $model->order instanceof \app\models\Order && !empty($model->order->user) && $model->order->user instanceof \app\modules\admin\models\User) {
                                $buyer = (!empty($model->order->user->first_name)) ? $model->order->user->first_name . " " . $model->order->user->last_name : "Buyer";
                            }
                            return $buyer;
                        },
                    ],
                    [
                        'attribute' => 'amount',
                        'label' => 'Total Amount (Product Price + Tax + Shipping)',
                        'value' => function ($model) {
                            $amount = 0.0;
                            if (!empty($model) && $model instanceof \app\models\BridecycleToSellerPayments && !empty($model->amount)) {
                                $amount = $model->amount;
                            }
                            return Yii::$app->formatter->asCurrency($amount);
                        },
                    ],
                    [
                        'label' => 'BrideCycle Earning',
                        'value' => function ($model) {
                            $bridecycleAmount = 0.0;
                            if (!empty($model) && $model instanceof \app\models\BridecycleToSellerPayments) {
                                $bridecycleAmount = $model->getBrideEarning($model->product_price);
                            }
                            return Yii::$app->formatter->asCurrency($bridecycleAmount);
                        },
                    ],
                    [
                        'label' => 'Seller Earning',
                        'value' => function ($model) {
                            $bridecycleEarningAmount = 0.0;
                            if (!empty($model) && $model instanceof \app\models\BridecycleToSellerPayments) {
                                $bridecycleEarningAmount = $model->getBrideEarning($model->product_price);
                            }
                            return Yii::$app->formatter->asCurrency(($model->amount - $bridecycleEarningAmount));
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            if (!empty($model->status) && $model->status == \app\models\BridecycleToSellerPayments::STATUS_COMPLETE) {
                                return "Complete";
                            } else {
                                return "Pending";
                            }
                        },
                    ],
                    'note_content:ntext',
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>
    </div>
</div>