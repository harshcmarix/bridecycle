<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Color */

$this->title = 'View Color';
$this->params['breadcrumbs'][] = ['label' => 'Colors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'name',
                'code',
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        $status = "";
                        if ($model->status == \app\models\Color::STATUS_PENDING_APPROVAL) {
                            $status = "Pending Approval";
                        } elseif ($model->status == \app\models\Color::STATUS_APPROVE) {
                            $status = "Approved";
                        } elseif ($model->status == \app\models\Color::STATUS_DECLINE) {
                            $status = "Decline";
                        }
                        return $status;
                    },
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style']
                ],
            ],
        ]) ?>
    </div>
    <div class="box-footer">
        <p>
            <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
        </p>
    </div>
</div>
