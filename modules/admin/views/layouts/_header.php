<?php

use yii\helpers\Html;

?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini"><img src="' . Yii::$app->request->baseUrl . '/uploads/logo.png" ></span><span class="logo-lg1"><img src="' . Yii::$app->request->baseUrl . '/uploads/logo-home.svg" width="200px" ></span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <!-- Messages: style can be found in dropdown.less-->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Cilck to View Profile">
                        <?php
                        if (!empty(Yii::$app->user->identity->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture)) {
                            $profilePic = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . Yii::$app->user->identity->profile_picture;
                        } else {
                            $profilePic = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                        ?>
                        <img src="<?= $profilePic ?>" class="user-image" alt="User Image"/>
                    </a>
                    <ul class="dropdown-menu">

                        <li class="user-header" onclick="event.stopPropagation()">

                            <?php
                            if (!empty(Yii::$app->user->identity->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture)) {
                                $profilePicInner = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . Yii::$app->user->identity->profile_picture;
                            } else {
                                $profilePicInner = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            ?>

                            <img src="<?= $profilePicInner ?>" class="img-circle" alt="User Image"/>

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
                            </p>

                        </li>

                        <li class="user-footer" onclick="event.stopPropagation()">
                            <div class="pull-left">
                                <?= Html::a(
                                    'Profile',
                                    ['user/update', 'id' => Yii::$app->user->identity->id, 'f' => 'ap'],
                                    ['data-method' => 'post', 'class' => 'btn btn-success ']
                                ) ?>
                            </div>
                            <div class="pull-right" onclick="event.stopPropagation()">
                                <?= Html::a(
                                    'Sign out',
                                    ['site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-success ']
                                ) ?>
                            </div>
                        </li>

                    </ul>
                </li>
            </ul>
            
        </div>
    </nav>
</header>