<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubAdmin */

$this->title = 'View Sub Admin';
$this->params['breadcrumbs'][] = ['label' => 'Sub Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="sub-admin-view">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            // 'profile_picture',
            'first_name',
            'last_name',
            'email:email',
            'mobile',
            'user_type',
            'created_at',
            'updated_at',
        ],
    ]) ?>

    <p>
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>
</div>