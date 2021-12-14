<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Trial */

$this->title = 'View Trial';
$this->params['breadcrumbs'][] = ['label' => 'Trials', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="trial-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'product_id',
            'sender_id',
            'receiver_id',
            'status',
            'date',
            'time',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
