<?php

use yii\helpers\Url;
use app\modules\admin\models\Module;
use app\modules\admin\models\DailyReportType;

?>


<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= Yii::getAlias('@profilePictureThumbRelativePath') . '/' . Yii::$app->user->identity->profile_picture ?>"
                     alt="User Image"
                     class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->first_name ?> <?= Yii::$app->user->identity->last_name ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i>Online</a>
            </div>
        </div>
        <?php
        //$modelsModule = Module::find()->where(['is_show' => 1])->orderBy(['in_order' => SORT_ASC])->all();
        $menuList[] = ['label' => 'Dashboard', 'icon' => 'home', 'url' => ['site/index'], 'visible' => true];
        if (!empty($modelsModule)) {
            foreach ($modelsModule as $modelsModuleRow) {
                if ($modelsModuleRow instanceof Module) {
                    if (!in_array($modelsModuleRow->id, [15, 19, 20, 21, 27, 28, 29, 30]) && (Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "View") || Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "Create"))) {

                        $isVisible = true;
                        $moduleName = "";
                        if (!empty($modelsModuleRow->name) && (!in_array($modelsModuleRow->name, ["E-contract Document Permission", "E-contract Folder Permission"]))) {
                            $moduleName = $modelsModuleRow->name;
                        } else {
                            $moduleName = "Permission";
                        }
                        $menuList[] = [
                            'label' => $modelsModuleRow->display_name, 'title' => $modelsModuleRow->display_name, 'icon' => $modelsModuleRow->icon, 'items' => [
                                ['label' => 'List', 'icon' => 'fas fa-angle-right', 'url' => Url::to([$modelsModuleRow->list_url]), 'visible' => Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "View")],
                                ['label' => "Add " . $moduleName, 'icon' => 'fas fa-angle-right', 'url' => Url::to([$modelsModuleRow->add_url]), 'visible' => Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "Create") && $isVisible],
                            ],
                        ];
                    } else if (in_array($modelsModuleRow->id, [15, 19, 20, 21, 27, 28, 29, 30]) && (Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "View") || Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "Create"))) {
                        //$isVisible = (in_array($modelsModuleRow->id, [27, 28, 29])) ? false : true;
                        //$isVisible = (in_array($modelsModuleRow->id, [28, 29])) ? false : true;
                        $isVisible = true;
                        $moduleName = "";
                        if (!empty($modelsModuleRow->name) && (!in_array($modelsModuleRow->name, ["E-contract Document Permission", "E-contract Folder Permission"]))) {
                            $moduleName = $modelsModuleRow->name;
                        } else {
                            $moduleName = "Permission";
                        }
                        $menuList[] = [
                            'label' => $modelsModuleRow->display_name, 'title' => $modelsModuleRow->display_name, 'icon' => $modelsModuleRow->icon, 'items' => [
                                ['label' => 'List', 'icon' => 'fas fa-angle-right', 'url' => Url::to([$modelsModuleRow->list_url]), 'visible' => (Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "View"))],
                                ['label' => "Add " . $moduleName, 'icon' => 'fas fa-angle-right', 'url' => Url::to([$modelsModuleRow->add_url]), 'visible' => (Yii::$app->userAccess->checkUserWisePermission(Yii::$app->user->identity->role_id, Yii::$app->user->identity->id, $modelsModuleRow->id, "Create") && $isVisible)],
                            ],
                        ];
                    }
                }
            }
        }

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