<?php

use app\modules\admin\models\DailyReportType;
use app\modules\admin\models\Module;

?>


<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <?php
                if (!empty(Yii::$app->user->identity->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture)) {
                    $profilePic = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . Yii::$app->user->identity->profile_picture;
                } else {
                    $profilePic = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                }
                ?>

                <img src="<?= $profilePic ?>" alt="User Image" class="sidebar-profile" />
            </div>
            <?php if (!Yii::$app->user->isGuest) { ?>
                <div class="pull-left info">
                    <p class=""><?= Yii::$app->user->identity->first_name ?> <?= Yii::$app->user->identity->last_name ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i>Online</a>
                </div>
            <?php } ?>
        </div>
        <?php
        $visible = false;
        if (!Yii::$app->user->isGuest) {
            $visible = true;
        }
        $route = $this->context->route;
        //$modelsModule = Module::find()->where(['is_show' => 1])->orderBy(['in_order' => SORT_ASC])->all();
        $menuList[] = ['label' => 'Dashboard', 'icon' => 'home', 'url' => ['site/index'], 'visible' => $visible];

        //$menuList[] = ['label' => 'Customer', 'icon' => 'users', 'url' => ['user/index'], 'visible' => $visible, 'active' => ($route === 'admin/user/index' || $route === 'admin/user/create' || $route === 'admin/user/update' || $route === 'admin/user/view')];
        $menuList[] = ['label' => 'Customer', 'icon' => 'users', 'items' => [
            ['label' => 'All Customers', 'icon' => 'fas fa-list', 'url' => ['user/index'], 'visible' => $visible, 'active' => ($route === 'admin/user/index' || $route === 'admin/user/create' || $route === 'admin/user/update' || $route === 'admin/user/view')],
            ['label' => "Customer", 'icon' => 'fas fa-user', 'url' => ['user/index-new-customer'], 'visible' => $visible, 'active' => ($route === 'admin/user/index-new-customer' || $route === 'admin/user/new-customer-create' || $route === 'admin/user/new-customer-update' || $route === 'admin/user/new-customer-view')],
            ['label' => "Shop Owner", 'icon' => 'fas fa-user', 'url' => ['user/index-new-shop-owner-customer'], 'visible' => $visible, 'active' => ($route === 'admin/user/index-new-shop-owner-customer' || $route === 'admin/user/new-shop-owner-customer-create' || $route === 'admin/user/new-shop-owner-customer-update' || $route === 'admin/user/new-shop-owner-customer-view')],
        ],];

        $menuList[] = ['label' => 'Order', 'icon' => 'cart-arrow-down', 'url' => ['order/index'], 'visible' => $visible, 'active' => ($route === 'admin/order/index' || $route === 'admin/order/update' || $route === 'admin/order/view')];
        $menuList[] = ['label' => 'Sub-Admin', 'icon' => 'users', 'url' => ['sub-admin/index'], 'visible' => false, 'active' => ($route === 'admin/sub-admin/index' || $route === 'admin/sub-admin/create' || $route === 'admin/sub-admin/update' || $route === 'admin/sub-admin/view')];
        $menuList[] = ['label' => 'Ads', 'icon' => 'film', 'url' => ['ads/index'], 'visible' => $visible, 'active' => ($route === 'admin/ads/index' || $route === 'admin/ads/create' || $route === 'admin/ads/update' || $route === 'admin/ads/view')];
        // $menuList[] = ['label' => 'Brand', 'icon' => 'tag', 'url' => ['brand/index'], 'visible' => $visible, 'active' => ($route === 'admin/brand/index' || $route === 'admin/brand/create' || $route === 'admin/brand/update' || $route === 'admin/brand/view')];
        $menuList[] = ['label' => 'Brands', 'icon' => 'tag', 'items' => [
            ['label' => 'All Brands', 'icon' => 'fas fa-list', 'url' => ['brand/index'], 'visible' => $visible, 'active' => ($route === 'admin/brand/index' || $route === 'admin/brand/create' || $route === 'admin/brand/update' || $route === 'admin/brand/view')],
            ['label' => "New Brands", 'icon' => 'plus-square', 'url' => ['brand/new-brand'], 'visible' => $visible, 'active' => ($route === 'admin/brand/new-brand' || $route === 'admin/brand/new-brand-create' || $route === 'admin/brand/new-brand-update' || $route === 'admin/brand/new-brand-view')],
        ],];
        //$menuList[] = ['label' => 'Product', 'icon' => 'product-hunt', 'url' => ['product/index'], 'visible' => $visible, 'active' => ($route === 'admin/product/index' || $route === 'admin/product/create' || $route === 'admin/product/update' || $route === 'admin/product/view')];
        $menuList[] = ['label' => 'Products', 'icon' => 'product-hunt', 'items' => [
            ['label' => 'All Products', 'icon' => 'fas fa-list', 'url' => ['product/index'], 'visible' => $visible, 'active' => ($route === 'admin/product/index' || $route === 'admin/product/create' || $route === 'admin/product/update' || $route === 'admin/product/view')],
            ['label' => "New Products", 'icon' => 'plus-square', 'url' => ['product/new-product'], 'visible' => $visible, 'active' => ($route === 'admin/product/new-product' || $route === 'admin/product/new-product-create' || $route === 'admin/product/new-product-update' || $route === 'admin/product/new-product-view')],
        ],];
        $menuList[] = ['label' => 'All Categories', 'icon' => 'list', 'url' => ['product-category/index'], 'visible' => $visible, 'active' => ($route === 'admin/product-category/index' || $route === 'admin/product-category/create' || $route === 'admin/product-category/update' || $route === 'admin/product-category/view')];
        $menuList[] = ['label' => 'Product Rating', 'icon' => 'star', 'url' => ['product-rating/index'], 'visible' => $visible, 'active' => ($route === 'admin/product-rating/index' || $route === 'admin/product-rating/update' || $route === 'admin/product-rating/view')];
        $menuList[] = ['label' => 'Promo Code', 'icon' => 'money', 'url' => ['promo-code/index'], 'visible' => $visible, 'active' => ($route === 'admin/promo-code/index' || $route === 'admin/promo-code/create' || $route === 'admin/promo-code/update' || $route === 'admin/promo-code/view')];
        $menuList[] = ['label' => 'Seller Payments', 'icon' => 'eur', 'url' => ['bridecycle-to-seller-payments/index'], 'visible' => $visible, 'active' => ($route === 'admin/bridecycle-to-seller-payments/index' || $route === 'admin/bridecycle-to-seller-payments/create' || $route === 'admin/bridecycle-to-seller-payments/update' || $route === 'admin/bridecycle-to-seller-payments/view')];
        $menuList[] = ['label' => 'Subscription', 'icon' => 'bell', 'url' => ['user-purchased-subscriptions/index'], 'visible' => $visible, 'active' => ($route === 'admin/user-purchased-subscriptions/index' || $route === 'admin/user-purchased-subscriptions/create' || $route === 'admin/user-purchased-subscriptions/update' || $route === 'admin/user-purchased-subscriptions/view')];
        $menuList[] = ['label' => 'Content', 'icon' => 'file-text-o', 'url' => ['cms-page/index'], 'visible' => $visible, 'active' => ($route === 'admin/cms-page/index' || $route === 'admin/cms-page/create' || $route === 'admin/cms-page/update' || $route === 'admin/cms-page/view')];
        $menuList[] = ['label' => 'Report', 'icon' => 'flag', 'items' => [
            ['label' => 'Sales', 'icon' => 'fas fa-angle-right', 'url' => ['report/sales', 'p' => 'w'], 'visible' => $visible, 'active' => ($route === 'admin/report/sales')],
            ['label' => "Customers", 'icon' => 'fas fa-angle-right', 'url' => ['report/customers', 'p' => 'w'], 'visible' => $visible, 'active' => ($route === 'admin/report/customers')],
        ],];
        $menuList[] = ['label' => 'Abuse Reports', 'icon' => 'ban', 'url' => ['abuse-report/index'], 'visible' => false, 'active' => ($route === 'admin/abuse-report/index' || $route === 'admin/abuse-report/view')];
        $menuList[] = ['label' => 'Dress Type', 'icon' => 'female', 'url' => ['dress-type/index'], 'visible' => false, 'active' => ($route === 'admin/dress-type/index' || $route === 'admin/dress-type/view')];
        $menuList[] = ['label' => 'Setting', 'icon' => 'cogs', 'url' => ['setting/index'], 'visible' => $visible, 'active' => ($route === 'admin/setting/index')];
        $menuList[] = ['label' => 'Banner', 'icon' => 'image', 'url' => ['banner/index'], 'visible' => $visible, 'active' => ($route === 'admin/banner/index' || $route === 'admin/banner/create' || $route === 'admin/banner/update' || $route === 'admin/banner/view')];
        $menuList[] = ['label' => 'Tailor', 'icon' => 'cut', 'url' => ['tailor/index'], 'visible' => $visible, 'active' => ($route === 'admin/tailor/index' || $route === 'admin/tailor/create' || $route === 'admin/tailor/update' || $route === 'admin/tailor/view')];
        $menuList[] = ['label' => 'Color', 'icon' => 'paint-brush', 'url' => ['color/index'], 'visible' => $visible, 'active' => ($route === 'admin/color/index' || $route === 'admin/color/create' || $route === 'admin/color/update' || $route === 'admin/color/view')];
        $menuList[] = ['label' => 'Trial', 'icon' => 'ticket', 'url' => ['trial/index'], 'visible' => $visible, 'active' => ($route === 'admin/trial/index')];

        ?>
        <!-- Sidebar menu start -->
        <div class="left-scroll">
            <?php
            echo dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                    'items' => $menuList
                ]
            );
            ?>
        </div>
        <!-- Sidebar menu end -->
    </section>
</aside>


<style>
    .skin-blue .sidebar-menu>li>a {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .treeview>a {
        white-space: break-spaces
    }

    .treeview a span {
        display: inline-block;
        width: 140px;
    }
</style>