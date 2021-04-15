<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;
use app\models\PromoCode;

/* @var $this yii\web\View */
/* @var $model app\models\PromoCode */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Promo Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="promo-code-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof PromoCode) {
                        $id = $model->id;
                    }
                    return $id;
                },
            ],
             [
                'attribute' => 'code',
                'value' => function ($model) {
                    $code = '';
                    if ($model instanceof PromoCode) {
                        $code = $model->code;
                    }
                    return $code;
                },
            ],
             [
             'label'=>'User',
                'attribute' => 'user_id',
                'value' => function ($model) {
                    $user_name = '';
                    if ($model instanceof PromoCode) {
                        $user_name = $model->user->first_name.' '.$model->user->last_name;
                    }
                    return $user_name;
                },
            ],
             [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    $created_at = '';
                    if ($model instanceof PromoCode) {
                        $created_at = $model->created_at;
                    }
                    return $created_at;
                },
            ],
            [
                'attribute' => 'updated_at',
                'value' => function ($model) {
                    $updated_at = '';
                    if ($model instanceof PromoCode) {
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
