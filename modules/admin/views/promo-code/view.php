<?php

use yii\helpers\{
    Html,
    Url
};
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
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>
        </div>

    </div>
</div>