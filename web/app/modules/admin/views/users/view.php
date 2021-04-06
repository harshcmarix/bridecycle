<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Users */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="users-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'profile_picture',
            'first_name',
            'last_name',
            'email:email',
            'password_hash',
            'temporary_password',
            'access_token',
            'access_token_expired_at',
            'password_reset_token',
            'mobile',
            'weight',
            'height',
            'personal_information:ntext',
            'user_type',
            'is_shop_owner',
            'shop_name',
            'shop_email:email',
            'shop_phone_number',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
