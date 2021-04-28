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
<div class="subscription-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
        [
        'attribute' => 'name',
            'value' => function ($model) {
                $name = '';
                if($model instanceof Subscription){
                   $name = $model->name;
                }
                return $name;
            },           
        ],
        [
        'attribute' => 'amount',
            'value' => function ($model) {
                $amount = '';
                 if($model instanceof Subscription){
                    $amount = $model->amount;
                 }
                 return $amount;
            },          
        ],
        [
        'attribute' => 'status',
            'value' => function ($model) {
                $status = '';
                 if($model instanceof Subscription){
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
