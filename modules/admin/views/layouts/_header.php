<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">BC</span><span class="logo-lg"><img src="' . Yii::$app->request->baseUrl . '/uploads/logo.png" width="100px" ></span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <?php //if (Yii::$app->user->identity->isAdmin() || Yii::$app->user->identity->isSuperAdmin()) { ?>
                <!--                    <li class="dropdown live-users-menu">-->
                <!--                        <a href="-->
                <?php //echo Url::to(['admin-user/online-users']); ?><!--" class="dropdown-toggle"-->
                <!--                           title="Online Users">-->
                <!--                            <i class="fa fa-users" id="live-users-count" style="color:greenyellow"></i>-->
                <!--                        </a>-->
                <!--                    </li>-->
                <?php //} ?>
                <!-- Messages: style can be found in dropdown.less-->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?php
                        $profilePic = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        if (!empty(Yii::$app->user->identity->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture)) {
                            $profilePic = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . Yii::$app->user->identity->profile_picture;
                        }
                        ?>
                        <img src="<?= $profilePic ?>"
                             class="user-image" alt="User Image"/>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <?php
                            $profilePicInner = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            if (!empty(Yii::$app->user->identity->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture)) {
                                $profilePicInner = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . Yii::$app->user->identity->profile_picture;
                            }
                            ?>
                            <img src="<?= $profilePicInner ?>"
                                 class="img-circle" alt="User Image"/>

                            <?php if (!Yii::$app->user->isGuest) { ?>
                                <p>
                                    <b>

                                        <?= Yii::$app->user->identity->first_name ?>
                                        <?= Yii::$app->user->identity->last_name ?>

                                    </b>
                                </p>
                            <?php } ?>

                            <p>
                                <?php
                                $role = "";
                                if (!empty(Yii::$app->user->identity->user_type)) {
                                    if (Yii::$app->user->identity->user_type == \app\modules\admin\models\User::USER_TYPE_ADMIN) {
                                        $role = 'Admin';
                                    } elseif (Yii::$app->user->identity->user_type == \app\modules\admin\models\User::USER_TYPE_SUB_ADMIN) {
                                        $role = 'Sub Admin';
                                    } elseif (Yii::$app->user->identity->user_type == \app\modules\admin\models\User::USER_TYPE_NORMAL_USER) {
                                        $role = 'Normal User';
                                    }
                                }
                                ?>
                                BrideCycle - <?= $role ?>
                            </p>
                        </li>

                        <li class="user-footer">
                            <div class="pull-left">
                                <?php /* Html::a(
                                    'Profile',
                                    ['user/update' ,'id' => base64_encode(Yii::$app->user->identity->id) ],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                )*/ ?>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'Sign out',
                                    ['site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>