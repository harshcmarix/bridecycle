<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */

$this->title = $model->first_name . " " . $model->last_name;
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
            'first_name',
            'last_name',
            'email:email',
            //'password_hash',
            //'profile_picture',
            [
                'format' => 'raw',
                'attribute' => 'profile_picture',
                'value' => function ($data) {
                    $image_path = "";
                    if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profileRelativePath') . '/' . $data->profile_picture)) {
                        $image_path = Yii::getAlias('@profileAbsolutePath') . '/' . $data->profile_picture;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'contentmodal_' . $data->id,
                        'header' => '<h3>Profile Picture</h3>',
                    ]);
                    echo Html::img($image_path, ['width' => '570']);
                    Modal::end();
                    $contentmodel = "contentmodel('" . $data->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodel, 'height' => '100px', 'width' => '100px']);
                },
            ],
            //'temporary_password',
            //'access_token',
            //'access_token_expired_at',
            //'password_reset_token',
            'mobile',
            //'weight',
            //'height',
            'personal_information:ntext',
            [
                'attribute' => "user_type",
                'value' => function ($model) {
                    $userType = "-";
                    if ($model->user_type == 1) {
                        $userType = 'Admin';
                    } elseif ($model->user_type == 2) {
                        $userType = 'Sub-admin';
                    } elseif ($model->user_type == 3) {
                        $userType = 'Normal-user';
                    }
                    return $userType;
                }
            ],
            [
                'attribute' => "is_shop_owner",
                'value' => function ($model) {
                    return ($model->is_shop_owner == 1) ? "Yes" : "No";
                }
            ],
            'shop_name',
            'shop_email:email',
            'shop_phone_number',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
<script type="text/javascript">
    function contentmodel(id) {
        $('#contentmodal_' + id).modal('show');
    }
</script>
