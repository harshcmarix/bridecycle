<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\AbuseReport */

$this->title = 'View Abuse Report';
$this->params['breadcrumbs'][] = ['label' => 'Abuse Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <div class="products-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'user_id',
                        'label' => 'User',
                        'value' => function ($model) {
                            return (!empty($model) && !empty($model->user) && $model->user instanceof \app\modules\api\v2\models\User && !empty($model->user->first_name)) ? $model->user->first_name . " " . $model->user->last_name . " (" . $model->user->email . ")" : "user";
                        },
                    ],
                    [
                        'attribute' => 'seller_id',
                        'label' => 'Seller',
                        'value' => function ($model) {
                            $status = "In-active";
                            if (!empty($model) && !empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User && $model->seller->user_status == \app\modules\api\v2\models\User::USER_STATUS_ACTIVE) {
                                $status = "Active";
                            }
                            return (!empty($model) && !empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User && !empty($model->seller->first_name)) ? $model->seller->first_name . " " . $model->seller->last_name . " (" . $model->seller->email . ")(Status:" . $status . ")" : "seller";
                        },
                    ],
                    'content:ntext',
                    'created_at',
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', Yii::$app->request->referrer, ['class' => 'btn btn-default']) ?>
            </p>
        </div>
    </div>
</div>
