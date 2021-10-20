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
                    //'id',
                    'order_id',
                    [
                        'attribute' => 'order_item_id',
                        'label' => 'Order Item',
                        'value' => function ($model) {
                            if (!empty($model->orderItem) && $model->orderItem instanceof \app\models\OrderItem && !empty($model->orderItem->product) && $model->orderItem->product instanceof \app\models\Product) {
                                return (!empty($model->orderItem->product->name)) ? $model->orderItem->product->name : "";
                            }
                        },
                    ],
                    [
                        'attribute' => 'seller_id',
                        'value' => function ($model) {
                            if (!empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User) {
                                return (!empty($model->seller->first_name)) ? $model->seller->first_name . " " . $model->seller->last_name : "seller";
                            }
                        },
                    ],
                    [
                        'attribute' => 'amount',
                        'value' => function ($model) {
                            if (!empty($model->amount)) {
                                return Yii::$app->formatter->asCurrency($model->amount);
                            }
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
                    //'created_at',
                    //'updated_at',
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>
    </div>
</div>