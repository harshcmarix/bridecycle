<?php

use yii\helpers\{
    Html,
    Url
};
use app\models\Subscription;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Subscription */

$this->title = $model->name;
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
        'attribute' => 'id',
        'value' => function ($model) {
            $id = '';
             if($model instanceof Subscription){
                 $id = $model->id;
             }
             return $id;
           },
        ],
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
        [
        'attribute' => 'created_at',
            'value' => function ($model) {
                $created_at = '';
                if($model instanceof Subscription){
                    $created_at = $model->created_at;
                }
                return $created_at;
            },
        ],
        [
             'attribute' => 'updated_at',
            'value' => function ($model) {
                $updated_at = '';
                if($model instanceof Subscription){
                    $updated_at = $model->updated_at;
                }
                return $updated_at;
            },
        ],
    ],
    ]) ?>
    <p>
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>

</div>
