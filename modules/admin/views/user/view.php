<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\ShopDetail;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */

$this->title = $model->first_name . " " . $model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="users-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <!--    <p>-->
    <!--        --><?php //echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <!--        --><?php //echo Html::a('Delete', ['delete', 'id' => $model->id], [
    //            'class' => 'btn btn-danger',
    //            'data' => [
    //                'confirm' => 'Are you sure you want to delete this item?',
    //                'method' => 'post',
    //            ],
    //        ]) ?>
    <!--    </p>-->

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
            //'personal_information:ntext',
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
            [
                'format' => 'raw',
                'attribute' => 'shop_logo',
                'value' => function ($data) {
                    $image_path = "";
                    if (!empty($data->shopDetail) && $data->shopDetail instanceof ShopDetail && !empty($data->shopDetail->shop_logo) && file_exists(Yii::getAlias('@shopLogoRelativePath') . '/' . $data->shopDetail->shop_logo)) {
                        $image_path = Yii::getAlias('@shopLogoAbsolutePath') . '/' . $data->shopDetail->shop_logo;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'contentmodalShopLogo_' . $data->id,
                        'header' => '<h3>Shop Logo</h3>',
                    ]);
                    echo Html::img($image_path, ['width' => '570']);
                    Modal::end();
                    $contentmodelShopLogo = "contentmodalShopLogo('" . $data->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodelShopLogo, 'height' => '100px', 'width' => '100px']);
                },
            ],
            [
                'attribute' => 'shop_name',
                'value' => function ($model) {
                    return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_name)) ? $model->shopDetail->shop_name : "";
                }
            ],
            [
                'format' => 'email',
                'attribute' => 'shop_email',
                'value' => function ($model) {
                    return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_email)) ? $model->shopDetail->shop_email : "";
                }
            ],
            [
                'attribute' => 'shop_phone_number',
                'value' => function ($model) {
                    return (!empty($model->shopDetail) && $model->shopDetail instanceof ShopDetail && !empty($model->shopDetail->shop_phone_number)) ? $model->shopDetail->shop_phone_number : "";
                }
            ],
            [
                'label' => 'Shop Address',
                'value' => (!empty($shopAddress) && $shopAddress instanceof \app\models\UserAddress && !empty($shopAddress->address)) ? $shopAddress->address : "",
            ],
            'created_at',
            'updated_at',
        ],
    ]) ?>
    <p>
        <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>
</div>
<script type="text/javascript">
    function contentmodel(id) {
        $('#contentmodal_' + id).modal('show');
    }

    function contentmodelShopLogo(id) {
        $('#contentmodalShopLogo_' + id).modal('show');
    }
</script>
