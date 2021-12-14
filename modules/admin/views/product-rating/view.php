<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ProductRating */

$this->title = "View Product Rating";
$this->params['breadcrumbs'][] = ['label' => 'Product Ratings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
<!--    <div class="box-header"></div>-->
    <div class="box-body">

        <div class="product-rating-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'user_id',
                        'label' => 'User',
                        'value' => function ($model) {
                            return (!empty($model->user) && $model->user instanceof \app\modules\api\v2\models\User) ? $model->user->first_name . " " . $model->user->last_name : "";
                        },
                    ],
                    [
                        'attribute' => 'product_id',
                        'label' => 'Product',
                        'value' => function ($model) {
                            return (!empty($model->product) && $model->product instanceof \app\models\Product) ? $model->product->name : "";
                        },
                    ],
                    [
                        'attribute' => 'rating',
                        'value' => function ($model) {
                            return (!empty($model->rating)) ? $model->rating . " / 5" : "0";
                        },
                    ],
                    'review:ntext',
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == \app\models\ProductRating::STATUS_PENDING) {
                                $status = "Pending Approval";
                            } elseif ($model->status == \app\models\ProductRating::STATUS_APPROVE) {
                                $status = "Approved";
                            } elseif ($model->status == \app\models\ProductRating::STATUS_DECLINE) {
                                $status = "Decline";
                            }
                            return $status;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                ],
            ]) ?>

            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>
    </div>
</div>
