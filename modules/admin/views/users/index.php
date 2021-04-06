<?php

use kartik\select2\Select2;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Users', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'summary' => '<div class="summary">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> Users.</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'first_name',
            'last_name',
            'email:email',
            //'profile_picture',
            [
                'format' => ['raw'],
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'profile_picture',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $model->profile_picture)) {
                        $image_path = Yii::getAlias('@profilePictureAbsolutePath') . '/' . $model->profile_picture;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'contentmodal_' . $model->id,
                        'header' => '<h3>Profile Picture</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $contentmodel = "contentmodel('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodel, 'height' => '100px', 'width' => '100px']);
                }
            ],

            //'password_hash',
            //'temporary_password',
            //'access_token',
            //'access_token_expired_at',
            //'password_reset_token',
            //'mobile',
            [
                'attribute' => 'mobile',
                'value' => function ($model) {
                    return (!empty($model) && !empty($model->mobile)) ? $model->mobile : "-";
                },
            ],
            //'weight',
            //'height',
            //'personal_information:ntext',
            //'user_type',
            //'is_shop_owner',
            [
                'attribute' => 'is_shop_owner',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'is_shop_owner',
                    'value' => $searchModel->is_shop_owner,
                    'data' => $searchModel->isShopOwner,
                    'size' => Select2::MEDIUM,
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]),
                'content' => function ($data) {
                    return isset($data->isShopOwner[$data['is_shop_owner']]) ? $data->isShopOwner[$data['is_shop_owner']] : '-';
                }
            ],
            //'shop_name',
            //'shop_email:email',
            //'shop_phone_number',
            //'created_at',
            //'updated_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Actions'
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<script type="text/javascript">
    function contentmodel(id) {
        $('#contentmodal_' + id).modal('show');
    }
</script>