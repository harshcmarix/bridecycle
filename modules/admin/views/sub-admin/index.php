<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\SubAdminSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sub Admin';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sub-admin-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Sub Admin', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'profile_picture',
            'first_name',
            'last_name',
            'email:email',
            //'password_hash',
            //'temporary_password',
            //'access_token',
            //'access_token_expired_at',
            //'password_reset_token',
            //'mobile',
            //'weight',
            //'height',
            //'personal_information:ntext',
            //'user_type',
            //'is_shop_owner',
            //'shop_name',
            //'shop_email:email',
            //'shop_phone_number',
            //'created_at',
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>