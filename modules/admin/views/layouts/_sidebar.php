<?php

use yii\helpers\Url;
use app\modules\admin\models\Module;
use app\modules\admin\models\DailyReportType;

?>


<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <?php
                $profilePic = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty(Yii::$app->user->identity->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture)) {
                    $profilePic = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . Yii::$app->user->identity->profile_picture;
                }
                ?>
                <img src="<?= $profilePic ?>"
                     alt="User Image"
                     class="img-circle"/>
            </div>
            <?php if (!Yii::$app->user->isGuest) { ?>
                <div class="pull-left info">
                    <p><?= Yii::$app->user->identity->first_name ?> <?= Yii::$app->user->identity->last_name ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i>Online</a>
                </div>
            <?php } ?>
        </div>
        <?php
        $visible = false;
        if (!Yii::$app->user->isGuest) {
            $visible = true;
        }
        //$modelsModule = Module::find()->where(['is_show' => 1])->orderBy(['in_order' => SORT_ASC])->all();
        $menuList[] = ['label' => 'Dashboard', 'icon' => 'home', 'url' => ['site/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Ads', 'icon' => 'picture-o', 'url' => ['ads/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Sub-Admin', 'icon' => 'users', 'url' => ['sub-admin/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'User', 'icon' => 'users', 'url' => ['user/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Category', 'icon' => 'list', 'url' => ['product-category/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Brand', 'icon' => 'list', 'url' => ['brand/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Product', 'icon' => 'product-hunt', 'url' => ['product/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Product Rating', 'icon' => 'star', 'url' => ['product-rating/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Promo Code', 'icon' => 'money', 'url' => ['promo-code/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Order', 'icon' => 'reorder', 'url' => ['order/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Subscription', 'icon' => 'bell', 'url' => ['subscription/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Content', 'icon' => 'align-center', 'url' => ['cms-page/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Report', 'icon' => 'book', 'items' => [
            ['label' => 'Sales', 'icon' => 'fas fa-angle-right', 'url' => ['report/sales', 'p' => 'w'], 'visible' => $visible],
            ['label' => "Customers", 'icon' => 'fas fa-angle-right', 'url' => ['report/customers', 'p' => 'w'], 'visible' => $visible],
        ],];
        $menuList[] = ['label' => 'Dress Type', 'icon' => 'snowflake-o', 'url' => ['dress-type/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Setting', 'icon' => 'cogs', 'url' => ['setting/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Banner', 'icon' => 'image', 'url' => ['banner/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Tailor', 'icon' => 'cut', 'url' => ['tailor/index'], 'visible' => $visible];
        $menuList[] = ['label' => 'Color', 'icon' => 'paint-brush', 'url' => ['color/index'], 'visible' => $visible];
        
        ?>
        <!-- Sidebar menu start -->
        <?php
        echo dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => $menuList
            ]
        );
        ?>
        <!-- Sidebar menu end -->
    </section>
</aside>


<style>
    /*.treeview > a {*/
    /*    white-space: nowrap;*/
    /*    overflow: hidden;*/
    /*    text-overflow: ellipsis;*/
    /*    max-width: 228px;*/
    /*}*/

    .skin-blue .sidebar-menu > li > a {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .treeview > a {
        white-space: break-spaces
    }

    .treeview a span {
        display: inline-block;
        width: 140px;
    }
</style>