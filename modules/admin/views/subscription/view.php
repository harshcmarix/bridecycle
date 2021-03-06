<?php

use yii\helpers\{
    Html,
    Url
};
use app\models\Subscription;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Subscription */

$this->title = 'View Subscription';
$this->params['breadcrumbs'][] = ['label' => 'Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="subscription-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'name',
                        'value' => function ($model) {
                            $name = '';
                            if ($model instanceof Subscription) {
                                $name = $model->name;
                            }
                            return $name;
                        },
                    ],
                    [
                        'attribute' => 'amount',
                        'value' => function ($model) {
                            $amount = '';
                            if ($model instanceof Subscription) {
                                $amount = Yii::$app->formatter->asCurrency($model->amount);
                            }
                            return $amount;
                        },
                    ],
                    [
                        'label' => 'Total Subscribed users',
                        'value' => function ($model) {
                            $created_at = '';
                            if ($model instanceof Subscription) {
                                $created_at = $model->subscribedUsersCount;
                            }
                            return $created_at;
                        },
                        'filter' => false,
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = '';
                            if ($model instanceof Subscription) {
                                $status = Subscription::SUBSCRIPTION_STATUS_ARRAY[$model->status];
                            }
                            return $status;
                        },
                    ],
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>

    </div>
</div>