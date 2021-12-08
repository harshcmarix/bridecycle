<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\models\PromoCode;

/* @var $this yii\web\View */
/* @var $model app\models\PromoCode */

$this->title = 'View Promo Code';
$this->params['breadcrumbs'][] = ['label' => 'Promo Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="promo-code-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
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
                        'label' => 'User',
                        'attribute' => 'user_id',
                        'value' => function ($model) {
                            $user_name = '';
                            if ($model instanceof PromoCode) {
                                $user_name = $model->user->first_name . ' ' . $model->user->last_name;
                            }
                            return $user_name;
                        },
                    ],
                    [
                        'attribute' => 'type',
                        'value' => function ($model) {
                            $type = '';
                            if ($model instanceof PromoCode) {
                                $type = $model->type;
                            }
                            return $type;
                        },
                    ],
                    [
                        'attribute' => 'value',
                        'value' => function ($model) {
                            $value = '';
                            if ($model instanceof PromoCode) {
                                $value = $model->value;
                            }
                            return $value;
                        },
                    ],
                    [
                        'attribute' => 'start_date',
                        'value' => function ($model) {
                            $startDate = '';
                            if ($model instanceof PromoCode) {
                                $startDate = $model->start_date;
                            }
                            return $startDate;
                        },
                    ],
                    [
                        'attribute' => 'end_date',
                        'value' => function ($model) {
                            $endDate = '';
                            if ($model instanceof PromoCode) {
                                $endDate = $model->end_date;
                            }
                            return $endDate;
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = '';
                            if ($model instanceof PromoCode) {
                                if ($model->status == PromoCode::STATUS_ACTIVE) {
                                    $status = PromoCode::ARR_PROMOCODE_STATUS[PromoCode::STATUS_ACTIVE];
                                } else {
                                    $status = PromoCode::ARR_PROMOCODE_STATUS[PromoCode::STATUS_INACTIVE];
                                }
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