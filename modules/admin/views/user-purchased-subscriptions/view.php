<?php

use yii\helpers\{Html, Url};
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\UserPurchasedSubscriptions */

$this->title = 'View User Subscription';
$this->params['breadcrumbs'][] = ['label' => 'User Subscriptions', 'url' => ['index']];
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
                    //'id',
                    //'user_id',
                    'subscription_type',
                    [
                        'attribute' => 'user_id',
                        'label' => 'User',
                        'value' => function ($model) {
                            return (!empty($model) && !empty($model->user) && $model->user instanceof \app\modules\api\v2\models\User) ? $model->user->first_name . " " . $model->user->last_name . " (" . $model->user->email . ")" : '';
                        },
                    ],
                    'transaction_id:ntext',
                    'subscription_id:ntext',
                    [
                        'attribute' => 'amount',
                        'value' => function ($model) {
                            return Yii::$app->formatter->asCurrency($model->amount);
                        },
                    ],
                    [
                        'attribute' => 'date_time',
                        'value' => function ($model) {
                            //return date('Y M, d H:i:s', strtotime($model->date_time));
                            return $model->date_time;
                        },
                    ],
                    //'status',
                    //'created_at',
                    //'updated_at',
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>
    </div>
</div>

